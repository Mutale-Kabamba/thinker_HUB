<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Badge;
use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Friendship;
use App\Models\LearningMaterial;
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
 * Complete Gamification & Weighted XP Engine for Thinker HUB.
 * Every award is idempotent: XP rows dedupe on the business key
 * (user_id, source, source_id) backed by a unique index, and badge
 * grants dedupe on unique(user_id, badge_id) — replays, double-fires,
 * and concurrent requests can never award twice.
 */
class GamificationService
{
    public const XP_DAILY_LOGIN = 10;

    public const XP_STREAK_3 = 25;

    public const XP_STREAK_7 = 75;

    public const XP_STREAK_30 = 250;

    public const XP_SUBMISSION_ONTIME = 40;

    public const XP_SUBMISSION_EARLY = 20;

    public const XP_PASSING_GRADE = 50;

    public const XP_DISTINCTION_GRADE = 40;

    public const XP_PERFECT_GRADE = 60;

    public const XP_QUIZ_PASSED = 50;

    public const XP_QUIZ_PERFECT = 50;

    public const XP_ATTENDANCE_PRESENT = 25;

    public const XP_PERFECT_ATTENDANCE = 150;

    public const XP_COURSE_COMPLETED = 300;

    public const XP_COURSE_RATING = 20;

    public const XP_MATERIAL_VIEW = 5;

    public const XP_STUDY_BUDDY = 15;

    public const XP_OPPORTUNITY_SUBMIT = 50;

    /**
     * Record daily login for student and evaluate streak bonuses.
     */
    public function recordDailyLogin(User $user): void
    {
        if ($user->role !== 'student') {
            return;
        }

        $todayKey = (int) now()->format('Ymd');

        $this->awardXp(
            $user,
            self::XP_DAILY_LOGIN,
            'daily_login',
            $todayKey,
            'Daily active check-in: '.now()->format('M d, Y')
        );

        $this->evaluateStreak($user);
    }

    /**
     * Award XP for an on-time or early assignment/assessment submission.
     */
    public function awardSubmission(User $user, AssignmentSubmission|AssessmentSubmission $submission): void
    {
        if ($user->role !== 'student') {
            return;
        }

        $type = $submission instanceof AssignmentSubmission ? 'assignment' : 'assessment';
        $item = $submission instanceof AssignmentSubmission ? $submission->assignment : $submission->assessment;
        $dueDate = $item?->due_date;
        $title = $item?->name ?? ucfirst($type);

        if ($dueDate) {
            $submittedAt = $submission->submitted_at ?? $submission->created_at ?? now();
            $dueDateEnd = Carbon::parse($dueDate)->endOfDay();

            if ($submittedAt->lte($dueDateEnd)) {
                $this->awardXp(
                    $user,
                    self::XP_SUBMISSION_ONTIME,
                    "{$type}_ontime",
                    $submission->id,
                    "On-time submission: {$title}"
                );

                // Early submission bonus (at least 24 hours before deadline end)
                if ($submittedAt->lte($dueDateEnd->copy()->subHours(24))) {
                    $this->awardXp(
                        $user,
                        self::XP_SUBMISSION_EARLY,
                        "{$type}_early",
                        $submission->id,
                        "Early submission bonus: {$title}"
                    );
                }
            }
        }

        $this->evaluateStreak($user);

        // Check Punctual Scholar badge (5 on-time submissions)
        $onTimeCount = XpTransaction::query()
            ->where('user_id', $user->id)
            ->whereIn('source', ['assignment_ontime', 'assessment_ontime'])
            ->count();

        if ($onTimeCount >= 5) {
            $this->awardBadge($user, 'punctual_scholar');
        }

        // Check Early Bird badge (3 early submissions)
        $earlyCount = XpTransaction::query()
            ->where('user_id', $user->id)
            ->whereIn('source', ['assignment_early', 'assessment_early'])
            ->count();

        if ($earlyCount >= 3) {
            $this->awardBadge($user, 'early_bird');
        }
    }

