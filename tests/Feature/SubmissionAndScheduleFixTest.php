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

        $this->actingAs($student)
            ->get('/learn/schedule')
            ->assertOk();

        // Test Instructor Schedule Page does not throw 500 error
        Livewire::actingAs($instructor)
            ->test(InstructorSchedule::class)
            ->assertOk();

        $this->actingAs($instructor)
            ->get('/teach/schedule')
            ->assertOk();
    }

    public function test_instructor_schedule_with_pending_reschedule_notifications(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);
        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Advanced Python',
            'code' => 'PY201',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);

        $session = CourseSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'student_id' => $student->id,
            'type' => 'one_on_one',
            'title' => 'Python Mentoring',
            'session_date' => now()->addDays(2)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'status' => 'scheduled',
        ]);

        // Create reschedule notification for instructor
        $instructor->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\RescheduleRequestNotification',
            'data' => [
                'session_id' => $session->id,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'reason' => 'Doctor appointment',
                'preferred_date' => now()->addDays(3)->toDateString(),
                'preferred_time' => '15:00:00',
            ],
            'created_at' => now(),
        ]);

        // Test HTTP GET /teach/schedule with pending notifications
        $this->actingAs($instructor)
            ->get('/teach/schedule')
            ->assertOk();

        // Test Livewire component with decision wizard interactions
        Livewire::actingAs($instructor)
            ->test(InstructorSchedule::class)
            ->assertOk()
            ->assertSee('Doctor appointment')
            ->call('openSessionDetails', $session->id)
            ->assertSet('showSessionDetailsModal', true)
            ->assertSet('selectedSessionDetails.title', 'Python Mentoring')
            ->call('closeSessionDetails')
            ->assertSet('showSessionDetailsModal', false)
            ->call('setDecisionStep', 'accept')
            ->set('decisionDate', now()->addDays(3)->toDateString())
            ->set('decisionStartTime', '15:00:00')
            ->call('acceptRescheduleRequest')
            ->assertHasNoErrors();

        $session->refresh();
        $this->assertSame('scheduled', $session->status);
        $this->assertSame(now()->addDays(3)->toDateString(), $session->session_date->toDateString());
        $this->assertSame('15:00:00', $session->start_time);
    }

    public function test_session_rescheduled_notification_database_and_mail_rendering(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'student@example.com',
            'is_active' => true,
        ]);
        $course = Course::create([
            'title' => 'Graphic Design',
            'code' => 'DSGN101',
            'is_active' => true,
        ]);
        $session = CourseSession::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'type' => 'one_on_one',
            'title' => 'Portfolio Review',
            'session_date' => now()->addDays(5)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'scheduled',
        ]);

        $notification = new \App\Notifications\SessionRescheduledNotification($session, 'Graphic Design');

        // Test database array formatting (must not throw Call to undefined method markAsRead)
        $dbData = $notification->toArray($student);
        $this->assertIsArray($dbData);
        $this->assertSame('Session schedule updated', $dbData['title']);

        // Test mail rendering
        $mail = $notification->toMail($student);
        $this->assertSame('Session Moved: Graphic Design', $mail->subject);

        // Student can be notified without throwing 500 error
        $student->notify($notification);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $student->id,
            'type' => \App\Notifications\SessionRescheduledNotification::class,
        ]);
    }
}
