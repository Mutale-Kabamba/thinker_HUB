<?php

namespace Tests\Feature;

use App\Livewire\ClaimHub\Storefront;
use App\Livewire\MaterialReader;
use App\Livewire\Public\HubIndex;
use App\Livewire\VideoPlayer;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Badge;
use App\Models\Course;
use App\Models\CourseGamificationRule;
use App\Models\CourseRating;
use App\Models\CourseSession;
use App\Models\Friendship;
use App\Models\HubPost;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PointEarningMatrixAccumulationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Badge::query()->delete();
        Badge::create([
            'key' => 'innovator',
            'name' => 'Innovator',
            'description' => 'Submitted to Opportunities Hub',
            'category' => 'academic',
            'icon' => 'sparkles',
            'xp_reward' => 50,
        ]);
    }

    public function test_active_point_earning_matrix_accumulates_points_across_activities(): void
    {
        $service = app(GamificationService::class);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        /** @var Course $course */
        $course = Course::create([
            'title' => 'Fullstack Laravel Mastery',
            'code' => 'FL-101',
            'is_active' => true,
        ]);

        // 1. Daily Login (+5 XP, +2 TC)
        $service->checkDailyStreak($student);
        $student->refresh();

        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);

        // 2. Reading Learning Material (+5 XP, +2 TC)
        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Architecture Deep Dive PDF',
            'file_path' => 'materials/arch.pdf',
            'is_published' => true,
        ]);

        $service->awardMaterialView($student, $material);
        $student->refresh();

        $this->assertSame(10, $student->lifetime_xp);
        $this->assertSame(4, $student->spendable_coins);

        // 3. Completing Lesson Video (+10 XP, +3 TC)
        $session = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Live Coding Session 1',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $service->awardVideoWatched($student, $session, 90.0);
        $student->refresh();

        $this->assertSame(20, $student->lifetime_xp);
        $this->assertSame(7, $student->spendable_coins);

        // 4. Quiz Attempt (+5 XP, +2 TC) & Quiz Passed 80%+ (+25 XP, +8 TC)
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Module 1 Quiz',
            'passing_score' => 60,
            'is_published' => true,
        ]);

        $attempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 85,
            'total_questions' => 10,
            'percentage' => 85,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $service->awardQuizAttempt($student, $attempt);
        $service->awardQuizPassed($student, $attempt);
        $student->refresh();

        // 20 + 5 (attempt) + 25 (passed) = 50 XP; 7 + 2 + 8 = 17 TC
        $this->assertSame(50, $student->lifetime_xp);
        $this->assertSame(17, $student->spendable_coins);

        // 5. On-time Assignment Submission (+30 XP, +9 TC)
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'API Integration Homework',
            'due_date' => now()->addDays(3)->toDateString(),
            'is_published' => true,
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'submitted_at' => now(),
        ]);

        $service->awardSubmission($student, $submission);
        $student->refresh();

        // 50 + 30 (ontime) + 10 (early bonus) = 90 XP; 17 + 9 + 3 = 29 TC
        $this->assertSame(90, $student->lifetime_xp);
        $this->assertSame(29, $student->spendable_coins);

        // 6. Live Session Attendance (+25 XP, +8 TC)
        $attendance = Attendance::create([
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $service->awardAttendance($student, $attendance);
        $student->refresh();

        // 90 + 25 = 115 XP; 29 + 8 = 37 TC
        $this->assertSame(115, $student->lifetime_xp);
        $this->assertSame(37, $student->spendable_coins);

        // 7. Study Buddy Connection (+15 XP, +5 TC)
        $buddy = User::factory()->create(['role' => 'student']);
        $friendship = Friendship::create([
            'user_id' => $student->id,
            'friend_id' => $buddy->id,
            'status' => 'accepted',
        ]);

        $service->awardFriendship($student, $friendship);
        $student->refresh();

        // 115 + 15 = 130 XP; 37 + 5 = 42 TC
        $this->assertSame(130, $student->lifetime_xp);
        $this->assertSame(42, $student->spendable_coins);

        // 8. Opportunity Hub Submission (+50 XP, +15 TC) & Innovator Badge (+50 XP, +5 TC)
        $service->awardOpportunitySubmission($student, 1, 'Tech Internship 2026');
        $student->refresh();

        // 130 + 50 (submission) + 50 (innovator badge reward) = 230 XP; 42 + 15 + 5 = 62 TC
        $this->assertSame(230, $student->lifetime_xp);
        $this->assertSame(62, $student->spendable_coins);

        // 9. Community Hub Post (+15 XP, +5 TC)
        $hubPost = HubPost::create([
            'author_id' => $student->id,
            'title' => 'Top 10 Livewire 3 Tips',
            'type' => 'tip_trick',
            'category' => 'Development',
            'is_published' => true,
        ]);

        $service->awardHubPost($student, $hubPost);
        $student->refresh();

        // 230 + 15 = 245 XP; 62 + 5 = 67 TC
        $this->assertSame(245, $student->lifetime_xp);
        $this->assertSame(67, $student->spendable_coins);

        // 10. Rating a Course (+10 XP, +3 TC)
        $rating = CourseRating::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 5,
            'review' => 'Excellent course!',
        ]);

        $service->awardCourseRating($student, $rating);
        $student->refresh();

        // 245 + 10 = 255 XP; 67 + 3 = 70 TC
        $this->assertSame(255, $student->lifetime_xp);
        $this->assertSame(70, $student->spendable_coins);
    }

    public function test_custom_course_point_matrix_overrides_specific_rules_and_preserves_others(): void
    {
        $service = app(GamificationService::class);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        /** @var Course $course */
        $course = Course::create([
            'title' => 'AI Engineering Pro',
            'code' => 'AIE-500',
            'is_active' => true,
        ]);

        // Custom Matrix: Boosted Quiz Score (100 XP / 30 TC) and Attendance (50 XP / 15 TC)
        CourseGamificationRule::create([
            'course_id' => $course->id,
            'is_active' => true,
            'rules' => [
                [
                    'activity_key' => 'quiz_score_80',
                    'activity_name' => 'AI Quiz Passed',
                    'category' => 'Quizzes & Assessments',
                    'xp' => 100,
                    'coins' => 30,
                    'enabled' => true,
                ],
                [
                    'activity_key' => 'attendance',
                    'activity_name' => 'Lab Attendance',
                    'category' => 'Attendance & Participation',
                    'xp' => 50,
                    'coins' => 15,
                    'enabled' => true,
                ],
            ],
        ]);

        // 1. Customized rule: Quiz Pass awards overridden 100 XP / 30 TC
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Transformers Assessment',
            'passing_score' => 60,
            'is_published' => true,
        ]);

        $attempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 90,
            'total_questions' => 10,
            'percentage' => 90,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $service->awardQuizPassed($student, $attempt);
        $student->refresh();

        $this->assertSame(100, $student->lifetime_xp);
        $this->assertSame(30, $student->spendable_coins);

        // 2. Un-overridden rule (e.g. material read) falls back to active platform default (5 XP / 2 TC)
        $material = LearningMaterial::create([
            'course_id' => $course->id,
            'title' => 'Attention Is All You Need Paper',
            'file_path' => 'materials/attention.pdf',
            'is_published' => true,
        ]);

        $service->awardMaterialView($student, $material);
        $student->refresh();

        $this->assertSame(105, $student->lifetime_xp);
        $this->assertSame(32, $student->spendable_coins);
    }

    public function test_storefront_displays_complete_active_point_earning_matrix(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);

        Livewire::actingAs($student)
            ->test(Storefront::class, ['activeTab' => 'matrix'])
            ->assertSet('activeTab', 'matrix')
            ->assertSee('Daily Login & Streak')
            ->assertSee('Course & Learning Material')
            ->assertSee('Quizzes & Assessments')
            ->assertSee('Assignments')
            ->assertSee('Community & Peer Engagement')
            ->assertSee('Attendance & Participation')
            ->assertSee('Feedback & Platform Support');
    }

    public function test_daily_login_only_awards_once_per_day_on_first_login(): void
    {
        $service = app(GamificationService::class);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        // First login of the day: awards +5 XP and +2 TC
        $service->checkDailyStreak($student);
        $student->refresh();

        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);

        // Second login on the same day: awards 0 additional points
        $service->checkDailyStreak($student);
        $student->refresh();

        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);

        // Third login on the same day: awards 0 additional points
        $service->recordDailyLogin($student);
        $student->refresh();

        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);

        $loginTransactions = \App\Models\XpTransaction::query()
            ->where('user_id', $student->id)
            ->where(fn ($q) => $q->where('activity_type', 'daily_login')->orWhere('source', 'daily_login'))
            ->count();

        $this->assertSame(1, $loginTransactions);
    }

    public function test_quiz_attempt_awards_points_on_first_try_and_zero_points_on_second_try(): void
    {
        $service = app(GamificationService::class);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'lifetime_xp' => 0,
            'spendable_coins' => 0,
        ]);

        /** @var Course $course */
        $course = Course::create([
            'title' => 'Python Fundamentals',
            'code' => 'PY-101',
            'is_active' => true,
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Functions and Loops Quiz',
            'passing_score' => 60,
            'is_published' => true,
        ]);

        // 1. First Quiz Attempt (1st try): awards +5 XP and +2 TC
        $firstAttempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'is_retake' => false,
            'score' => 50,
            'total_questions' => 10,
            'percentage' => 50,
            'passed' => false,
            'completed_at' => now(),
        ]);

        $service->awardQuizAttempt($student, $firstAttempt);
        $student->refresh();

        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);

        // 2. Second Quiz Attempt (2nd try / Retake with is_retake = true): awards 0 points
        $secondAttempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'is_retake' => true,
            'score' => 90,
            'total_questions' => 10,
            'percentage' => 90,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $service->awardQuizAttempt($student, $secondAttempt);
        $student->refresh();

        // Points should remain unchanged (+0 from 2nd try attempt)
        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);

        // 3. Another attempt for the same quiz even without is_retake flag set: awards 0 points
        $thirdAttempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'is_retake' => false,
            'score' => 100,
            'total_questions' => 10,
            'percentage' => 100,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $service->awardQuizAttempt($student, $thirdAttempt);
        $student->refresh();

        // Points still remain unchanged
        $this->assertSame(5, $student->lifetime_xp);
        $this->assertSame(2, $student->spendable_coins);

        $attemptTransactions = \App\Models\XpTransaction::query()
            ->where('user_id', $student->id)
            ->where(fn ($q) => $q->where('activity_type', 'quiz_attempt')->orWhere('source', 'quiz_attempt'))
            ->count();

        $this->assertSame(1, $attemptTransactions);
    }
}

