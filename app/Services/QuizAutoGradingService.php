<?php

namespace App\Services;

use App\Ai\Agents\QuizQuestionGrader;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Log;
use Throwable;

class QuizAutoGradingService
{
    protected ?string $apiKey;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: config('ai.providers.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->timeout = (int) config('services.gemini.timeout', 15);
    }

    /**
     * Check if AI grading is configured with an active key or fake agent.
     */
    public function isConfigured(): bool
    {
        if (QuizQuestionGrader::isFaked()) {
            return true;
        }

        return ! empty($this->apiKey)
            || ! empty(config('services.gemini.api_key'))
            || ! empty(config('ai.providers.gemini.key'))
            || ! empty(config('ai.providers.openai.key'));
    }

    /**
     * Grade a theory or practical question using the QuizQuestionGrader agent.
     *
     * @return array{points_earned: int, is_correct: bool, feedback: string, auto_graded: bool}
     */
    public function grade(QuizQuestion $question, ?string $studentAnswer): array
    {
        $cleanAnswer = trim((string) $studentAnswer);
        $maxPoints = max(1, (int) $question->points);

        // If student submitted an empty answer
        if ($cleanAnswer === '') {
            return [
                'points_earned' => 0,
                'is_correct' => false,
                'feedback' => 'No response provided.',
                'auto_graded' => true,
            ];
        }

        // If AI is not configured, fall back to pending manual review
        if (! $this->isConfigured()) {
            Log::info('QuizAutoGradingService: AI API key not configured. Queuing for manual instructor review.');

            return [
                'points_earned' => 0,
                'is_correct' => false,
                'feedback' => 'Pending instructor review.',
                'auto_graded' => false,
            ];
        }

        try {
            return $this->evaluateWithAgent($question, $cleanAnswer, $maxPoints);
        } catch (Throwable $e) {
            Log::warning('QuizAutoGradingService evaluation error: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'exception' => $e,
            ]);

            return [
                'points_earned' => 0,
                'is_correct' => false,
                'feedback' => 'Automated evaluation unavailable. Pending instructor review.',
                'auto_graded' => false,
            ];
        }
    }

    /**
     * Call the QuizQuestionGrader agent to evaluate the student answer.
     *
     * @return array{points_earned: int, is_correct: bool, feedback: string, auto_graded: bool}
     */
    protected function evaluateWithAgent(QuizQuestion $question, string $studentAnswer, int $maxPoints): array
    {
        if (! empty(config('services.gemini.api_key')) && empty(config('ai.providers.gemini.key'))) {
            config(['ai.providers.gemini.key' => config('services.gemini.api_key')]);
        }

        $userMessage = <<<USER
Question Prompt:
{$question->question}

Reference Rubric / Explanation:
{$question->explanation}

Maximum Points Available:
{$maxPoints}

Student Submitted Answer:
{$studentAnswer}
USER;

        $response = QuizQuestionGrader::make(question: $question)->prompt($userMessage, timeout: $this->timeout);

        $pointsEarned = max(0, min($maxPoints, (int) ($response['points_earned'] ?? 0)));
        $isCorrect = (bool) ($response['is_correct'] ?? ($pointsEarned >= ceil($maxPoints / 2)));
        $feedback = trim((string) ($response['feedback'] ?? 'Graded automatically.'));

        return [
            'points_earned' => $pointsEarned,
            'is_correct' => $isCorrect,
            'feedback' => $feedback !== '' ? $feedback : 'Graded automatically.',
            'auto_graded' => true,
        ];
    }
}
