<?php

namespace Tests\Feature;

use App\Livewire\SessionViewer;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseSessionDeliveryAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $instructor;
    protected User $student;
    protected User $otherStudent;
    protected Course $course;
    protected CourseIntake $intake;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->instructor = User::factory()->create([
            'role' => 'instructor',
            'name' => 'Prof. Ada Lovelace',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Alan Turing',
            'email' => 'alan@thinkerhub.test',
        ]);

        $this->otherStudent = User::factory()->create([
            'role' => 'student',
            'name' => 'Grace Hopper',
            'email' => 'grace@thinkerhub.test',
        ]);

        $this->course = Course::query()->create([
            'title' => 'Advanced AI & Machine Learning',
            'code' => 'AIML-401',
            'is_active' => true,
            'course_by' => (string) $this->instructor->id,
        ]);

        $this->intake = CourseIntake::query()->create([
            'course_id' => $this->course->id,
            'name' => 'Cohort 2026-A',
            'status' => CourseIntake::STATUS_ACTIVE,
            'start_date' => now()->subMonth()->toDateString(),
        ]);

        // Enroll only $this->student in the course & intake
        Enrollment::query()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
        ]);
    }

    public function test_can_create_sessions_with_different_delivery_modes_and_attributes(): void
    {
        $virtualSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Virtual Live Session',
            'delivery_mode' => CourseSession::DELIVERY_LIVE_VIRTUAL,
            'meeting_link' => 'https://meet.google.com/xyz-abcd-efg',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
        ]);

        $physicalSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Campus Lab Practical',
            'delivery_mode' => CourseSession::DELIVERY_LIVE_PHYSICAL,
            'physical_address' => 'Lab Building 4, Innovation Campus, Lusaka',
            'session_date' => now()->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '13:00:00',
        ]);

        $videoSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Neural Networks Video Guide',
            'delivery_mode' => CourseSession::DELIVERY_VIDEO,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'required_duration_minutes' => 20,
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
        ]);

        $readingSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Transformers Paper Reading',
            'delivery_mode' => CourseSession::DELIVERY_READ,
            'reading_content' => '# Attention is all you need\nDetailed reading breakdown...',
            'required_duration_minutes' => 15,
            'session_date' => now()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
        ]);

        $this->assertTrue($virtualSession->isLiveVirtual());
        $this->assertFalse($virtualSession->isSelfPaced());
        $this->assertSame('https://meet.google.com/xyz-abcd-efg', $virtualSession->meeting_link);

        $this->assertTrue($physicalSession->isLivePhysical());
        $this->assertFalse($physicalSession->isSelfPaced());
        $this->assertSame('Lab Building 4, Innovation Campus, Lusaka', $physicalSession->physical_address);

        $this->assertTrue($videoSession->isVideo());
        $this->assertTrue($videoSession->isSelfPaced());
        $this->assertSame('dQw4w9WgXcQ', $videoSession->youtube_id);
        $this->assertSame(20, $videoSession->required_duration_minutes);

        $this->assertTrue($readingSession->isRead());
        $this->assertTrue($readingSession->isSelfPaced());
        $this->assertSame(15, $readingSession->required_duration_minutes);
    }

    public function test_access_control_for_session_viewer(): void
    {
        $videoSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Deep Learning Video',
            'delivery_mode' => CourseSession::DELIVERY_VIDEO,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'required_duration_minutes' => 5,
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
        ]);

        // 1. Guest redirected to login
        $guestResponse = $this->get(route('sessions.view', $videoSession));
        $guestResponse->assertRedirect(route('login'));

        // 2. Unenrolled student gets 403
        $unenrolledResponse = $this->actingAs($this->otherStudent)->get(route('sessions.view', $videoSession));
        $unenrolledResponse->assertForbidden();

        // 3. Enrolled student gets 200
        $enrolledResponse = $this->actingAs($this->student)->get(route('sessions.view', $videoSession));
        $enrolledResponse->assertOk();

        // 4. Instructor gets 200
        $instructorResponse = $this->actingAs($this->instructor)->get(route('sessions.view', $videoSession));
        $instructorResponse->assertOk();

        // 5. Admin gets 200
        $adminResponse = $this->actingAs($this->admin)->get(route('sessions.view', $videoSession));
        $adminResponse->assertOk();
    }

    public function test_student_watching_video_marks_attendance_when_threshold_met(): void
    {
        $videoSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Computer Vision Video Guide',
            'delivery_mode' => CourseSession::DELIVERY_VIDEO,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'required_duration_minutes' => 2, // 120 seconds required
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
        ]);

        $this->actingAs($this->student);

        // Pre-condition: Attendance has not been recorded yet
        $this->assertDatabaseMissing('attendances', [
            'course_session_id' => $videoSession->id,
            'user_id' => $this->student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        // Attempt completion with sufficient watch time (125 seconds >= 120 seconds target)
        Livewire::test(SessionViewer::class, ['session' => $videoSession])
            ->call('completeVideoSession', [
                'actualSecondsWatched' => 125,
                'duration' => 300,
                'currentTime' => 125,
            ])
            ->assertReturned([
                'status' => 'success',
                'message' => 'Session completed! Your attendance has been officially marked on the register.',
            ]);

        // Verify attendance record created as present
        $this->assertDatabaseHas('attendances', [
            'course_session_id' => $videoSession->id,
            'user_id' => $this->student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $attendance = Attendance::query()
            ->where('course_session_id', $videoSession->id)
            ->where('user_id', $this->student->id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertStringContainsString('Completed video guide', $attendance->notes);
    }

    public function test_student_watching_video_does_not_mark_attendance_when_threshold_not_met(): void
    {
        $videoSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'NLP Foundations Video Guide',
            'delivery_mode' => CourseSession::DELIVERY_VIDEO,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'required_duration_minutes' => 5, // 300 seconds required
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
        ]);

        $this->actingAs($this->student);

        // Attempt completion with only 45 seconds watched
        Livewire::test(SessionViewer::class, ['session' => $videoSession])
            ->call('completeVideoSession', [
                'actualSecondsWatched' => 45,
                'duration' => 600,
                'currentTime' => 45,
            ])
            ->assertReturned(function ($result) {
                return is_array($result)
                    && ($result['status'] ?? null) === 'threshold_not_met'
                    && ($result['remaining_seconds'] ?? null) == 255;
            });

        // Verify attendance was NOT marked present
        $this->assertDatabaseMissing('attendances', [
            'course_session_id' => $videoSession->id,
            'user_id' => $this->student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);
    }

    public function test_student_reading_material_marks_attendance_when_threshold_met(): void
    {
        $readingSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Ethics in Artificial Intelligence Guide',
            'delivery_mode' => CourseSession::DELIVERY_READ,
            'reading_content' => '# Ethics in AI\nFairness, Accountability, Transparency.',
            'required_duration_minutes' => 1, // 60 seconds
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $this->actingAs($this->student);

        Livewire::test(SessionViewer::class, ['session' => $readingSession])
            ->call('completeReadingSession', [
                'activeSeconds' => 62,
            ])
            ->assertReturned([
                'status' => 'success',
                'message' => 'Reading completed! Your attendance has been officially marked on the register.',
            ]);

        $this->assertDatabaseHas('attendances', [
            'course_session_id' => $readingSession->id,
            'user_id' => $this->student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $attendance = Attendance::query()
            ->where('course_session_id', $readingSession->id)
            ->where('user_id', $this->student->id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertStringContainsString('Completed reading material', $attendance->notes);
    }

    public function test_student_reading_material_does_not_mark_attendance_when_threshold_not_met(): void
    {
        $readingSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'MLOps Architecture Reading',
            'delivery_mode' => CourseSession::DELIVERY_READ,
            'reading_content' => '# MLOps pipeline components',
            'required_duration_minutes' => 2, // 120 seconds
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $this->actingAs($this->student);

        Livewire::test(SessionViewer::class, ['session' => $readingSession])
            ->call('completeReadingSession', [
                'activeSeconds' => 40,
            ])
            ->assertReturned(function ($result) {
                return is_array($result)
                    && ($result['status'] ?? null) === 'threshold_not_met'
                    && ($result['remaining_seconds'] ?? null) == 80;
            });

        $this->assertDatabaseMissing('attendances', [
            'course_session_id' => $readingSession->id,
            'user_id' => $this->student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);
    }

    public function test_already_marked_attendance_returns_appropriate_status(): void
    {
        $videoSession = CourseSession::query()->create([
            'course_id' => $this->course->id,
            'course_intake_id' => $this->intake->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Data Engineering Video',
            'delivery_mode' => CourseSession::DELIVERY_VIDEO,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'required_duration_minutes' => 1,
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
        ]);

        // Pre-create attendance as present
        Attendance::query()->create([
            'course_session_id' => $videoSession->id,
            'user_id' => $this->student->id,
            'status' => Attendance::STATUS_PRESENT,
        ]);

        $this->actingAs($this->student);

        Livewire::test(SessionViewer::class, ['session' => $videoSession])
            ->call('completeVideoSession', [
                'actualSecondsWatched' => 120,
                'duration' => 120,
            ])
            ->assertReturned([
                'status' => 'already_marked',
                'message' => 'Attendance is already recorded for this session.',
            ]);
    }
}
