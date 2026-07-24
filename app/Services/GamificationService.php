<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Badge;
use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\XpTransaction;
use App\Notifications\BadgeEarnedNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Awards XP and badges. Every award is idempotent: XP rows dedupe on the
 * business key (user_id, source, source_id) backed by a unique index, and
 * badge grants dedupe on unique(user_id, badge_id) — replays, double-fires,
 * and concurrent requests can never award twice.
 *
 * Dedupe key scheme (source → source_id):
 *   quiz_passed      → quiz id     (once per user per quiz)
 *   quiz_perfect     → quiz id     (once per user per quiz, 100% only)
 *   course_completed → course id   (once per user per course)
 *   badge            → badge id    (xp_reward granted with the badge)
 */
class GamificationService
{
    public const XP_QUIZ_PASSED = 50;

    public const XP_QUIZ_PERFECT = 100;

    public const XP_COURSE_COMPLETED = 200;

    /**
     * Award XP for a passed quiz attempt, plus the perfect-score bonus and
     * Perfectionist badge on a 100%.
     */
    public function awardQuizPassed(User $user, QuizAttempt $attempt): void
    {
        $quiz = $attempt->quiz;

        if (! $quiz) {
            return;
        }

        $this->awardXp($user, self::XP_QUIZ_PASSED, 'quiz_passed', $quiz->id, 'Passed quiz: '.$quiz->title);

        if ((int) $attempt->percentage >= 100) {
            $this->awardXp($user, self::XP_QUIZ_PERFECT, 'quiz_perfect', $quiz->id, 'Perfect score: '.$quiz->title);
            $this->awardBadge($user, 'first_perfect_quiz');
        }
    }

    /**
     * Award course-completion XP and the Graduate badge. Skipped for
     * zero-quiz courses: their completion is claim-based (certificate claim
     * button), not event-driven, so there is no reliable flip moment.
     */
    public function awardCourseCompleted(User $user, Course $course): void
    {
        if (! $course->quizzes()->where('is_active', true)->exists()) {
            return;
        }

        $this->awardXp($user, self::XP_COURSE_COMPLETED, 'course_completed', $course->id, 'Completed course: '.$course->title);
        $this->awardBadge($user, 'course_completed');
    }

    /**
     * Award the On Fire badge when the student's most recent run of
     * consecutive active days reaches 7. Activity days are distinct dates
     * across quiz attempts, assignment/assessment submissions, chat
     * messages, and attendance markings (same sources as Analytics at-risk).
     * Safe to call on every activity event and on read — re-evaluation is
     * cheap and idempotent.
     */
    public function evaluateStreak(User $user): void
    {
        if ($user->badges()->where('badges.key', 'streak_7')->exists()) {
            return;
        }

        $dates = $this->activityDates($user);

        if ($dates->count() < 7) {
            return;
        }

        $desc = $dates->sortDesc()->values();
        $streak = 1;

        for ($i = 1; $i < $desc->count(); $i++) {
            $gap = (int) abs(Carbon::parse($desc[$i - 1])->diffInDays(Carbon::parse($desc[$i])));

            if ($gap !== 1) {
                break;
            }

            $streak++;
        }

        if ($streak >= 7) {
            $this->awardBadge($user, 'streak_7');
        }
    }

    /**
     * Grant a badge once. A newly granted badge also banks its xp_reward
     * (source='badge', source_id=badge id) and notifies the student.
     */
    public function awardBadge(User $user, string $key): ?Badge
    {
        $badge = Badge::query()->where('key', $key)->first();

        if (! $badge) {
            return null;
        }

        $alreadyHas = DB::table('user_badge')
            ->where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->exists();

        if ($alreadyHas) {
            return $badge;
        }

        try {
            DB::table('user_badge')->insert([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'earned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $e) {
            // Concurrent grant hit unique(user_id, badge_id) — other row wins.
            report($e);

            return $badge;
        }

        if ($badge->xp_reward > 0) {
            $this->awardXp($user, $badge->xp_reward, 'badge', $badge->id, 'Badge earned: '.$badge->name);
        }

        try {
            $user->notify(new BadgeEarnedNotification($badge));
        } catch (Throwable $e) {
            report($e);
        }

        return $badge;
    }

    /**
     * Students ranked by total XP (best first, stable id tiebreak), with
     * badge counts and up to 5 badge icons each. Returns the full ranking;
     * callers slice the top N and locate the viewer.
     *
     * @return Collection<int, array{rank: int, user_id: int, name: string, xp: int, badge_count: int, badge_icons: array<int, string>}>
     */
    public function leaderboard(): Collection
    {
        $totals = XpTransaction::query()
            ->join('users', 'users.id', '=', 'xp_transactions.user_id')
            ->where('users.role', 'student')
            ->groupBy('xp_transactions.user_id')
            ->selectRaw('xp_transactions.user_id as user_id, SUM(xp_transactions.points) as xp')
            ->orderByDesc('xp')
            ->orderBy('xp_transactions.user_id')
            ->get();

        $userIds = $totals->pluck('user_id')->all();

        if ($userIds === []) {
            return collect();
        }

        $names = User::query()->whereIn('id', $userIds)->pluck('name', 'id');

        $badgeCounts = DB::table('user_badge')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as c')
            ->pluck('c', 'user_id');

        $badgeIcons = DB::table('user_badge')
            ->join('badges', 'badges.id', '=', 'user_badge.badge_id')
            ->whereIn('user_badge.user_id', $userIds)
            ->orderBy('user_badge.earned_at')
            ->get(['user_badge.user_id', 'badges.icon'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('icon')->filter()->take(5)->values()->all());

        return $totals->values()->map(fn ($row, int $index): array => [
            'rank' => $index + 1,
            'user_id' => (int) $row->user_id,
            'name' => $names[$row->user_id] ?? 'Student',
            'xp' => (int) $row->xp,
            'badge_count' => (int) ($badgeCounts[$row->user_id] ?? 0),
            'badge_icons' => $badgeIcons[$row->user_id] ?? [],
        ]);
    }

    /**
     * Idempotent XP insert — a concurrent double-fire hits the unique index
     * and loses quietly.
     */
    private function awardXp(User $user, int $points, string $source, ?int $sourceId, ?string $description): void
    {
        try {
            XpTransaction::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'source' => $source,
                    'source_id' => $sourceId,
                ],
                [
                    'points' => $points,
                    'description' => $description,
                ],
            );
        } catch (QueryException $e) {
            report($e);
        }
    }

    /**
     * Distinct activity dates (Y-m-d) for the streak evaluation.
     *
     * @return Collection<int, string>
     */
    private function activityDates(User $user): Collection
    {
        return collect()
            ->merge(QuizAttempt::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(AssignmentSubmission::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(AssessmentSubmission::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(ChatMessage::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(Attendance::query()->where('user_id', $user->id)->selectRaw('DATE(updated_at) as d')->pluck('d'))
            ->filter()
            ->unique()
            ->values();
    }
}
