<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\Schedule as InstructorSchedule;
use App\Filament\Student\Pages\Schedule as StudentSchedule;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseCalendarColorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_and_course_session_get_color_scheme_returns_valid_palette_structure(): void
    {
        $courseA = Course::create([
            'title' => 'Advanced Python Mastery',
            'code' => 'PY-301',
            'description' => 'Comprehensive Python Course',
            'price' => 100,
        ]);

        $courseB = Course::create([
            'title' => 'Fullstack React & Next.js',
            'code' => 'REACT-202',
            'description' => 'React development',
            'price' => 150,
        ]);

        $schemeA = $courseA->getColorScheme();
        $schemeB = $courseB->getColorScheme();

        $this->assertIsArray($schemeA);
        $this->assertArrayHasKey('key', $schemeA);
        $this->assertArrayHasKey('label', $schemeA);
        $this->assertArrayHasKey('hex', $schemeA);
        $this->assertArrayHasKey('dot', $schemeA);
        $this->assertArrayHasKey('pill_bg', $schemeA);
        $this->assertArrayHasKey('badge_bg', $schemeA);
        $this->assertArrayHasKey('card_border', $schemeA);
        $this->assertArrayHasKey('card_bg', $schemeA);
        $this->assertArrayHasKey('accent_text', $schemeA);
        $this->assertArrayHasKey('time_badge', $schemeA);
        $this->assertArrayHasKey('bar', $schemeA);

        // Deterministic check: calling it again returns identical scheme
        $this->assertEquals($schemeA, $courseA->getColorScheme());

        // CourseSession delegates to Course scheme
        $session = CourseSession::create([
            'course_id' => $courseA->id,
            'title' => 'Python Async Workshop',
            'type' => 'group',
            'session_date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
        ]);

        $sessionScheme = $session->getColorScheme();
        $this->assertEquals($schemeA['key'], $sessionScheme['key']);
        $this->assertEquals($schemeA['hex'], $sessionScheme['hex']);
    }

    public function test_get_color_scheme_for_handles_null_and_empty_values_gracefully(): void
    {
        $schemeNull = Course::getColorSchemeFor(null, '', '');
        $this->assertIsArray($schemeNull);
        $this->assertArrayHasKey('hex', $schemeNull);
        $this->assertArrayHasKey('key', $schemeNull);

        $schemeFallback = Course::getColorSchemeFor(null, 'Special Course Without ID', '');
        $this->assertIsArray($schemeFallback);
        $this->assertNotEmpty($schemeFallback['key']);
    }

    public function test_instructor_schedule_page_displays_course_legend_and_colored_sessions(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course1 = Course::create([
            'title' => 'Data Science Bootcamp',
            'code' => 'DS-101',
            'description' => 'DS course',
            'instructor_id' => $instructor->id,
            'price' => 200,
        ]);

        $course2 = Course::create([
            'title' => 'Cloud Architecture AWS',
            'code' => 'AWS-401',
            'description' => 'AWS course',
            'instructor_id' => $instructor->id,
            'price' => 250,
        ]);

        $session1 = CourseSession::create([
            'course_id' => $course1->id,
            'instructor_id' => $instructor->id,
            'title' => 'Pandas & NumPy Intro',
            'type' => 'group',
            'session_date' => now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
        ]);

        $session2 = CourseSession::create([
            'course_id' => $course2->id,
            'instructor_id' => $instructor->id,
            'title' => 'AWS VPC & Subnets',
            'type' => 'group',
            'session_date' => now()->format('Y-m-d'),
            'start_time' => '14:00:00',
            'end_time' => '15:30:00',
            'status' => 'scheduled',
        ]);

        $col1 = $course1->getColorScheme();
        $col2 = $course2->getColorScheme();

        Livewire::actingAs($instructor)
            ->test(InstructorSchedule::class)
            ->assertSee('Courses:')
            ->assertSee('DS-101')
            ->assertSee('AWS-401')
            ->assertSee('Pandas &amp; NumPy Intro', false)
            ->assertSee('AWS VPC &amp; Subnets', false)
            ->assertSee($col1['dot'])
            ->assertSee($col2['dot']);
    }

    public function test_student_schedule_page_displays_course_legend_and_colored_sessions(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Machine Learning Foundations',
            'code' => 'ML-201',
            'description' => 'ML course',
            'instructor_id' => $instructor->id,
            'price' => 300,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort 2026',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(3),
            'status' => 'active',
        ]);

        \App\Models\Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'course_intake_id' => $intake->id,
        ]);

        $session = CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Linear Regression Deep Dive',
            'type' => 'group',
            'session_date' => now()->format('Y-m-d'),
            'start_time' => '11:00:00',
            'end_time' => '12:30:00',
            'status' => 'scheduled',
        ]);

        $col = $course->getColorScheme();

        Livewire::actingAs($student)
            ->test(StudentSchedule::class)
            ->assertSee('Courses:')
            ->assertSee('ML-201')
            ->assertSee('Linear Regression Deep Dive')
            ->assertSee($col['dot']);
    }
}
