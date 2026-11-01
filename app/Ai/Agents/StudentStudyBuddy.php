<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Student\GetStudentUpcomingScheduleTool;
use App\Ai\Tools\Student\ReviewCompletedQuizAttemptsTool;
use App\Ai\Tools\Student\SearchStudentMaterialsTool;
use App\Models\User;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\RemembersConversations as RemembersConversationsContract;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider([Lab::Gemini, Lab::OpenAI])]
#[Temperature(0.4)]
#[MaxSteps(5)]
#[Timeout(30)]
class StudentStudyBuddy implements Agent, Conversational, HasTools, RemembersConversationsContract
{
    use Promptable;
    use RemembersConversations;

    public function __construct(public User $student)
    {
    }

    /**
     * Determine the provider dynamically based on available configuration.
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
You are the dedicated Socratic Study Buddy and academic tutor for {$this->student->name} at "think.er HUB".
Your mission is to empower the student's mastery of their enrolled courses.

Pedagogical Guidelines:
1. Always use a friendly, encouraging, and Socratic tutoring approach. Break down complex programming and theoretical concepts step-by-step.
2. When answering questions about course content or preps, search accessible course materials using `SearchStudentMaterialsTool`.
3. When the student asks about classes, schedule, or upcoming deadlines, look up live data using `GetStudentUpcomingScheduleTool`.
4. When reviewing past quizzes, use `ReviewCompletedQuizAttemptsTool` to analyze their mistakes, praise what they got right, and explain why the rubric solution works.
5. Never hand over full homework or assessment solutions directly. Guide the student with leading questions, conceptual hints, and debugging strategies.
6. When documents are attached to the prompt, carefully read and reference the attached document contents.
PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Tool>
     */
    public function tools(): iterable
    {
        return [
            new SearchStudentMaterialsTool($this->student),
            new GetStudentUpcomingScheduleTool($this->student),
            new ReviewCompletedQuizAttemptsTool($this->student),
        ];
    }
}

