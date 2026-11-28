<?php

namespace Tests\Feature;

use App\Ai\Agents\AdminSystemCopilot;
use App\Ai\Agents\InstructorAssistant;
use App\Ai\Agents\PublicCourseAdvisor;
use App\Ai\Agents\StudentStudyBuddy;
use App\Ai\Tools\Admin\DispatchSystemBroadcastTool;
use App\Ai\Tools\Admin\ManagePlatformCoursesTool;
use App\Ai\Tools\Admin\ManagePromoCodesTool;
use App\Ai\Tools\Admin\ManageSystemEnrollmentsTool;
use App\Ai\Tools\Admin\ManageUserAccountTool;
use App\Ai\Tools\Admin\ProcessClaimRequestsTool;
use App\Ai\Tools\Admin\ReviewInstructorApplicationsTool;
use App\Ai\Tools\Admin\SystemFinanceAnalyticsTool;
use App\Ai\Tools\Admin\SystemOverviewTool;
use App\Ai\Tools\Instructor\CreateCourseSessionTool;
use App\Ai\Tools\Instructor\GetCohortPerformanceSummaryTool;
use App\Ai\Tools\Instructor\InspectQuizAnalyticsTool;
use App\Ai\Tools\Instructor\ListInstructorSessionsTool;
use App\Ai\Tools\Instructor\MarkSessionAttendanceTool;
use App\Ai\Tools\Instructor\ReviewPendingSubmissionsTool;
use App\Ai\Tools\Instructor\SearchCourseCurriculumTool;
use App\Ai\Tools\Instructor\SearchStudentProgressTool;
use App\Ai\Tools\Instructor\SendCohortBroadcastTool;
use App\Ai\Tools\Instructor\UpdateCourseSessionTool;
use App\Ai\Tools\Public\GetPlatformInfoTool;
use App\Ai\Tools\Public\SearchArticlesTool;
use App\Ai\Tools\Public\SearchOpportunitiesTool;
use App\Ai\Tools\Public\SearchPublicCoursesTool;
use App\Ai\Tools\Student\ReviewCompletedQuizAttemptsTool;
use App\Filament\Instructor\Pages\AiAssistant;
use App\Filament\Pages\AiCopilot;
use App\Filament\Student\Pages\StudyBuddy;
use App\Livewire\Ai\PortalFloatingChatWidget;
use App\Livewire\Ai\PublicChatWidget;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Broadcast;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\HubPost;
use App\Models\InstructorApplication;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Tools\Request;
use Livewire\Livewire;
use Tests\TestCase;

class AiAssistantsAndToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_public_courses_tool_returns_active_courses_with_intakes_and_excludes_inactive(): void
    {
        $activeCourse = Course::query()->create([
            'title' => 'Python for Data Science',
            'code' => 'PY-301',
            'fees' => 3500,
            'is_active' => true,
        ]);

        CourseIntake::query()->create([
            'course_id' => $activeCourse->id,
            'name' => 'January 2027 Cohort',
            'start_date' => now()->addMonth(),
            'status' => CourseIntake::STATUS_UPCOMING,
            'is_active' => false,
        ]);

        $inactiveCourse = Course::query()->create([
            'title' => 'Legacy Cobol',
            'code' => 'COB-001',
            'is_active' => false,
        ]);

        $tool = new SearchPublicCoursesTool;
        $result = (string) $tool->handle(new Request(['keyword' => 'Python']));

        $this->assertStringContainsString('Python for Data Science', $result);
        $this->assertStringContainsString('PY-301', $result);
        $this->assertStringContainsString('January 2027 Cohort', $result);
        $this->assertStringNotContainsString('Legacy Cobol', $result);

        // Searching for inactive course should return nothing
        $resultInactive = (string) $tool->handle(new Request(['keyword' => 'Cobol']));
        $this->assertStringContainsString('No active courses matching your criteria', $resultInactive);
    }

    public function test_public_chat_widget_integrates_with_public_course_advisor_fake(): void
    {
        PublicCourseAdvisor::fake([
            'Welcome to think.er HUB! We offer courses in Full-Stack Web Development and Python.',
        ]);

        Livewire::test(PublicChatWidget::class)
            ->set('isOpen', true)
            ->set('message', 'What courses do you offer?')
            ->call('sendMessage')
            ->assertSet('message', '')
            ->assertCount('messages', 2)
            ->assertSee('Welcome to think.er HUB');

        PublicCourseAdvisor::assertPrompted(function (AgentPrompt $prompt): bool {
            return str_contains($prompt->prompt, 'What courses do you offer?');
        });
    }

    public function test_review_completed_quiz_attempts_tool_security_boundaries(): void
    {
        $studentA = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $studentB = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'Fullstack Mastery',
            'code' => 'FS-101',
            'is_active' => true,
        ]);

        $quiz = Quiz::query()->create([
            'course_id' => $course->id,
            'title' => 'DOM Manipulation Quiz',
            'show_results' => true,
            'is_active' => true,
        ]);

        $question = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'type' => 'theory',
            'question' => 'What is the Virtual DOM?',
            'explanation' => 'An in-memory representation of real DOM elements.',
            'points' => 5,
            'sort_order' => 1,
        ]);

        // Student A completed attempt
        $completedAttempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $studentA->id,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
            'score' => 5,
            'total_points' => 5,
            'percentage' => 100,
            'passed' => true,
        ]);

        QuizAnswer::query()->create([
            'quiz_attempt_id' => $completedAttempt->id,
            'quiz_question_id' => $question->id,
            'text_answer' => 'Virtual DOM is a lightweight copy of the real DOM tree.',
            'is_correct' => true,
            'points_earned' => 5,
            'feedback' => 'Excellent understanding of Virtual DOM.',
        ]);

        // Student A in-progress attempt (should NOT be leaked)
        $inProgressAttempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $studentA->id,
            'started_at' => now(),
            'completed_at' => null,
        ]);

        // Tool for Student A
        $toolA = new ReviewCompletedQuizAttemptsTool($studentA);
        $resultA = (string) $toolA->handle(new Request([]));

        $this->assertStringContainsString('DOM Manipulation Quiz', $resultA);
        $this->assertStringContainsString('An in-memory representation of real DOM elements', $resultA);
        $this->assertStringContainsString('Virtual DOM is a lightweight copy', $resultA);

        // Tool for Student B (must NOT see Student A's completed attempt)
        $toolB = new ReviewCompletedQuizAttemptsTool($studentB);
        $resultB = (string) $toolB->handle(new Request([]));

        $this->assertStringContainsString('No completed quiz attempts with published results were found', $resultB);
    }

    public function test_student_study_buddy_fake_in_filament_page(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        StudentStudyBuddy::fake([
            'The Model-View-Controller pattern divides an application into three interconnected parts.',
        ]);

        $this->actingAs($student);

        Livewire::test(StudyBuddy::class)
            ->set('message', 'Explain MVC architecture simply.')
            ->call('sendMessage')
            ->assertSet('message', '')
            ->assertSee('Model-View-Controller pattern');

        StudentStudyBuddy::assertPrompted(function (AgentPrompt $prompt): bool {
            return str_contains($prompt->prompt, 'Explain MVC architecture');
        });
    }

    public function test_mark_session_attendance_tool_syncs_roster_marks_batch_and_overrides(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $student1 = User::factory()->create(['name' => 'Alice Musonda', 'email' => 'alice@test.com', 'role' => 'student', 'is_active' => true]);
        $student2 = User::factory()->create(['name' => 'Bob Tembo', 'email' => 'bob@test.com', 'role' => 'student', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'Software Engineering',
            'code' => 'SE-201',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        Enrollment::query()->create(['user_id' => $student1->id, 'course_id' => $course->id, 'status' => 'enrolled', 'is_active' => true]);
        Enrollment::query()->create(['user_id' => $student2->id, 'course_id' => $course->id, 'status' => 'enrolled', 'is_active' => true]);

        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Agile Scrum Workshop',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => CourseSession::STATUS_SCHEDULED,
        ]);

        $tool = new MarkSessionAttendanceTool($instructor, [$course->id]);

        $response = (string) $tool->handle(new Request([
            'course_session_id' => $session->id,
            'mark_all_status' => Attendance::STATUS_PRESENT,
            'individual_overrides' => [
                [
                    'student_name_or_email' => 'Bob Tembo',
                    'status' => Attendance::STATUS_APOLOGY,
                    'notes' => 'Medical appointment',
                ],
            ],
        ]));

        $this->assertStringContainsString('"success": true', $response);

        // Check attendance records
        $attAlice = Attendance::query()->where('course_session_id', $session->id)->where('user_id', $student1->id)->first();
        $this->assertNotNull($attAlice);
        $this->assertEquals(Attendance::STATUS_PRESENT, $attAlice->status);

        $attBob = Attendance::query()->where('course_session_id', $session->id)->where('user_id', $student2->id)->first();
        $this->assertNotNull($attBob);
        $this->assertEquals(Attendance::STATUS_APOLOGY, $attBob->status);
        $this->assertEquals('Medical appointment', $attBob->notes);

        // Session should be marked completed
        $session->refresh();
        $this->assertEquals(CourseSession::STATUS_COMPLETED, $session->status);
    }

    public function test_update_course_session_tool_reschedules_session(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'Web Dev',
            'code' => 'WD-101',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'CSS Flexbox Lab',
            'session_date' => '2026-11-10',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => CourseSession::STATUS_SCHEDULED,
        ]);

        $tool = new UpdateCourseSessionTool($instructor, [$course->id]);

        $response = (string) $tool->handle(new Request([
            'course_session_id' => $session->id,
            'status' => CourseSession::STATUS_RESCHEDULED,
            'rescheduled_date' => '2026-11-12',
            'rescheduled_start_time' => '14:00',
            'rescheduled_end_time' => '16:00',
            'meeting_link' => 'https://meet.google.com/xyz-test',
        ]));

        $this->assertStringContainsString('"success": true', $response);

        $session->refresh();
        $this->assertEquals('rescheduled', $session->status);
        $this->assertEquals('2026-11-12', $session->rescheduled_date->toDateString());
        $this->assertEquals('14:00', $session->rescheduled_start_time);
        $this->assertEquals('https://meet.google.com/xyz-test', $session->meeting_link);
    }

    public function test_instructor_tools_enforce_course_authorization_boundary(): void
    {
        $instructor1 = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $instructor2 = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $course1 = Course::query()->create(['title' => 'Course 1', 'code' => 'C1', 'course_by' => (string) $instructor1->id, 'is_active' => true]);
        $course2 = Course::query()->create(['title' => 'Course 2', 'code' => 'C2', 'course_by' => (string) $instructor2->id, 'is_active' => true]);

        $session2 = CourseSession::query()->create([
            'course_id' => $course2->id,
            'instructor_id' => $instructor2->id,
            'title' => 'Instructor 2 Session',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => CourseSession::STATUS_SCHEDULED,
        ]);

        // Instructor 1 attempts to mark attendance on Instructor 2's session
        $attendanceTool = new MarkSessionAttendanceTool($instructor1, [$course1->id]);
        $resultAtt = (string) $attendanceTool->handle(new Request([
            'course_session_id' => $session2->id,
            'mark_all_status' => 'present',
        ]));

        $this->assertStringContainsString('Error: Unauthorized', $resultAtt);

        // Instructor 1 attempts to reschedule Instructor 2's session
        $updateTool = new UpdateCourseSessionTool($instructor1, [$course1->id]);
        $resultUpdate = (string) $updateTool->handle(new Request([
            'course_session_id' => $session2->id,
            'title' => 'Hijacked Title',
        ]));

        $this->assertStringContainsString('Error: Unauthorized', $resultUpdate);

        $session2->refresh();
        $this->assertEquals('Instructor 2 Session', $session2->title);
        $this->assertEquals(CourseSession::STATUS_SCHEDULED, $session2->status);
    }

    public function test_instructor_assistant_fake_in_filament_page(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        InstructorAssistant::fake([
            'I have listed your sessions for today.',
        ]);

        $this->actingAs($instructor);

        Livewire::test(AiAssistant::class)
            ->set('message', 'List my sessions scheduled for today')
            ->call('sendMessage')
            ->assertSet('message', '')
            ->assertSee('I have listed your sessions for today');

        InstructorAssistant::assertPrompted(function (AgentPrompt $prompt): bool {
            return str_contains($prompt->prompt, 'List my sessions scheduled for today');
        });
    }

    public function test_candice_public_advisor_registers_all_expanded_tools(): void
    {
        $advisor = new PublicCourseAdvisor;
        $tools = $advisor->tools();

        $toolClasses = array_map(fn ($tool) => get_class($tool), $tools);

        $this->assertContains(SearchPublicCoursesTool::class, $toolClasses);
        $this->assertContains(GetPlatformInfoTool::class, $toolClasses);
        $this->assertContains(SearchOpportunitiesTool::class, $toolClasses);
        $this->assertContains(SearchArticlesTool::class, $toolClasses);

        $instructions = $advisor->instructions();
        $this->assertStringContainsString('Student Portal', $instructions);
        $this->assertStringContainsString('Instructor Portal', $instructions);
        $this->assertStringContainsString('Parent Portal', $instructions);
        $this->assertStringContainsString('Contributor Studio', $instructions);
        $this->assertStringContainsString('Admin Portal', $instructions);
        $this->assertStringContainsString('Career Services', $instructions);
        $this->assertStringContainsString('Gamification', $instructions);
    }

    public function test_get_platform_info_tool_returns_comprehensive_institutional_knowledge(): void
    {
        $tool = new GetPlatformInfoTool;
        $result = (string) $tool->handle(new Request([]));

        $data = json_decode($result, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('platform', $data);
        $this->assertArrayHasKey('core_portals', $data);
        $this->assertArrayHasKey('learning_tracks', $data);
        $this->assertArrayHasKey('certifications_and_credentials', $data);
        $this->assertArrayHasKey('delivery_modes', $data);
        $this->assertArrayHasKey('gamification_and_rewards', $data);
        $this->assertArrayHasKey('tuition_and_payments', $data);
        $this->assertArrayHasKey('admissions_and_onboarding', $data);

        $this->assertStringContainsString('think.er HUB', $data['platform']['name']);
        $this->assertArrayHasKey('Student Portal (/learn)', $data['core_portals']);
        $this->assertArrayHasKey('Parent Portal (/parent)', $data['core_portals']);
        $this->assertStringContainsString('Mobile Money', $data['tuition_and_payments']['Supported Payment Methods']);
    }

    public function test_search_opportunities_tool_filters_active_and_excludes_expired(): void
    {
        Opportunity::query()->create([
            'title' => 'Junior Flutter Developer Internship',
            'type' => 'Job',
            'provider' => 'Lusaka Tech Labs',
            'is_published' => true,
            'expires_at' => now()->addDays(20),
        ]);

        Opportunity::query()->create([
            'title' => 'Expired AI Hackathon',
            'type' => 'Event',
            'provider' => 'Old Partner',
            'is_published' => true,
            'expires_at' => now()->subDays(5),
        ]);

        Opportunity::query()->create([
            'title' => 'Unpublished Scholarship',
            'type' => 'Scholarship',
            'provider' => 'Private Donor',
            'is_published' => false,
            'expires_at' => now()->addDays(30),
        ]);

        $tool = new SearchOpportunitiesTool;
        $result = (string) $tool->handle(new Request(['keyword' => 'Flutter']));

        $this->assertStringContainsString('Junior Flutter Developer Internship', $result);
        $this->assertStringContainsString('Lusaka Tech Labs', $result);
        $this->assertStringNotContainsString('Expired AI Hackathon', $result);
        $this->assertStringNotContainsString('Unpublished Scholarship', $result);
    }

    public function test_search_articles_tool_returns_published_community_guides(): void
    {
        $author = User::factory()->create(['role' => 'blogger', 'is_active' => true]);

        HubPost::query()->create([
            'title' => 'Getting Started with Laravel and Livewire',
            'slug' => 'getting-started-laravel-livewire',
            'category' => 'Programming',
            'type' => 'blog',
            'excerpt' => 'Learn the fundamentals of reactive web applications.',
            'content' => 'Full guide content goes here.',
            'is_published' => true,
            'author_id' => $author->id,
        ]);

        HubPost::query()->create([
            'title' => 'Draft Unpublished Post',
            'slug' => 'draft-unpublished-post',
            'category' => 'Technology',
            'type' => 'blog',
            'excerpt' => 'Draft content.',
            'is_published' => false,
            'author_id' => $author->id,
        ]);

        $tool = new SearchArticlesTool;
        $result = (string) $tool->handle(new Request(['keyword' => 'Livewire']));

        $this->assertStringContainsString('Getting Started with Laravel and Livewire', $result);
        $this->assertStringContainsString('Programming', $result);
        $this->assertStringNotContainsString('Draft Unpublished Post', $result);
    }

    public function test_instructor_assistant_registers_all_expanded_tools(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $assistant = new InstructorAssistant($instructor, [10, 20]);
        $tools = $assistant->tools();

        $toolClasses = array_map(fn ($tool) => get_class($tool), $tools);

        $this->assertContains(ListInstructorSessionsTool::class, $toolClasses);
        $this->assertContains(MarkSessionAttendanceTool::class, $toolClasses);
        $this->assertContains(UpdateCourseSessionTool::class, $toolClasses);
        $this->assertContains(CreateCourseSessionTool::class, $toolClasses);
        $this->assertContains(GetCohortPerformanceSummaryTool::class, $toolClasses);
        $this->assertContains(SearchStudentProgressTool::class, $toolClasses);
        $this->assertContains(ReviewPendingSubmissionsTool::class, $toolClasses);
        $this->assertContains(InspectQuizAnalyticsTool::class, $toolClasses);
        $this->assertContains(SendCohortBroadcastTool::class, $toolClasses);
        $this->assertContains(SearchCourseCurriculumTool::class, $toolClasses);

        $instructions = $assistant->instructions();
        $this->assertStringContainsString('Student Progress', $instructions);
        $this->assertStringContainsString('Assignment Grading', $instructions);
        $this->assertStringContainsString('Quiz Performance', $instructions);
        $this->assertStringContainsString('Cohort Broadcasts', $instructions);
        $this->assertStringContainsString('Curriculum, Syllabus', $instructions);
    }

    public function test_search_student_progress_tool_finds_enrolled_student_with_attendance_and_quizzes(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Chanda Mwansa', 'email' => 'chanda@example.com']);

        $course = Course::query()->create([
            'title' => 'Mobile App Engineering with Flutter',
            'code' => 'FLT-200',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        Enrollment::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => 'active',
        ]);

        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'State Management in Flutter',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => CourseSession::STATUS_COMPLETED,
        ]);

        Attendance::query()->create([
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $tool = new SearchStudentProgressTool($instructor, [$course->id]);
        $result = (string) $tool->handle(new Request(['query' => 'Chanda']));

        $this->assertStringContainsString('Chanda Mwansa', $result);
        $this->assertStringContainsString('Mobile App Engineering with Flutter', $result);
        $this->assertStringContainsString('"rate_percentage": 100', $result);
    }

    public function test_review_pending_submissions_tool_lists_and_grades_submission(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Lupiya Banda']);

        $course = Course::query()->create([
            'title' => 'Full-Stack Web Dev',
            'code' => 'FSW-100',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'name' => 'Build a REST API',
            'description' => 'Create Laravel API endpoints with Sanctum auth',
        ]);

        $submission = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'https://github.com/lupiya/api-project',
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $tool = new ReviewPendingSubmissionsTool($instructor, [$course->id]);

        // 1. List pending submissions
        $listResult = (string) $tool->handle(new Request(['status' => 'pending']));
        $this->assertStringContainsString('Build a REST API', $listResult);
        $this->assertStringContainsString('Lupiya Banda', $listResult);

        // 2. Grade submission
        $gradeResult = (string) $tool->handle(new Request([
            'submission_id' => $submission->id,
            'grade' => '92',
            'feedback' => 'Excellent work on Sanctum authentication and API resource transformers!',
        ]));

        $this->assertStringContainsString('"success": true', $gradeResult);

        $submission->refresh();
        $this->assertEquals('92', $submission->grade);
        $this->assertEquals('Graded', $submission->status);
        $this->assertStringContainsString('Sanctum', $submission->feedback);
    }

    public function test_inspect_quiz_analytics_tool_summarizes_cohort_performance(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'Database Architecture',
            'code' => 'DB-201',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $quiz = Quiz::query()->create([
            'course_id' => $course->id,
            'title' => 'SQL Indexing & Normalization Quiz',
            'pass_percentage' => 70,
            'is_active' => true,
        ]);

        $student1 = User::factory()->create(['role' => 'student', 'name' => 'Passed Student']);
        $student2 = User::factory()->create(['role' => 'student', 'name' => 'Failed Student']);

        Enrollment::query()->create(['course_id' => $course->id, 'user_id' => $student1->id, 'status' => 'active']);
        Enrollment::query()->create(['course_id' => $course->id, 'user_id' => $student2->id, 'status' => 'active']);

        QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student1->id,
            'score' => 85,
            'total_points' => 100,
            'percentage' => 85,
            'passed' => true,
            'completed_at' => now(),
        ]);

        QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student2->id,
            'score' => 45,
            'total_points' => 100,
            'percentage' => 45,
            'passed' => false,
            'completed_at' => now(),
        ]);

        $tool = new InspectQuizAnalyticsTool($instructor, [$course->id]);
        $result = (string) $tool->handle(new Request(['quiz_id' => $quiz->id]));

        $this->assertStringContainsString('SQL Indexing & Normalization Quiz', $result);
        $this->assertStringContainsString('"average_score_percentage": 65', $result);
        $this->assertStringContainsString('"pass_rate_percentage": 50', $result);
        $this->assertStringContainsString('Failed Student', $result);
    }

    public function test_send_cohort_broadcast_tool_creates_broadcast_record(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'DevOps & Cloud Deployments',
            'code' => 'DEV-401',
            'course_by' => (string) $instructor->id,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'email' => 'devops_student@example.com']);
        Enrollment::query()->create(['course_id' => $course->id, 'user_id' => $student->id, 'status' => 'active']);

        $tool = new SendCohortBroadcastTool($instructor, [$course->id]);
        $result = (string) $tool->handle(new Request([
            'course_id' => $course->id,
            'subject' => 'Docker Lab Environment Update',
            'message' => 'Please pull the latest container image before tomorrow morning class session.',
        ]));

        $this->assertStringContainsString('"success": true', $result);
        $this->assertStringContainsString('Docker Lab Environment Update', $result);

        $this->assertDatabaseHas('broadcasts', [
            'course_id' => $course->id,
            'user_id' => $instructor->id,
            'subject' => 'Docker Lab Environment Update',
        ]);
    }

    public function test_admin_system_copilot_registers_all_expanded_system_tools(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $copilot = new AdminSystemCopilot($admin);
        $tools = $copilot->tools();

        $toolClasses = array_map(fn ($tool) => get_class($tool), $tools);

        $this->assertContains(SystemOverviewTool::class, $toolClasses);
        $this->assertContains(ManageUserAccountTool::class, $toolClasses);
        $this->assertContains(ReviewInstructorApplicationsTool::class, $toolClasses);
        $this->assertContains(SystemFinanceAnalyticsTool::class, $toolClasses);
        $this->assertContains(ManagePlatformCoursesTool::class, $toolClasses);
        $this->assertContains(ManageSystemEnrollmentsTool::class, $toolClasses);
        $this->assertContains(ManagePromoCodesTool::class, $toolClasses);
        $this->assertContains(ProcessClaimRequestsTool::class, $toolClasses);
        $this->assertContains(DispatchSystemBroadcastTool::class, $toolClasses);
        $this->assertCount(9, $tools);

        $this->assertStringContainsString('System-Level Manager and AI Operations Co-Pilot', $copilot->instructions());
    }

    public function test_system_overview_tool_aggregates_platform_vitals(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        User::factory()->count(2)->create(['role' => 'student', 'is_active' => true]);
        User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'Executive Cloud Architecture',
            'code' => 'CLD-900',
            'course_by' => (string) $admin->id,
            'is_active' => true,
        ]);

        Payment::query()->create([
            'user_id' => $admin->id,
            'course_id' => $course->id,
            'payable_type' => Course::class,
            'payable_id' => $course->id,
            'amount' => 1500.00,
            'currency' => 'ZMW',
            'status' => 'completed',
            'payment_method' => 'card',
            'provider' => 'broadpay',
            'reference' => 'TEST-PAY-001',
        ]);

        $tool = new SystemOverviewTool($admin);
        $result = (string) $tool->handle(new Request);

        $this->assertStringContainsString('"active_courses":', $result);
        $this->assertStringContainsString('"total_users":', $result);
        $this->assertStringContainsString('"all_time_revenue": 1500', $result);
    }

    public function test_manage_user_account_tool_searches_and_toggles_active(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $targetStudent = User::factory()->create([
            'name' => 'Grace Hopper',
            'email' => 'hopper@navy.gov',
            'role' => 'student',
            'is_active' => true,
        ]);

        $tool = new ManageUserAccountTool($admin);

        // Search user
        $searchResult = (string) $tool->handle(new Request([
            'action' => 'search',
            'query' => 'hopper',
        ]));
        $this->assertStringContainsString('Grace Hopper', $searchResult);
        $this->assertStringContainsString('hopper@navy.gov', $searchResult);

        // Toggle user inactive
        $toggleResult = (string) $tool->handle(new Request([
            'action' => 'toggle_active',
            'user_id' => $targetStudent->id,
        ]));
        $this->assertStringContainsString('"is_active": false', $toggleResult);
        $this->assertFalse($targetStudent->fresh()->is_active);

        // Prevent self-deactivation
        $selfToggleResult = (string) $tool->handle(new Request([
            'action' => 'toggle_active',
            'user_id' => $admin->id,
        ]));
        $this->assertStringContainsString('Security Error', $selfToggleResult);
    }

    public function test_review_instructor_applications_tool_approves_and_promotes_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $candidate = User::factory()->create([
            'email' => 'alan.turing@thinkerhub.net',
            'role' => 'student',
            'is_active' => true,
        ]);

        $app = InstructorApplication::query()->create([
            'name' => 'Alan Turing',
            'email' => 'alan.turing@thinkerhub.net',
            'proficiency' => 'Cryptography & Computation',
            'qualifications' => 'PhD Mathematics, Cambridge',
            'course_concept_note' => 'Foundational discrete computation structures and cryptographic primitives.',
            'proposed_curriculum' => 'Weeks 1-12 comprehensive computing foundations.',
            'proposed_course_name' => 'Foundations of Computer Science',
            'status' => 'pending',
            'user_id' => $candidate->id,
        ]);

        $tool = new ReviewInstructorApplicationsTool($admin);
        $result = (string) $tool->handle(new Request([
            'action' => 'approve',
            'application_id' => $app->id,
            'admin_notes' => 'Exceptional qualifications verified.',
        ]));

        $this->assertStringContainsString('"success": true', $result);
        $this->assertStringContainsString('"status": "approved"', $result);

        $this->assertEquals('approved', $app->fresh()->status);
        $this->assertEquals('instructor', $candidate->fresh()->role);
    }

    public function test_admin_ai_copilot_page_renders_for_admin_and_denies_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $this->actingAs($student);
        $this->assertFalse(AiCopilot::canAccess());

        $this->actingAs($admin);
        $this->assertTrue(AiCopilot::canAccess());

        Livewire::test(AiCopilot::class)
            ->assertSuccessful()
            ->assertSee('Executive AI System Co-Pilot')
            ->assertSee('Admin Threads')
            ->assertSee('System Broadcast')
            ->assertSee('Unpin to Floating Bot');
    }

    public function test_unpin_action_on_full_pages_redirects_to_respective_dashboards(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        // Admin Portal
        $this->actingAs($admin);
        Livewire::test(AiCopilot::class)
            ->call('unpinToFloating')
            ->assertRedirect(url('/manage'));
        $this->assertTrue(session('ai_unpinned_admin'));
        $this->assertTrue(session('ai_floating_open_admin'));

        // Instructor Portal
        $this->actingAs($instructor);
        Livewire::test(AiAssistant::class)
            ->call('unpinToFloating')
            ->assertRedirect(url('/teach'));
        $this->assertTrue(session('ai_unpinned_instructor'));
        $this->assertTrue(session('ai_floating_open_instructor'));

        // Student Portal
        $this->actingAs($student);
        Livewire::test(StudyBuddy::class)
            ->call('unpinToFloating')
            ->assertRedirect(url('/learn'));
        $this->assertTrue(session('ai_unpinned_student'));
        $this->assertTrue(session('ai_floating_open_student'));
    }

    public function test_portal_floating_chat_widget_pins_back_to_full_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        // Admin Portal pinBack
        $this->actingAs($admin);
        session(['ai_unpinned_admin' => true, 'ai_floating_open_admin' => true]);
        Livewire::test(PortalFloatingChatWidget::class, ['portal' => 'admin'])
            ->call('pinBack')
            ->assertRedirect(url('/manage/ai-copilot'));
        $this->assertFalse(session('ai_unpinned_admin'));
        $this->assertFalse(session('ai_floating_open_admin'));

        // Instructor Portal pinBack
        $this->actingAs($instructor);
        session(['ai_unpinned_instructor' => true, 'ai_floating_open_instructor' => true]);
        Livewire::test(PortalFloatingChatWidget::class, ['portal' => 'instructor'])
            ->call('pinBack')
            ->assertRedirect(url('/teach/ai-assistant'));
        $this->assertFalse(session('ai_unpinned_instructor'));

        // Student Portal pinBack
        $this->actingAs($student);
        session(['ai_unpinned_student' => true, 'ai_floating_open_student' => true]);
        Livewire::test(PortalFloatingChatWidget::class, ['portal' => 'student'])
            ->call('pinBack')
            ->assertRedirect(url('/learn/study-buddy'));
        $this->assertFalse(session('ai_unpinned_student'));
    }

    public function test_portal_floating_chat_widget_can_toggle_and_send_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);

        AdminSystemCopilot::fake([
            'System operations online and healthy.',
        ]);

        Livewire::test(PortalFloatingChatWidget::class, ['portal' => 'admin'])
            ->assertSet('isOpen', true)
            ->call('toggleOpen')
            ->assertSet('isOpen', false)
            ->call('toggleOpen')
            ->assertSet('isOpen', true)
            ->set('message', 'What is the system status?')
            ->call('sendMessage')
            ->assertDispatched('message-sent');

        $this->assertDatabaseHas((new \Laravel\Ai\Models\Conversation)->getTable(), [
            'participant_id' => (string) $admin->id,
        ]);
    }
}
