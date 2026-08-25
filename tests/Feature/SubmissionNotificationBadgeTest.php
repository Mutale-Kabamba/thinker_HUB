<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\AssessmentSubmissionResource\AssessmentSubmissionResource;
use App\Filament\Instructor\Resources\AssessmentSubmissionResource\Pages\EditAssessmentSubmission;
use App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource;
use App\Filament\Instructor\Resources\AssignmentSubmissionResource\Pages\EditAssignmentSubmission;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubmissionNotificationBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_submission_badge_counts_only_unviewed_and_disappears_when_viewed(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Physics 101',
            'code' => 'PHY-101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $student1 = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $student2 = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $assignment = Assignment::query()->create([
            'name' => 'Lab Report 1',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $submission1 = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student1->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'content' => 'First submission',
            'viewed_at' => null,
        ]);

        $submission2 = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student2->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'content' => 'Second submission',
            'viewed_at' => null,
        ]);

        $this->actingAs($instructor);

        // Initially 2 unviewed submissions -> badge count should be "2"
        $this->assertEquals('2', AssignmentSubmissionResource::getNavigationBadge());

        // Instructor views first submission by opening edit page
        Livewire::test(EditAssignmentSubmission::class, [
            'record' => $submission1->getRouteKey(),
        ])->assertSuccessful();

        $submission1->refresh();
        $this->assertNotNull($submission1->viewed_at);
        $this->assertTrue($submission1->isViewed());

        // Now only 1 unviewed submission remains -> badge count should be "1"
        $this->assertEquals('1', AssignmentSubmissionResource::getNavigationBadge());

        // Instructor views second submission
        Livewire::test(EditAssignmentSubmission::class, [
            'record' => $submission2->getRouteKey(),
        ])->assertSuccessful();

        $submission2->refresh();
        $this->assertNotNull($submission2->viewed_at);

        // All submissions viewed -> badge must disappear (returns null)
        $this->assertNull(AssignmentSubmissionResource::getNavigationBadge());
    }

    public function test_assessment_submission_badge_counts_only_unviewed_and_disappears_when_viewed(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Math 101',
            'code' => 'MTH-101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $assessment = Assessment::query()->create([
            'name' => 'Calculus Test',
            'course_id' => $course->id,
            'target_level' => 'Beginner',
            'date_given' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $submission = AssessmentSubmission::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'content' => 'Calculus answer',
            'viewed_at' => null,
        ]);

        $this->actingAs($instructor);

        // Unviewed assessment submission -> badge is "1"
        $this->assertEquals('1', AssessmentSubmissionResource::getNavigationBadge());

        // Instructor views assessment submission
        Livewire::test(EditAssessmentSubmission::class, [
            'record' => $submission->getRouteKey(),
        ])->assertSuccessful();

        $submission->refresh();
        $this->assertNotNull($submission->viewed_at);

        // Badge disappears once viewed
        $this->assertNull(AssessmentSubmissionResource::getNavigationBadge());
    }

    public function test_resubmission_resets_viewed_at_so_badge_reappears(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $course = Course::query()->create([
            'title' => 'Design 101',
            'code' => 'DSN-101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);
        $student->courses()->attach($course->id);

        $assignment = Assignment::query()->create([
            'name' => 'Portfolio UI',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(7),
            'scope' => 'all',
        ]);

        // Student submits via Student Assignments page
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('student'));
        $this->actingAs($student);
        Livewire::test(\App\Filament\Student\Pages\Assignments::class)
            ->set("submissionDrafts.{$assignment->id}.text", 'Initial wireframe')
            ->call('submit', $assignment->id);

        $submission = AssignmentSubmission::query()->where('assignment_id', $assignment->id)->first();
        $this->assertNotNull($submission);
        $this->assertNull($submission->viewed_at);

        // Instructor views it
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));
        $this->actingAs($instructor);
        $this->assertEquals('1', AssignmentSubmissionResource::getNavigationBadge());

        Livewire::test(EditAssignmentSubmission::class, [
            'record' => $submission->getRouteKey(),
        ])->assertSuccessful();

        $this->assertNull(AssignmentSubmissionResource::getNavigationBadge());

        // Grant retake
        $submission->refresh();
        $submission->grantRetake($instructor);

        // Student resubmits
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('student'));
        $this->actingAs($student);
        Livewire::test(\App\Filament\Student\Pages\Assignments::class)
            ->set("submissionDrafts.{$assignment->id}.text", 'Updated wireframe revised')
            ->call('submit', $assignment->id);

        $submission->refresh();
        $this->assertNull($submission->viewed_at);

        // Instructor badge reappears with "1"
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));
        $this->actingAs($instructor);
        $this->assertEquals('1', AssignmentSubmissionResource::getNavigationBadge());
    }

    public function test_mark_viewed_table_action_and_bulk_actions(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $course = Course::query()->create(['title' => 'Chemistry', 'code' => 'CHM-101', 'is_active' => true]);
        $instructor->instructorCourses()->attach($course->id);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $assignment = Assignment::query()->create([
            'name' => 'Lab 1',
            'course_id' => $course->id,
            'date_given' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $submission = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'content' => 'Sample solution',
            'viewed_at' => null,
        ]);

        $this->actingAs($instructor);
        $this->assertEquals('1', AssignmentSubmissionResource::getNavigationBadge());

        // Call table action markViewed
        Livewire::test(\App\Filament\Instructor\Resources\AssignmentSubmissionResource\Pages\ListAssignmentSubmissions::class)
            ->callTableAction('markViewed', $submission);

        $submission->refresh();
        $this->assertNotNull($submission->viewed_at);
        $this->assertNull(AssignmentSubmissionResource::getNavigationBadge());
    }
}