    /**
     * Award layered XP when an assignment or assessment is graded.
     */
    public function awardGradedSubmission(User $user, AssignmentSubmission|AssessmentSubmission $submission): void
    {
        if ($user->role !== 'student') {
            return;
        }

        $type = $submission instanceof AssignmentSubmission ? 'assignment' : 'assessment';
        $item = $submission instanceof AssignmentSubmission ? $submission->assignment : $submission->assessment;
        $title = $item?->name ?? ucfirst($type);

        $rawScore = $submission instanceof AssignmentSubmission ? $submission->grade : $submission->score;

        if ($rawScore === null || $rawScore === '') {
            return;
        }

        // Extract numeric score from string/numeric grade (e.g. "85%", "85/100", 85)
        $numericScore = (float) filter_var((string) $rawScore, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Standard passing grade (>= 50%)
        if ($numericScore >= 50) {
            $this->awardXp(
                $user,
                self::XP_PASSING_GRADE,
                "{$type}_passed",
                $submission->id,
                "Passed {$type} ({$numericScore}%): {$title}"
            );
        }

        // Distinction bonus (>= 80%)
        if ($numericScore >= 80) {
            $this->awardXp(
                $user,
                self::XP_DISTINCTION_GRADE,
                "{$type}_distinction",
                $submission->id,
                "Distinction bonus ({$numericScore}%): {$title}"
            );

            // Check Distinction Club badge (3 distinction scores)
            $distinctionCount = XpTransaction::query()
                ->where('user_id', $user->id)
                ->whereIn('source', ['assignment_distinction', 'assessment_distinction'])
                ->count();

            if ($distinctionCount >= 3) {
                $this->awardBadge($user, 'distinction_club');
            }
        }

        // Perfect score bonus (100%)
        if ($numericScore >= 100) {
            $this->awardXp(
                $user,
                self::XP_PERFECT_GRADE,
                "{$type}_perfect",
                $submission->id,
                "Perfect score bonus (100%): {$title}"
            );
            $this->awardBadge($user, 'first_perfect_quiz');
        }
    }

    /**
     * Award XP for a passed quiz attempt, plus perfect-score bonus & badge.
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
     * Award live session attendance XP and evaluate course attendance badge.
     */
    public function awardAttendance(User $user, Attendance $attendance): void
    {
        if ($attendance->status !== Attendance::STATUS_PRESENT) {
            return;
        }

        $session = $attendance->session;
        $sessionTitle = $session?->title ?? 'Live Class Session';

        $this->awardXp(
            $user,
            self::XP_ATTENDANCE_PRESENT,
            'attendance_present',
            $attendance->id,
            "Attended session: {$sessionTitle}"
        );

        $this->evaluateStreak($user);

        // Check if all sessions for the course are completed with 100% attendance
        $courseId = $session?->course_id;

        if ($courseId) {
            $totalCourseSessions = CourseSession::query()->where('course_id', $courseId)->count();

            if ($totalCourseSessions >= 2) {
                $presentCount = Attendance::query()
                    ->where('user_id', $user->id)
                    ->where('status', Attendance::STATUS_PRESENT)
                    ->whereHas('session', fn ($q) => $q->where('course_id', $courseId))
                    ->count();

                if ($presentCount >= $totalCourseSessions) {
                    $this->awardXp(
                        $user,
                        self::XP_PERFECT_ATTENDANCE,
                        'perfect_attendance',
                        $courseId,
                        '100% Course Attendance: '.($session->course?->title ?? 'Course')
                    );
                    $this->awardBadge($user, 'always_present');
                }
            }
        }
    }

    /**
     * Award course-completion XP, Graduate badge, and evaluate Mastermind badge.
     * Requires the enrollment to have been signed off as completed by an instructor.
     */
    public function awardCourseCompleted(User $user, Course $course): void
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment || $enrollment->completed_at === null) {
            return;
        }

        $this->awardXp($user, self::XP_COURSE_COMPLETED, 'course_completed', $course->id, 'Completed course: '.$course->title);
        $this->awardBadge($user, 'course_completed');

