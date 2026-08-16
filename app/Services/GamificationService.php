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
use App\Models\CourseGamificationRule;
use App\Models\CourseRating;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Friendship;
use App\Models\LearningMaterial;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\XpTransaction;
use App\Notifications\BadgeEarnedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Complete Gamification & Claim Hub Economy Engine for Thinker HUB.
 *
 * Handles:
 * - Lifetime XP (rank determination) & Spendable Thinker Coins (TC).
 * - Rank tiers and multipliers (Novice 1.0x to Grandmaster 1.25x).
 * - Daily 150 TC cap enforcement and anti-gaming rules.
 * - Daily login streaks and milestone rewards.
 * - Idempotent point awards and badge unlock evaluations.
 */
class GamificationService
{
    public const DAILY_COIN_CAP = 150;

    public const XP_DAILY_LOGIN = 5;
    public const COINS_DAILY_LOGIN = 2;

    public const XP_STREAK_7_BONUS = 50;
    public const COINS_STREAK_7_BONUS = 15;

    public const XP_STREAK_30_BONUS = 250;
    public const COINS_STREAK_30_BONUS = 75;

    public const XP_VIDEO_WATCHED = 10;
    public const COINS_VIDEO_WATCHED = 3;

    public const XP_MATERIAL_VIEW = 5;
    public const COINS_MATERIAL_VIEW = 2;

    public const XP_COURSE_COMPLETED = 200;
    public const COINS_COURSE_COMPLETED = 60;

    public const XP_QUIZ_ATTEMPT = 5;
    public const COINS_QUIZ_ATTEMPT = 2;

    public const XP_QUIZ_PASSED = 25;
    public const COINS_QUIZ_PASSED = 8;

    public const XP_QUIZ_PERFECT = 50;
    public const COINS_QUIZ_PERFECT = 15;

    public const XP_ASSESSMENT_PASSED = 100;
    public const COINS_ASSESSMENT_PASSED = 30;

    public const XP_SUBMISSION_ONTIME = 30;
    public const COINS_SUBMISSION_ONTIME = 9;

    public const XP_SUBMISSION_EARLY = 10;
    public const COINS_SUBMISSION_EARLY = 3;

    public const XP_PASSING_GRADE = 30;
    public const COINS_PASSING_GRADE = 9;

    public const XP_DISTINCTION_GRADE = 40;
    public const COINS_DISTINCTION_GRADE = 12;

    public const XP_HIGH_GRADE_A = 70;
    public const COINS_HIGH_GRADE_A = 21;

    public const XP_PERFECT_GRADE = 60;
    public const COINS_PERFECT_GRADE = 18;

    public const XP_ATTENDANCE_PRESENT = 25;
    public const COINS_ATTENDANCE_PRESENT = 8;

    public const XP_PERFECT_ATTENDANCE = 150;
    public const COINS_PERFECT_ATTENDANCE = 45;

    public const XP_COURSE_RATING = 10;
    public const COINS_COURSE_RATING = 3;

    public const XP_STUDY_BUDDY = 15;
    public const COINS_STUDY_BUDDY = 5;

    public const XP_OPPORTUNITY_SUBMIT = 50;
    public const COINS_OPPORTUNITY_SUBMIT = 15;

    public const XP_HUB_POST = 15;
    public const COINS_HUB_POST = 5;

    public const XP_BEST_ANSWER = 30;
    public const COINS_BEST_ANSWER = 9;

    public const XP_REACTIONS_10 = 10;
    public const COINS_REACTIONS_10 = 3;

    public const XP_BUG_REPORT = 50;
    public const COINS_BUG_REPORT = 15;

    /**
     * Calculate user Rank Tier and Multiplier based on lifetime_xp.
     *
     * 0 - 499 XP: 1.0x (Novice)
     * 500 - 1,499 XP: 1.05x (Apprentice)
     * 1,500 - 3,499 XP: 1.10x (Scholar)
     * 3,500 - 7,499 XP: 1.15x (Master)
     * 7,500+ XP: 1.25x (Grandmaster)
     *
     * @return array{rank_name: string, multiplier: float}
     */
    public function calculateUserRank(int $lifetimeXp): array
    {
        return match (true) {
            $lifetimeXp >= 7500 => ['rank_name' => 'Grandmaster', 'multiplier' => 1.25],
            $lifetimeXp >= 3500 => ['rank_name' => 'Master', 'multiplier' => 1.15],
            $lifetimeXp >= 1500 => ['rank_name' => 'Scholar', 'multiplier' => 1.10],
            $lifetimeXp >= 500 => ['rank_name' => 'Apprentice', 'multiplier' => 1.05],
            default => ['rank_name' => 'Novice', 'multiplier' => 1.0],
        };
    }

