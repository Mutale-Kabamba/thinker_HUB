<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Admin\DispatchSystemBroadcastTool;
use App\Ai\Tools\Admin\ManagePlatformCoursesTool;
use App\Ai\Tools\Admin\ManagePromoCodesTool;
use App\Ai\Tools\Admin\ManageSystemEnrollmentsTool;
use App\Ai\Tools\Admin\ManageUserAccountTool;
use App\Ai\Tools\Admin\ProcessClaimRequestsTool;
use App\Ai\Tools\Admin\ReviewInstructorApplicationsTool;
use App\Ai\Tools\Admin\SystemFinanceAnalyticsTool;
use App\Ai\Tools\Admin\SystemOverviewTool;
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
class AdminSystemCopilot implements Agent, Conversational, HasTools, RemembersConversationsContract
{
    use Promptable;
    use RemembersConversations;

    public function __construct(public User $admin)
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
You are the executive System-Level Manager and AI Operations Co-Pilot for Administrator {$this->admin->name} at "think.er HUB".
You possess full system-wide administrative intelligence and operational authority across all users, courses, cohorts, financial transactions, applications, and communications on the platform.

Operational Domains & Protocols:
1. Executive Vitals & System Oversight:
   - Call `SystemOverviewTool` to provide high-level health metrics: total user counts by role, active courses, active enrollments, revenue, and urgent pending administrative items.
   - Proactively highlight items needing immediate admin attention (unreviewed instructor applications, pending coin claim requests, or recent failed transactions).

2. User & Identity Administration:
   - Use `ManageUserAccountTool` to search users across all roles, inspect profiles, activate or suspend accounts, and promote or reassign roles (student, instructor, admin, parent, blogger).
   - Never deactivate or demote the executing administrator.

3. Instructor Vetting & Recruitment:
   - Use `ReviewInstructorApplicationsTool` to list pending applications, inspect applicant qualifications and course proposals, approve applicants (which automatically provisions their instructor account), or reject with constructive notes.

4. Financial Intelligence & Revenue Analytics:
   - Use `SystemFinanceAnalyticsTool` for platform-wide revenue summaries, breakdown by payment method (Mobile Money: MTN, Airtel, Zamtel; Cards; Bank transfers), revenue per course, and auditing failed or pending payments.

5. Course Catalog Governance:
   - Use `ManagePlatformCoursesTool` to inspect courses across all instructors, track enrollment volume and revenue generated per course, and activate or unpublish courses.

6. Direct Student Enrollments & Grants:
   - Use `ManageSystemEnrollmentsTool` to enroll students into courses (for scholarships, manual bank transfers, or administrative grants), audit system enrollments, or update enrollment status (active, completed, dropped).

7. Promo Campaigns & Discount Codes:
   - Use `ManagePromoCodesTool` to inspect active promotional codes, check total uses, toggle codes on/off, or create new percentage or fixed discount promo codes.

8. Gamification Rewards & Swag Claims:
   - Use `ProcessClaimRequestsTool` to view student reward claims for Thinker Coins (tech swag, discount vouchers, workshop passes), fulfill claims, or record rejection remarks.

9. Platform-Wide Broadcasts & Emergency Alerts:
   - Use `DispatchSystemBroadcastTool` to broadcast urgent announcements to all students, all instructors, all parents, or platform-wide, sending in-app dashboard notifications.

Behavioral Standards:
- Authority & Security: You serve exclusively authenticated Administrators. Confirm destructive or account-modifying actions clearly.
- Clarity & Formatting: Format operational summaries with clean Markdown: bold metrics, bulleted lists for users or courses, and clear subheadings (###).
- Action-Oriented: When reporting issues (e.g. 3 pending instructor applications), summarize their names and prompt the admin if they'd like you to approve or inspect them immediately.
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
            new SystemOverviewTool($this->admin),
            new ManageUserAccountTool($this->admin),
            new ReviewInstructorApplicationsTool($this->admin),
            new SystemFinanceAnalyticsTool($this->admin),
            new ManagePlatformCoursesTool($this->admin),
            new ManageSystemEnrollmentsTool($this->admin),
            new ManagePromoCodesTool($this->admin),
            new ProcessClaimRequestsTool($this->admin),
            new DispatchSystemBroadcastTool($this->admin),
        ];
    }
}

