<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Assessments;
use App\Filament\Student\Pages\Assignments;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GradedSubmissionLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_graded_assignment_submit_button_is_disabled_and_cannot_be_resubmitted(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        $student->courses()->attach($course->id);

        $assignment = Assignment::query()->create([
            'name' => 'HTML & CSS Project',
            'course_id' => $course->id,
            'scope' => 'all',
        ]);

        AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'My initial work',
            'status' => 'Graded',
            'grade' => 95,
            'feedback' => 'Excellent job!',
            'submitted_at' => now(),
        ]);

        $this->actingAs($student);

        // Verify Livewire component indicates graded and renders disabled submit button
        Livewire::test(Assignments::class)
            ->assertSee('HTML & CSS Project')
            ->assertSee('Assignment is graded — submissions locked')
            ->call('submit', $assignment->id)
            ->assertNotified('This assignment has already been graded and cannot be resubmitted.');

        // Verify submission was NOT overwritten
        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals(95, $submission->grade);
        $this->assertEquals('Graded', $submission->status);
        $this->assertEquals('My initial work', $submission->content);
    }

    public function test_graded_assessment_submit_button_is_disabled_and_cannot_be_resubmitted(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        $student->courses()->attach($course->id);

        $assessment = Assessment::query()->create([
            'name' => 'Midterm Assessment',
            'user_id' => $student->id,
            'course_id' => $course->id,
            'scope' => 'all',
        ]);

        AssessmentSubmission::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'content' => 'My midterm answers',
            'status' => 'Graded',
            'score' => 88,
            'feedback' => 'Good work',
            'submitted_at' => now(),
        ]);

        $this->actingAs($student);

        // Verify Livewire component indicates graded and renders disabled submit button
        Livewire::test(Assessments::class)
            ->assertSee('Midterm Assessment')
            ->assertSee('Assessment is graded — submissions locked')
            ->call('submit', $assessment->id)
            ->assertNotified('This assessment has already been graded and cannot be resubmitted.');

        // Verify submission was NOT overwritten
        $submission = AssessmentSubmission::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals(88, $submission->score);
        $this->assertEquals('Graded', $submission->status);
        $this->assertEquals('My midterm answers', $submission->content);
    }

    public function test_ungraded_assignment_can_be_submitted(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        $student->courses()->attach($course->id);

        $assignment = Assignment::query()->create([
            'name' => 'Ungraded Task',
            'course_id' => $course->id,
            'scope' => 'all',
        ]);

        $this->actingAs($student);

        Livewire::test(Assignments::class)
            ->assertSee('Ungraded Task')
            ->assertDontSee('Assignment is graded — submissions locked')
            ->set('submissionDrafts.' . $assignment->id . '.text', 'Submitted content')
            ->call('submit', $assignment->id)
            ->assertNotified('Assignment submitted.');

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Submitted content',
            'status' => 'Submitted',
        ]);
    }
}
