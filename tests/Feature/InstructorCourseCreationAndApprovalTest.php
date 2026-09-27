<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InstructorCourseCreationAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_model_resolves_uploaded_image_url_and_fallback(): void
    {
        Storage::fake('public');

        // 1. Course with uploaded image
        $courseWithImage = Course::query()->create([
            'title' => 'Mastering Laravel & Vue',
            'code' => 'LARAVEL-101',
            'image_path' => 'course-images/laravel_hero.png',
            'is_active' => true,
        ]);

        $this->assertNotNull($courseWithImage->image_url);
        $this->assertStringContainsString('course-images/laravel_hero.png', $courseWithImage->course_image_url);

        // 2. Course without uploaded image (uses keyword or default fallback)
        $courseWithoutImage = Course::query()->create([
            'title' => 'Office Excel Bootcamp',
            'code' => 'EXCEL-202',
            'image_path' => null,
            'is_active' => true,
        ]);

        $this->assertNull($courseWithoutImage->image_url);
        $this->assertStringContainsString('images/courses/office.png', $courseWithoutImage->course_image_url);
    }

    public function test_public_course_cards_display_uploaded_image_on_courses_and_home_pages(): void
    {
        Storage::fake('public');

        $course = Course::query()->create([
            'title' => 'Graphic Design Masterclass',
            'code' => 'DES-300',
            'image_path' => 'course-images/design_cover.png',
            'is_active' => true,
        ]);

        // Home page
        $homeResponse = $this->get('/');
        $homeResponse->assertOk();
        $homeResponse->assertSee($course->course_image_url);
        $homeResponse->assertSee('Graphic Design Masterclass');

        // Courses page
        $coursesResponse = $this->get('/courses');
        $coursesResponse->assertOk();
        $coursesResponse->assertSee($course->course_image_url);
        $coursesResponse->assertSee('Graphic Design Masterclass');

        // Course details page
        $courseDetailResponse = $this->get('/courses/' . $course->id);
        $courseDetailResponse->assertOk();
        $courseDetailResponse->assertSee($course->course_image_url);
    }

    public function test_instructor_can_access_create_course_page_in_teach_panel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'name' => 'Jane Instructor',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($instructor);

        $response = $this->get('/teach/course-resource/courses/create');
        $response->assertOk();
    }

    public function test_instructor_course_creation_creates_inactive_course_pending_admin_approval(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'name' => 'Prof. Alex Smith',
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($instructor);

        Livewire::test(\App\Filament\Instructor\Resources\CourseResource\Pages\CreateCourse::class)
            ->fillForm([
                'title' => 'Robotics & Automation 101',
                'code' => 'ROB-101',
                'offering_mode' => 'once_off',
                'description' => 'Hands-on robotics course.',
                'overview' => 'Learn microcontroller programming.',
                'image_path' => [UploadedFile::fake()->image('robotics.png')],
                'is_open_enrollment' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $course = Course::query()->where('code', 'ROB-101')->first();
        $this->assertNotNull($course);
        $this->assertSame('Robotics & Automation 101', $course->title);
        $this->assertNotNull($course->image_path);
        $this->assertSame('Prof. Alex Smith', $course->course_by);

        // MUST be inactive pending admin approval
        $this->assertFalse($course->is_active);

        // Instructor must be attached to the course
        $this->assertTrue($instructor->instructorCourses()->where('courses.id', $course->id)->exists());
    }

    public function test_inactive_course_is_hidden_from_public_until_admin_approves(): void
    {
        $course = Course::query()->create([
            'title' => 'Advanced AI & Deep Learning',
            'code' => 'AI-400',
            'image_path' => 'course-images/ai_chip.png',
            'is_active' => false, // Pending approval
        ]);

        // Should not be visible on public courses page
        $coursesResponse = $this->get('/courses');
        $coursesResponse->assertOk();
        $coursesResponse->assertDontSee('Advanced AI & Deep Learning');

        // Should return 404 when directly accessed publicly
        $singleResponse = $this->get('/courses/' . $course->id);
        $singleResponse->assertNotFound();

        // Admin approves and activates the course
        $course->update(['is_active' => true]);

        // Now visible on public pages
        $coursesAfterApproval = $this->get('/courses');
        $coursesAfterApproval->assertOk();
        $coursesAfterApproval->assertSee('Advanced AI & Deep Learning');
        $coursesAfterApproval->assertSee($course->course_image_url);

        $singleAfterApproval = $this->get('/courses/' . $course->id);
        $singleAfterApproval->assertOk();
        $singleAfterApproval->assertSee('Advanced AI & Deep Learning');
    }
}
