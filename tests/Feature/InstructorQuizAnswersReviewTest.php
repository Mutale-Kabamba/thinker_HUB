<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\StudentResults;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstructorQuizAnswersReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_open_and_view_student_quiz_answers_breakdown(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor', 'name' => 'Dr. Smith']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Charlie Brown', 'email' => 'charlie@example.com']);

        $course = Course::query()->create([
            'title' => 'Python for Beginners',
            'code' => 'PY101',
            'is_active' => true,
        ]);
        $course->instructors()->attach($instructor->id);

        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'Python Basics Quiz',
            'description' => 'Test your basic python skills',
            'time_limit_minutes' => 30,
            'pass_percentage' => 70,
            'is_active' => true,
            'show_results' => true,
        ]);

        // Q1: Multiple choice (Student answered correctly)
        $q1 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'What is the keyword to define a function in Python?',
            'explanation' => 'The `def` keyword defines functions in Python.',
            'points' => 10,
            'sort_order' => 1,
        ]);
        $opt1A = QuizOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'func', 'is_correct' => false, 'sort_order' => 1]);
        $opt1B = QuizOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'def', 'is_correct' => true, 'sort_order' => 2]);
        $opt1C = QuizOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'function', 'is_correct' => false, 'sort_order' => 3]);

        // Q2: Multiple choice (Student answered incorrectly)
        $q2 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'Which data type is immutable?',
            'explanation' => 'Tuples cannot be modified after creation.',
            'points' => 10,
            'sort_order' => 2,
        ]);
        $opt2A = QuizOption::create(['quiz_question_id' => $q2->id, 'option_text' => 'List', 'is_correct' => false, 'sort_order' => 1]);
        $opt2B = QuizOption::create(['quiz_question_id' => $q2->id, 'option_text' => 'Tuple', 'is_correct' => true, 'sort_order' => 2]);
        $opt2C = QuizOption::create(['quiz_question_id' => $q2->id, 'option_text' => 'Dictionary', 'is_correct' => false, 'sort_order' => 3]);

        // Q3: Theory question
        $q3 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'type' => 'theory',
            'question' => 'Explain PEP 8 in one sentence.',
            'explanation' => 'PEP 8 is the Python style guide.',
            'points' => 10,
            'sort_order' => 3,
        ]);

        // Create completed attempt
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 10,
            'total_points' => 30,
            'percentage' => 33,
            'passed' => false,
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
        ]);

        // Answers
        QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $q1->id,
            'quiz_option_id' => $opt1B->id,
            'is_correct' => true,
            'points_earned' => 10,
        ]);
        QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $q2->id,
            'quiz_option_id' => $opt2A->id,
            'is_correct' => false,
            'points_earned' => 0,
        ]);
        QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $q3->id,
            'text_answer' => 'PEP 8 is the official style guide for writing readable Python code.',
            'is_correct' => null,
            'points_earned' => 0,
        ]);

        $this->actingAs($instructor);

        // Test Livewire component
        Livewire::test(StudentResults::class)
            ->assertSee('Python Basics Quiz')
            ->assertSee('Charlie Brown')
            ->assertSee('View Answers')
            ->call('viewQuizAttempt', $attempt->id)
            ->assertSet('selectedQuizAttemptId', $attempt->id)
            ->assertSee('Student Quiz Review & Answers')
            ->assertSee('What is the keyword to define a function in Python?')
            ->assertSee('Selected (Correct)')
            ->assertSee('Which data type is immutable?')
            ->assertSee('Selected (Incorrect)')
            ->assertSee('Correct Answer')
            ->assertSee('Explain PEP 8 in one sentence.')
            ->assertSee('PEP 8 is the official style guide for writing readable Python code.')
            ->assertSee('The `def` keyword defines functions in Python.')
            ->assertSee('Tuples cannot be modified after creation.')
            ->call('closeQuizAttemptModal')
            ->assertSet('selectedQuizAttemptId', null);
    }

    public function test_instructor_cannot_view_quiz_answers_for_unauthorized_course(): void
    {
        $instructor1 = User::factory()->create(['role' => 'instructor']);
        $instructor2 = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $course1 = Course::query()->create(['title' => 'Instructor 1 Course', 'code' => 'I101', 'is_active' => true]);
        $course1->instructors()->attach($instructor1->id);

        $course2 = Course::query()->create(['title' => 'Instructor 2 Course', 'code' => 'I102', 'is_active' => true]);
        $course2->instructors()->attach($instructor2->id);

        $quiz = Quiz::create([
            'course_id' => $course2->id,
            'title' => 'Private Quiz',
            'is_active' => true,
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 80,
            'total_points' => 100,
            'percentage' => 80,
            'passed' => true,
            'completed_at' => now(),
        ]);

        $this->actingAs($instructor1);

        Livewire::test(StudentResults::class)
            ->call('viewQuizAttempt', $attempt->id)
            ->assertNotified('Quiz attempt not found or unauthorized.')
            ->assertSet('selectedQuizAttemptId', null);
    }
}
