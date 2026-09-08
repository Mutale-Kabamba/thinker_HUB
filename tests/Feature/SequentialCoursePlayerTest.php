<?php

namespace Tests\Feature;

use App\Events\CourseCompleted;
use App\Livewire\CoursePlayer;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\ProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class SequentialCoursePlayerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CourseGamificationRuleSeeder::class);
    }

    protected function createTestCourseWithSequentialLessons(): array
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'student_seq@thinker.test',
        ]);

        $course = Course::create([
            'title' => 'Fullstack Mastery',
            'slug' => 'fullstack-mastery',
            'code' => 'FS-101',
            'description' => 'Complete Sequential Mastery',
            'is_active' => true,
            'is_open_enrollment' => true,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'progress_percentage' => 0,
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Module 1: Foundations',
            'order_column' => 1,
        ]);

        $lesson1 = Lesson::create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Lesson 1: Introduction to Framework',
            'slug' => 'lesson-1-intro',
            'type' => 'video',
            'video_url' => 'https://example.com/video1.mp4',
            'duration_seconds' => 300,
            'min_watch_percentage' => 90,
            'order_column' => 1,
        ]);

        $lesson2 = Lesson::create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Lesson 2: Core Concepts Guide',
            'slug' => 'lesson-2-reading',
            'type' => 'reading',
            'reading_body' => '<p>Deep dive into architecture components.</p>',
            'order_column' => 2,
        ]);

        $lesson3 = Lesson::create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Lesson 3: Knowledge Check Quiz',
            'slug' => 'lesson-3-quiz',
            'type' => 'quiz',
            'order_column' => 3,
        ]);

        $lesson4 = Lesson::create([
            'course_id' => $course->id,
            'course_section_id' => $section->id,
            'title' => 'Lesson 4: Capstone Submission Task',
            'slug' => 'lesson-4-task',
            'type' => 'assignment',
            'order_column' => 4,
        ]);

        return [$student, $course, $enrollment, $lesson1, $lesson2, $lesson3, $lesson4];
    }

    public function test_progression_service_initializes_and_unlocks_first_lesson_only(): void
    {
        [$student, $course, $enrollment, $lesson1, $lesson2, $lesson3, $lesson4] = $this->createTestCourseWithSequentialLessons();
        $service = app(ProgressionService::class);

        $service->initializeCourseProgress($student, $course);

        $this->assertTrue($service->isLessonUnlocked($student, $lesson1));
        $this->assertFalse($service->isLessonUnlocked($student, $lesson2));
        $this->assertFalse($service->isLessonUnlocked($student, $lesson3));
        $this->assertFalse($service->isLessonUnlocked($student, $lesson4));

        $p1 = LessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson1->id)->first();
        $this->assertTrue((bool) $p1->is_unlocked);
        $this->assertFalse((bool) $p1->is_completed);
    }

    public function test_student_cannot_select_locked_lesson_in_course_player(): void
    {
        [$student, $course, $enrollment, $lesson1, $lesson2] = $this->createTestCourseWithSequentialLessons();
        $this->actingAs($student);

        Livewire::test(CoursePlayer::class, ['course' => $course])
            ->assertSet('activeLesson.id', $lesson1->id)
            ->call('selectLesson', $lesson2->id)
            ->assertDispatched('notify')
            ->assertSet('activeLesson.id', $lesson1->id); // Remains on lesson 1
    }

    public function test_video_completion_advances_to_reading_lesson(): void
    {
        [$student, $course, $enrollment, $lesson1, $lesson2] = $this->createTestCourseWithSequentialLessons();
        $this->actingAs($student);

        Livewire::test(CoursePlayer::class, ['course' => $course])
            ->assertSet('activeLesson.id', $lesson1->id)
            ->call('handleVideoCompletion')
            ->assertSet('activeLesson.id', $lesson2->id)
            ->assertDispatched('lesson-changed');

        $service = app(ProgressionService::class);
        $this->assertTrue($service->isLessonUnlocked($student, $lesson2));

        $p1 = LessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson1->id)->first();
        $this->assertTrue((bool) $p1->is_completed);
        $this->assertNotNull($p1->completed_at);
    }

    public function test_reading_mark_as_read_advances_to_quiz_lesson(): void
    {
        [$student, $course, $enrollment, $lesson1, $lesson2, $lesson3] = $this->createTestCourseWithSequentialLessons();
        $service = app(ProgressionService::class);
        $service->initializeCourseProgress($student, $course);
        $service->completeAndAdvance($student, $lesson1);

        $this->actingAs($student);

        Livewire::test(CoursePlayer::class, ['course' => $course, 'lessonId' => $lesson2->id])
            ->assertSet('activeLesson.id', $lesson2->id)
            ->call('markReadingComplete')
            ->assertSet('activeLesson.id', $lesson3->id);

        $this->assertTrue($service->isLessonUnlocked($student, $lesson3));
    }

    public function test_full_sequential_completion_reaches_100_percent_and_dispatches_course_completed_event(): void
    {
        Event::fake([CourseCompleted::class]);

        [$student, $course, $enrollment, $lesson1, $lesson2, $lesson3, $lesson4] = $this->createTestCourseWithSequentialLessons();
        $service = app(ProgressionService::class);
        $this->actingAs($student);

        $player = Livewire::test(CoursePlayer::class, ['course' => $course]);

        // 1. Complete Video -> moves to reading
        $player->call('handleVideoCompletion')
            ->assertSet('activeLesson.id', $lesson2->id);
        $enrollment->refresh();
        $this->assertEquals(25, $enrollment->progress_percentage);

        // 2. Complete Reading -> moves to quiz
        $player->call('markReadingComplete')
            ->assertSet('activeLesson.id', $lesson3->id);
        $enrollment->refresh();
        $this->assertEquals(50, $enrollment->progress_percentage);

        // 3. Complete Quiz -> moves to assignment
        $player->call('handleQuizPassed')
            ->assertSet('activeLesson.id', $lesson4->id);
        $enrollment->refresh();
        $this->assertEquals(75, $enrollment->progress_percentage);

        // 4. Complete Assignment Task -> 100% completion
        $player->call('handleTaskSubmitted');
        $enrollment->refresh();
        $this->assertEquals(100, $enrollment->progress_percentage);
        $this->assertNotNull($enrollment->completed_at);

        Event::assertDispatched(CourseCompleted::class, function ($event) use ($student, $course) {
            return $event->user->id === $student->id && $event->course->id === $course->id;
        });
    }

    public function test_player_route_loads_successfully_for_authenticated_student(): void
    {
        [$student, $course] = $this->createTestCourseWithSequentialLessons();
        $this->actingAs($student);

        $response = $this->get("/learn/{$course->slug}");
        $response->assertStatus(200);
        $response->assertSee($course->title);
    }

    public function test_filament_student_routes_are_not_intercepted_by_player_wildcard(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'student_panel@thinker.test',
        ]);
        $this->actingAs($student);

        // /learn/courses should be handled by Filament student panel
        $response = $this->get('/learn/courses');
        $response->assertStatus(200);

        // /learn/overview should be handled by Filament student panel
        $response = $this->get('/learn/overview');
        $response->assertStatus(200);
    }

    public function test_embedded_quiz_player_dispatches_quiz_passed_on_passing_score(): void
    {
        [$student, $course, $enrollment, $lesson1, $lesson2, $lesson3] = $this->createTestCourseWithSequentialLessons();
        $this->actingAs($student);

        $quiz = \App\Models\Quiz::create([
            'course_id' => $course->id,
            'title' => 'Module 1 Quiz',
            'pass_percentage' => 70,
            'is_active' => true,
        ]);

        $q1 = \App\Models\QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'What is the standard stack?',
            'points' => 10,
            'sort_order' => 1,
        ]);

        $opt1 = \App\Models\QuizOption::create([
            'quiz_question_id' => $q1->id,
            'option_text' => 'TALL Stack',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $opt2 = \App\Models\QuizOption::create([
            'quiz_question_id' => $q1->id,
            'option_text' => 'Legacy CGI',
            'is_correct' => false,
            'sort_order' => 2,
        ]);

        // Wrong answer -> fails, no event dispatched
        Livewire::test(\App\Livewire\QuizPlayerEmbedded::class, ['quizId' => $quiz->id])
            ->set("answers.{$q1->id}", $opt2->id)
            ->call('submitQuiz')
            ->assertSet('hasPassed', false)
            ->assertNotDispatched('quizPassed');

        // Correct answer -> passes, dispatches quizPassed
        Livewire::test(\App\Livewire\QuizPlayerEmbedded::class, ['quizId' => $quiz->id])
            ->set("answers.{$q1->id}", $opt1->id)
            ->call('submitQuiz')
            ->assertSet('hasPassed', true)
            ->assertDispatched('quizPassed');
    }

    public function test_embedded_assignment_task_submits_and_dispatches_task_submitted(): void
    {
        [$student, $course] = $this->createTestCourseWithSequentialLessons();
        $this->actingAs($student);

        $assignment = \App\Models\Assignment::create([
            'course_id' => $course->id,
            'name' => 'Module 1 Project Task',
            'description' => 'Build and push your repository link',
        ]);

        Livewire::test(\App\Livewire\AssignmentTaskEmbedded::class, ['assignmentId' => $assignment->id])
            ->set('link', 'https://github.com/thinker/project')
            ->set('content', 'Finished all specifications.')
            ->call('submitTask')
            ->assertSet('isSubmitted', true)
            ->assertDispatched('taskSubmitted');

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'link' => 'https://github.com/thinker/project',
        ]);
    }
}