        // Check Mastermind badge (3 completed courses signed off by instructor)
        $completedCoursesCount = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        if ($completedCoursesCount >= 3) {
            $this->awardBadge($user, 'mastermind');
        }
    }

    /**
     * Revoke course-completion XP, Graduate badge, and Mastermind badge if no longer eligible.
     */
    public function revokeCourseCompleted(User $user, Course $course): void
    {
        // 1. Remove course completed XP transaction for this course
        XpTransaction::query()
            ->where('user_id', $user->id)
            ->where('source', 'course_completed')
            ->where('source_id', $course->id)
            ->delete();

        // 2. Re-evaluate remaining instructor-completed courses
        $completedCoursesCount = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        // If fewer than 3 completed courses, revoke Mastermind badge
        if ($completedCoursesCount < 3) {
            $this->revokeBadge($user, 'mastermind');
        }

        // If 0 completed courses, revoke Graduate (course_completed) badge
        if ($completedCoursesCount < 1) {
            $this->revokeBadge($user, 'course_completed');
        }
    }

    /**
     * Revoke a badge and remove its awarded badge XP.
     */
    public function revokeBadge(User $user, string $key): void
    {
        $badge = Badge::query()->where('key', $key)->first();

        if (! $badge) {
            return;
        }

        DB::table('user_badge')
            ->where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->delete();

        XpTransaction::query()
            ->where('user_id', $user->id)
            ->where('source', 'badge')
            ->where('source_id', $badge->id)
            ->delete();
    }

    /**
     * Award course rating / review submission XP.
     */
    public function awardCourseRating(User $user, CourseRating $rating): void
    {
        $this->awardXp(
            $user,
            self::XP_COURSE_RATING,
            'course_rating',
            $rating->id,
            'Rated course: '.($rating->course?->title ?? 'Course')
        );

        $this->evaluateStreak($user);
    }

    /**
     * Award learning material review / download XP with a daily cap.
     */
    public function awardMaterialView(User $user, LearningMaterial $material): void
    {
        // Daily cap of 4 material views per day (20 XP max/day)
        $todayCount = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where('source', 'material_viewed')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayCount < 4) {
            $this->awardXp(
                $user,
                self::XP_MATERIAL_VIEW,
                'material_viewed',
                $material->id,
                'Reviewed material: '.$material->title
            );
        }

        $this->evaluateStreak($user);
    }

    /**
     * Award study buddy connection XP and evaluate Study Networker badge.
     */
    public function awardFriendship(User $user, Friendship $friendship): void
    {
        $buddyCount = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where('source', 'study_buddy')
            ->count();

        if ($buddyCount < 5) {
            $this->awardXp(
                $user,
                self::XP_STUDY_BUDDY,
                'study_buddy',
                $friendship->id,
                'Connected with study buddy'
            );
        }

        $friendsCount = Friendship::query()
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('friend_id', $user->id))
            ->where('status', 'accepted')
            ->count();

        if ($friendsCount >= 5) {
            $this->awardBadge($user, 'study_networker');
        }
    }

    /**
     * Award Opportunity Hub submission XP and Innovator badge.
     */
    public function awardOpportunitySubmission(User $user, int $opportunityId, string $title): void
    {
        $this->awardXp(
            $user,
            self::XP_OPPORTUNITY_SUBMIT,
            'opportunity_submitted',
            $opportunityId,
            "Submitted to Opportunities Hub: {$title}"
        );

        $this->awardBadge($user, 'innovator');
        $this->evaluateStreak($user);
    }

    /**
     * Evaluate chat activity threshold for Active Contributor badge.
     */
    public function evaluateChatActivity(User $user): void
    {
        if ($user->badges()->where('badges.key', 'active_contributor')->exists()) {
            return;
        }

        $messageCount = ChatMessage::query()->where('user_id', $user->id)->count();

        if ($messageCount >= 25) {
            $this->awardBadge($user, 'active_contributor');
        }

        $this->evaluateStreak($user);
    }

    /**
     * Evaluate consecutive active day streaks (3, 7, and 30 days).
     */
    public function evaluateStreak(User $user): void
    {
        $dates = $this->activityDates($user);

        if ($dates->isEmpty()) {
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

        // 3-Day streak bonus
        if ($streak >= 3) {
            $streak3Key = (int) (now()->format('YW').'3');
            $this->awardXp($user, self::XP_STREAK_3, 'streak_3', $streak3Key, '3-day activity streak bonus');
        }

        // 7-Day streak bonus & On Fire badge
        if ($streak >= 7) {
            $this->awardBadge($user, 'streak_7');
        }

        // 30-Day streak bonus & Unstoppable badge
        if ($streak >= 30) {
            $this->awardBadge($user, 'streak_30');
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
            ->merge(XpTransaction::query()->where('user_id', $user->id)->where('source', 'daily_login')->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(QuizAttempt::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(AssignmentSubmission::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(AssessmentSubmission::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(ChatMessage::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->merge(Attendance::query()->where('user_id', $user->id)->where('status', Attendance::STATUS_PRESENT)->selectRaw('DATE(updated_at) as d')->pluck('d'))
            ->merge(CourseRating::query()->where('user_id', $user->id)->selectRaw('DATE(created_at) as d')->pluck('d'))
            ->filter()
            ->unique()
            ->values();
    }
}
