<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdTechDashboardSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_overview_renders_successfully(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Full-Stack Web Development',
            'code' => 'CS-301',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);

        $response = $this->actingAs($student)->followingRedirects()->get('/learn');
        $response->assertSuccessful();
        $response->assertSee('My ranking');
        $response->assertSee('Explore More');
        $response->assertSee('Status');
        $response->assertSee('Learning Materials');
        $response->assertSee('Assignments');
        $response->assertSee('Assessments');
        $response->assertSee('My Courses');
        $response->assertSee('Upcoming');
        $response->assertSee('Full-Stack Web Development');
    }

    public function test_instructor_dashboard_overview_renders_successfully(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Data Structures & Algorithms',
            'code' => 'CS-201',
            'is_active' => true,
        ]);

        $instructor->instructorCourses()->attach($course->id);

        $response = $this->actingAs($instructor)->followingRedirects()->get('/teach');
        $response->assertSuccessful();
        $response->assertSee('Welcome, ' . $instructor->first_name);
        $response->assertSee('Total Classes');
        $response->assertSee('Total Students');
        $response->assertSee('Pending Reviews');
        $response->assertSee('Upcoming Sessions');
        $response->assertSee('Data Structures & Algorithms');
    }

    public function test_admin_dashboard_renders_successfully(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->followingRedirects()->get('/manage');
        $response->assertSuccessful();
    }
}
