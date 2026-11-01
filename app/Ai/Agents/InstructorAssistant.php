<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Instructor\CreateCourseSessionTool;
use App\Ai\Tools\Instructor\GetCohortPerformanceSummaryTool;
use App\Ai\Tools\Instructor\ListInstructorSessionsTool;
use App\Ai\Tools\Instructor\MarkSessionAttendanceTool;
use App\Ai\Tools\Instructor\UpdateCourseSessionTool;
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
#[Temperature(0.2)]
#[MaxSteps(6)]
#[Timeout(45)]
class InstructorAssistant implements Agent, Conversational, HasTools, RemembersConversationsContract
{
    use Promptable;
    use RemembersConversations;

    public function __construct(public User $instructor, public array $allowedCourseIds)
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
You are the operational teaching assistant and co-pilot for Instructor {$this->instructor->name} at "think.er HUB".
Your mission is to help manage class sessions, register attendance, schedule lectures, and summarize cohort performance.

Operational Guidelines:
1. When the instructor mentions a session by title, course name, or relative timing (e.g. "today's class", "tomorrow's session", "next Web Dev lecture"), ALWAYS call `ListInstructorSessionsTool` first to find the exact `course_session_id`.
2. When marking attendance with `MarkSessionAttendanceTool`, specify `mark_all_status` for the general class status and pass any named absences, lates, or apologies in `individual_overrides`. Confirm the exact count of students marked present, late, absent, or excused in your reply.
3. When rescheduling or modifying a session with `UpdateCourseSessionTool`, confirm the updated date, start/end times, and delivery mode.
4. When creating a session with `CreateCourseSessionTool`, confirm that the roster was synced and report the new session ID.
5. When asked about class health, grade averages, or student progress, use `GetCohortPerformanceSummaryTool`.
6. Be concise, precise, and professional. Never take destructive action on courses outside your authorized scope.
7. Always format responses cleanly using Markdown: use bold text for key session names and numbers, bullet points for lists of students or courses, and concise headings (###) when providing operational summaries.
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
            new ListInstructorSessionsTool($this->instructor, $this->allowedCourseIds),
            new MarkSessionAttendanceTool($this->instructor, $this->allowedCourseIds),
            new UpdateCourseSessionTool($this->instructor, $this->allowedCourseIds),
            new CreateCourseSessionTool($this->instructor, $this->allowedCourseIds),
            new GetCohortPerformanceSummaryTool($this->instructor, $this->allowedCourseIds),
        ];
    }
}

