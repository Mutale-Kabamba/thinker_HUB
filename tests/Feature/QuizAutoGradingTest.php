<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\QuizAutoGradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuizAutoGradingTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private Quiz $quiz;
    private QuizQuestion $theoryQuestion;
    private QuizQuestion $practicalQuestion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::query()->create([
            'title' => 'Web Development',
            'code' => 'WEB-101',
            'is_active' => true,
        ]);

        $this->quiz = Quiz::query()->create([
            'course_id' => $this->course->id,
            'title' => 'Midterm Assessment',
            'pass_percentage' => 50,
            'is_active' => true,
        ]);

        $this->theoryQuestion = QuizQuestion::query()->create([
            'quiz_id' => $this->quiz->id,
            'type' => 'theory',
            'question' => 'Explain the concept of asynchronous execution in JavaScript.',
            'explanation' => 'Must mention non-blocking behavior, event loop, and callback/promises/async-await.',
            'points' => 5,
            'sort_order' => 1,
        ]);

        $this->practicalQuestion = QuizQuestion::query()->create([
            'quiz_id' => $this->quiz->id,
            'type' => 'practical',
            'question' => 'Write a function filterPositiveEven that filters positive even integers.',
            'explanation' => 'function filterPositiveEven(arr) { return arr.filter(n => n > 0 && n % 2 === 0); }',
            'points' => 5,
            'sort_order' => 2,
        ]);
    }

    public function test_service_returns_zero_points_for_empty_answer(): void
    {
        $service = app(QuizAutoGradingService::class);

        $result = $service->grade($this->theoryQuestion, '');

        $this->assertEquals(0, $result['points_earned']);
        $this->assertFalse($result['is_correct']);
        $this->assertEquals('No response provided.', $result['feedback']);
    }

    public function test_service_falls_back_gracefully_when_gemini_key_is_missing(): void
    {
        Config::set('services.gemini.api_key', null);
        $service = new QuizAutoGradingService();

        $this->assertFalse($service->isConfigured());

        $result = $service->grade($this->theoryQuestion, 'JavaScript runs asynchronously using the event loop.');

        $this->assertEquals(0, $result['points_earned']);
        $this->assertFalse($result['is_correct']);
        $this->assertEquals('Pending instructor review.', $result['feedback']);
        $this->assertFalse($result['auto_graded']);
    }

    public function test_service_grades_theory_successfully_via_mocked_gemini_response(): void
    {
        Config::set('services.gemini.api_key', 'mock-gemini-key');
        $service = new QuizAutoGradingService();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'points_earned' => 5,
                                        'is_correct' => true,
                                        'feedback' => 'Comprehensive explanation covering non-blocking I/O and the event loop.',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $service->grade($this->theoryQuestion, 'JavaScript is non-blocking and handles async tasks using the Event Loop.');

        $this->assertEquals(5, $result['points_earned']);
        $this->assertTrue($result['is_correct']);
        $this->assertStringContainsString('Comprehensive explanation', $result['feedback']);
        $this->assertTrue($result['auto_graded']);
    }

    public function test_service_grades_practical_code_with_partial_marks_via_gemini(): void
    {
        Config::set('services.gemini.api_key', 'mock-gemini-key');
        $service = new QuizAutoGradingService();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'points_earned' => 3,
                                        'is_correct' => true,
                                        'feedback' => 'Correctly filtered even numbers using modulo, but forgot to ensure numbers are strictly positive (> 0).',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $code = 'function filterPositiveEven(arr) { return arr.filter(n => n % 2 === 0); }';
        $result = $service->grade($this->practicalQuestion, $code);

        $this->assertEquals(3, $result['points_earned']);
        $this->assertTrue($result['is_correct']);
        $this->assertStringContainsString('forgot to ensure numbers are strictly positive', $result['feedback']);
    }

    public function test_quiz_grade_attempt_integrates_with_auto_grading_service(): void
    {
        Config::set('services.gemini.api_key', 'mock-gemini-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'points_earned' => 4,
                                        'is_correct' => true,
                                        'feedback' => 'Good answer with minor points missed.',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $student = User::factory()->create(['role' => 'student']);
        $attempt = QuizAttempt::query()->create([
            'quiz_id' => $this->quiz->id,
            'user_id' => $student->id,
            'started_at' => now(),
        ]);

        $answer = QuizAnswer::query()->create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $this->theoryQuestion->id,
            'text_answer' => 'JS handles async via callbacks and promises.',
            'points_earned' => 0,
        ]);

        $gradedAttempt = $this->quiz->gradeAttempt($attempt);

        $answer->refresh();
        $this->assertEquals(4, $answer->points_earned);
        $this->assertTrue($answer->is_correct);
        $this->assertEquals('Good answer with minor points missed.', $answer->feedback);
        $this->assertEquals(4, $gradedAttempt->score);
    }
}
