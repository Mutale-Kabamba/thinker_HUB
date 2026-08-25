<?php

namespace Tests\Feature;

use App\Filament\Instructor\Resources\CourseIntakeResource\CourseIntakeResource as InstructorCourseIntakeResource;
use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\ListCourseIntakes as InstructorListCourseIntakes;
use App\Filament\Instructor\Resources\CourseIntakeResource\Pages\ViewCourseIntake as InstructorViewCourseIntake;
use App\Filament\Resources\CourseIntakes\CourseIntakeResource as AdminCourseIntakeResource;
use App\Filament\Resources\CourseIntakes\Pages\ListCourseIntakes as AdminListCourseIntakes;
use App\Filament\Resources\CourseIntakes\Pages\ViewCourseIntake as AdminViewCourseIntake;
use App\Filament\Resources\CourseIntakes\RelationManagers\StudentsRelationManager;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseIntakeStudentEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_and_admin_can_add_students_to_intake_via_relation_manager(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $course = Course::query()->create([
            'title' => 'Web Development Bootcamp',
            'code' => 'WEB-101',
            'is_active' => true,
        ]);

        $intake = CourseIntake::query()->create([
            'course_id' => $course->id,
            'name' => 'Intake 1 - Jan 2026',
            'start_date' => now(),
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $student1 = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $student2 = User::factory()->create(['role' => 'student', 'is_active' => true]);

        // Student 1 is already enrolled in the course without an intake
        Enrollment::query()->create([
            'user_id' => $student1->id,
            'course_id' => $course->id,
            'course_intake_id' => null,
        ]);

        // Add both students to the intake via relation manager action
        Livewire::test(StudentsRelationManager::class, [
            'ownerRecord' => $intake,
            'pageClass' => AdminViewCourseIntake::class,
        ])
            ->callTableAction('add_students', data: [
                'user_ids' => [$student1->id, $student2->id],
            ])
            ->assertHasNoTableActionErrors();

        // Student 1 enrollment updated
        $enrollment1 = Enrollment::query()->where('user_id', $student1->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment1);
        $this->assertEquals($intake->id, $enrollment1->course_intake_id);

        // Student 2 new enrollment created with intake
        $enrollment2 = Enrollment::query()->where('user_id', $student2->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment2);
        $this->assertEquals($intake->id, $enrollment2->course_intake_id);

        $this->assertEquals(2, $intake->enrollments()->count());
    }

    public function test_can_transfer_student_to_another_intake_and_remove_from_intake(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $course = Course::query()->create([
            'title' => 'Data Science Track',
            'code' => 'DAT-201',
            'is_active' => true,
        ]);

        $intakeA = CourseIntake::query()->create([
            'course_id' => $course->id,
            'name' => 'Cohort Alpha',
            'start_date' => now(),
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $intakeB = CourseIntake::query()->create([
            'course_id' => $course->id,
            'name' => 'Cohort Beta',
            'start_date' => now()->addMonth(),
            'status' => CourseIntake::STATUS_UPCOMING,
            'is_active' => false,
        ]);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $enrollment = Enrollment::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'course_intake_id' => $intakeA->id,
        ]);

        // Transfer student from intakeA to intakeB
        Livewire::test(StudentsRelationManager::class, [
            'ownerRecord' => $intakeA,
            'pageClass' => AdminViewCourseIntake::class,
        ])
            ->callTableAction('transfer_intake', $enrollment, [
                'target_intake_id' => $intakeB->id,
            ])
            ->assertHasNoTableActionErrors();

        $enrollment->refresh();
        $this->assertEquals($intakeB->id, $enrollment->course_intake_id);

        // Remove student from intake cohort
        Livewire::test(StudentsRelationManager::class, [
            'ownerRecord' => $intakeB,
            'pageClass' => AdminViewCourseIntake::class,
        ])
            ->callTableAction('remove_from_intake', $enrollment)
            ->assertHasNoTableActionErrors();

        $enrollment->refresh();
        $this->assertNull($enrollment->course_intake_id);
        // Student is still in course
        $this->assertTrue(Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->exists());
    }

    public function test_can_add_students_directly_from_course_intakes_table_row_action(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $course = Course::query()->create([
            'title' => 'Graphic Design Masterclass',
            'code' => 'GDM-101',
            'is_active' => true,
        ]);

        $intake = CourseIntake::query()->create([
            'course_id' => $course->id,
            'name' => 'Spring 2026',
            'start_date' => now(),
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        Livewire::test(AdminListCourseIntakes::class)
            ->callTableAction('add_students', $intake, [
                'user_ids' => [$student->id],
            ])
            ->assertHasNoTableActionErrors();

        $enrollment = Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertEquals($intake->id, $enrollment->course_intake_id);
    }

    public function test_instructor_can_add_students_from_view_course_intake_header_action(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('instructor'));

        $instructor = User::factory()->create(['role' => 'instructor', 'is_active' => true]);
        $course = Course::query()->create([
            'title' => 'Mobile App Development',
            'code' => 'MAD-301',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $intake = CourseIntake::query()->create([
            'course_id' => $course->id,
            'name' => 'Cohort 1',
            'start_date' => now(),
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $this->actingAs($instructor);

        Livewire::test(InstructorViewCourseIntake::class, [
            'record' => $intake->getRouteKey(),
        ])
            ->callAction('add_students', [
                'user_ids' => [$student->id],
            ])
            ->assertHasNoActionErrors();

        $enrollment = Enrollment::query()->where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertEquals($intake->id, $enrollment->course_intake_id);
    }
}
