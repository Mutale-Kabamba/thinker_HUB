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

    public function test_resource_video_course_scoping_and_global_visibility(): void
    {
        $course1 = Course::create(['title' => 'Course 1', 'code' => 'C1', 'is_active' => true]);
        $course2 = Course::create(['title' => 'Course 2', 'code' => 'C2', 'is_active' => true]);

        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        $student3 = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);

        Enrollment::create(['user_id' => $student1->id, 'course_id' => $course1->id]);
        Enrollment::create(['user_id' => $student2->id, 'course_id' => $course2->id]);

        // Video tied to Course 1
        $videoCourse1 = ResourceVideo::create([
            'title' => 'Course 1 Video',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'course_id' => $course1->id,
            'is_published' => true,
            'is_recorded_lesson' => false,
        ]);

        // Video tied to Course 2
        $videoCourse2 = ResourceVideo::create([
            'title' => 'Course 2 Video',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'course_id' => $course2->id,
            'is_published' => true,
            'is_recorded_lesson' => false,
        ]);

        // Global video not tied to any course
        $videoGlobal = ResourceVideo::create([
            'title' => 'Global Public Video',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'course_id' => null,
            'is_published' => true,
            'is_recorded_lesson' => false,
        ]);

        // Student 1 (enrolled in Course 1) sees Video 1 and Global Video, but NOT Video 2
        $s1Videos = ResourceVideo::query()->visibleTo($student1)->pluck('id')->all();
        $this->assertContains($videoCourse1->id, $s1Videos);
        $this->assertContains($videoGlobal->id, $s1Videos);
        $this->assertNotContains($videoCourse2->id, $s1Videos);

        // Student 2 (enrolled in Course 2) sees Video 2 and Global Video, but NOT Video 1
        $s2Videos = ResourceVideo::query()->visibleTo($student2)->pluck('id')->all();
        $this->assertContains($videoCourse2->id, $s2Videos);
        $this->assertContains($videoGlobal->id, $s2Videos);
        $this->assertNotContains($videoCourse1->id, $s2Videos);

        // Student 3 (un-enrolled) sees ONLY Global Video
        $s3Videos = ResourceVideo::query()->visibleTo($student3)->pluck('id')->all();
        $this->assertContains($videoGlobal->id, $s3Videos);
        $this->assertNotContains($videoCourse1->id, $s3Videos);
        $this->assertNotContains($videoCourse2->id, $s3Videos);

        // Admin sees all videos
        $adminVideos = ResourceVideo::query()->visibleTo($admin)->pluck('id')->all();
        $this->assertContains($videoCourse1->id, $adminVideos);
        $this->assertContains($videoCourse2->id, $adminVideos);
        $this->assertContains($videoGlobal->id, $adminVideos);
    }

    public function test_student_learning_resources_page_respects_course_tied_videos(): void
    {
        $courseA = Course::create(['title' => 'Course Alpha', 'code' => 'CA', 'is_active' => true]);
        $courseB = Course::create(['title' => 'Course Beta', 'code' => 'CB', 'is_active' => true]);

        $studentA = User::factory()->create(['role' => 'student']);
        Enrollment::create(['user_id' => $studentA->id, 'course_id' => $courseA->id]);

        $videoA = ResourceVideo::create([
            'title' => 'Alpha Only Video',
            'category' => 'Web Development',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'course_id' => $courseA->id,
            'is_published' => true,
            'is_recorded_lesson' => false,
        ]);

        $videoB = ResourceVideo::create([
            'title' => 'Beta Only Video',
            'category' => 'Web Development',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'course_id' => $courseB->id,
            'is_published' => true,
            'is_recorded_lesson' => false,
        ]);

        $videoGlobal = ResourceVideo::create([
            'title' => 'Universal Public Video',
            'category' => 'Web Development',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'course_id' => null,
            'is_published' => true,
            'is_recorded_lesson' => false,
        ]);

        $this->actingAs($studentA);

        $component = Livewire::test(\App\Filament\Student\Pages\LearningResources::class);

        // Should be able to open Alpha video and Universal video
        $component->call('openGeneralVideo', $videoA->id)
            ->assertSet('showPlayer', true)
            ->assertSet('activeVideoId', $videoA->id);

        $component->call('closePlayer')
            ->assertSet('showPlayer', false);

        $component->call('openGeneralVideo', $videoGlobal->id)
            ->assertSet('showPlayer', true)
            ->assertSet('activeVideoId', $videoGlobal->id);

        $component->call('closePlayer')
            ->assertSet('showPlayer', false);

        // Attempting to open unauthorized Course Beta video should fail silently (showPlayer remains false)
        $component->call('openGeneralVideo', $videoB->id)
            ->assertSet('showPlayer', false);
    }
}
