<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Badge;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Friendship;
use App\Models\LearningMaterial;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GamificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->seed(\Database\Seeders\CourseGamificationRuleSeeder::class);
    }

    public function test_daily_login_awards_xp_and_is_idempotent(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $service = app(GamificationService::class);

        $service->recordDailyLogin($student);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'daily_login',
            'points' => 5,
        ]);

        $initialXp = $student->xpTotal();
        $this->assertSame(5, $initialXp);

        // Second call on the same day must not create duplicate transaction
        $service->recordDailyLogin($student);
        $this->assertSame(5, $student->xpTotal());
    }

    public function test_on_time_and_early_submission_awards_bonus_xp(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Final Project Assignment',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        // Submit 2 days before due date (both on-time and early)
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Completed project',
            'submitted_at' => now(),
        ]);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'assignment_ontime',
            'points' => 30,
        ]);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'assignment_early',
            'points' => 10,
        ]);
    }

    public function test_grading_awards_pass_distinction_and_perfect_score_xp(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Computer Science',
            'code' => 'CS101',
            'is_active' => true,
        ]);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Algorithm Design',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'My solution',
            'submitted_at' => now(),
        ]);

        // Instructor grades submission with 100%
        $submission->update(['grade' => '100%']);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'assignment_distinction',
            'points' => 70,
        ]);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'assignment_perfect',
            'points' => 50,
        ]);

        // Perfectionist badge awarded
        $this->assertTrue($student->badges()->where('badges.key', 'first_perfect_quiz')->exists());
    }

    public function test_attendance_present_awards_xp(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $service = app(GamificationService::class);
        $course = Course::query()->create([
            'title' => 'Robotics',
            'code' => 'ROB101',
            'is_active' => true,
        ]);

        $session = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Live Lab 1',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $attendance = Attendance::create([
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => 'present',
        ]);

        $service->awardAttendance($student, $attendance);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'attendance_present',
            'points' => 25,
        ]);
    }

    public function test_friendship_acceptance_awards_study_buddy_xp(): void
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        $service = app(GamificationService::class);

        $friendship = Friendship::create([
            'user_id' => $student1->id,
            'friend_id' => $student2->id,
            'status' => 'accepted',
        ]);

        $service->awardFriendship($student1, $friendship);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student1->id,
            'source' => 'study_buddy',
            'points' => 15,
        ]);
    }

    public function test_opportunity_hub_submission_awards_xp_and_innovator_badge(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $service = app(GamificationService::class);

        $service->awardOpportunitySubmission($student, 10, 'Tech Grant 2026');

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'opportunity_submitted',
            'points' => 50,
        ]);

        $this->assertTrue($student->badges()->where('badges.key', 'innovator')->exists());
    }

    public function test_punctual_scholar_badge_awarded_after_five_ontime_submissions(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Software Engineering',
            'code' => 'SE101',
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $assignment = Assignment::create([
                'course_id' => $course->id,
                'name' => "Assignment {$i}",
                'due_date' => now()->addDays(5)->toDateString(),
            ]);

            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => "Work {$i}",
                'submitted_at' => now(),
            ]);
        }

        $this->assertTrue($student->badges()->where('badges.key', 'punctual_scholar')->exists());
    }

    public function test_course_completion_and_mastermind_badges_only_awarded_after_instructor_signoff_and_reset_on_unmarking(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $service = app(GamificationService::class);

        $courses = [];
        for ($i = 1; $i <= 3; $i++) {
            $courses[$i] = Course::query()->create([
                'title' => "Track Course {$i}",
                'code' => "TRK-{$i}",
                'is_active' => true,
            ]);
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $courses[$i]->id,
                'completed_at' => null,
            ]);
        }

        // 1. Before sign-off, awardCourseCompleted does nothing
        $service->awardCourseCompleted($student, $courses[1]);
        $this->assertFalse($student->badges()->where('badges.key', 'course_completed')->exists());
        $this->assertDatabaseMissing('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'course_completed',
        ]);

        // 2. Instructor signs off first course
        $enr1 = Enrollment::where('user_id', $student->id)->where('course_id', $courses[1]->id)->first();
        $enr1->markAsCompleted($instructor);

        $service->awardCourseCompleted($student, $courses[1]);
        $this->assertTrue($student->badges()->where('badges.key', 'course_completed')->exists());
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'course_completed',
            'source_id' => $courses[1]->id,
            'points' => 200,
        ]);
        // Not mastermind yet (only 1 course)
        $this->assertFalse($student->badges()->where('badges.key', 'mastermind')->exists());

        // 3. Complete 2 more courses
        $enr2 = Enrollment::where('user_id', $student->id)->where('course_id', $courses[2]->id)->first();
        $enr2->markAsCompleted($instructor);
        $service->awardCourseCompleted($student, $courses[2]);

        $enr3 = Enrollment::where('user_id', $student->id)->where('course_id', $courses[3]->id)->first();
        $enr3->markAsCompleted($instructor);
        $service->awardCourseCompleted($student, $courses[3]);

        // Now Mastermind (3 courses) should be earned
        $this->assertTrue($student->badges()->where('badges.key', 'mastermind')->exists());

        // 4. Instructor resets/unmarks course 3
        $enr3->markAsIncomplete();
        $service->revokeCourseCompleted($student, $courses[3]);

        // Mastermind should be revoked because now only 2 completed courses
        $this->assertFalse($student->badges()->where('badges.key', 'mastermind')->exists());
        // Still has Graduate badge
        $this->assertTrue($student->badges()->where('badges.key', 'course_completed')->exists());

        // 5. Instructor resets course 1 and course 2
        $enr1->markAsIncomplete();
        $service->revokeCourseCompleted($student, $courses[1]);
        $enr2->markAsIncomplete();
        $service->revokeCourseCompleted($student, $courses[2]);

        // Graduate badge revoked because 0 completed courses
        $this->assertFalse($student->badges()->where('badges.key', 'course_completed')->exists());
        $this->assertDatabaseMissing('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'course_completed',
        ]);
    }

    public function test_no_points_or_badges_awarded_when_rules_are_not_set_or_inactive(): void
    {
        // 1. Clear all rules so neither course nor global rules exist
        \App\Models\CourseGamificationRule::query()->delete();

        $student = User::factory()->create(['role' => 'student', 'lifetime_xp' => 0, 'spendable_coins' => 0]);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $service = app(GamificationService::class);

        $course = Course::query()->create([
            'title' => 'Unconfigured Course',
            'code' => 'UNC101',
            'is_active' => true,
        ]);

        $enr = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);

        // Daily login
        $service->recordDailyLogin($student);

        // Assignment submission
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Unconfigured Assignment',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'My solution',
            'submitted_at' => now(),
        ]);
        $submission->update(['grade' => '100%']);

        // Attendance
        $session = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Class 1',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);
        $attendance = Attendance::create([
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => 'present',
        ]);
        $service->awardAttendance($student, $attendance);

        // Course completion
        $enr->markAsCompleted($instructor);
        $service->awardCourseCompleted($student, $course);

        // Direct badge award attempt without active rules
        $badgeResult = $service->awardBadge($student, 'streak_7');
        $this->assertNull($badgeResult);

        // Assert zero transactions and zero badges
        $this->assertSame(0, XpTransaction::where('user_id', $student->id)->count());
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('user_badge')->where('user_id', $student->id)->count());
        $this->assertSame(0, (int) $student->fresh()->lifetime_xp);
        $this->assertSame(0, (int) $student->fresh()->spendable_coins);
    }

    public function test_points_and_badges_awarded_only_from_active_rules(): void
    {
        // 1. Clear all rules
        \App\Models\CourseGamificationRule::query()->delete();

        $student = User::factory()->create(['role' => 'student', 'lifetime_xp' => 0, 'spendable_coins' => 0]);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $service = app(GamificationService::class);

        $course = Course::query()->create([
            'title' => 'Configured Course',
            'code' => 'CFG101',
            'is_active' => true,
        ]);

        $enr = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);

        // 2. Instructor sets ONLY course completion rule (active, 120 XP, 36 coins), keeping all others inactive
        \App\Models\CourseGamificationRule::query()->create([
            'course_id' => $course->id,
            'is_active' => true,
            'rules' => [
                [
                    'activity_key' => 'course_completion',
                    'activity_name' => 'Course Completion',
                    'category' => 'Core Milestones',
                    'xp' => 120,
                    'coins' => 36,
                    'limit' => '1 time per course',
                    'enabled' => true,
                ],
                [
                    'activity_key' => 'assignment_ontime',
                    'activity_name' => 'On-Time Submission',
                    'category' => 'Assignments',
                    'xp' => 30,
                    'coins' => 9,
                    'limit' => '',
                    'enabled' => false, // explicitly disabled
                ],
            ],
        ]);

        // On-time submission should yield NO points or badges because it is disabled
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Assignment 1',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Work',
            'submitted_at' => now(),
        ]);

        $this->assertSame(0, XpTransaction::where('user_id', $student->id)->count());

        // Course completion is enabled -> awards exact configured 120 XP / 36 coins and Graduate badge
        $enr->markAsCompleted($instructor);
        $service->awardCourseCompleted($student, $course);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'course_completed',
            'amount_xp' => 120,
            'amount_coins' => 36,
        ]);
        $this->assertTrue($student->badges()->where('badges.key', 'course_completed')->exists());
    }
}
