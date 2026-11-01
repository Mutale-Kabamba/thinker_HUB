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
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
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
            ],
            'supported_delivery_modes' => [
                'Live Virtual' => 'Interactive online Zoom / Google Meet classroom sessions with instructor feedback.',
                'Live Physical' => 'On-site collaborative campus workshops and in-person lab sessions.',
                'Video Guide' => 'High-yield recorded walk-throughs, tutorials, and practical code-alongs.',
                'Reading Material' => 'Curated lecture notes, cheat sheets, and documentation kits.',
            ],
            'support_and_contact' => [
                'email' => 'support@thinkerhub.net',
                'location' => 'Lusaka, Zambia (Global Online)',
                'portals' => [
                    'public' => '/',
                    'student_portal' => '/learn',
                    'instructor_portal' => '/teach',
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

