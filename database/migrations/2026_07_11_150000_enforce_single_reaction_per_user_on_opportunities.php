<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deduplicate legacy multi-emoji reactions per user/opportunity pair,
        // keeping the most recent reaction.
        $pairs = DB::table('opportunity_reactions')
            ->select('opportunity_id', 'user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('opportunity_id', 'user_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($pairs as $pair) {
            $keepId = DB::table('opportunity_reactions')
                ->where('opportunity_id', $pair->opportunity_id)
                ->where('user_id', $pair->user_id)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('id');

            DB::table('opportunity_reactions')
                ->where('opportunity_id', $pair->opportunity_id)
                ->where('user_id', $pair->user_id)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('opportunity_reactions', function (Blueprint $table): void {
            $table->dropUnique('opp_user_emoji_unique');
            $table->unique(['opportunity_id', 'user_id'], 'opp_user_single_reaction_unique');
        });
    }

    public function down(): void
    {
        Schema::table('opportunity_reactions', function (Blueprint $table): void {
            $table->dropUnique('opp_user_single_reaction_unique');
            $table->unique(['opportunity_id', 'user_id', 'emoji'], 'opp_user_emoji_unique');
        });
    }
};
