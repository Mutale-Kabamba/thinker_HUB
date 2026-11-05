<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\ResourceVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstructorCreateCourseReproduceTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_options_contain_all_six_tiers_in_specified_order(): void
    {
        $expected = [
            'Beginner' => 'Beginner',
            'Intermediate' => 'Intermediate',
            'Advanced' => 'Advanced',
            'Advanced (Beginner to Advanced)' => 'Advanced (Beginner to Advanced)',
            'Advanced (Intermediate to Advanced)' => 'Advanced (Intermediate to Advanced)',
            'Intermediate (Beginner to Intermediate)' => 'Intermediate (Beginner to Intermediate)',
        ];

        $this->assertSame($expected, CourseForm::getLevelOptions());
    }

    public function test_normalize_level_preserves_compound_skill_levels(): void
    {
        $this->assertSame('Beginner', CourseForm::normalizeLevel('Beginner'));
        $this->assertSame('Intermediate', CourseForm::normalizeLevel('Intermediate'));
        $this->assertSame('Advanced', CourseForm::normalizeLevel('Advanced'));
        $this->assertSame('Advanced (Beginner to Advanced)', CourseForm::normalizeLevel('Advanced (Beginner to Advanced)'));
        $this->assertSame('Advanced (Intermediate to Advanced)', CourseForm::normalizeLevel('Advanced (Intermediate to Advanced)'));
        $this->assertSame('Intermediate (Beginner to Intermediate)', CourseForm::normalizeLevel('Intermediate (Beginner to Intermediate)'));

        // Case insensitivity and common prompt typos
        $this->assertSame('Beginner', CourseForm::normalizeLevel('beginer'));
        $this->assertSame('Advanced (Beginner to Advanced)', CourseForm::normalizeLevel('advanced (beginer to advanced)'));
        $this->assertSame('Intermediate (Beginner to Intermediate)', CourseForm::normalizeLevel('intermediate (beginner to intermediate)'));
    }

    public function test_instructor_can_render_create_course_page(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $this->actingAs($instructor);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        Livewire::test(CreateCourse::class)
            ->assertSuccessful();
    }

    public function test_instructor_submitting_course_with_all_six_levels_and_curriculum_path(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $existingCourse = Course::create([
            'title' => 'Existing Course',
            'code' => 'EXIST-1',
        ]);

        $assignment = Assignment::create([
            'name' => 'First Homework',
            'description' => 'Complete chapter 1',
            'course_id' => $existingCourse->id,
        ]);

        $quiz = Quiz::create([
            'course_id' => $existingCourse->id,
            'title' => 'Midterm Assessment',
            'description' => 'Test your knowledge',
            'is_active' => true,
        ]);

        $video = ResourceVideo::create([
            'course_id' => $existingCourse->id,
            'title' => 'Intro Video',
            'youtube_url' => 'https://youtube.com/watch?v=sample',
            'category' => 'Programming',
        ]);

        $this->actingAs($instructor);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        $test = Livewire::test(CreateCourse::class);

        $test->fillForm([
            'title' => 'Mastering AI & Cloud',
            'code' => 'AIC-2026',
            'timeline' => '12 Weeks',
            'offering_mode' => 'ongoing',
            'sections' => [
                [
                    'title' => 'Module 1: Foundations',
                    'lessons' => [
                        [
                            'title' => 'Lesson 1: Getting Started',
                            'type' => 'reading',
                            'reading_body' => '<p>Welcome to the course.</p>',
                        ],
                        [
                            'title' => 'Lesson 2: Practical Task',
                            'type' => 'assignment',
                            'content' => $assignment->id,
                        ],
                        [
                            'title' => 'Lesson 3: Review Quiz',
                            'type' => 'quiz',
                            'content' => $quiz->id,
                        ],
                        [
                            'title' => 'Lesson 4: Video Lecture',
                            'type' => 'video',
                            'content' => $video->id,
                        ],
                    ],
                ],
            ],
            'fees' => [
                ['category' => 'one_on_one', 'level' => 'Beginner', 'amount' => 'K500', 'duration' => '4 Weeks'],
                ['category' => 'group', 'level' => 'Intermediate', 'amount' => 'K800', 'duration' => '6 Weeks'],
                ['category' => 'self_paced', 'level' => 'Advanced', 'amount' => 'K1200', 'duration' => '8 Weeks'],
                ['category' => 'one_on_one', 'level' => 'Advanced (Beginner to Advanced)', 'amount' => 'K2500', 'duration' => '16 Weeks'],
                ['category' => 'group', 'level' => 'Advanced (Intermediate to Advanced)', 'amount' => 'K1800', 'duration' => '10 Weeks'],
                ['category' => 'self_paced', 'level' => 'Intermediate (Beginner to Intermediate)', 'amount' => 'K1100', 'duration' => '8 Weeks'],
            ],
            'level_progression' => [
                ['level' => 'Beginner', 'details' => 'Foundational concepts and setup.'],
                ['level' => 'Intermediate', 'details' => 'Independent practical projects.'],
                ['level' => 'Advanced', 'details' => 'Enterprise architecture.'],
                ['level' => 'Advanced (Beginner to Advanced)', 'details' => 'Zero-to-hero comprehensive mastery.'],
                ['level' => 'Advanced (Intermediate to Advanced)', 'details' => 'Accelerated specialist track.'],
                ['level' => 'Intermediate (Beginner to Intermediate)', 'details' => 'Bridge from novice to confident developer.'],
            ],
        ]);

        $test->call('create');
        $test->assertHasNoFormErrors();

        $course = Course::where('code', 'AIC-2026')->first();
        $this->assertNotNull($course);
        $this->assertFalse($course->is_active); // Pending admin review
        $this->assertTrue($course->instructors->contains($instructor));

        $feesDecoded = json_decode($course->fees, true);
        $this->assertIsArray($feesDecoded);

        $progressionDecoded = json_decode($course->level_progression, true);
        $this->assertIsArray($progressionDecoded);
        $this->assertCount(6, $progressionDecoded);
        $this->assertSame('Advanced (Beginner to Advanced)', $progressionDecoded[3]['level']);
        $this->assertSame('Advanced (Intermediate to Advanced)', $progressionDecoded[4]['level']);
        $this->assertSame('Intermediate (Beginner to Intermediate)', $progressionDecoded[5]['level']);
    }
}
