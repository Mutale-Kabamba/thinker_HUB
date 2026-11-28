<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\CourseResource\Pages\ListCourses as InstructorListCourses;
use App\Filament\Resources\Courses\Pages\ListCourses as AdminListCourses;
use App\Filament\Resources\Users\Pages\ListUsers as AdminListUsers;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManualStudentEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enroll_student_from_courses_table_and_users_table(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $studentA = User::factory()->create(['name' => 'Student One', 'role' => 'student', 'is_active' => true]);
        $studentB = User::factory()->create(['name' => 'Student Two', 'role' => 'student', 'is_active' => true]);
        $course = Course::create(['title' => 'Cloud Fundamentals', 'code' => 'CLD-101', 'is_active' => true]);

        // Enroll from Admin Courses table
        Livewire::actingAs($admin)
            ->test(AdminListCourses::class)
            ->callTableAction('enrollStudent', $course, [
                'student_ids' => [$studentA->id],
                'payment_status' => Enrollment::STATUS_PAID,
                'fee_amount' => 350,
                'payment_notes' => 'Paid at desk',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $studentA->id,
            'course_id' => $course->id,
            'payment_status' => Enrollment::STATUS_PAID,
            'registration_type' => Enrollment::CHANNEL_ADMIN_MANUAL,
            'registered_by_user_id' => $admin->id,
        ]);

        // Enroll from Admin Users table
        Livewire::actingAs($admin)
            ->test(AdminListUsers::class)
            ->callTableAction('enrollInCourse', $studentB, [
                'course_id' => $course->id,
                'payment_status' => Enrollment::STATUS_FREE,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $studentB->id,
            'course_id' => $course->id,
            'payment_status' => Enrollment::STATUS_FREE,
            'registration_type' => Enrollment::CHANNEL_ADMIN_MANUAL,
        ]);
    }

    public function test_instructor_can_enroll_student_in_assigned_course(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $student = User::factory()->create(['name' => 'Learner Jane', 'role' => 'student', 'is_active' => true]);
        $course = Course::create([
            'title' => 'React Essentials',
            'code' => 'RCT-101',
            'instructor_id' => $instructor->id,
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        Livewire::actingAs($instructor)
            ->test(InstructorListCourses::class)
            ->callTableAction('enrollStudent', $course, [
                'student_ids' => [$student->id],
                'payment_status' => Enrollment::STATUS_FREE,
                'payment_notes' => 'Added by instructor',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'registration_type' => Enrollment::CHANNEL_INSTRUCTOR_MANUAL,
            'registered_by_user_id' => $instructor->id,
        ]);
    }
}
