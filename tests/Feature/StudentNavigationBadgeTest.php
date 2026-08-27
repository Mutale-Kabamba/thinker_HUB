<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Assessments;
use App\Filament\Student\Pages\Assignments;
use App\Filament\Student\Pages\LearningResources;
use App\Filament\Student\Pages\Materials;
use App\Filament\Student\Pages\Quizzes;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ResourceVideo;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class StudentNavigationBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('student'));
        Cache::flush();
    }

    public function test_quizzes_badge_shows_only_active_published_uncompleted_quizzes_in_enrolled_courses(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $enrolledCourse = Course::query()->create([
            'title' => 'Biology 101',
            'code' => 'BIO-101',
            'is_active' => true,
        ]);

        $otherCourse = Course::query()->create([
            'title' => 'Chemistry 101',
            'code' => 'CHE-101',
            'is_active' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $enrolledCourse->id,
        ]);

        // Active, published quiz in enrolled course (should be counted)
        $quiz1 = Quiz::query()->create([
            'course_id' => $enrolledCourse->id,
            'title' => 'Bio Quiz 1',
            'is_active' => true,
            'publish_at' => now()->subHour(),
        ]);

        // Another active quiz in enrolled course (should be counted)
        $quiz2 = Quiz::query()->create([
            'course_id' => $enrolledCourse->id,
            'title' => 'Bio Quiz 2',
            'is_active' => true,
            'publish_at' => null,
        ]);

        // Inactive quiz in enrolled course (should NOT be counted)
        Quiz::query()->create([
            'course_id' => $enrolledCourse->id,
            'title' => 'Bio Quiz Draft',
            'is_active' => false,
        ]);

        // Scheduled future quiz in enrolled course (should NOT be counted)
        Quiz::query()->create([
            'course_id' => $enrolledCourse->id,
            'title' => 'Bio Quiz Future',
            'is_active' => true,
            'publish_at' => now()->addDays(2),
        ]);

        // Active quiz in non-enrolled course (should NOT be counted)
        Quiz::query()->create([
            'course_id' => $otherCourse->id,
            'title' => 'Chem Quiz 1',
            'is_active' => true,
        ]);

        $this->actingAs($student);

        // Count should be 2
        $this->assertEquals('2', Quizzes::getNavigationBadge());
        $this->assertEquals('warning', Quizzes::getNavigationBadgeColor());

        // Student completes quiz1
        QuizAttempt::query()->create([
            'quiz_id' => $quiz1->id,
            'user_id' => $student->id,
            'completed_at' => now(),
            'score' => 80,
            'total_points' => 100,
            'percentage' => 80,
            'passed' => true,
        ]);

        // Now count should be 1
        $this->assertEquals('1', Quizzes::getNavigationBadge());

        // Student completes quiz2
        QuizAttempt::query()->create([
            'quiz_id' => $quiz2->id,
            'user_id' => $student->id,
            'completed_at' => now(),
            'score' => 90,
            'total_points' => 100,
            'percentage' => 90,
            'passed' => true,
        ]);

        // No pending quizzes -> badge must return null
        $this->assertNull(Quizzes::getNavigationBadge());
    }

    public function test_materials_badge_displays_new_materials_and_auto_dismisses_on_view(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'Math 101',
            'code' => 'MTH-101',
            'is_active' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        LearningMaterial::query()->create([
            'title' => 'Math Lecture 1 Notes',
            'course_id' => $course->id,
            'created_at' => now(),
        ]);

        $this->actingAs($student);

        // Initially badge appears with count 1
        $this->assertEquals('1', Materials::getNavigationBadge());
        $this->assertEquals('primary', Materials::getNavigationBadgeColor());

        // Student visits the Materials page (mounts)
        Livewire::test(Materials::class)->assertSuccessful();

        // Badge should now be auto-dismissed (null)
        $this->assertNull(Materials::getNavigationBadge());
    }

    public function test_learning_resources_badge_displays_new_videos_and_auto_dismisses_on_view(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        ResourceVideo::query()->create([
            'title' => 'Introduction to Calculus',
            'youtube_url' => 'https://youtube.com/watch?v=12345',
            'is_published' => true,
            'created_at' => now(),
        ]);

        $this->actingAs($student);

        // Initially badge appears with count 1
        $this->assertEquals('1', LearningResources::getNavigationBadge());
        $this->assertEquals('info', LearningResources::getNavigationBadgeColor());

        // Student visits the LearningResources page (mounts)
        Livewire::test(LearningResources::class)->assertSuccessful();

        // Badge should now be auto-dismissed (null)
        $this->assertNull(LearningResources::getNavigationBadge());
    }

    public function test_assignments_badge_displays_pending_and_auto_dismisses_on_view(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'History 101',
            'code' => 'HIS-101',
            'is_active' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $assignment = Assignment::query()->create([
            'name' => 'Essay 1',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(7),
            'created_at' => now(),
        ]);

        $this->actingAs($student);

        // Initially badge appears with count 1
        $this->assertEquals('1', Assignments::getNavigationBadge());
        $this->assertEquals('danger', Assignments::getNavigationBadgeColor());

        // Student visits the Assignments page (mounts)
        Livewire::test(Assignments::class)->assertSuccessful();

        // Badge should now be auto-dismissed (null)
        $this->assertNull(Assignments::getNavigationBadge());
    }

    public function test_assessments_badge_displays_pending_and_auto_dismisses_on_view(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'English 101',
            'code' => 'ENG-101',
            'is_active' => true,
        ]);

        Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $assessment = Assessment::query()->create([
            'name' => 'Midterm Assessment',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(7),
            'created_at' => now(),
        ]);

        $this->actingAs($student);

        // Initially badge appears with count 1
        $this->assertEquals('1', Assessments::getNavigationBadge());
        $this->assertEquals('danger', Assessments::getNavigationBadgeColor());

        // Student visits the Assessments page (mounts)
        Livewire::test(Assessments::class)->assertSuccessful();

        // Badge should now be auto-dismissed (null)
        $this->assertNull(Assessments::getNavigationBadge());
    }
}
