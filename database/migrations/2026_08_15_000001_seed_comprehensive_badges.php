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
                'key' => 'first_perfect_quiz',
                'name' => 'Perfectionist',
                'description' => 'Score 100% on a quiz, assessment, or assignment.',
                'icon' => '💯',
                'xp_reward' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'streak_7',
                'name' => 'On Fire',
                'description' => 'Be active on 7 consecutive days.',
                'icon' => '🔥',
                'xp_reward' => 75,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'streak_30',
                'name' => 'Unstoppable',
                'description' => 'Maintain an active study streak for 30 consecutive days.',
                'icon' => '⚡',
                'xp_reward' => 250,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'course_completed',
                'name' => 'Graduate',
                'description' => 'Complete your first course.',
                'icon' => '🎓',
                'xp_reward' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'punctual_scholar',
                'name' => 'Punctual Scholar',
                'description' => 'Submit 5 assignments or assessments before the deadline.',
                'icon' => '🎯',
                'xp_reward' => 75,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'early_bird',
                'name' => 'Early Bird',
                'description' => 'Submit 3 assignments at least 24 hours before the deadline.',
                'icon' => '🌅',
                'xp_reward' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'distinction_club',
                'name' => 'Distinction Club',
                'description' => 'Score 80% or higher on 3 graded assignments or assessments.',
                'icon' => '🌟',
                'xp_reward' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'mastermind',
                'name' => 'Mastermind',
                'description' => 'Successfully complete 3 full courses.',
                'icon' => '🏆',
                'xp_reward' => 300,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'always_present',
                'name' => 'Always Present',
                'description' => 'Achieve 100% verified attendance in a course.',
                'icon' => '📅',
                'xp_reward' => 150,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'innovator',
                'name' => 'Innovator',
                'description' => 'Submit a project, article, or opportunity on Thinker HUB.',
                'icon' => '💡',
                'xp_reward' => 75,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'study_networker',
                'name' => 'Study Networker',
                'description' => 'Connect with 5 study buddies in the community.',
                'icon' => '🤝',
                'xp_reward' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'active_contributor',
                'name' => 'Active Contributor',
                'description' => 'Send 25 or more messages in course study groups.',
                'icon' => '💬',
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
        // Badge definitions are retained.
    }
};
