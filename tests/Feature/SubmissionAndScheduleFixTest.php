<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\Schedule as InstructorSchedule;
use App\Filament\Student\Pages\Assignments as StudentAssignments;
use App\Filament\Student\Pages\Assessments as StudentAssessments;
use App\Filament\Student\Pages\Schedule as StudentSchedule;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SubmissionAndScheduleFixTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_instructor_can_view_student_assignment_submission_file(): void
    {
        Storage::fake('public');

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $otherStudent = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);
        $assignment = Assignment::create([
            'name' => 'Assignment 1',
            'course_id' => $course->id,
            'scope' => 'all',
            'is_released' => true,
        ]);

        $filePath = 'submissions/test_assignment.pdf';
        Storage::disk('public')->put($filePath, 'fake content');

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => $filePath,
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        // Student owner can view
        $response = $this->actingAs($student)->get(route('file.view', ['type' => 'submission', 'id' => $submission->id]));
        $response->assertOk();

        // Admin can view
        $response = $this->actingAs($admin)->get(route('file.view', ['type' => 'submission', 'id' => $submission->id]));
        $response->assertOk();

        // Instructor can view
        $response = $this->actingAs($instructor)->get(route('file.view', ['type' => 'submission', 'id' => $submission->id]));
        $response->assertOk();

        // Other student is forbidden
        $response = $this->actingAs($otherStudent)->get(route('file.view', ['type' => 'submission', 'id' => $submission->id]));
        $response->assertForbidden();
    }

    public function test_admin_and_instructor_can_view_student_assessment_submission_file(): void
    {
        Storage::fake('public');

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $otherStudent = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);
        $assessment = Assessment::create([
            'name' => 'Assessment 1',
            'course_id' => $course->id,
            'user_id' => $instructor->id,
            'scope' => 'all',
            'is_released' => true,
        ]);

        $filePath = 'submissions/test_assessment.pdf';
        Storage::disk('public')->put($filePath, 'fake assessment content');

        $submission = AssessmentSubmission::create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'file_path' => $filePath,
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        // Student owner can view
        $response = $this->actingAs($student)->get(route('file.view', ['type' => 'assessment-submission', 'id' => $submission->id]));
        $response->assertOk();

        // Admin can view
        $response = $this->actingAs($admin)->get(route('file.view', ['type' => 'assessment-submission', 'id' => $submission->id]));
        $response->assertOk();

        // Instructor can view
        $response = $this->actingAs($instructor)->get(route('file.view', ['type' => 'assessment-submission', 'id' => $submission->id]));
        $response->assertOk();

        // Other student is forbidden
        $response = $this->actingAs($otherStudent)->get(route('file.view', ['type' => 'assessment-submission', 'id' => $submission->id]));
        $response->assertForbidden();
    }

    public function test_student_assignment_resubmit_preserves_existing_file(): void
    {
        Storage::fake('public');

        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
        $course = Course::create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);
        $student->courses()->attach($course->id);

        $assignment = Assignment::create([
            'name' => 'Assignment 1',
            'course_id' => $course->id,
            'scope' => 'all',
            'is_released' => true,
            'due_date' => now()->addDays(7),
        ]);

        // First submit with file
        $file = UploadedFile::fake()->create('homework.pdf', 100, 'application/pdf');

        Livewire::actingAs($student)
            ->test(StudentAssignments::class)
            ->set("submissionDrafts.{$assignment->id}.file", $file)
            ->set("submissionDrafts.{$assignment->id}.text", 'Initial submission text')
            ->call('submit', $assignment->id)
            ->assertHasNoErrors();

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->first();
        $this->assertNotNull($submission);
        $this->assertNotNull($submission->file_path);
        $originalFilePath = $submission->file_path;

        // Second submit updating only text (no file re-upload)
        Livewire::actingAs($student)
            ->test(StudentAssignments::class)
            ->set("submissionDrafts.{$assignment->id}.text", 'Updated text notes')
            ->call('submit', $assignment->id)
            ->assertHasNoErrors();

        $submission->refresh();
        $this->assertSame('Updated text notes', $submission->content);
        $this->assertSame($originalFilePath, $submission->file_path);
    }

    public function test_course_session_model_methods_handle_null_and_malformed_dates(): void
    {
        $emptySession = new CourseSession();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $emptySession->getEffectiveDate());
        $this->assertSame('00:00:00', $emptySession->getEffectiveStartTime());
        $this->assertSame('23:59:59', $emptySession->getEffectiveEndTime());
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $emptySession->effectiveStartAt());
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $emptySession->effectiveEndAt());
    }

    public function test_schedule_pages_render_safely_with_null_and_malformed_session_dates(): void
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
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);
        $student->courses()->attach($course->id);

        // Create session with partial data
        CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'type' => 'group',
            'title' => 'Null Date Session',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'scheduled',
        ]);

        // Create session with rescheduled data
        CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'student_id' => $student->id,
            'type' => 'one_on_one',
            'title' => 'One on one session',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
            'rescheduled_end_time' => null,
            'status' => 'rescheduled',
        ]);

        // Test Student Schedule Page does not throw 500 error
        Livewire::actingAs($student)
            ->test(StudentSchedule::class)
            ->assertOk();

        // Test Instructor Schedule Page does not throw 500 error
        Livewire::actingAs($instructor)
            ->test(InstructorSchedule::class)
            ->assertOk();
    }
}
