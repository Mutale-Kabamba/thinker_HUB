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
    }

    public function test_daily_login_awards_xp_and_is_idempotent(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $service = app(GamificationService::class);

        $service->recordDailyLogin($student);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'daily_login',
            'points' => 10,
        ]);

        $initialXp = $student->xpTotal();
        $this->assertSame(10, $initialXp);

        // Second call on the same day must not create duplicate transaction
        $service->recordDailyLogin($student);
        $this->assertSame(10, $student->xpTotal());
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
            'points' => 40,
        ]);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'assignment_early',
            'points' => 20,
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
            'source' => 'assignment_passed',
            'points' => 50,
        ]);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'assignment_distinction',
            'points' => 40,
        ]);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student->id,
            'source' => 'assignment_perfect',
            'points' => 60,
        ]);

        // Perfectionist badge awarded
        $this->assertTrue($student->badges()->where('badges.key', 'first_perfect_quiz')->exists());
    }

    public function test_attendance_present_awards_xp(): void
    {
        $student = User::factory()->create(['role' => 'student']);
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

        $friendship = Friendship::create([
            'user_id' => $student1->id,
            'friend_id' => $student2->id,
            'status' => 'pending',
        ]);

        // Accept friendship
        $friendship->update(['status' => 'accepted']);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student1->id,
            'source' => 'study_buddy',
            'points' => 15,
        ]);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $student2->id,
            'source' => 'study_buddy',
            'points' => 15,
        ]);
    }

    public function test_opportunity_hub_submission_awards_xp_and_innovator_badge(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $service = app(GamificationService::class);

        $service->awardOpportunitySubmission($student, 999, 'AI Study Tool Pitch');

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
}
