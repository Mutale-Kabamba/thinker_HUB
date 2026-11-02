<?php

namespace App\Ai\Tools\Public;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetPlatformInfoTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get general information about think.er HUB, enrollment procedure, platform delivery modes, and contact options.';
        return 'Get comprehensive knowledge about think.er HUB: platform mission, learning tracks, all 5 portals (Student, Instructor, Parent, Contributor, Admin), certifications, career services, gamification economy, tuition and payment options, delivery formats, and admissions FAQs.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
        return [
            'topic' => $schema->string()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return json_encode([
            'platform' => 'think.er HUB',
            'mission' => 'Empowering modern tech education through cohort-based interactive learning, hands-on coding, and gamified mastery.',
            'enrollment_steps' => [
                '1. Create a free Student account on think.er HUB (/register).',
                '2. Browse available courses in the course catalog and review requirements.',
                '3. Choose your desired cohort intake or select open enrollment.',
                '4. Complete enrollment (or tuition payment where applicable).',
                '5. Access live classes, interactive quizzes, study materials, and community forums via the Student Portal (/learn).',
        $topic = strtolower(trim((string) ($request['topic'] ?? '')));

        $info = [
            'platform' => [
                'name' => 'think.er HUB',
                'tagline' => 'Empowering Modern Tech Leaders & Innovators',
                'mission' => 'Bridging the global tech skills gap through immersive, project-driven learning, cohort collaboration, AI-guided mentorship, and verified career credentials.',
                'campus_location' => 'Lusaka, Zambia (Global Online & Hybrid Access)',
                'contact_email' => 'support@thinkerhub.net',
            ],
            'supported_delivery_modes' => [
                'Live Virtual' => 'Interactive online Zoom / Google Meet classroom sessions with instructor feedback.',
                'Live Physical' => 'On-site collaborative campus workshops and in-person lab sessions.',
                'Video Guide' => 'High-yield recorded walk-throughs, tutorials, and practical code-alongs.',
                'Reading Material' => 'Curated lecture notes, cheat sheets, and documentation kits.',
            'core_portals' => [
                'Student Portal (/learn)' => [
                    'audience' => 'Active learners, career switchers, and young coders.',
                    'features' => 'Enrolled course tracking, lesson modules, downloadable materials & cheatsheets, 24/7 AI Study Buddy, live video classes, assignments with revision retakes, automated quizzes, certificates, and student community channels.',
                ],
                'Instructor Portal (/teach)' => [
                    'audience' => 'Industry practitioners, lecturers, and teaching assistants.',
                    'features' => 'Assigned course oversight, AI Teaching Co-Pilot for conversational operations, digital attendance registers, roster sync, assignment & assessment grading, cohort broadcasts, and analytics.',
                ],
                'Parent Portal (/parent)' => [
                    'audience' => 'Parents and guardians of enrolled youth students.',
                    'features' => 'Real-time attendance tracking, assignment submission monitoring, quiz scorecards, milestone achievements, and direct communication with course instructors.',
                ],
                'Contributor Studio (/contribute)' => [
                    'audience' => 'Technical writers, developers, and researchers.',
                    'features' => 'Drafting, formatting, and publishing community articles, programming tips, code tutorials, and tech guides on the Thinker Hub (/hub).',
                ],
                'Admin Portal (/manage)' => [
                    'audience' => 'Platform directors, admissions officers, and finance managers.',
                    'features' => 'User and role management, course catalog creation, intake cohort scheduling, payment reconciliation, accounting dashboard, and institutional reporting.',
                ],
            ],
            'support_and_contact' => [
                'email' => 'support@thinkerhub.net',
                'location' => 'Lusaka, Zambia (Global Online)',
                'portals' => [
                    'public' => '/',
                    'student_portal' => '/learn',
                    'instructor_portal' => '/teach',
            'learning_tracks' => [
                'Full-Stack Web Development' => 'HTML5/CSS3, JavaScript (ES6+), PHP & Laravel, React, relational databases, REST APIs, and deployment.',
                'Mobile App Engineering' => 'Cross-platform mobile application development using Flutter, Dart, and native Android integrations.',
                'Data Science & AI' => 'Python data analysis, machine learning fundamentals, automated workflows, and practical AI tooling.',
                'Kids & Teens Coding' => 'Gamified visual programming with Scratch, foundational Python, logical thinking, game development, and digital citizenship.',
                'Cloud & DevOps' => 'Version control with Git/GitHub, CI/CD pipelines, containerization, and modern server hosting.',
            ],
            'certifications_and_credentials' => [
                'Certificate of Completion' => 'Earned upon completing curriculum milestones, passing required evaluations, and submitting a capstone project.',
                'Verification' => 'Every certificate comes with a unique tamper-proof credential ID verifiable online by employers and academic institutions.',
            ],
            'delivery_modes' => [
                'Live Virtual' => 'Interactive online video classroom sessions with live screen-sharing, live Q&A, and instructor feedback.',
                'Live Physical' => 'In-person lab workshops, collaborative cohort sessions, and hardware hackathons at the Lusaka campus.',
                'Video Guides' => 'High-yield on-demand video walk-throughs and code-along recordings accessible 24/7 in the Student Portal.',
                'Reading Materials' => 'Curated lecture slides, downloadable PDF study notes, and concise syntax cheatsheets.',
            ],
            'gamification_and_rewards' => [
                'Learning Economy' => 'Students earn XP points and Thinker Coins by maintaining attendance streaks, scoring high on quizzes, and submitting coursework.',
                'Claim Hub' => 'Redeem accumulated coins for tech merchandise, swag, discount vouchers, and bonus workshop access.',
                'Leaderboards' => 'Friendly cohort leaderboards celebrating top active learners and milestone champions.',
            ],
            'tuition_and_payments' => [
                'Transparent Fees' => 'Clear upfront course fees with no hidden subscription charges.',
                'Flexible Installments' => 'Split tuition payments across milestones or cohort terms.',
                'Supported Payment Methods' => 'Mobile Money (MTN, Airtel, Zamtel), Credit/Debit Cards (Visa, Mastercard), Bank Electronic Transfers, and Cash/POS at campus.',
                'Promo Codes & Scholarships' => 'Seasonal promo codes and merit-based scholarship opportunities announced via the platform.',
            ],
            'admissions_and_onboarding' => [
                'Prerequisites' => 'No prior coding experience is required for beginner courses. All that is needed is a working laptop, internet connection, and motivation to learn.',
                'Enrollment Steps' => [
                    '1. Create a free account at /register.',
                    '2. Browse the course catalog and select your program.',
                    '3. Choose your preferred cohort intake or start with open enrollment.',
                    '4. Complete tuition payment or apply an approved promo code.',
                    '5. Log in to the Student Portal (/learn) to access your timetable, materials, and meet your cohort.',
                ],
                'Becoming an Instructor' => 'Experienced tech professionals can apply at /instructors/apply or contact admissions at support@thinkerhub.net.',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        ];

        if ($topic !== '' && isset($info[$topic])) {
            return json_encode([$topic => $info[$topic]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