    /**
     * Award lifetime XP and spendable Thinker Coins (TC) with rank multipliers and daily cap.
     */
    public function awardPoints(
        User $user,
        string $activityType,
        ?Model $subject = null,
        int $baseXp = 0,
        int $baseCoins = 0,
        string $description = ''
    ): bool {
        if ($user->role !== 'student' && ! $user->isStudent()) {
            return false;
        }

        // 1. Daily coin limit check (max 150 TC/day earned from xp_transactions created today)
        $todayEarnedCoins = (int) XpTransaction::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->where('amount_coins', '>', 0)
            ->sum('amount_coins');

        $remainingCap = max(0, self::DAILY_COIN_CAP - $todayEarnedCoins);

        // 2. Rank multiplier calculation
        $currentXp = (int) ($user->lifetime_xp ?? 0);
        $rank = $this->calculateUserRank($currentXp);
        $multiplier = (float) ($rank['multiplier'] ?? 1.0);

        // 3. Final coin calculation (Rank multiplier applied then capped by daily remaining)
        $calculatedCoins = $baseCoins > 0 ? (int) round($baseCoins * $multiplier) : 0;
        $finalCoins = min($calculatedCoins, $remainingCap);

        if ($baseXp <= 0 && $finalCoins <= 0) {
            return false;
        }

        // Idempotency check: Don't award twice for the exact same source event
        if ($subject) {
            $alreadyAwarded = XpTransaction::query()
                ->where('user_id', $user->id)
                ->where('source', $activityType)
                ->where('source_id', (string) $subject->getKey())
                ->exists();

            if ($alreadyAwarded) {
                return false;
            }
        }

        return DB::transaction(function () use ($user, $activityType, $subject, $baseXp, $finalCoins, $description) {
            $lockedUser = User::query()->where('id', $user->id)->lockForUpdate()->first();
            if (! $lockedUser) {
                return false;
            }

            if ($baseXp > 0) {
                $lockedUser->increment('lifetime_xp', $baseXp);
            }
            if ($finalCoins > 0) {
                $lockedUser->increment('spendable_coins', $finalCoins);
            }
            $lockedUser->update(['last_activity_at' => now()]);

            // Sync user memory instance
            $user->lifetime_xp = $lockedUser->lifetime_xp;
            $user->spendable_coins = $lockedUser->spendable_coins;
            $user->last_activity_at = $lockedUser->last_activity_at;

            XpTransaction::create([
                'user_id' => $user->id,
                'amount_xp' => $baseXp,
                'amount_coins' => $finalCoins,
                'activity_type' => $activityType,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject ? $subject->getKey() : null,
                'points' => $baseXp,
                'source' => $activityType,
                'source_id' => $subject ? $subject->getKey() : null,
                'description' => $description,
            ]);

            return true;
        });
    }

    /**
     * Check and record daily login, update streaks, and award milestone bonuses.
     */
    public function checkDailyStreak(User $user): void
    {
        if ($user->role !== 'student' && ! $user->isStudent()) {
            return;
        }

        $alreadyClaimedToday = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where('activity_type', 'daily_login')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyClaimedToday) {
            return;
        }

        $lastActivity = $user->last_activity_at ? Carbon::parse($user->last_activity_at) : null;
        $now = now();

        if ($lastActivity && $lastActivity->isYesterday()) {
            $user->increment('current_streak');
        } elseif (! $lastActivity || ! $lastActivity->isToday()) {
            $user->update(['current_streak' => 1]);
        }

        $user->refresh();

        // Base daily login bonus: +5 XP / +2 TC
        $dailyLoginRule = CourseGamificationRule::getRuleForCourse(null, 'daily_login');
        if ($dailyLoginRule['enabled']) {
            $this->awardPoints(
                $user,
                'daily_login',
                null,
                $dailyLoginRule['xp'],
                $dailyLoginRule['coins'],
                'Daily login check-in: '.$now->format('M d, Y')
            );
        }

