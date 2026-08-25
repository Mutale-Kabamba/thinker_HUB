<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DualRoleAdminInstructorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_holds_both_admin_and_instructor_roles(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isInstructor());
        $this->assertTrue($admin->hasDualRole());
    }

    public function test_admin_can_access_both_admin_and_instructor_panels(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $adminPanel = Filament::getPanel('admin');
        $instructorPanel = Filament::getPanel('instructor');

        $this->assertTrue($admin->canAccessPanel($adminPanel));
        $this->assertTrue($admin->canAccessPanel($instructorPanel));
    }

    public function test_admin_can_be_assigned_courses_as_instructor(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Full-Stack Web Development',
            'code' => 'CS-101',
            'is_active' => true,
        ]);

        $admin->instructorCourses()->attach($course->id);

        $this->assertTrue($admin->instructorCourses()->where('courses.id', $course->id)->exists());
        $this->assertTrue($course->instructors()->where('users.id', $admin->id)->exists());
    }

    public function test_admin_can_be_assigned_to_course_session(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Lead',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Algorithms & Data Structures',
            'code' => 'CS-201',
            'is_active' => true,
        ]);

        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'instructor_id' => $admin->id,
            'title' => 'Binary Trees and Graphs',
            'session_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);

        $this->assertEquals($admin->id, $session->instructor_id);
        $this->assertEquals('Admin Lead', $session->instructor->name);
    }

    public function test_public_instructors_page_includes_admins_teaching_courses(): void
    {
        $adminInstructor = User::factory()->create([
            'name' => 'Dr. Mutale',
            'role' => 'admin',
            'is_active' => true,
            'bio' => 'Lead AI Instructor and Platform Architect',
        ]);

        $course = Course::query()->create([
            'title' => 'Artificial Intelligence & Robotics',
            'code' => 'AI-301',
            'is_active' => true,
        ]);

        $adminInstructor->instructorCourses()->attach($course->id);

        $response = $this->get('/network?role=instructor');
        $response->assertStatus(200);
        $response->assertSee('Dr. Mutale');
    }
}
