<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
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
use App\Services\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_service_compiles_student_data_and_quiz_answer_sheets(): void
    {
        $student = User::factory()->create([
            'name' => 'Alice Wonder',
            'email' => 'alice@thinker.test',
            'role' => 'student',
            'track' => 'Intermediate',
        ]);

        $course = Course::create([
            'title' => 'Advanced Backend Engineering',
            'code' => 'BACKEND301',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);

        // Create a scheduled session and attendance
        $session = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Session 1: Architecture',
            'session_date' => Carbon::now()->subDays(5),
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        Attendance::create([
            'user_id' => $student->id,
            'course_session_id' => $session->id,
            'status' => 'Present',
        ]);

        // Create assignment and submission
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'name' => 'Design API Specs',
            'due_date' => Carbon::now()->addDays(2),
            'scope' => 'all',
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'grade' => '95',
            'status' => 'Graded',
            'submitted_at' => Carbon::now()->subDay(),
        ]);

        // Create Quiz with Questions and Options
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'REST API Fundamentals',
            'passing_score' => 70,
            'is_active' => true,
        ]);

        $question1 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'Which HTTP status represents Success with No Content?',
            'points' => 10,
            'explanation' => 'HTTP 204 indicates success with no response payload.',
            'sort_order' => 1,
        ]);

        $optCorrect = QuizOption::create([
            'quiz_question_id' => $question1->id,
            'option_text' => '204 No Content',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $optWrong = QuizOption::create([
            'quiz_question_id' => $question1->id,
            'option_text' => '404 Not Found',
            'is_correct' => false,
            'sort_order' => 2,
        ]);

        // Create Attempt and Answer
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'started_at' => Carbon::now()->subHours(2),
            'completed_at' => Carbon::now()->subHour(),
            'score' => 10,
            'total_points' => 10,
            'percentage' => 100,
            'passed' => true,
        ]);

        QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $question1->id,
            'quiz_option_id' => $optCorrect->id,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        $service = app(ReportGenerationService::class);
        $data = $service->getStudentAcademicData($student, $course);

        $this->assertEquals('Alice Wonder', $data['student']->name);
        $this->assertCount(1, $data['courses_data']);
        
        $courseData = $data['courses_data'][0];
        $this->assertEquals(100, $courseData['attendance_rate']);
        $this->assertEquals(95, $courseData['avg_assignment_grade']);
        $this->assertCount(1, $courseData['quizzes_log']);
        
        $quizData = $courseData['quizzes_log'][0];
        $this->assertEquals(100, $quizData['percentage']);
        $this->assertTrue($quizData['passed']);
        $this->assertCount(1, $quizData['answer_sheet']);
        
        $sheetItem = $quizData['answer_sheet'][0];
        $this->assertEquals('Which HTTP status represents Success with No Content?', $sheetItem['question_text']);
        $this->assertTrue($sheetItem['is_correct']);
        $this->assertEquals('204 No Content', $sheetItem['student_selected_text']);
        $this->assertEquals('204 No Content', $sheetItem['correct_answer_text']);

        // Verify PDF builds cleanly with valid PDF binary output
        $pdf = $service->renderStudentReportPdf($student, $course);
        $output = $pdf->output();
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_admin_can_download_student_report_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Bob Builder']);
        $course = Course::create([
            'title' => 'Web Development 101',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($admin)->get(route('reports.student', [
            'student' => $student->id,
            'course_id' => $course->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_course_analytics_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'title' => 'Web Design Masterclass',
            'code' => 'DESIGN201',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('reports.course', [
            'course' => $course->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_non_admin_is_forbidden_from_accessing_reports(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $targetStudent = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get(route('reports.student', [
            'student' => $targetStudent->id,
        ]));

        $response->assertForbidden();
    }
}
