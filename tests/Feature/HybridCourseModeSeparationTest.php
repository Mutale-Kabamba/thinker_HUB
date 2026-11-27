<?php

namespace Tests\Feature;

use App\Livewire\CoursePlayer;
use App\Livewire\Student\ContinueLearningWidget;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HybridCourseModeSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cohort_courses_do_not_overlap_with_scorm_classroom_views(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $cohortCourse = Course::create([
            'title' => 'Digital Skills Cohort',
            'code' => 'DSP-COHORT',
            'slug' => 'digital-skills-cohort',
            'delivery_mode' => Course::DELIVERY_COHORT,
            'is_active' => true,
        ]);

        $selfPacedCourse = Course::create([
            'title' => 'Cloud & AI Interactive Lab',
            'code' => 'CAI-101',
            'slug' => 'cloud-ai-interactive-lab',
            'delivery_mode' => Course::DELIVERY_SELF_PACED,
            'is_active' => true,
        ]);

        Lesson::create([
            'course_id' => $selfPacedCourse->id,
            'title' => 'Module 1: Intro',
            'type' => 'reading',
            'reading_body' => '<p>Welcome</p>',
            'order_column' => 1,
            'sort_order' => 1,
            'is_published' => true,
        ]);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $cohortCourse->id]);
        Enrollment::create(['user_id' => $student->id, 'course_id' => $selfPacedCourse->id]);

        // 1. SCORM course picker scope excludes cohort-only courses
        $eligibleIds = Course::query()->classroomCapable()->pluck('id')->all();
        $this->assertNotContains($cohortCourse->id, $eligibleIds);
        $this->assertContains($selfPacedCourse->id, $eligibleIds);

        // 2. ContinueLearningWidget routes cohort course to Course Hub and self-paced to Classroom
        $this->actingAs($student);
        Livewire::test(ContinueLearningWidget::class)
            ->assertSee('Digital Skills Cohort')
            ->assertSee('Live Cohort')
            ->assertSee('Open Course Hub')
            ->assertSee('Cloud & AI Interactive Lab')
            ->assertSee('Self-Paced')
            ->assertSee('Resume')
            ->assertSee('/classroom/cloud-ai-interactive-lab')
            ->assertDontSee('/classroom/digital-skills-cohort');

        // 3. Direct classroom visit on a cohort-only course shows helpful Student Portal links
        Livewire::test(CoursePlayer::class, ['course' => $cohortCourse])
            ->assertSee('Live Cohort Course')
            ->assertSee('Course Materials')
            ->assertSee('Live Schedule');

        // 4. Submitting a rating in CoursePlayer updates Course average_rating & review_count for public pages
        Livewire::test(CoursePlayer::class, ['course' => $selfPacedCourse])
            ->call('submitRating', 5, 'Super clear SCORM module!');

        $selfPacedCourse->refresh();
        $this->assertEquals(5.00, (float) $selfPacedCourse->average_rating);
        $this->assertSame(1, (int) $selfPacedCourse->review_count);
    }
}
