<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\AttendanceRegister as InstructorAttendanceRegister;
use App\Filament\Pages\AttendanceRegister as AdminAttendanceRegister;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $instructor;
    protected User $studentA;
    protected User $studentB;
    protected Course $course;
    protected CourseIntake $intake;
    protected CourseSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->instructor = User::factory()->create([
            'role' => 'instructor',
            'name' => 'Prof. Alan Turing',
        ]);

        $this->studentA = User::factory()->create([
            'role' => 'student',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);

        $this->studentB = User::factory()->create([
            'role' => 'student',
            'name' => 'Charles Babbage',
            'email' => 'charles@example.com',
        ]);

        $this->course = Course::query()->create([
            'title' => 'Computer Science Fundamentals',
            'code' => 'CS-101',
            'is_active' => true,
            'course_by' => (string) $this->instructor->id,
        ]);

        $this->intake = CourseIntake::query()->create([
            'course_id' => $this->course->id,
            'name' => 'Cohort Alpha 2026',
            'status' => CourseIntake::STATUS_ACTIVE,
            'start_date' => now()->subMonth()->toDateString(),
        ]);

        Enrollment::query()->create([
            'user_id' => $this->studentA->id,
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
        ]);

        Enrollment::query()->create([
            'user_id' => $this->studentB->id,
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
        ]);

        $this->session = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Lecture 1: Algorithms',
            'type' => 'group',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
        ]);
    }

    public function test_attendance_sync_populates_enrolled_students(): void
    {
        $result = Attendance::syncForSession($this->session);

        $this->assertSame(2, $result['total_eligible']);
        $this->assertSame(2, $result['newly_created']);
        $this->assertDatabaseHas('attendances', [
            'course_session_id' => $this->session->id,
            'user_id' => $this->studentA->id,
        ]);
        $this->assertDatabaseHas('attendances', [
            'course_session_id' => $this->session->id,
            'user_id' => $this->studentB->id,
        ]);

        // Second sync is idempotent
        $secondResult = Attendance::syncForSession($this->session);
        $this->assertSame(2, $secondResult['total_eligible']);
        $this->assertSame(0, $secondResult['newly_created']);
    }

    public function test_attendance_sync_respects_intake_scoping(): void
    {
        $otherIntake = CourseIntake::query()->create([
            'course_id' => $this->course->id,
            'name' => 'Cohort Beta 2026',
            'status' => CourseIntake::STATUS_ACTIVE,
            'start_date' => now()->toDateString(),
        ]);

        $studentC = User::factory()->create([
            'role' => 'student',
            'name' => 'Grace Hopper',
        ]);

        Enrollment::query()->create([
            'user_id' => $studentC->id,
            'course_id' => $this->course->id,
            'course_intake_id' => $otherIntake->id,
        ]);

        // Session is scoped to $this->intake (Cohort Alpha)
        $result = Attendance::syncForSession($this->session);

        $this->assertSame(2, $result['total_eligible']);
        $this->assertContains($this->studentA->id, $result['student_ids']);
        $this->assertContains($this->studentB->id, $result['student_ids']);
        $this->assertNotContains($studentC->id, $result['student_ids']);
    }

    public function test_attendance_sync_handles_one_on_one_session(): void
    {
        $oneOnOneSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'instructor_id' => $this->instructor->id,
            'student_id' => $this->studentA->id,
            'title' => 'Personal Mentorship',
            'type' => 'one_on_one',
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'status' => 'scheduled',
        ]);

        $result = Attendance::syncForSession($oneOnOneSession);

        $this->assertSame(1, $result['total_eligible']);
        $this->assertSame([$this->studentA->id], $result['student_ids']);
        $this->assertDatabaseHas('attendances', [
            'course_session_id' => $oneOnOneSession->id,
            'user_id' => $this->studentA->id,
        ]);
    }

    public function test_attendance_service_marks_status_and_notes(): void
    {
        Attendance::syncForSession($this->session);
        $attendance = Attendance::where('course_session_id', $this->session->id)
            ->where('user_id', $this->studentA->id)
            ->firstOrFail();

        $service = app(AttendanceService::class);
        $updated = $service->markAttendance($attendance->id, Attendance::STATUS_LATE, 'Joined 15m late due to traffic');

        $this->assertSame(Attendance::STATUS_LATE, $updated->status);
        $this->assertSame('Joined 15m late due to traffic', $updated->notes);
    }

    public function test_attendance_service_batch_marks_session(): void
    {
        Attendance::syncForSession($this->session);
        $service = app(AttendanceService::class);

        $count = $service->markBatchAttendance($this->session, Attendance::STATUS_PRESENT);
        $this->assertSame(2, $count);

        $summary = $service->getSessionAttendanceSummary($this->session);
        $this->assertSame(2, $summary['present']);
        $this->assertSame(0, $summary['absent']);
        $this->assertSame(100, $summary['attendance_rate']);
    }

    public function test_excel_export_generates_valid_csv_stream(): void
    {
        Attendance::syncForSession($this->session);
        $service = app(AttendanceService::class);

        $response = $service->exportSessionExcel($this->session);

        $this->assertTrue($response->headers->contains('content-type', 'text/csv; charset=UTF-8'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Check for UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        // Check for headers and student data
        $this->assertStringContainsString('THINKER HUB', $content);
        $this->assertStringContainsString('CS-101', $content);
        $this->assertStringContainsString('Ada Lovelace', $content);
        $this->assertStringContainsString('Charles Babbage', $content);
    }

    public function test_pdf_export_generates_valid_dompdf_document(): void
    {
        Attendance::syncForSession($this->session);
        $service = app(AttendanceService::class);

        $pdf = $service->exportSessionPdf($this->session);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_schedule_pdf_export_generates_marked_schedule_grid(): void
    {
        Attendance::syncForSession($this->session);
        $service = app(AttendanceService::class);

        $pdf = $service->exportScheduleRegisterPdf($this->course, $this->intake->id);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_admin_attendance_register_page_renders_and_marks_status(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminAttendanceRegister::class, ['session_id' => $this->session->id])
            ->assertSee('Attendance Register')
            ->assertSee('Computer Science Fundamentals')
            ->assertSee('Ada Lovelace')
            ->assertSee('Charles Babbage')
            ->call('markStatus', Attendance::where('user_id', $this->studentA->id)->first()->id, 'absent')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'course_session_id' => $this->session->id,
            'user_id' => $this->studentA->id,
            'status' => 'absent',
        ]);
    }

    public function test_instructor_attendance_register_scoped_to_assigned_sessions(): void
    {
        $otherInstructor = User::factory()->create(['role' => 'instructor']);
        $otherCourse = Course::query()->create([
            'title' => 'Art History',
            'code' => 'ART-101',
            'is_active' => true,
            'course_by' => (string) $otherInstructor->id,
        ]);
        $otherSession = CourseSession::query()->create([
            'course_id' => $otherCourse->id,
            'instructor_id' => $otherInstructor->id,
            'title' => 'Renaissance Painting',
            'type' => 'group',
            'session_date' => now()->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->instructor);

        // Can access their own session
        Livewire::test(InstructorAttendanceRegister::class, ['session_id' => $this->session->id])
            ->assertSee('Computer Science Fundamentals')
            ->assertSee('Ada Lovelace');

        // Cannot access other instructor's session
        Livewire::test(InstructorAttendanceRegister::class, ['session_id' => $otherSession->id])
            ->assertDontSee('Renaissance Painting');
    }

    public function test_designated_test_student_is_excluded_from_pdf_exports_and_rosters(): void
    {
        $testStudent = User::factory()->create([
            'role' => 'student',
            'name' => 'Bwalya Mutale',
            'email' => '190031@zcuniversity.edu.zm',
        ]);

        Enrollment::query()->create([
            'user_id' => $testStudent->id,
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
        ]);

        $this->assertTrue($testStudent->isTestStudent());

        // 1. Sync does not create attendance row for test student
        $syncResult = Attendance::syncForSession($this->session);
        $this->assertNotContains($testStudent->id, $syncResult['student_ids']);
        $this->assertDatabaseMissing('attendances', [
            'course_session_id' => $this->session->id,
            'user_id' => $testStudent->id,
        ]);

        $service = app(AttendanceService::class);

        // 2. Schedule PDF does not capture test student
        $schedulePdf = $service->exportScheduleRegisterPdf($this->course, $this->intake->id);
        $scheduleOutput = $schedulePdf->output();
        $this->assertStringNotContainsString('190031@zcuniversity.edu.zm', $scheduleOutput);
        $this->assertStringNotContainsString('Bwalya Mutale', $scheduleOutput);

        // 3. Single session PDF does not capture test student
        $sessionPdf = $service->exportSessionPdf($this->session);
        $sessionOutput = $sessionPdf->output();
        $this->assertStringNotContainsString('190031@zcuniversity.edu.zm', $sessionOutput);
        $this->assertStringNotContainsString('Bwalya Mutale', $sessionOutput);

        // 4. Course Cumulative PDF does not capture test student
        $cumulativePdf = $service->exportCourseCumulativePdf($this->course, $this->intake->id);
        $cumulativeOutput = $cumulativePdf->output();
        $this->assertStringNotContainsString('190031@zcuniversity.edu.zm', $cumulativeOutput);
        $this->assertStringNotContainsString('Bwalya Mutale', $cumulativeOutput);
    }

    public function test_play_it_forward_course_pdf_has_play_it_forward_branding_and_shows_gender_and_nrc(): void
    {
        $pifCourse = Course::query()->create([
            'title' => 'Digital Skills Program',
            'code' => 'DSP-PIF',
            'is_active' => true,
            'course_by' => (string) $this->instructor->id,
        ]);

        $this->assertTrue($pifCourse->isPlayItForward());

        $pifStudent = User::factory()->create([
            'role' => 'student',
            'name' => 'Chileshe Mwamba',
            'email' => 'chileshe@example.com',
            'gender' => 'Female',
            'nrc_passport' => '987654/10/1',
        ]);

        Enrollment::query()->create([
            'user_id' => $pifStudent->id,
            'course_id' => $pifCourse->id,
        ]);

        $session = CourseSession::query()->create([
            'course_id' => $pifCourse->id,
            'title' => 'Module 1: Computer Basics',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'instructor_id' => $this->instructor->id,
            'status' => 'scheduled',
        ]);

        $service = app(AttendanceService::class);

        // 1. Verify Schedule Register PDF
        $schedulePdf = $service->exportScheduleRegisterPdf($pifCourse);
        $scheduleHtml = $schedulePdf->getDomPDF()->output_html();
        $scheduleOutput = $schedulePdf->output();

        $this->assertStringStartsWith('%PDF-', $scheduleOutput);
        $this->assertStringContainsString('Play it Forward', $scheduleHtml);
        $this->assertStringContainsString('Digital Skills Program (DSP-PIF)', $scheduleHtml);
        $this->assertStringContainsString('Chileshe Mwamba', $scheduleHtml);
        $this->assertStringContainsString('Female', $scheduleHtml);
        $this->assertStringContainsString('987654/10/1', $scheduleHtml);
        // Header must NOT contain thinker
        $this->assertStringNotContainsString('think<span>.er</span> HUB', $scheduleHtml);
        // Footer must contain thinker attribution
        $this->assertStringContainsString('Powered by thinker HUB LMS', $scheduleHtml);

        // 2. Verify Session PDF
        $sessionPdf = $service->exportSessionPdf($session);
        $sessionHtml = $sessionPdf->getDomPDF()->output_html();
        $sessionOutput = $sessionPdf->output();

        $this->assertStringStartsWith('%PDF-', $sessionOutput);
        $this->assertStringContainsString('Play it Forward', $sessionHtml);
        $this->assertStringContainsString('Female', $sessionHtml);
        $this->assertStringContainsString('987654/10/1', $sessionHtml);
        $this->assertStringNotContainsString('think<span>.er</span> HUB', $sessionHtml);
        $this->assertStringContainsString('Powered by thinker HUB LMS', $sessionHtml);

        // 3. Verify Non-PIF course retains standard Thinker HUB header
        $nonPifSchedulePdf = $service->exportScheduleRegisterPdf($this->course);
        $nonPifHtml = $nonPifSchedulePdf->getDomPDF()->output_html();
        $nonPifOutput = $nonPifSchedulePdf->output();

        $this->assertStringStartsWith('%PDF-', $nonPifOutput);
        $this->assertStringContainsString('think<span>.er</span> HUB', $nonPifHtml);
        $this->assertStringNotContainsString('Play it Forward Zambia', $nonPifHtml);
    }

    public function test_gender_and_nrc_passport_can_be_updated_in_profile(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'gender' => 'Male',
            'nrc_passport' => '111222/33/1',
        ]);

        $response = $this->actingAs($student)->patch(route('profile.update'), [
            'name' => 'Updated Student Name',
            'email' => $student->email,
            'gender' => 'Female',
            'nrc_passport' => '444555/66/1',
        ]);

        $response->assertSessionHasNoErrors();
        $student->refresh();
        $this->assertSame('Female', $student->gender);
        $this->assertSame('444555/66/1', $student->nrc_passport);
    }
}
