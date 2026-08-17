<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $badges = [
            [
                'key' => 'outstanding_presentation',
                'name' => 'Presentation Star',
                'description' => 'Delivered an outstanding classroom presentation or project pitch.',
                'icon' => '🎤',
                'xp_reward' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'hackathon_winner',
                'name' => 'Hackathon Champion',
                'description' => 'Excelled in a hackathon, coding challenge, or project competition.',
                'icon' => '🚀',
                'xp_reward' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'leadership_award',
                'name' => 'Team Leader',
                'description' => 'Demonstrated exemplary leadership and collaboration in class projects.',
                'icon' => '👑',
                'xp_reward' => 75,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'practical_excellence',
                'name' => 'Practical Master',
                'description' => 'Demonstrated exceptional hands-on skills in lab or workshop sessions.',
                'icon' => '⚙️',
                'xp_reward' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'instructor_star',
                'name' => "Instructor's Choice",
                'description' => 'Special commendation awarded by an instructor for off-platform excellence.',
                'icon' => '⭐',
                'xp_reward' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($badges as $badge) {
            DB::table('badges')->updateOrInsert(
                ['key' => $badge['key']],
                $badge
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Badges retained.
    }
};
