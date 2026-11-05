<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider([Lab::Gemini, Lab::OpenAI])]
#[Temperature(0.3)]
#[Timeout(60)]
class QuizGeneratorAgent implements Agent, HasStructuredOutput
{
    use Promptable;

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
        return <<<PROMPT
You are a senior curriculum designer and technical assessment specialist for "think.er HUB".
Your objective is to author rigorous, academically sound, and engaging quizzes aligned with course materials and requested topics.

Question Guidelines:
1. Support three question types:
   - multiple_choice: Concept checks with plausible distractors. Always provide exactly 4 options with exactly one correct option (is_correct: true) and three incorrect options (is_correct: false).
   - theory: Deep-dive conceptual questions testing understanding, mechanisms, and real-world trade-offs. Provide an exhaustive grading rubric in the explanation.
   - practical: Real-world technical tasks, code snippets to write or debug, or architectural exercises. Provide complete reference solutions or grading criteria in the explanation.
2. Structure the quiz logically with clear instructions, fair point values (default 1-5 points), and concise question prompts.
3. Every question must include a thorough, pedagogical 'explanation' that serves as the instructor grading key and student review guide.
PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'description' => $schema->string()->nullable(),
            'questions' => $schema->array()->items(
                $schema->object([
                    'type' => $schema->string()->enum(['multiple_choice', 'theory', 'practical'])->required(),
                    'question' => $schema->string()->required(),
                    'explanation' => $schema->string()->required(),
                    'points' => $schema->integer()->min(1)->required(),
                    'options' => $schema->array()->items(
                        $schema->object([
                            'option_text' => $schema->string()->required(),
                            'is_correct' => $schema->boolean()->required(),
                        ])
                    )->nullable(),
                ])
            )->required(),
        ];
    }
}

