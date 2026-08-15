<?php

namespace Tests\Feature;

use App\Livewire\ClaimHub\Storefront;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Badge;
use App\Models\ClaimItem;
use App\Models\ClaimRequest;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseTiedGamificationAndClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Badge::query()->delete();
        Badge::create([
            'key' => 'course_completed',
            'name' => 'Graduate',
            'description' => 'Completed a course',
            'category' => 'academic',
            'icon' => 'academic-cap',
            'xp_reward' => 150,
        ]);
    }

    public function test_course_custom_gamification_rules_are_applied(): void
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
            'title' => 'Advanced AI & Python Mastery',
            'code' => 'AI-401',
            'is_active' => true,
            'gamification_settings' => [
                'quiz_xp' => 100, // Custom 100 XP instead of 50
                'quiz_coins' => 20, // Custom 20 TC instead of 10
                'assignment_xp' => 80, // Custom 80 XP instead of 40
                'assignment_coins' => 25, // Custom 25 TC instead of 10
                'attendance_xp' => 50, // Custom 50 XP instead of 25
                'attendance_coins' => 15, // Custom 15 TC instead of 5
                'course_completion_xp' => 500, // Custom 500 XP instead of 300
                'course_completion_coins' => 100, // Custom 100 TC instead of 50
            ],
        ]);

        // 1. Quiz Pass with custom course rules
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Midterm Quiz',
            'passing_score' => 60,
            'is_published' => true,
        ]);
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 70,
            'total_questions' => 10,
            'passed' => true,
            'percentage' => 70,
        ]);

        $service->awardQuizPassed($student, $attempt);
        $student->refresh();

        $this->assertSame(100, $student->lifetime_xp);
        $this->assertSame(20, $student->spendable_coins);

        // 2. Assignment Submission with custom course rules
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Deep Learning Project',
            'due_date' => now()->addDays(5)->toDateString(),
            'is_published' => true,
        ]);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $service->awardSubmission($student, $submission);
        $student->refresh();

        // 100 (quiz) + 80 (custom assignment XP) + 10 (early submission) = 190 XP
        // 20 (quiz) + 25 (custom assignment TC) + 3 (early submission 30%) = 48 TC
        $this->assertSame(190, $student->lifetime_xp);
        $this->assertSame(48, $student->spendable_coins);

        // 3. Attendance with custom course rules
        $session = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Neural Networks Deep Dive',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);
        $attendance = Attendance::create([
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $service->awardAttendance($student, $attendance);
        $student->refresh();

        // 190 + 50 (custom attendance XP) = 240 XP
        // 48 + 15 (custom attendance TC) = 63 TC
        $this->assertSame(240, $student->lifetime_xp);
        $this->assertSame(63, $student->spendable_coins);

        // 4. Course completion with custom course rules
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => now(),
        ]);

        $service->awardCourseCompleted($student, $course);
        $student->refresh();

        // 240 + 500 (custom completion XP) + 150 (badge XP) = 890 XP
        // 65 + 100 + 15 = 180 TC, but strictly capped by Daily Cap of 150 TC
        $this->assertSame(890, $student->lifetime_xp);
        $this->assertSame(150, $student->spendable_coins);
    }

    public function test_storefront_displays_and_filters_course_tied_rewards(): void
    {
        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'spendable_coins' => 1000,
        ]);

        $course1 = Course::create(['title' => 'Laravel Mastery', 'code' => 'LAR-101', 'is_active' => true]);
        $course2 = Course::create(['title' => 'React Native Pro', 'code' => 'RN-201', 'is_active' => true]);

        // Student enrolled only in Course 1
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course1->id,
        ]);

        $generalItem = ClaimItem::create([
            'title' => 'General Data Pack',
            'category' => ClaimItem::CATEGORY_DATA,
            'coin_cost' => 100,
            'stock_quantity' => -1,
            'is_active' => true,
        ]);

        $course1Item = ClaimItem::create([
            'course_id' => $course1->id,
            'title' => 'Laravel Mentorship Hour',
            'category' => ClaimItem::CATEGORY_PERK,
            'coin_cost' => 300,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $course2Item = ClaimItem::create([
            'course_id' => $course2->id,
            'title' => 'React Native Physical Swag',
            'category' => ClaimItem::CATEGORY_MERCH,
            'coin_cost' => 400,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        // Student visits storefront: sees general item and course1 item, but not course2 item (since not enrolled)
        Livewire::actingAs($student)
            ->test(Storefront::class)
            ->assertSee('General Data Pack')
            ->assertSee('Laravel Mentorship Hour')
            ->assertDontSee('React Native Physical Swag')
            // Can filter specifically by course1
            ->set('selectedCourse', (string) $course1->id)
            ->assertSee('Laravel Mentorship Hour')
            ->assertDontSee('General Data Pack')
            // Can redeem course1 reward
            ->call('openRedeemModal', $course1Item->id)
            ->set('phoneNumber', '0977000000')
            ->call('redeemItem');

        $this->assertDatabaseHas('claim_requests', [
            'user_id' => $student->id,
            'claim_item_id' => $course1Item->id,
            'coins_spent' => 300,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);

        $student->refresh();
        $this->assertSame(700, $student->spendable_coins);
    }

    public function test_claim_hub_route_redirects_inside_student_platform(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/claim-hub');
        $response->assertRedirect('/student/claim-hub');
    }

    public function test_instructor_scoped_claim_requests_and_fulfillment(): void
    {
        /** @var User $instructor */
        $instructor = User::factory()->create(['role' => 'instructor', 'name' => 'Prof. Mutale']);

        $course = Course::create([
            'title' => 'Instructor Course',
            'code' => 'INS-101',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $item = ClaimItem::create([
            'course_id' => $course->id,
            'title' => '1-on-1 Code Review',
            'category' => ClaimItem::CATEGORY_PERK,
            'coin_cost' => 250,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);

        /** @var User $student */
        $student = User::factory()->create(['role' => 'student', 'spendable_coins' => 500]);

        $claimRequest = ClaimRequest::create([
            'user_id' => $student->id,
            'claim_item_id' => $item->id,
            'coins_spent' => 250,
            'status' => ClaimRequest::STATUS_PENDING,
            'phone_number' => '0966112233',
        ]);

        // Verify relationship
        $this->assertSame($course->id, $claimRequest->claimItem->course_id);
    }

    public function test_course_gamification_rule_matrix_resource_routes_for_admin_and_instructor(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $adminResponse = $this->actingAs($admin)->get('/manage/course-gamification-rules');
        $adminResponse->assertSuccessful();

        /** @var User $instructor */
        $instructor = User::factory()->create(['role' => 'instructor']);
        Course::create([
            'title' => 'Instructor Matrix Course',
            'code' => 'IMC-101',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $instructorResponse = $this->actingAs($instructor)->get('/teach/course-gamification-rule-resource/course-gamification-rules');
        $instructorResponse->assertSuccessful();
    }

    public function test_dedicated_course_gamification_rule_matrix_applies_properly(): void
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
            'title' => 'Data Science & Analytics',
            'code' => 'DSA-201',
            'is_active' => true,
        ]);

        // Create dedicated CourseGamificationRule record
        \App\Models\CourseGamificationRule::create([
            'course_id' => $course->id,
            'name' => 'DSA Custom Point Matrix',
            'rules' => [
                'quiz_score_80' => ['xp' => 120, 'coins' => 36, 'enabled' => true],
                'assignment_ontime' => ['xp' => 90, 'coins' => 27, 'enabled' => true],
                'course_completion' => ['xp' => 600, 'coins' => 180, 'enabled' => true],
            ],
            'is_active' => true,
        ]);

        // 1. Quiz test
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'SQL Basics Quiz',
            'passing_score' => 60,
            'is_published' => true,
        ]);
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 85,
            'total_questions' => 10,
            'passed' => true,
            'percentage' => 85,
        ]);

        $service->awardQuizPassed($student, $attempt);
        $student->refresh();

        $this->assertSame(120, $student->lifetime_xp);
        $this->assertSame(36, $student->spendable_coins);
    }

    public function test_custom_actions_and_repeater_list_rules_with_30_percent_coins(): void
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
            'title' => 'Web3 Bootcamp',
            'code' => 'W3-301',
            'is_active' => true,
        ]);

        // Repeater list structure with custom action and auto 30% coins
        \App\Models\CourseGamificationRule::create([
            'course_id' => $course->id,
            'name' => 'Web3 Custom CRUD Matrix',
            'rules' => [
                [
                    'activity_key' => 'quiz_score_80',
                    'activity_name' => 'Quiz Score 80%+',
                    'category' => 'Quizzes & Assessments',
                    'xp' => 100,
                    'coins' => 30, // 30% of 100
                    'limit' => 'First passing attempt',
                    'enabled' => true,
                ],
                [
                    'activity_key' => 'custom_hackathon_winner',
                    'activity_name' => 'Hackathon 1st Place',
                    'category' => 'Custom Actions',
                    'xp' => 300,
                    'coins' => 90, // 30% of 300
                    'limit' => 'Per hackathon',
                    'enabled' => true,
                ],
            ],
            'is_active' => true,
        ]);

        $rule = \App\Models\CourseGamificationRule::getRuleForCourse($course, 'quiz_score_80');
        $this->assertSame(100, $rule['xp']);
        $this->assertSame(30, $rule['coins']);

        $customRule = \App\Models\CourseGamificationRule::getRuleForCourse($course, 'custom_hackathon_winner');
        $this->assertSame(300, $customRule['xp']);
        $this->assertSame(90, $customRule['coins']);
    }

    public function test_create_rule_matrix_page_renders_crud_table(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/manage/course-gamification-rules/create');
        $response->assertSuccessful();

        /** @var User $instructor */
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'title' => 'Instructor Course',
            'code' => 'INS-202',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $instResponse = $this->actingAs($instructor)->get('/teach/course-gamification-rule-resource/course-gamification-rules/create');
        $instResponse->assertSuccessful();
    }

    public function test_storefront_renders_point_earning_matrix_tab(): void
    {
        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'spendable_coins' => 200,
        ]);

        $course = Course::create([
            'title' => 'Advanced Robotics',
            'code' => 'ROB-401',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Custom gamification rule for this course
        \App\Models\CourseGamificationRule::create([
            'course_id' => $course->id,
            'name' => 'Robotics Custom Points',
            'rules' => [
                [
                    'activity_key' => 'custom_robot_build',
                    'activity_name' => 'Complete Robot Assembly',
                    'category' => 'Custom Actions',
                    'xp' => 250,
                    'coins' => 75,
                    'limit' => 'Per build milestone',
                    'enabled' => true,
                ],
                [
                    'activity_key' => 'quiz_score_100',
                    'activity_name' => 'Perfect Quiz Score (100%)',
                    'category' => 'Quizzes & Assessments',
                    'xp' => 80,
                    'coins' => 24,
                    'limit' => 'First attempt',
                    'enabled' => true,
                ],
            ],
            'is_active' => true,
        ]);

        Livewire::actingAs($student)
            ->test(Storefront::class)
            ->assertSee('Course Rewards')
            ->assertSee('Point Earning Matrix')
            // Switch to matrix tab
            ->call('switchTab', 'matrix')
            ->assertSet('activeTab', 'matrix')
            ->assertSee('Complete Robot Assembly')
            ->assertSee('+250 XP')
            ->assertSee('75 TC')
            ->assertSee('Perfect Quiz Score (100%)')
            ->assertSee('+80 XP')
            ->assertSee('24 TC');
    }
}
