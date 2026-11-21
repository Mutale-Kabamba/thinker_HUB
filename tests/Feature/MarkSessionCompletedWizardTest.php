<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\CourseSessionResource\Pages\ListCourseSessions as InstructorListCourseSessions;
use App\Filament\Resources\CourseSessions\Pages\ListCourseSessions as AdminListCourseSessions;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarkSessionCompletedWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_mark_completed_wizard_marks_students_and_completes_session(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'SQL Mastery',
            'code' => 'SQL-101',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $studentA = User::factory()->create(['name' => 'Alice Banda', 'role' => 'student', 'is_active' => true]);
        $studentB = User::factory()->create(['name' => 'Brian Phiri', 'role' => 'student', 'is_active' => true]);

        Enrollment::create(['user_id' => $studentA->id, 'course_id' => $course->id, 'status' => 'active']);
        Enrollment::create(['user_id' => $studentB->id, 'course_id' => $course->id, 'status' => 'active']);

        $session = CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'type' => 'group',
            'title' => 'Week 14 Recap',
            'session_date' => now()->toDateString(),
            'start_time' => '18:00:00',
            'end_time' => '19:30:00',
            'status' => CourseSession::STATUS_SCHEDULED,
        ]);

        // Mount wizard action (auto-syncs roster)
        $test = Livewire::actingAs($instructor)
            ->test(InstructorListCourseSessions::class)
            ->assertTableActionExists('markCompleted')
            ->mountTableAction('markCompleted', $session);

        $attendanceA = Attendance::where('course_session_id', $session->id)->where('user_id', $studentA->id)->firstOrFail();
        $attendanceB = Attendance::where('course_session_id', $session->id)->where('user_id', $studentB->id)->firstOrFail();

        $test
            ->setTableActionData([
                'attendances' => [
                    [
                        'attendance_id' => $attendanceA->id,
                        'student_name' => $studentA->name,
                        'student_email' => $studentA->email,
                        'status' => Attendance::STATUS_PRESENT,
                        'notes' => 'Participated actively',
                    ],
                    [
                        'attendance_id' => $attendanceB->id,
                        'student_name' => $studentB->name,
                        'student_email' => $studentB->email,
                        'status' => Attendance::STATUS_LATE,
                        'notes' => 'Joined 10 mins late',
                    ],
                ],
                'session_notes' => 'Covered schema auditing and indexing.',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $session->refresh();
        $this->assertSame(CourseSession::STATUS_COMPLETED, $session->status);
        $this->assertSame('Covered schema auditing and indexing.', $session->notes);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendanceA->id,
            'status' => Attendance::STATUS_PRESENT,
            'notes' => 'Participated actively',
        ]);
        $this->assertDatabaseHas('attendances', [
            'id' => $attendanceB->id,
            'status' => Attendance::STATUS_LATE,
            'notes' => 'Joined 10 mins late',
        ]);
    }

    public function test_admin_mark_completed_wizard_works_with_empty_roster(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Empty Cohort Course',
            'code' => 'EMP-101',
            'is_active' => true,
        ]);

        $session = CourseSession::create([
            'course_id' => $course->id,
            'type' => 'group',
            'title' => 'Orientation',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => CourseSession::STATUS_SCHEDULED,
        ]);

        Livewire::actingAs($admin)
            ->test(AdminListCourseSessions::class)
            ->callTableAction('markCompleted', $session, [
                'attendances' => [],
                'session_notes' => 'No students attended.',
            ])
            ->assertHasNoTableActionErrors();

        $session->refresh();
        $this->assertSame(CourseSession::STATUS_COMPLETED, $session->status);
        $this->assertSame('No students attended.', $session->notes);
    }
}
