<?php

namespace App\Ai\Agents;

use App\Models\QuizQuestion;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider([Lab::Gemini, Lab::OpenAI])]
#[Temperature(0.1)]
#[Timeout(15)]
class QuizQuestionGrader implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(public QuizQuestion $question)
    {
    }

    /**
     * Determine the provider dynamically based on available configuration.
     * Gemini is primary, OpenAI is automatic failover.
     *
     * @return array<int, Lab>|Lab|string
     */
    public function provider(): array|Lab|string
    {
        $providers = [];

        if (! empty(config('ai.providers.gemini.key')) || ! empty(config('services.gemini.api_key'))) {
            $providers[] = Lab::Gemini;
        }

        if (! empty(config('ai.providers.openai.key'))) {
            $providers[] = Lab::OpenAI;
        }

        return $providers !== [] ? $providers : [Lab::Gemini, Lab::OpenAI];
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        $isTheory = $this->question->isTheory();
        $typeLabel = $isTheory ? 'theory (conceptual explanation)' : 'practical (code / technical implementation)';
        $maxPoints = max(1, (int) $this->question->points);

        return <<<PROMPT
You are an expert academic evaluator and programming instructor at "think.er HUB".
Your job is to objectively, accurately, and constructively evaluate student answers to {$typeLabel} questions.

Evaluation Guidelines:
1. Compare the student's answer against the Question Prompt and the Reference Rubric / Explanation provided.
2. For Theory questions: Assess accuracy of terminology, conceptual understanding, key mechanisms explained, and practical examples.
3. For Practical questions: Assess algorithmic logic, syntax correctness, function signatures, edge cases, and whether the code fulfills the prompt's objectives. Minor stylistic differences or variable names should not be penalized.
4. Award points proportionally from 0 up to {$maxPoints}. You can award partial credit.
5. Provide concise, friendly, and pedagogical feedback (1-3 sentences) explaining why marks were awarded or where mistakes occurred.
PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        $maxPoints = max(1, (int) $this->question->points);

        return [
            'points_earned' => $schema->integer()->min(0)->max($maxPoints)->required(),
            'is_correct' => $schema->boolean()->required(),
            'feedback' => $schema->string()->required(),
        ];
    }
}

