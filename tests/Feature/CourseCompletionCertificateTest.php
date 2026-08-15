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

    public function test_preexisting_certificate_is_locked_and_hidden_until_instructor_signs_off(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $course = Course::create([
            'title' => 'Cloud Computing',
            'code' => 'CLD-101',
            'description' => 'Cloud infrastructure',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);
        $course->instructors()->attach($instructor);

        // Student is enrolled, but instructor has NOT signed off (completed_at is null)
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);

        // Pre-existing certificate row exists in database (e.g. from prior platform version)
        $certificate = Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'verification_code' => 'LOCKTEST123',
            'issued_at' => now()->subDays(5),
        ]);

        // 1. CertificateService::issue returns null because enrollment completed_at is null
        $this->assertNull(app(CertificateService::class)->issue($student, $course));

        // 2. Student cannot download certificate (forbidden / locked)
        $this->actingAs($student);
        $downloadResponse = $this->get(route('certificates.download', $certificate));
        $downloadResponse->assertForbidden();

        // 3. Public verification does not validate it
        $verifyResponse = $this->get(route('certificates.verify', 'LOCKTEST123'));
        $verifyResponse->assertOk();
        $verifyResponse->assertSee('Certificate Not Found');

        // 4. Student Certificates page does not display it
        $this->actingAs($student);
        Livewire::test(\App\Filament\Student\Pages\Certificates::class)
            ->assertSet('certificates', []);

        // 5. Student Courses page shows certificate locked (not claimed)
        $coursesTest = Livewire::test(StudentCoursesPage::class);
        $courseData = collect($coursesTest->get('courses'))->firstWhere('id', $course->id);
        $this->assertFalse($courseData['certificate_claimed']);
        $this->assertStringContainsString('Program completion must be signed off by your instructor', $courseData['certificate_lock_reason']);

        // 6. Instructor signs off
        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('markCourseComplete', $student->id, $course->id);

        $enrollment->refresh();
        $this->assertTrue($enrollment->isCompleted());

        // 7. Now student can download
        $this->actingAs($student);
        $this->get(route('certificates.download', $certificate))->assertOk();

        // 8. Public verification is now authentic
        $verifySuccess = $this->get(route('certificates.verify', 'LOCKTEST123'));
        $verifySuccess->assertOk();
        $verifySuccess->assertSee('Authentic Certificate');
    }

    public function test_instructor_unmarking_completion_deletes_and_locks_certificate(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $course = Course::create([
            'title' => 'Data Engineering',
            'code' => 'DAT-201',
            'description' => 'Data pipelines',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => now(),
            'completed_by_user_id' => $instructor->id,
        ]);

        $certificate = Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'verification_code' => 'DATAENG999',
            'issued_at' => now(),
        ]);

        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('unmarkCourseComplete', $student->id, $course->id);

        $enrollment->refresh();
        $this->assertNull($enrollment->completed_at);
        $this->assertFalse($enrollment->isCompleted());

        // Certificate record must be deleted
        $this->assertDatabaseMissing('certificates', [
            'id' => $certificate->id,
        ]);
    }
}
