<?php

namespace Tests\Feature;

use App\Ai\Agents\InstructorAssistant;
use App\Ai\Agents\PublicCourseAdvisor;
use App\Ai\Agents\StudentStudyBuddy;
use App\Ai\Tools\Instructor\MarkSessionAttendanceTool;
use App\Ai\Tools\Instructor\UpdateCourseSessionTool;
use App\Ai\Tools\Public\SearchPublicCoursesTool;
use App\Ai\Tools\Student\ReviewCompletedQuizAttemptsTool;
use App\Filament\Instructor\Pages\AiAssistant;
use App\Filament\Student\Pages\StudyBuddy;
use App\Livewire\Ai\PublicChatWidget;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
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
}
