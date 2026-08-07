<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Schedule;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_model_supports_apology_status(): void
    {
        $this->assertSame('apology', Attendance::STATUS_APOLOGY);
        $this->assertContains(Attendance::STATUS_APOLOGY, Attendance::STATUSES);

        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Test Course',
            'code' => 'TC-101',
            'is_active' => true,
        ]);
        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'title' => 'Session 1',
            'type' => 'group',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        $attendance = Attendance::query()->create([
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => Attendance::STATUS_APOLOGY,
            'notes' => 'Sent apology due to doctor appointment',
        ]);

        $this->assertSame(Attendance::STATUS_APOLOGY, $attendance->fresh()->status);
    }

    public function test_student_schedule_page_displays_apology_attendance_record(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Physics 101',
            'code' => 'PHY-101',
            'is_active' => true,
        ]);
        $student->courses()->attach($course->id);

        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'title' => 'Lab 1',
            'type' => 'group',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'completed',
        ]);

        Attendance::query()->create([
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'status' => Attendance::STATUS_APOLOGY,
            'notes' => 'Excused',
        ]);

        $this->actingAs($student);

        Livewire::test(Schedule::class)
            ->assertSee('Physics 101')
            ->assertSee('Apology');
    }
}
