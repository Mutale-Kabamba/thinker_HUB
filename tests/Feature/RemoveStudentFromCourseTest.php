<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\StudentResults;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class RemoveStudentFromCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_instructor_can_delete_enrollment_via_policy(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $otherInstructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'title' => 'Web Development Bootcamp',
            'code' => 'WEB-101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Admin can delete enrollment
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $enrollment));

        // Assigned instructor can delete enrollment
        $this->assertTrue(Gate::forUser($instructor)->allows('delete', $enrollment));

        // Other instructor not teaching the course cannot delete enrollment
        $this->assertFalse(Gate::forUser($otherInstructor)->allows('delete', $enrollment));
    }

    public function test_instructor_can_remove_student_from_course_on_student_results_page(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'John Doe']);

        $course = Course::create([
            'title' => 'Full Stack Laravel',
            'code' => 'LAR-101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'completed_at' => now(),
        ]);

        Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'verification_code' => 'CERT-TEST-123',
            'issued_at' => now(),
        ]);

        $this->actingAs($instructor);

        Livewire::test(StudentResults::class)
            ->call('removeStudentFromCourse', $student->id, $course->id)
            ->assertNotified('Student Removed from Course');

        $this->assertDatabaseMissing('enrollments', [
            'id' => $enrollment->id,
        ]);

        $this->assertDatabaseMissing('certificates', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_instructor_cannot_remove_student_from_unassigned_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $unassignedCourse = Course::create([
            'title' => 'Unassigned Physics Course',
            'code' => 'PHY-201',
            'is_active' => true,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $unassignedCourse->id,
        ]);

        $this->actingAs($instructor);

        Livewire::test(StudentResults::class)
            ->call('removeStudentFromCourse', $student->id, $unassignedCourse->id)
            ->assertNotified('Unauthorized');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
        ]);
    }
}
