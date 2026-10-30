<?php

namespace App\Services;

use App\Models\QuizQuestion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class QuizAutoGradingService
{
    protected ?string $apiKey;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->timeout = (int) config('services.gemini.timeout', 15);
    }

    /**
     * Check if Gemini API is configured with an active key.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Grade a theory or practical question using Gemini.
     *
     * @return array{points_earned: int, is_correct: bool, feedback: string, auto_graded: bool}
     */
    public function grade(QuizQuestion $question, ?string $studentAnswer): array
    {
        $cleanAnswer = trim((string) $studentAnswer);
        $maxPoints = (int) $question->points;

        // If student submitted an empty answer
        if ($cleanAnswer === '') {
            return [
                'points_earned' => 0,
                'is_correct' => false,
                'feedback' => 'No response provided.',
                'auto_graded' => true,
            ];
        }

        // If Gemini is not configured, fall back to pending manual review
        if (! $this->isConfigured()) {
            Log::info('QuizAutoGradingService: Gemini API key not configured. Queuing for manual instructor review.');

            return [
                'points_earned' => 0,
                'is_correct' => false,
                'feedback' => 'Pending instructor review.',
                'auto_graded' => false,
            ];
        }

        try {
            return $this->evaluateWithGemini($question, $cleanAnswer, $maxPoints);
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
     * Call the Gemini REST endpoint with strict JSON schema instructions.
     *
     * @return array{points_earned: int, is_correct: bool, feedback: string, auto_graded: bool}
     */
    protected function evaluateWithGemini(QuizQuestion $question, string $studentAnswer, int $maxPoints): array
    {
        $isTheory = $question->isTheory();
        $typeLabel = $isTheory ? 'theory (conceptual explanation)' : 'practical (code / technical implementation)';

        $systemPrompt = <<<PROMPT
You are an expert academic evaluator and programming instructor at "think.er HUB".
Your job is to objectively, accurately, and constructively evaluate student answers to {$typeLabel} questions.

Evaluation Guidelines:
1. Compare the student's answer against the Question Prompt and the Reference Rubric / Explanation provided.
2. For Theory questions: Assess accuracy of terminology, conceptual understanding, key mechanisms explained, and practical examples.
3. For Practical questions: Assess algorithmic logic, syntax correctness, function signatures, edge cases, and whether the code fulfills the prompt's objectives. Minor stylistic differences or variable names should not be penalized.
4. Award points proportionally from 0 up to {$maxPoints}. You can award partial credit.
5. Provide concise, friendly, and pedagogical feedback (1-3 sentences) explaining why marks were awarded or where mistakes occurred.
6. Return STRICT JSON with the following schema:
{
  "points_earned": <integer between 0 and {$maxPoints}>,
  "is_correct": <boolean: true if points_earned is at least 50% of {$maxPoints}, false otherwise>,
  "feedback": "<concise constructive feedback for the student>"
}
Do not include any Markdown ticks or code blocks outside the JSON. Return only the raw JSON string.
PROMPT;

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

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\n\n" . $userMessage],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1, // Low temperature for consistent, deterministic grading
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Gemini API call failed: ' . $response->body());
        }

        $body = $response->json();
        $rawText = Arr::get($body, 'candidates.0.content.parts.0.text', '');

        $cleaned = trim($rawText);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $parsed = json_decode($cleaned, true);

        if (! is_array($parsed)) {
            throw new \RuntimeException('Failed to parse Gemini response as JSON: ' . $rawText);
        }

        $pointsEarned = max(0, min($maxPoints, (int) Arr::get($parsed, 'points_earned', 0)));
        $isCorrect = (bool) Arr::get($parsed, 'is_correct', $pointsEarned >= ceil($maxPoints / 2));
        $feedback = trim((string) Arr::get($parsed, 'feedback', 'Graded automatically.'));

        return [
            'points_earned' => $pointsEarned,
            'is_correct' => $isCorrect,
            'feedback' => $feedback !== '' ? $feedback : 'Graded automatically.',
            'auto_graded' => true,
        ];
    }
}
