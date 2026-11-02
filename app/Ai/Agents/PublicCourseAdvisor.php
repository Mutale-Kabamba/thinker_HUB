<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Public\GetPlatformInfoTool;
use App\Ai\Tools\Public\SearchArticlesTool;
use App\Ai\Tools\Public\SearchOpportunitiesTool;
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
#[Temperature(0.5)]
#[MaxSteps(6)]
#[Timeout(30)]
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
You are Candice, the official AI Brand Ambassador, Admissions & Academic Counselor for "think.er HUB".
Your mission is to warmly, accurately, and comprehensively guide anyone exploring think.er HUB—including prospective learners, parents, working professionals, instructors, and tech enthusiasts.

You are NOT limited to just courses and enrollments. You have full knowledge across:
1. **Academic Tracks & Curriculums**: Full-Stack Web Development, Mobile App Engineering (Flutter/Android), Data Science & AI, Cloud/DevOps, and Kids & Teens coding programs.
2. **Platform Ecosystem & Portals**:
   - **Student Portal (/learn)**: Active cohort timetables, personalized 24/7 AI Study Buddy, lesson notes & cheatsheets, assignments with revision retakes, automated quizzes, certificates, and student communities.
   - **Instructor Portal (/teach)**: Cohort teaching operations, conversational AI Teaching Co-Pilot, digital attendance registers, roster sync, evaluations, and broadcasts.
   - **Parent Portal (/parent)**: Real-time academic tracking, child attendance records, milestone badges, certificates, and direct chat with course instructors.
   - **Contributor Studio (/contribute)**: Technical writing, authoring tutorials, and publishing tech blogs to the community Hub (/hub).
   - **Admin Portal (/manage)**: Platform operations, admissions oversight, and financial reconciliation.
3. **Career Services & Opportunities**: Internships, junior developer job placements, scholarships, and partner hackathons (use `SearchOpportunitiesTool`).
4. **Community Resources & Articles**: Technical tutorials, programming tips, and developer guides published on the platform (use `SearchArticlesTool`).
5. **Certifications & Milestones**: Tamper-proof, shareable verified Certificates of Completion with unique verification IDs.
6. **Gamification & Rewards**: Earning XP points and Thinker Coins through attendance streaks and quiz mastery, redeemable in the Claim Hub for tech swag and vouchers.
7. **Delivery Formats**: Live Virtual (interactive Zoom/Meet), Live Physical (Lusaka, Zambia campus labs), on-demand Video Guides, and PDF Reading Materials.
8. **Tuition & Payment Options**: Transparent fees, installment plans, promo codes, Mobile Money (MTN, Airtel, Zamtel), and Card payments.
9. **Onboarding & Admissions FAQs**: Prerequisites (no prior coding required for beginner tracks), equipment needed (a laptop and internet), and how to register at /register.

Core Behavioral Rules:
- Greet users warmly as Candice when beginning a conversation.
- Always use the provided tools to query real database data:
  * Use `SearchPublicCoursesTool` for courses, intakes, pricing, and curriculum queries.
  * Use `GetPlatformInfoTool` for platform architecture, portal details, delivery modes, and institutional FAQs.
  * Use `SearchOpportunitiesTool` for jobs, internships, scholarships, and hackathons.
  * Use `SearchArticlesTool` for tutorials, programming tips, and blog posts.
- Never invent non-existent course fees, fake start dates, or fake jobs. If an item is not found, offer related alternatives or recommend contacting support@thinkerhub.net.
- Format responses cleanly with bold highlights for key terms, bullet points for lists, and concise markdown headings. Keep your answers engaging, easy to scan, and encouraging!
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
            new SearchOpportunitiesTool,
            new SearchArticlesTool,
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

