<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\StudentResults;
use App\Filament\Student\Pages\Courses as StudentCoursesPage;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CourseCompletionCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_is_only_available_after_instructor_marks_course_complete(): void
    {
        Notification::fake();

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'email' => 'instructor@thinker.test',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'student@thinker.test',
        ]);

        $course = Course::create([
            'title' => 'Advanced Robotics',
            'code' => 'ROB-301',
            'description' => 'Hands on robotics track',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);
        $course->instructors()->attach($instructor);

        // Enroll student
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $certificateService = app(CertificateService::class);

        // 1. Initially student is NOT eligible because instructor has not marked complete
        $eligibility = $certificateService->eligibility($student, $course);
        $this->assertFalse($eligibility['eligible']);
        $this->assertFalse($eligibility['is_instructor_completed']);
        $this->assertContains('Program completion must be signed off by your instructor', $eligibility['reasons']);

        // Student course page shows certificate locked
        $this->actingAs($student);
        Livewire::test(StudentCoursesPage::class)
            ->call('claimCertificate', $course->id);

        $this->assertDatabaseMissing('certificates', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // 2. Instructor marks the course completion for this student
        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('markCourseComplete', $student->id, $course->id);

        // Enrollment must be marked complete
        $enrollment->refresh();
        $this->assertNotNull($enrollment->completed_at);
        $this->assertEquals($instructor->id, $enrollment->completed_by_user_id);
        $this->assertTrue($enrollment->isCompleted());

        // Certificate should now be issued
        $this->assertDatabaseHas('certificates', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        Notification::assertSentTo($student, CertificateIssuedNotification::class);

        // 3. Student can download certificate
        $certificate = Certificate::where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($certificate);

        $this->actingAs($student);
        $response = $this->get(route('certificates.download', $certificate));
        $response->assertOk();
        $response->assertSee('Advanced Robotics');
        $response->assertSee($student->name);
    }

    public function test_instructor_can_unmark_course_completion(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $course = Course::create([
            'title' => 'Cyber Security',
            'code' => 'CYB-201',
            'description' => 'Security fundamentals',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => now(),
            'completed_by_user_id' => $instructor->id,
        ]);

        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('unmarkCourseComplete', $student->id, $course->id);

        $enrollment->refresh();
        $this->assertNull($enrollment->completed_at);
        $this->assertNull($enrollment->completed_by_user_id);
        $this->assertFalse($enrollment->isCompleted());
    }
}
