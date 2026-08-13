<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\Schedule as InstructorSchedule;
use App\Filament\Student\Pages\Schedule as StudentSchedule;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiRoleAndScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dual_role_student_and_instructor_has_contributor_access(): void
    {
        $dualRoleUser = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->assertTrue($dualRoleUser->isInstructor());
        $this->assertTrue($dualRoleUser->isStudent());
        $this->assertTrue($dualRoleUser->isContributor());

        // Check contributor content types
        $this->assertTrue($dualRoleUser->canSubmitType('blog'));
        $this->assertTrue($dualRoleUser->canSubmitType('tip_trick'));
        $this->assertTrue($dualRoleUser->canSubmitType('opportunity'));
        $this->assertTrue($dualRoleUser->canSubmitType('video'));

        // Check panel access permissions
        $studentPanel = filament()->getPanel('student');
        $instructorPanel = filament()->getPanel('instructor');
        $contributorPanel = filament()->getPanel('contributor');

        $this->assertTrue($dualRoleUser->canAccessPanel($studentPanel));
        $this->assertTrue($dualRoleUser->canAccessPanel($instructorPanel));
        $this->assertTrue($dualRoleUser->canAccessPanel($contributorPanel));
    }

    public function test_regular_student_cannot_access_instructor_or_contributor_panels(): void
    {
        $regularStudent = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $this->assertFalse($regularStudent->isInstructor());
        $this->assertTrue($regularStudent->isStudent());
        $this->assertFalse($regularStudent->isContributor());

        $studentPanel = filament()->getPanel('student');
        $instructorPanel = filament()->getPanel('instructor');
        $contributorPanel = filament()->getPanel('contributor');

        $this->assertTrue($regularStudent->canAccessPanel($studentPanel));
        $this->assertFalse($regularStudent->canAccessPanel($instructorPanel));
        $this->assertFalse($regularStudent->canAccessPanel($contributorPanel));
    }

    public function test_unapproved_dual_role_user_cannot_access_panels(): void
    {
        $unapprovedDual = User::factory()->create([
            'role' => 'instructor',
            'is_active' => false,
        ]);

        $studentPanel = filament()->getPanel('student');
        $instructorPanel = filament()->getPanel('instructor');
        $contributorPanel = filament()->getPanel('contributor');

        $this->assertFalse($unapprovedDual->canAccessPanel($studentPanel));
        $this->assertFalse($unapprovedDual->canAccessPanel($instructorPanel));
        $this->assertFalse($unapprovedDual->canAccessPanel($contributorPanel));
    }

    public function test_student_schedule_defaults_to_month_and_supports_filters_and_modal(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $course = Course::query()->create([
            'title' => 'Advanced Robotics',
            'code' => 'ROB-301',
            'is_active' => true,
        ]);
        $student->courses()->attach($course->id);

        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'title' => 'Robotics Lab 1',
            'type' => 'group',
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'status' => 'scheduled',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $this->actingAs($student);

        Livewire::test(StudentSchedule::class)
            ->assertSet('rangeMode', 'month')
            ->assertSet('filterStatus', '')
            ->assertSee('Advanced Robotics')
            ->assertSee('Robotics Lab 1')
            ->call('setRangeMode', 'week')
            ->assertSet('rangeMode', 'week')
            ->call('setFilterStatus', 'scheduled')
            ->assertSet('filterStatus', 'scheduled')
            ->call('openClassModal', $session->id)
            ->assertSet('showSessionDetailsModal', true)
            ->assertSet('selectedSessionDetails.id', $session->id)
            ->assertSet('selectedSessionDetails.title', 'Robotics Lab 1')
            ->call('closeClassModal')
            ->assertSet('showSessionDetailsModal', false)
            ->assertSet('selectedSessionDetails', null);
    }

    public function test_instructor_schedule_defaults_to_month_and_supports_modal(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $course = Course::query()->create([
            'title' => 'Web Architecture',
            'code' => 'WEB-201',
            'is_active' => true,
            'instructor_id' => $instructor->id,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Microservices Workshop',
            'type' => 'group',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
            'meeting_link' => 'https://meet.google.com/xyz-uvw-rst',
        ]);

        $this->actingAs($instructor);

        Livewire::test(InstructorSchedule::class)
            ->assertSet('rangeMode', 'month')
            ->assertSet('filterStatus', '')
            ->assertSee('WEB-201')
            ->assertSee('Microservices Workshop')
            ->call('setRangeMode', 'day')
            ->assertSet('rangeMode', 'day')
            ->call('openClassModal', $session->id)
            ->assertSet('showSessionDetailsModal', true)
            ->assertSet('selectedSessionDetails.id', $session->id)
            ->assertSet('selectedSessionDetails.title', 'Microservices Workshop')
            ->call('closeClassModal')
            ->assertSet('showSessionDetailsModal', false)
            ->assertSet('selectedSessionDetails', null);
    }
}
