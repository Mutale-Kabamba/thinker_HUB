<?php

namespace Tests\Feature;

use App\Filament\Instructor\Pages\StudentResults;
use App\Filament\Student\Pages\Assessments;
use App\Filament\Student\Pages\Assignments;
use App\Filament\Student\Pages\Quizzes;
use App\Filament\Student\Pages\TakeQuiz;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EvaluationRetakeSecondChanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_grant_second_chance_and_student_retake_caps_at_pass_percentage(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'track' => 'Beginner',
        ]);

        $course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB101',
            'is_active' => true,
        ]);

        $course->instructors()->attach($instructor->id);
        $student->courses()->attach($course->id);

        $quiz = Quiz::query()->create([
            'course_id' => $course->id,
            'title' => 'HTML & CSS Basics',
            'pass_percentage' => 50,
            'is_active' => true,
            'is_published' => true,
        ]);

        $question1 = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'What does HTML stand for?',
            'points' => 10,
        ]);

        $opt1A = QuizOption::query()->create([
            'quiz_question_id' => $question1->id,
            'option_text' => 'HyperText Markup Language',
            'is_correct' => true,
        ]);

        $opt1B = QuizOption::query()->create([
            'quiz_question_id' => $question1->id,
            'option_text' => 'HighText Machine Language',
            'is_correct' => false,
        ]);

        $question2 = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'What is CSS?',
            'points' => 10,
        ]);

        $opt2A = QuizOption::query()->create([
            'quiz_question_id' => $question2->id,
            'option_text' => 'Cascading Style Sheets',
            'is_correct' => true,
        ]);

        $opt2B = QuizOption::query()->create([
            'quiz_question_id' => $question2->id,
            'option_text' => 'Creative Sheet Style',
            'is_correct' => false,
        ]);

        // Student takes quiz and fails (0%)
        $initialAttempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 0,
            'total_points' => 20,
            'percentage' => 0,
            'passed' => false,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        // 1. Instructor grants retake via StudentResults
        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('grantQuizRetake', $student->id, $quiz->id)
            ->assertNotified('Second Chance Granted!');

        $initialAttempt->refresh();
        $this->assertTrue($initialAttempt->retake_allowed);
        $this->assertEquals($instructor->id, $initialAttempt->retake_granted_by);

        // 2. Student sees retake available in Quizzes list
        $this->actingAs($student);
        Livewire::test(Quizzes::class)
            ->assertSee('2nd Try Available')
            ->assertSee('Retake Quiz (2nd Try)');

        // 3. Student takes quiz and answers all questions correctly (100% raw score)
        Livewire::withQueryParams(['quiz' => $quiz->id])
            ->test(TakeQuiz::class)
            ->assertSee('⭐ 2nd Attempt / Retake')
            ->set("answers.{$question1->id}", $opt1A->id)
            ->set("answers.{$question2->id}", $opt2A->id)
            ->call('submitQuiz')
            ->assertSet('submitted', true);

        // 4. Verify recorded score is capped at the passing mark (50% = 10 pts), but raw score is 20
        $retakeAttempt = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $student->id)
            ->where('is_retake', true)
            ->first();

        $this->assertNotNull($retakeAttempt);
        $this->assertTrue($retakeAttempt->is_retake);
        $this->assertEquals(50, $retakeAttempt->percentage, 'Percentage must be capped at 50% pass mark');
        $this->assertEquals(10, $retakeAttempt->score, 'Score must be capped at 10 pts (50% of 20)');
        $this->assertEquals(20, $retakeAttempt->raw_score, 'Raw score must be preserved as 20');
        $this->assertTrue($retakeAttempt->passed);
    }

    public function test_student_retake_failing_keeps_actual_failing_score(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);
        $course = Course::query()->create(['title' => 'Python', 'code' => 'PY101', 'is_active' => true]);
        $course->instructors()->attach($instructor->id);
        $student->courses()->attach($course->id);

        $quiz = Quiz::query()->create([
            'course_id' => $course->id,
            'title' => 'Python Basics',
            'pass_percentage' => 50,
            'is_active' => true,
            'is_published' => true,
        ]);

        $q1 = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'Question 1',
            'points' => 10,
        ]);

        $opt1Correct = QuizOption::query()->create([
            'quiz_question_id' => $q1->id,
            'option_text' => 'Correct',
            'is_correct' => true,
        ]);

        $opt1Wrong = QuizOption::query()->create([
            'quiz_question_id' => $q1->id,
            'option_text' => 'Wrong',
            'is_correct' => false,
        ]);

        $q2 = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question' => 'Question 2',
            'points' => 10,
        ]);

        $opt2Correct = QuizOption::query()->create([
            'quiz_question_id' => $q2->id,
            'option_text' => 'Correct',
            'is_correct' => true,
        ]);

        $opt2Wrong = QuizOption::query()->create([
            'quiz_question_id' => $q2->id,
            'option_text' => 'Wrong',
            'is_correct' => false,
        ]);

        $firstAttempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 0,
            'total_points' => 20,
            'percentage' => 0,
            'passed' => false,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        $firstAttempt->grantRetake($instructor);

        $this->actingAs($student);
        // Student answers wrong options (0%)
        Livewire::withQueryParams(['quiz' => $quiz->id])
            ->test(TakeQuiz::class)
            ->set("answers.{$q1->id}", $opt1Wrong->id)
            ->set("answers.{$q2->id}", $opt2Wrong->id)
            ->call('submitQuiz');

        $retakeAttempt = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $student->id)
            ->where('is_retake', true)
            ->first();

        $this->assertNotNull($retakeAttempt);
        $this->assertEquals(0, $retakeAttempt->percentage);
        $this->assertEquals(0, $retakeAttempt->score);
        $this->assertFalse($retakeAttempt->passed);
    }

    public function test_instructor_can_revoke_quiz_retake_permission(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);
        $course = Course::query()->create(['title' => 'Design', 'code' => 'DES101', 'is_active' => true]);
        $course->instructors()->attach($instructor->id);
        $student->courses()->attach($course->id);

        $quiz = Quiz::query()->create([
            'course_id' => $course->id,
            'title' => 'Design Fundamentals',
            'pass_percentage' => 50,
            'is_active' => true,
            'is_published' => true,
        ]);

        $attempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 0,
            'total_points' => 10,
            'percentage' => 0,
            'passed' => false,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(1),
        ]);

        $attempt->grantRetake($instructor);
        $this->assertTrue($attempt->fresh()->retake_allowed);

        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('revokeQuizRetake', $student->id, $quiz->id)
            ->assertNotified('Quiz retake permission revoked.');

        $this->assertFalse($attempt->fresh()->retake_allowed);
    }

    public function test_assignment_retake_flow_and_grade_capping(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);
        $course = Course::query()->create(['title' => 'React Course', 'code' => 'REA101', 'is_active' => true]);
        $course->instructors()->attach($instructor->id);
        $student->courses()->attach($course->id);

        $assignment = Assignment::query()->create([
            'name' => 'Portfolio Component',
            'course_id' => $course->id,
            'scope' => 'all',
        ]);

        $submission = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'First attempt text',
            'status' => 'Graded',
            'grade' => 30,
            'feedback' => 'Incomplete work.',
            'submitted_at' => now()->subDays(1),
        ]);

        // 1. Instructor grants retake
        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('grantAssignmentRetake', $submission->id)
            ->assertNotified('Second Chance Granted!');

        $submission->refresh();
        $this->assertTrue($submission->retake_allowed);

        // 2. Student resubmits
        $this->actingAs($student);
        Livewire::test(Assignments::class)
            ->set("submissionDrafts.{$assignment->id}.text", 'Updated perfected portfolio work')
            ->call('submit', $assignment->id)
            ->assertNotified('Revised assignment submitted successfully!');

        $submission->refresh();
        $this->assertTrue($submission->is_retake);
        $this->assertFalse($submission->retake_allowed);
        $this->assertEquals('Submitted', $submission->status);
        $this->assertEquals('Updated perfected portfolio work', $submission->content);

        // 3. Instructor grades retake with 95% -> automatically capped at 50% passing mark with raw_grade = 95
        $submission->update([
            'status' => 'Graded',
            'grade' => 95,
            'feedback' => 'Great improvements!',
        ]);

        $submission->refresh();
        $this->assertEquals(50, $submission->grade, 'Retake grade must be capped at 50%');
        $this->assertEquals(95, $submission->raw_grade, 'Raw grade must store 95%');
    }

    public function test_assessment_retake_flow_and_score_capping(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student', 'track' => 'Beginner']);
        $course = Course::query()->create(['title' => 'Data Science', 'code' => 'DS101', 'is_active' => true]);
        $course->instructors()->attach($instructor->id);
        $student->courses()->attach($course->id);

        $assessment = Assessment::query()->create([
            'name' => 'Data Analysis Capstone',
            'course_id' => $course->id,
            'user_id' => $student->id,
            'scope' => 'all',
        ]);

        $submission = AssessmentSubmission::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'content' => 'First buggy report',
            'status' => 'Graded',
            'score' => 25,
            'feedback' => 'Major errors found.',
            'submitted_at' => now()->subDays(1),
        ]);

        // 1. Instructor grants retake
        $this->actingAs($instructor);
        Livewire::test(StudentResults::class)
            ->call('grantAssessmentRetake', $submission->id)
            ->assertNotified('Second Chance Granted!');

        $submission->refresh();
        $this->assertTrue($submission->retake_allowed);

        // 2. Student resubmits
        $this->actingAs($student);
        Livewire::test(Assessments::class)
            ->set("submissionDrafts.{$assessment->id}.text", 'Clean dataset and fixed analysis')
            ->call('submit', $assessment->id)
            ->assertNotified('Revised assessment submitted successfully!');

        $submission->refresh();
        $this->assertTrue($submission->is_retake);
        $this->assertFalse($submission->retake_allowed);
        $this->assertEquals('Submitted', $submission->status);

        // 3. Instructor grades retake with 100% -> automatically capped at 50% with raw_score = 100
        $submission->update([
            'status' => 'Graded',
            'score' => 100,
            'feedback' => 'Perfect resubmission.',
        ]);

        $submission->refresh();
        $this->assertEquals(50, $submission->score, 'Retake score must be capped at 50%');
        $this->assertEquals(100, $submission->raw_score, 'Raw score must store 100%');
    }
}
