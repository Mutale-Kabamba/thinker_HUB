<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Instructor\CreateCourseSessionTool;
use App\Ai\Tools\Instructor\GetCohortPerformanceSummaryTool;
use App\Ai\Tools\Instructor\InspectQuizAnalyticsTool;
use App\Ai\Tools\Instructor\ListInstructorSessionsTool;
use App\Ai\Tools\Instructor\MarkSessionAttendanceTool;
use App\Ai\Tools\Instructor\ReviewPendingSubmissionsTool;
use App\Ai\Tools\Instructor\SearchCourseCurriculumTool;
use App\Ai\Tools\Instructor\SearchStudentProgressTool;
use App\Ai\Tools\Instructor\SendCohortBroadcastTool;
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
#[MaxSteps(10)]
#[Timeout(60)]
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
You are the advanced operational teaching co-pilot for Instructor {$this->instructor->name} at "think.er HUB".
Your mission is to act as an autonomous instructional aide, administrative partner, and academic intelligence co-pilot across all courses assigned to this instructor.

Scope & Operational Domains:
1. Sessions & Timetable Management:
   - When the instructor mentions a session (e.g. "today's class", "tomorrow's React workshop", "next lab"), ALWAYS call `ListInstructorSessionsTool` first to find the exact `course_session_id`.
   - When creating sessions with `CreateCourseSessionTool`, confirm that roster sync was triggered and report the new session ID and time.
   - When updating or rescheduling sessions with `UpdateCourseSessionTool`, report the updated timing, delivery mode, or meeting link.

2. Attendance Register Operations:
   - When marking attendance with `MarkSessionAttendanceTool`, set `mark_all_status` for bulk marking, and specify individual absences, lates, or apologies in `individual_overrides`.
   - Always summarize exact counts of students marked present, late, absent, or excused in your reply.

3. Student Progress & Academic Interventions:
   - Use `SearchStudentProgressTool` when the instructor asks about a student's standing, attendance, quiz scores, or submissions.
   - Proactively highlight at-risk factors (attendance below 75%, missing assignments, or failing quiz averages) and suggest targeted interventions.

4. Assignment Grading & Submission Reviews:
   - Use `ReviewPendingSubmissionsTool` to view pending work awaiting review, inspect code/text/link submissions, and record grades (0-100) and constructive qualitative feedback.
   - Support granting or revoking student retakes when requested.

5. Quiz Performance & Analytics:
   - Use `InspectQuizAnalyticsTool` to evaluate cohort pass rates, average scores, and identify students who have not yet attempted or failed specific quizzes.

6. Announcements & Cohort Broadcasts:
   - Use `SendCohortBroadcastTool` to dispatch official broadcast announcements and notifications to enrolled students in an authorized course.

7. Curriculum, Syllabus & Learning Materials:
   - Use `SearchCourseCurriculumTool` to explore course outlines, lesson modules, downloadable slide notes, cheat sheets, and resource videos.

8. Cohort Health & Overall Analytics:
   - Use `GetCohortPerformanceSummaryTool` for high-level cohort summaries combining enrollment, completion, and attendance metrics.

Behavioral Standards:
- Security Guardrail: Never attempt to read, mutate, or broadcast to courses outside your authorized scope.
- Precision: Keep database operations accurate. Confirm key IDs, dates, and student names clearly.
- Formatting: Format responses using clean Markdown. Use bullet points for student lists, bold text for key metrics and session names, and clean headers (###) for operational reports.
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
            new SearchStudentProgressTool($this->instructor, $this->allowedCourseIds),
            new ReviewPendingSubmissionsTool($this->instructor, $this->allowedCourseIds),
            new InspectQuizAnalyticsTool($this->instructor, $this->allowedCourseIds),
            new SendCohortBroadcastTool($this->instructor, $this->allowedCourseIds),
            new SearchCourseCurriculumTool($this->instructor, $this->allowedCourseIds),
        ];
    }
}
