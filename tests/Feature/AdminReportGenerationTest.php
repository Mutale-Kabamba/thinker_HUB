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

    public function test_instructor_can_download_student_report_endpoint(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Charlie Day']);
        $course = Course::create([
            'title' => 'Vue.js Framework',
            'code' => 'VUE101',
            'is_active' => true,
            'course_by' => (string) $instructor->id,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($instructor)->get(route('reports.student', [
            'student' => $student->id,
            'course_id' => $course->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_instructor_can_access_reports_side_nav_page(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $response = $this->actingAs($instructor)->get('/teach/reports');
        $response->assertOk();
    }

    public function test_admin_can_download_intake_report_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Cohort Student']);
        $course = Course::create([
            'title' => 'Python for AI',
            'code' => 'AI101',
            'is_active' => true,
        ]);
        $intake = \App\Models\CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Batch 2026-A',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'course_intake_id' => $intake->id,
        ]);

        $response = $this->actingAs($admin)->get(route('reports.intake', [
            'intake' => $intake->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_service_accurately_tracks_lowercase_and_mixed_attendance_statuses(): void
    {
        $student = User::factory()->create([
            'name' => 'David Present',
            'email' => 'david@thinker.test',
            'role' => 'student',
        ]);

        $course = Course::create([
            'title' => 'Flutter Mobile Architecture',
            'code' => 'FLUTTER401',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Session 1: Present (lowercase constant from Filament Attendance manager)
        $session1 = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Session 1',
            'session_date' => Carbon::now()->subDays(4),
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);
        Attendance::create([
            'user_id' => $student->id,
            'course_session_id' => $session1->id,
            'status' => Attendance::STATUS_PRESENT, // 'present'
            'notes' => 'Active participant',
        ]);

        // Session 2: Late
        $session2 = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Session 2',
            'session_date' => Carbon::now()->subDays(3),
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);
        Attendance::create([
            'user_id' => $student->id,
            'course_session_id' => $session2->id,
            'status' => Attendance::STATUS_LATE, // 'late'
            'notes' => 'Joined 10 mins late',
        ]);

        // Session 3: Apology
        $session3 = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Session 3',
            'session_date' => Carbon::now()->subDays(2),
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);
        Attendance::create([
            'user_id' => $student->id,
            'course_session_id' => $session3->id,
            'status' => Attendance::STATUS_APOLOGY, // 'apology'
            'notes' => 'Medical appointment',
        ]);

        // Session 4: Absent
        $session4 = CourseSession::create([
            'course_id' => $course->id,
            'title' => 'Session 4',
            'session_date' => Carbon::now()->subDays(1),
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);
        Attendance::create([
            'user_id' => $student->id,
            'course_session_id' => $session4->id,
            'status' => Attendance::STATUS_ABSENT, // 'absent'
        ]);

        $service = app(ReportGenerationService::class);
        $academicData = $service->getStudentAcademicData($student, $course);
        $courseData = $academicData['courses_data'][0];

        $this->assertEquals(1, $courseData['present_count'], 'Present count must be 1');
        $this->assertEquals(1, $courseData['late_count'], 'Late count must be 1');
        $this->assertEquals(1, $courseData['apology_count'], 'Apology count must be 1');
        $this->assertEquals(1, $courseData['absent_count'], 'Absent count must be 1');
        $this->assertEquals(2, $courseData['sessions_attended'], 'Sessions attended (present + late) must be 2');
        $this->assertEquals(4, $courseData['sessions_total'], 'Total sessions must be 4');
        $this->assertEquals(50, $courseData['attendance_rate'], 'Attendance rate must be 50%');

        $sessionLog = collect($courseData['session_log'])->keyBy('session_id');
        $this->assertEquals('Present', $sessionLog[$session1->id]['status']);
        $this->assertEquals('Active participant', $sessionLog[$session1->id]['remarks']);
        $this->assertEquals('Late', $sessionLog[$session2->id]['status']);
        $this->assertEquals('Apology', $sessionLog[$session3->id]['status']);
        $this->assertEquals('Absent', $sessionLog[$session4->id]['status']);

        // Test Course Analytics data
        $courseAnalytics = $service->getCourseAnalyticsData($course);
        $this->assertEquals(1, $courseAnalytics['attendance']['present']);
        $this->assertEquals(1, $courseAnalytics['attendance']['late']);
        $this->assertEquals(1, $courseAnalytics['attendance']['apology']);
        $this->assertEquals(1, $courseAnalytics['attendance']['absent']);
        $this->assertEquals(50, $courseAnalytics['attendance']['rate']);
        $this->assertEquals(2, $courseAnalytics['roster'][0]['attended_sessions']);
    }
}
