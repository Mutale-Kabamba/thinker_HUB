<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\Schedule as InstructorSchedule;
use App\Filament\Instructor\Resources\CourseSessionResource\Pages\ListCourseSessions as InstructorListCourseSessions;
use App\Filament\Resources\CourseSessions\Pages\ListCourseSessions as AdminListCourseSessions;
use App\Filament\Student\Pages\Schedule as StudentSchedule;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseSessionsListPerCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_course_sessions_table_is_grouped_by_course(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $courseA = Course::create([
            'title' => 'Laravel Mastery',
            'code' => 'LAR-101',
            'is_active' => true,
        ]);

        $courseB = Course::create([
            'title' => 'Vue.js Essentials',
            'code' => 'VUE-201',
            'is_active' => true,
        ]);

        CourseSession::create([
            'course_id' => $courseA->id,
            'title' => 'Routing and Middleware',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'scheduled',
        ]);

        CourseSession::create([
            'course_id' => $courseB->id,
            'title' => 'Reactivity and Components',
            'session_date' => now()->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'status' => 'scheduled',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminListCourseSessions::class)
            ->assertOk()
            ->assertSee('Laravel Mastery')
            ->assertSee('Vue.js Essentials')
            ->assertSee('Routing and Middleware')
            ->assertSee('Reactivity and Components');
    }

    public function test_instructor_course_sessions_table_is_grouped_by_course(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Python Backend',
            'code' => 'PY-101',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Django APIs',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'status' => 'scheduled',
        ]);

        Livewire::actingAs($instructor)
            ->test(InstructorListCourseSessions::class)
            ->assertOk()
            ->assertSee('Python Backend')
            ->assertSee('Django APIs');
    }

    public function test_instructor_schedule_page_presents_sessions_grouped_per_course(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course1 = Course::create([
            'title' => 'Cyber Security',
            'code' => 'SEC-101',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);

        $course2 = Course::create([
            'title' => 'Cloud Devops',
            'code' => 'DEV-202',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);

        CourseSession::create([
            'course_id' => $course1->id,
            'instructor_id' => $instructor->id,
            'title' => 'Network Penetration',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
        ]);

        CourseSession::create([
            'course_id' => $course2->id,
            'instructor_id' => $instructor->id,
            'title' => 'Docker Kubernetes CI/CD',
            'session_date' => now()->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '12:30:00',
            'status' => 'scheduled',
        ]);

        Livewire::actingAs($instructor)
            ->test(InstructorSchedule::class)
            ->assertOk()
            ->assertSee('Cyber Security')
            ->assertSee('SEC-101')
            ->assertSee('Network Penetration')
            ->assertSee('Cloud Devops')
            ->assertSee('DEV-202')
            ->assertSee('Docker Kubernetes CI/CD');
    }

    public function test_student_schedule_page_presents_sessions_grouped_per_course(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Mobile App Flutter',
            'code' => 'FLUT-301',
            'is_active' => true,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Spring 2026',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'course_intake_id' => $intake->id,
        ]);

        CourseSession::create([
            'course_id' => $course->id,
            'title' => 'State Management with Bloc',
            'session_date' => now()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:30:00',
            'status' => 'scheduled',
        ]);

        Livewire::actingAs($student)
            ->test(StudentSchedule::class)
            ->assertOk()
            ->assertSee('Mobile App Flutter')
            ->assertSee('FLUT-301')
            ->assertSee('State Management with Bloc');
    }
}
