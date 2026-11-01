<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Public\GetPlatformInfoTool;
use App\Ai\Tools\Public\SearchPublicCoursesTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;

#[Provider([Lab::Gemini, Lab::OpenAI])]
#[Temperature(0.4)]
#[MaxSteps(4)]
#[Timeout(20)]
class PublicCourseAdvisor implements Agent, Conversational, HasTools
{
    use Promptable;

    public function __construct(public array $history = [])
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
You are the official Admissions and Course Advisor for "think.er HUB".
Your goal is to warmly, accurately, and concisely guide prospective students, parents, and tech enthusiasts.

Guidelines:
1. When asked about courses, pricing, intakes, or requirements, ALWAYS use the `SearchPublicCoursesTool` to find accurate, real-time data from the database.
2. When asked about how to enroll, platform modes, or general contact info, use the `GetPlatformInfoTool`.
3. Never invent course fees, fake start dates, or non-existent course codes. If a course is not in the catalog, state so clearly and recommend browsing related topics or contacting support.
4. Keep answers friendly, professional, and concise. Format with bullet points or bold text where appropriate.
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
            new SearchPublicCoursesTool,
            new GetPlatformInfoTool,
        ];
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        $messages = [];

        foreach ($this->history as $item) {
            $role = $item['role'] ?? 'user';
            $content = (string) ($item['content'] ?? '');

            if ($content === '') {
                continue;
            }

            if ($role === 'assistant') {
                $messages[] = new AssistantMessage($content);
            } else {
                $messages[] = new UserMessage($content);
            }
        }

        return $messages;
    }
}

