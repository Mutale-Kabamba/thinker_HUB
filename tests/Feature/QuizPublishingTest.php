<?php

namespace Tests\Feature;

use App\Filament\Student\Pages\Quizzes;
use App\Filament\Student\Pages\TakeQuiz;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Notifications\QuizPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class QuizPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_scheduled_quiz_is_scheduled_and_blocked_from_taking(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $futureQuiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Midterm Exam',
            'publish_at' => now()->addDays(2),
            'is_active' => false,
        ]);

        $this->assertFalse($futureQuiz->isReleased());

        $this->actingAs($student);

        // Student quizzes page shows scheduled status
        Livewire::test(Quizzes::class)
            ->assertSee('Midterm Exam')
            ->assertSee('Available');

        // TakeQuiz page should deny access
        Livewire::withQueryParams(['quiz' => (string) $futureQuiz->id])
            ->test(TakeQuiz::class)
            ->assertRedirect(route('filament.student.pages.quizzes'));
    }

    public function test_quiz_is_automatically_published_when_publish_date_passes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Data Science',
            'code' => 'DS101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $scheduledQuiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Python Basics Quiz',
            'publish_at' => now()->subMinute(),
            'is_active' => false,
        ]);

        // Even with is_active = false in DB, publish_at <= now() marks it released
        $this->assertTrue($scheduledQuiz->isReleased());

        $quizzes = Quiz::query()
            ->with(['course', 'questions'])
            ->whereIn('course_id', [$course->id])
            ->released()
            ->get();

        $this->assertCount(1, $quizzes);
        $this->assertSame('Python Basics Quiz', $quizzes->first()->title);

        // Running publishScheduled or quizzes:publish activates it
        $this->artisan('quizzes:publish')
            ->expectsOutput('Published 1 scheduled quizzes.')
            ->assertSuccessful();

        $this->assertTrue($scheduledQuiz->fresh()->is_active);
    }

    public function test_future_quiz_does_not_block_course_progress(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Graphic Design',
            'code' => 'GD101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        // Create a future quiz
        Quiz::create([
            'course_id' => $course->id,
            'title' => 'Final Exam (Scheduled)',
            'publish_at' => now()->addWeek(),
            'is_active' => true,
        ]);

        $progress = $student->courseProgress($course);

        // Quizzes total should only count released quizzes (0 in this case)
        $this->assertSame(0, $progress['quizzes']['total']);
    }

    public function test_quiz_release_sends_notification_to_enrolled_students(): void
    {
        Notification::fake();

        $student = User::factory()->create(['role' => 'student']);
        $course = Course::query()->create([
            'title' => 'Cybersecurity',
            'code' => 'SEC101',
            'is_active' => true,
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Network Defense Quiz',
            'publish_at' => null,
            'is_active' => true,
        ]);

        Notification::assertSentTo($student, QuizPublishedNotification::class);
    }

    public function test_instructor_can_create_quiz_via_filament_form(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('instructor'));

        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'title' => 'Software Testing',
            'code' => 'TEST101',
            'is_active' => true,
        ]);
        $instructor->instructorCourses()->attach($course->id);

        $this->actingAs($instructor);

        Livewire::test(\App\Filament\Instructor\Resources\QuizResource\Pages\CreateQuiz::class)
            ->fillForm([
                'course_id' => $course->id,
                'title' => 'Unit Testing Quiz',
                'description' => 'Test your knowledge',
                'pass_percentage' => 70,
                'is_active' => true,
                'questions' => [
                    [
                        'type' => 'multiple_choice',
                        'question' => 'What is TDD?',
                        'points' => 2,
                        'options' => [
                            ['option_text' => 'Test Driven Development', 'is_correct' => true],
                            ['option_text' => 'Time Delay Device', 'is_correct' => false],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => 'Unit Testing Quiz',
        ]);

        $this->assertDatabaseHas('quiz_questions', [
            'question' => 'What is TDD?',
        ]);

        $this->assertDatabaseHas('quiz_options', [
            'option_text' => 'Test Driven Development',
            'is_correct' => true,
        ]);
    }
}
