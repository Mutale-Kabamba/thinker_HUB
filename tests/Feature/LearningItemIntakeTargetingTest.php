<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Courses;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\ResourceVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LearningItemIntakeTargetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_intake_scoping(): void
    {
        $course = Course::create([
            'title' => 'Web Development Bootcamp',
            'code' => 'WEB-101',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake1 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort Alpha',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $intake2 = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Cohort Beta',
            'start_date' => '2026-04-01',
            'end_date' => '2026-06-30',
            'status' => CourseIntake::STATUS_UPCOMING,
            'is_active' => false,
        ]);

        $student1 = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);
        $student2 = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);

        Enrollment::create([
            'user_id' => $student1->id,
            'course_id' => $course->id,
            'course_intake_id' => $intake1->id,
        ]);

        Enrollment::create([
            'user_id' => $student2->id,
            'course_id' => $course->id,
            'course_intake_id' => $intake2->id,
        ]);

        // Specific to Intake 1
        $assignmentIntake1 = Assignment::create([
            'course_id' => $course->id,
            'course_intake_id' => $intake1->id,
            'name' => 'Alpha Specific Assignment',
            'target_level' => 'Beginner',
        ]);

        // Course-wide assignment (no intake specified)
        $assignmentCourseWide = Assignment::create([
            'course_id' => $course->id,
            'course_intake_id' => null,
            'name' => 'Course Wide Assignment',
            'target_level' => 'Beginner',
        ]);

        // Student 1 sees both
        $student1Assignments = Assignment::query()->visibleTo($student1)->pluck('id')->all();
        $this->assertContains($assignmentIntake1->id, $student1Assignments);
        $this->assertContains($assignmentCourseWide->id, $student1Assignments);

        // Student 2 sees ONLY the course-wide assignment
        $student2Assignments = Assignment::query()->visibleTo($student2)->pluck('id')->all();
        $this->assertNotContains($assignmentIntake1->id, $student2Assignments);
        $this->assertContains($assignmentCourseWide->id, $student2Assignments);
    }

    public function test_quiz_and_assessment_and_materials_intake_scoping(): void
    {
        $course = Course::create([
            'title' => 'Python for Data Science',
            'code' => 'PY-201',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intakeA = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Intake A',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $intakeB = CourseIntake::create([
            'course_id' => $course->id,
            'name' => 'Intake B',
            'status' => CourseIntake::STATUS_UPCOMING,
            'is_active' => false,
        ]);

        $studentA = User::factory()->create(['role' => 'student', 'track' => 'Intermediate']);
        $studentB = User::factory()->create(['role' => 'student', 'track' => 'Intermediate']);

        Enrollment::create(['user_id' => $studentA->id, 'course_id' => $course->id, 'course_intake_id' => $intakeA->id]);
        Enrollment::create(['user_id' => $studentB->id, 'course_id' => $course->id, 'course_intake_id' => $intakeB->id]);

        // Quiz
        $quizA = Quiz::create(['course_id' => $course->id, 'course_intake_id' => $intakeA->id, 'title' => 'Quiz for Intake A', 'is_active' => true]);
        $quizAll = Quiz::create(['course_id' => $course->id, 'course_intake_id' => null, 'title' => 'Quiz for All', 'is_active' => true]);

        $this->assertTrue(Quiz::query()->visibleTo($studentA)->where('id', $quizA->id)->exists());
        $this->assertFalse(Quiz::query()->visibleTo($studentB)->where('id', $quizA->id)->exists());
        $this->assertTrue(Quiz::query()->visibleTo($studentB)->where('id', $quizAll->id)->exists());

        // Assessment
        $assessmentA = Assessment::create(['course_id' => $course->id, 'course_intake_id' => $intakeA->id, 'name' => 'Assessment A', 'target_level' => 'Intermediate']);
        $assessmentAll = Assessment::create(['course_id' => $course->id, 'course_intake_id' => null, 'name' => 'Assessment All', 'target_level' => 'Intermediate']);

        $this->assertTrue(Assessment::query()->visibleTo($studentA)->where('id', $assessmentA->id)->exists());
        $this->assertFalse(Assessment::query()->visibleTo($studentB)->where('id', $assessmentA->id)->exists());
        $this->assertTrue(Assessment::query()->visibleTo($studentB)->where('id', $assessmentAll->id)->exists());

        // Learning Material
        $matA = LearningMaterial::create(['course_id' => $course->id, 'course_intake_id' => $intakeA->id, 'title' => 'Notes A', 'category' => 'Study Material', 'material_type' => 'Document', 'scope' => 'all']);
        $matAll = LearningMaterial::create(['course_id' => $course->id, 'course_intake_id' => null, 'title' => 'Notes All', 'category' => 'Study Material', 'material_type' => 'Document', 'scope' => 'all']);

        $this->assertTrue(LearningMaterial::query()->visibleTo($studentA)->where('id', $matA->id)->exists());
        $this->assertFalse(LearningMaterial::query()->visibleTo($studentB)->where('id', $matA->id)->exists());
        $this->assertTrue(LearningMaterial::query()->visibleTo($studentB)->where('id', $matAll->id)->exists());

        // Course Session
        $sessionA = CourseSession::create(['course_id' => $course->id, 'course_intake_id' => $intakeA->id, 'title' => 'Session A', 'type' => 'group', 'session_date' => '2026-02-01', 'start_time' => '10:00', 'end_time' => '11:00']);
        $sessionAll = CourseSession::create(['course_id' => $course->id, 'course_intake_id' => null, 'title' => 'Session All', 'type' => 'group', 'session_date' => '2026-02-01', 'start_time' => '10:00', 'end_time' => '11:00']);

        $this->assertTrue(CourseSession::query()->visibleTo($studentA)->where('id', $sessionA->id)->exists());
        $this->assertFalse(CourseSession::query()->visibleTo($studentB)->where('id', $sessionA->id)->exists());
        $this->assertTrue(CourseSession::query()->visibleTo($studentB)->where('id', $sessionAll->id)->exists());
    }

    public function test_locked_course_strictly_blocks_unauthorized_students_in_portal(): void
    {
        $lockedCourse = Course::create([
            'title' => 'Executive Cyber Defense',
            'code' => 'EXEC-999',
            'is_open_enrollment' => false,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student);

        // Attempting to enroll in the student portal
        Livewire::test(Courses::class)
            ->call('enroll', $lockedCourse->id);

        $this->assertFalse(Enrollment::query()->where('user_id', $student->id)->where('course_id', $lockedCourse->id)->exists());
    }

    public function test_open_payable_course_redirects_student_to_checkout(): void
    {
        $payableCourse = Course::create([
            'title' => 'Certified Cloud Architect',
            'code' => 'CCA-500',
            'is_open_enrollment' => true,
            'fees' => '1500',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);

        $this->actingAs($student);

        Livewire::test(Courses::class)
            ->call('enroll', $payableCourse->id)
            ->assertRedirect(route('checkout.show', [$payableCourse->id, 'track' => 'Beginner']));

        $this->assertFalse(Enrollment::query()->where('user_id', $student->id)->where('course_id', $payableCourse->id)->exists());
    }

    public function test_open_free_course_allows_direct_enrollment_in_active_intake(): void
    {
        $freeCourse = Course::create([
            'title' => 'Open Source Community Fundamentals',
            'code' => 'OS-101',
            'is_open_enrollment' => true,
            'fees' => '0',
            'offering_mode' => 'ongoing',
            'is_active' => true,
        ]);

        $intake = CourseIntake::create([
            'course_id' => $freeCourse->id,
            'name' => 'Spring 2026 Cohort',
            'status' => CourseIntake::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);

        $this->actingAs($student);

        Livewire::test(Courses::class)
            ->call('enroll', $freeCourse->id);

        $enrollment = Enrollment::query()->where('user_id', $student->id)->where('course_id', $freeCourse->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertSame($intake->id, $enrollment->course_intake_id);
    }
}