        // Milestone streak bonuses
        if ($user->current_streak === 7) {
            $streak7Rule = CourseGamificationRule::getRuleForCourse(null, 'streak_7');
            if ($streak7Rule['enabled'] && ($streak7Rule['xp'] > 0 || $streak7Rule['coins'] > 0)) {
                $this->awardPoints(
                    $user,
                    'streak_7_milestone',
                    null,
                    $streak7Rule['xp'],
                    $streak7Rule['coins'],
                    '7-Day Streak Milestone Reward (+'.$streak7Rule['xp'].' XP / +'.$streak7Rule['coins'].' TC)'
                );
                $this->awardBadge($user, 'streak_7', awardBadgeXp: false);
            }
        } elseif ($user->current_streak === 30) {
            $streak30Rule = CourseGamificationRule::getRuleForCourse(null, 'streak_30');
            if ($streak30Rule['enabled'] && ($streak30Rule['xp'] > 0 || $streak30Rule['coins'] > 0)) {
                $this->awardPoints(
                    $user,
                    'streak_30_milestone',
                    null,
                    $streak30Rule['xp'],
                    $streak30Rule['coins'],
                    '30-Day Streak Milestone Reward (+'.$streak30Rule['xp'].' XP / +'.$streak30Rule['coins'].' TC)'
                );
                $this->awardBadge($user, 'streak_30', awardBadgeXp: false);
            }
        }
    }

    /**
     * Backward-compatible alias for checkDailyStreak.
     */
    public function recordDailyLogin(User $user): void
    {
        $this->checkDailyStreak($user);
    }

    /**
     * Award XP & TC for an on-time or early assignment/assessment submission.
     */
    public function awardSubmission(User $user, AssignmentSubmission|AssessmentSubmission $submission): void
    {
        if ($user->role !== 'student' && ! $user->isStudent()) {
            return;
        }

        $type = $submission instanceof AssignmentSubmission ? 'assignment' : 'assessment';
        $item = $submission instanceof AssignmentSubmission ? $submission->assignment : $submission->assessment;
        $dueDate = $item?->due_date;
        $title = $item?->name ?? ucfirst($type);
        $course = $item?->course;

        $matrixKey = 'assignment_ontime';
        $rule = CourseGamificationRule::getRuleForCourse($course, $matrixKey);

        if (! $rule['enabled'] || ($rule['xp'] <= 0 && $rule['coins'] <= 0)) {
            return;
        }

        $baseXp = $course ? $course->gamificationRule("{$type}_xp", $rule['xp']) : $rule['xp'];
        $baseCoins = $course ? $course->gamificationRule("{$type}_coins", $rule['coins']) : $rule['coins'];

        if ($dueDate) {
            $submittedAt = $submission->submitted_at ?? $submission->created_at ?? now();
            $dueDateEnd = Carbon::parse($dueDate)->endOfDay();

            if ($submittedAt->lte($dueDateEnd)) {
                $this->awardPoints(
                    $user,
                    "{$type}_ontime",
                    $submission,
                    $baseXp,
                    $baseCoins,
                    "On-time submission: {$title}"
                );

                // Early submission bonus (at least 24 hours before deadline end)
                if ($submittedAt->lte($dueDateEnd->copy()->subHours(24))) {
                    $earlyXp = self::XP_SUBMISSION_EARLY;
                    $earlyCoins = self::COINS_SUBMISSION_EARLY;
                    if ($earlyXp > 0 || $earlyCoins > 0) {
                        $this->awardPoints(
                            $user,
                            "{$type}_early",
                            $submission,
                            $earlyXp,
                            $earlyCoins,
                            "Early submission bonus: {$title}"
                        );
                    }
                }
            }
        }

        $this->evaluateStreak($user);

        // Check Punctual Scholar badge (5 on-time submissions)
        $onTimeCount = XpTransaction::query()
            ->where('user_id', $user->id)
            ->whereIn('activity_type', ['assignment_ontime', 'assessment_ontime'])
            ->count();

        if ($onTimeCount >= 5) {
            $this->awardBadge($user, 'punctual_scholar', course: $course);
        }

        // Check Early Bird badge (3 early submissions)
        $earlyCount = XpTransaction::query()
            ->where('user_id', $user->id)
            ->whereIn('activity_type', ['assignment_early', 'assessment_early'])
            ->count();

        if ($earlyCount >= 3) {
            $this->awardBadge($user, 'early_bird', course: $course);
        }
    }

    /**
     * Award layered XP & TC when an assignment or assessment is graded.
     */
    public function awardGradedSubmission(User $user, AssignmentSubmission|AssessmentSubmission $submission): void
    {
        if ($user->role !== 'student' && ! $user->isStudent()) {
            return;
        }

        $type = $submission instanceof AssignmentSubmission ? 'assignment' : 'assessment';
        $item = $submission instanceof AssignmentSubmission ? $submission->assignment : $submission->assessment;
        $title = $item?->name ?? ucfirst($type);
        $course = $item?->course;

        $rawScore = $submission instanceof AssignmentSubmission ? $submission->grade : $submission->score;

        if ($rawScore === null || $rawScore === '') {
            return;
        }

        $numericScore = (float) filter_var((string) $rawScore, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $gradeARule = CourseGamificationRule::getRuleForCourse($course, 'assignment_grade_a');
        $assessmentRule = CourseGamificationRule::getRuleForCourse($course, 'assessment_passed');
        $assignmentRule = CourseGamificationRule::getRuleForCourse($course, 'assignment_ontime');

        // Assessment passed
        if ($type === 'assessment' && $numericScore >= 50 && $assessmentRule['enabled'] && ($assessmentRule['xp'] > 0 || $assessmentRule['coins'] > 0)) {
            $this->awardPoints(
                $user,
                'assessment_passed',
                $submission,
                $course ? $course->gamificationRule('assessment_xp', $assessmentRule['xp']) : $assessmentRule['xp'],
                $course ? $course->gamificationRule('assessment_coins', $assessmentRule['coins']) : $assessmentRule['coins'],
                "Passed Assessment ({$numericScore}%): {$title}"
            );
        } elseif ($type === 'assignment' && $numericScore >= 50 && $assignmentRule['enabled'] && ($assignmentRule['xp'] > 0 || $assignmentRule['coins'] > 0)) {
            $passXp = $course ? $course->gamificationRule('passing_xp', $assignmentRule['xp']) : $assignmentRule['xp'];
            $passCoins = $course ? $course->gamificationRule('passing_coins', $assignmentRule['coins']) : $assignmentRule['coins'];

            if ($passXp > 0 || $passCoins > 0) {
                $this->awardPoints(
                    $user,
                    'assignment_passed',
                    $submission,
                    $passXp,
                    $passCoins,
                    "Passed assignment ({$numericScore}%): {$title}"
                );
            }
        }

        // High Grade (Grade A / 90%+)
        if ($numericScore >= 90 && $gradeARule['enabled'] && ($gradeARule['xp'] > 0 || $gradeARule['coins'] > 0)) {
            $this->awardPoints(
                $user,
                "{$type}_distinction",
                $submission,
                $gradeARule['xp'],
                $gradeARule['coins'],
                "High Grade ({$numericScore}% / Grade A): {$title}"
            );

            // Check Distinction Club badge (3 distinction scores)
            $distinctionCount = XpTransaction::query()
                ->where('user_id', $user->id)
                ->whereIn('activity_type', ['assignment_distinction', 'assessment_distinction'])
                ->count();

            if ($distinctionCount >= 3) {
                $this->awardBadge($user, 'distinction_club', course: $course);
            }
        } elseif ($numericScore >= 80 && $gradeARule['enabled'] && ($gradeARule['xp'] > 0 || $gradeARule['coins'] > 0)) {
            $distXp = (int) round($gradeARule['xp'] * 0.75);
            $distCoins = (int) round($gradeARule['coins'] * 0.75);
            if ($distXp > 0 || $distCoins > 0) {
                $this->awardPoints(
                    $user,
                    "{$type}_distinction",
                    $submission,
                    $distXp,
                    $distCoins,
                    "Distinction bonus ({$numericScore}%): {$title}"
                );
            }
        }

        // Perfect score bonus (100%)
        $perfectRule = CourseGamificationRule::getRuleForCourse($course, 'quiz_score_100');
        if ($numericScore >= 100 && $perfectRule['enabled'] && ($perfectRule['xp'] > 0 || $perfectRule['coins'] > 0)) {
            $this->awardPoints(
                $user,
                "{$type}_perfect",
                $submission,
                $perfectRule['xp'],
                $perfectRule['coins'],
                "Perfect score bonus (100%): {$title}"
            );
            $this->awardBadge($user, 'first_perfect_quiz', course: $course);
        }
    }

    /**
     * Award XP & TC for a passed quiz attempt.
     * Anti-Gaming: Re-taking an already passed quiz awards +0 TC / +0 XP.
     */
    public function awardQuizPassed(User $user, QuizAttempt $attempt): void
    {
        $quiz = $attempt->quiz;

        if (! $quiz) {
            return;
        }

        $course = $quiz->course;

        // Anti-gaming: Check if user already passed this quiz previously
        $priorPassExists = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('passed', true)
            ->where('id', '!=', $attempt->id)
            ->exists();

        if ($priorPassExists) {
            // Re-taking already passed quiz: +0 TC and +0 XP
            return;
        }

        $rule80 = CourseGamificationRule::getRuleForCourse($course, 'quiz_score_80');
        $rule100 = CourseGamificationRule::getRuleForCourse($course, 'quiz_score_100');

        $quizXp = $course ? $course->gamificationRule('quiz_xp', $rule80['xp']) : $rule80['xp'];
        $quizCoins = $course ? $course->gamificationRule('quiz_coins', $rule80['coins']) : $rule80['coins'];

        if ($rule80['enabled'] && ($quizXp > 0 || $quizCoins > 0)) {
            $this->awardPoints(
                $user,
                'quiz_passed',
                $attempt,
                $quizXp,
                $quizCoins,
                'Passed quiz (80%+): '.$quiz->title
            );
        }

        if ((int) $attempt->percentage >= 100 && $rule100['enabled'] && ($rule100['xp'] > 0 || $rule100['coins'] > 0)) {
            $this->awardPoints(
                $user,
                'quiz_perfect',
                $attempt,
                $rule100['xp'],
                $rule100['coins'],
                'Perfect quiz score (100%): '.$quiz->title
            );
            $this->awardBadge($user, 'first_perfect_quiz', course: $course);
        }
    }

    /**
     * Award XP & TC for attempting a quiz (Point Earning Matrix limit: Max 3 quiz attempts rewarded/day).
     */
    public function awardQuizAttempt(User $user, QuizAttempt $attempt): void
    {
        if ($user->role !== 'student' && ! $user->isStudent()) {
            return;
        }

        $quiz = $attempt->quiz;
        $course = $quiz?->course;

        $rule = CourseGamificationRule::getRuleForCourse($course, 'quiz_attempt');
        if (! $rule['enabled'] || ($rule['xp'] <= 0 && $rule['coins'] <= 0)) {
            return;
        }

        $todayAttempts = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where('activity_type', 'quiz_attempt')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayAttempts >= 3) {
            return;
        }

        $title = $quiz?->title ?? 'Quiz';

        $this->awardPoints(
            $user,
            'quiz_attempt',
            $attempt,
            $rule['xp'],
            $rule['coins'],
            "Attempted quiz: {$title}"
        );

        $this->evaluateStreak($user);
    }

    /**
     * Anti-Gaming: Video watched points trigger only if watch_duration >= 80%.
     */
    public function awardVideoWatched(User $user, Model $video, float $watchPercentage): bool
    {
        if ($watchPercentage < 80.0) {
            return false;
        }

        $course = $video instanceof CourseSession ? $video->course : ($video->course ?? null);
        $rule = CourseGamificationRule::getRuleForCourse($course, 'video_completed');

        if (! $rule['enabled'] || ($rule['xp'] <= 0 && $rule['coins'] <= 0)) {
            return false;
        }

        // Limit: Max 5 videos/day eligible for rewards
        $todayVideos = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q->where('activity_type', 'video_watched')->orWhere('activity_type', 'lesson_video_completed')->orWhere('source', 'video_watched'))
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayVideos >= 5) {
            return false;
        }

        $videoTitle = $video->title ?? 'Learning Video';

        return $this->awardPoints(
            $user,
            'video_watched',
            $video,
            $rule['xp'],
            $rule['coins'],
            "Completed video (80%+ watched): {$videoTitle}"
        );
    }

    /**
     * Award live session attendance XP & TC.
     */
    public function awardAttendance(User $user, Attendance $attendance): void
    {
        if ($attendance->status !== Attendance::STATUS_PRESENT) {
            return;
        }

        $session = $attendance->session;
        $sessionTitle = $session?->title ?? 'Live Class Session';
        $course = $session?->course;

        $rule = CourseGamificationRule::getRuleForCourse($course, 'attendance');
        $attendanceXp = $course ? $course->gamificationRule('attendance_xp', $rule['xp']) : $rule['xp'];
        $attendanceCoins = $course ? $course->gamificationRule('attendance_coins', $rule['coins']) : $rule['coins'];

        if ($rule['enabled'] && ($attendanceXp > 0 || $attendanceCoins > 0)) {
            $this->awardPoints(
                $user,
                'attendance_present',
                $attendance,
                $attendanceXp,
                $attendanceCoins,
                "Attended session: {$sessionTitle}"
            );
        }

        $this->evaluateStreak($user);

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
                    $rulePerf = CourseGamificationRule::getRuleForCourse($session->course, 'perfect_attendance');
                    if ($rulePerf['enabled'] && ($rulePerf['xp'] > 0 || $rulePerf['coins'] > 0)) {
                        $this->awardPoints(
                            $user,
                            'perfect_attendance',
                            $session,
                            $rulePerf['xp'],
                            $rulePerf['coins'],
                            '100% Course Attendance: '.($session->course?->title ?? 'Course')
                        );
                        $this->awardBadge($user, 'always_present', course: $session->course);
                    }
                }
            }
        }
    }

    /**
     * Award course-completion XP & TC, Graduate badge, and evaluate Mastermind badge.
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

        $rule = CourseGamificationRule::getRuleForCourse($course, 'course_completion');
        if (! $rule['enabled'] || ($rule['xp'] <= 0 && $rule['coins'] <= 0)) {
            return;
        }

        $completionXp = $course->gamificationRule('course_completion_xp', $rule['xp']);
        $completionCoins = $course->gamificationRule('course_completion_coins', $rule['coins']);

        $this->awardPoints(
            $user,
            'course_completed',
            $course,
            $completionXp,
            $completionCoins,
            'Completed course: '.$course->title
        );

        $this->awardBadge($user, 'course_completed', course: $course);

        // Check Mastermind badge (3 completed courses signed off by instructor)
        $completedCoursesCount = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        if ($completedCoursesCount >= 3) {
            $this->awardBadge($user, 'mastermind', course: $course);
        }
    }

    /**
     * Revoke course-completion XP, Graduate badge, and Mastermind badge if no longer eligible.
     */
    public function revokeCourseCompleted(User $user, Course $course): void
    {
        // 1. Fetch transaction to subtract XP / coins from user balances
        $tx = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where(function ($q) use ($course) {
                $q->where(fn ($sub) => $sub->where('activity_type', 'course_completed')->where('subject_id', $course->id))
                  ->orWhere(fn ($sub) => $sub->where('source', 'course_completed')->where('source_id', $course->id));
            })
            ->first();

        if ($tx) {
            $deductXp = (int) ($tx->amount_xp ?: $tx->points);
            $deductCoins = (int) ($tx->amount_coins ?: 0);

            $user->decrement('lifetime_xp', min((int) $user->lifetime_xp, $deductXp));
            if ($deductCoins > 0) {
                $user->decrement('spendable_coins', min((int) $user->spendable_coins, $deductCoins));
            }
            $tx->delete();
        }

        // 2. Re-evaluate remaining instructor-completed courses
        $completedCoursesCount = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        if ($completedCoursesCount < 3) {
            $this->revokeBadge($user, 'mastermind');
        }

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

        $tx = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q->where('activity_type', 'badge')->orWhere('source', 'badge'))
            ->where(fn ($q) => $q->where('subject_id', $badge->id)->orWhere('source_id', $badge->id))
            ->first();

        if ($tx) {
            $deductXp = (int) ($tx->amount_xp ?: $tx->points);
            $user->decrement('lifetime_xp', min((int) $user->lifetime_xp, $deductXp));
            $tx->delete();
        }
    }

    /**
     * Award course rating / review submission XP & TC.
     */
    public function awardCourseRating(User $user, CourseRating $rating): void
    {
        $course = $rating->course;
        $rule = CourseGamificationRule::getRuleForCourse($course, 'course_rating');

        if (! $rule['enabled'] || ($rule['xp'] <= 0 && $rule['coins'] <= 0)) {
            return;
        }

        $this->awardPoints(
            $user,
            'course_rating',
            $rating,
            $rule['xp'],
            $rule['coins'],
            'Rated course: '.($course?->title ?? 'Course')
        );

        $this->evaluateStreak($user);
    }

    /**
     * Award learning material review / download XP & TC with a daily cap.
     */
    public function awardMaterialView(User $user, LearningMaterial $material): void
    {
        $course = $material->course;
        $rule = CourseGamificationRule::getRuleForCourse($course, 'material_read');

        if (! $rule['enabled'] || ($rule['xp'] <= 0 && $rule['coins'] <= 0)) {
            return;
        }

        $todayCount = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q->where('activity_type', 'material_viewed')->orWhere('source', 'material_viewed'))
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayCount < 4) {
            $this->awardPoints(
                $user,
                'material_viewed',
                $material,
                $rule['xp'],
                $rule['coins'],
                'Reviewed material: '.$material->title
            );
        }

        $this->evaluateStreak($user);
    }

    /**
     * Award study buddy connection XP & TC and evaluate Study Networker badge.
     */
    public function awardFriendship(User $user, Friendship $friendship): void
    {
        $rule = CourseGamificationRule::getRuleForCourse(null, 'study_buddy');
        if ($rule['enabled'] && ($rule['xp'] > 0 || $rule['coins'] > 0)) {
            $buddyCount = XpTransaction::query()
                ->where('user_id', $user->id)
                ->where(fn ($q) => $q->where('activity_type', 'study_buddy')->orWhere('source', 'study_buddy'))
                ->count();

            if ($buddyCount < 5) {
                $this->awardPoints(
                    $user,
                    'study_buddy',
                    $friendship,
                    $rule['xp'],
                    $rule['coins'],
                    'Connected with study buddy'
                );
            }
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
     * Award Opportunity Hub submission XP & TC and Innovator badge.
     */
    public function awardOpportunitySubmission(User $user, int $opportunityId, string $title): void
    {
        $rule = CourseGamificationRule::getRuleForCourse(null, 'opportunity_submit');
        if ($rule['enabled'] && ($rule['xp'] > 0 || $rule['coins'] > 0)) {
            $this->awardPoints(
                $user,
                'opportunity_submitted',
                null,
                $rule['xp'],
                $rule['coins'],
                "Submitted to Opportunities Hub: {$title}"
            );
        }

        $this->awardBadge($user, 'innovator');
        $this->evaluateStreak($user);
    }

    /**
     * Award XP & TC for publishing a Community Hub post/tutorial (Point Earning Matrix).
     */
    public function awardHubPost(User $user, Model $post): void
    {
        if ($user->role !== 'student' && ! $user->isStudent()) {
            return;
        }

        $rule = CourseGamificationRule::getRuleForCourse(null, 'hub_post_published');
        if (! $rule['enabled'] || ($rule['xp'] <= 0 && $rule['coins'] <= 0)) {
            return;
        }

        $title = $post->title ?? 'Hub Post';

        $this->awardPoints(
            $user,
            'hub_post_published',
            $post,
            $rule['xp'],
            $rule['coins'],
            "Published Hub Post / Resource: {$title}"
        );

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
     * Evaluate consecutive active day streaks.
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

        // 7-Day streak badge
        if ($streak >= 7) {
            $this->awardBadge($user, 'streak_7');
        }

        // 30-Day streak badge
        if ($streak >= 30) {
            $this->awardBadge($user, 'streak_30');
        }
    }

    /**
     * Check whether gamification rules for a badge's underlying action are actively enabled.
     */
    public function isBadgeRuleActive(string $badgeKey, ?Course $course = null): bool
    {
        $activityKey = match ($badgeKey) {
            'first_perfect_quiz' => 'quiz_score_100',
            'streak_7' => 'streak_7',
            'streak_30' => 'streak_30',
            'course_completed', 'mastermind' => 'course_completion',
            'punctual_scholar', 'early_bird' => 'assignment_ontime',
            'distinction_club' => 'assignment_grade_a',
            'always_present' => 'perfect_attendance',
            'innovator' => 'opportunity_submit',
            'study_networker' => 'study_buddy',
            'active_contributor' => 'hub_post_published',
            default => $badgeKey,
        };

        $rule = CourseGamificationRule::getRuleForCourse($course, $activityKey);

        if (! empty($rule['enabled']) && ((int) ($rule['xp'] ?? 0) > 0 || (int) ($rule['coins'] ?? 0) > 0)) {
            return true;
        }

        if (! $course) {
            $globalRule = CourseGamificationRule::getRuleForCourse(null, $activityKey);
            if (! empty($globalRule['enabled']) && ((int) ($globalRule['xp'] ?? 0) > 0 || (int) ($globalRule['coins'] ?? 0) > 0)) {
                return true;
            }

            return CourseGamificationRule::query()
                ->whereNotNull('course_id')
                ->where('is_active', true)
                ->get()
                ->contains(function ($cr) use ($activityKey) {
                    if (! is_array($cr->rules)) {
                        return false;
                    }
                    $custom = CourseGamificationRule::findRuleInArray($cr->rules, $activityKey, $activityKey);

                    return $custom && ! empty($custom['enabled']) && ((int) ($custom['xp'] ?? 0) > 0 || (int) ($custom['coins'] ?? 0) > 0);
                });
        }

        return false;
    }

    /**
     * Grant a badge once. A newly granted badge also banks its xp_reward and notifies the student.
     * Badges can only be granted when the underlying point rules are actively enabled by an instructor or admin.
     */
    public function awardBadge(User $user, string $key, bool $awardBadgeXp = true, ?Course $course = null): ?Badge
    {
        if (! $this->isBadgeRuleActive($key, $course)) {
            return null;
        }

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
            report($e);

            return $badge;
        }

        if ($awardBadgeXp && $badge->xp_reward > 0) {
            $this->awardPoints(
                $user,
                'badge',
                $badge,
                $badge->xp_reward,
                (int) round($badge->xp_reward / 10),
                'Badge earned: '.$badge->name
            );
        }

        try {
            $user->notify(new BadgeEarnedNotification($badge));
        } catch (Throwable $e) {
            report($e);
        }

        return $badge;
    }

    /**
     * Leaderboard ranked by lifetime XP.
     */
    public function leaderboard(): Collection
    {
        $students = User::query()
            ->where('role', 'student')
            ->where('is_active', true)
            ->orderByDesc('lifetime_xp')
            ->orderBy('id')
            ->get();

        $userIds = $students->pluck('id')->all();

        if ($userIds === []) {
            return collect();
        }

        $badgeCounts = DB::table('user_badge')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as c')
            ->pluck('c', 'user_id');

        $badgeDetails = DB::table('user_badge')
            ->join('badges', 'badges.id', '=', 'user_badge.badge_id')
            ->whereIn('user_badge.user_id', $userIds)
            ->orderBy('user_badge.earned_at')
            ->get(['user_badge.user_id', 'badges.id', 'badges.key', 'badges.name', 'badges.icon', 'badges.xp_reward'])
            ->groupBy('user_id');

        $badgeIcons = $badgeDetails->map(fn ($rows) => $rows->pluck('icon')->filter()->take(5)->values()->all());

        return $students->values()->map(function (User $student, int $index) use ($badgeCounts, $badgeIcons, $badgeDetails): array {
            $rankInfo = $this->calculateUserRank((int) $student->lifetime_xp);

            return [
                'rank' => $index + 1,
                'user_id' => (int) $student->id,
                'name' => $student->name,
                'xp' => (int) $student->lifetime_xp,
                'coins' => (int) $student->spendable_coins,
                'rank_name' => $rankInfo['rank_name'],
                'multiplier' => $rankInfo['multiplier'],
                'badge_count' => (int) ($badgeCounts[$student->id] ?? 0),
                'badge_icons' => $badgeIcons[$student->id] ?? [],
                'badges' => ($badgeDetails[$student->id] ?? collect())->take(4)->values()->all(),
            ];
        });
    }


    /**
     * Distinct activity dates (Y-m-d) for streak evaluation.
     */
    private function activityDates(User $user): Collection
    {
        return collect()
            ->merge(XpTransaction::query()->where('user_id', $user->id)->whereIn('activity_type', ['daily_login'])->selectRaw('DATE(created_at) as d')->pluck('d'))
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
