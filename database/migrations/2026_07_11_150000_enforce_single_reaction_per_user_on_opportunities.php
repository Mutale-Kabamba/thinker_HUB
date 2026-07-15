<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('opportunity_reactions')) {
            return;
        }

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

        // Create the replacement unique index before dropping the old one,
        // so MySQL foreign keys still have a supporting index.
        if (! $this->indexExists('opportunity_reactions', 'opp_user_single_reaction_unique')) {
            Schema::table('opportunity_reactions', function (Blueprint $table): void {
                $table->unique(['opportunity_id', 'user_id'], 'opp_user_single_reaction_unique');
            });
        }

        if ($this->indexExists('opportunity_reactions', 'opp_user_emoji_unique')) {
            Schema::table('opportunity_reactions', function (Blueprint $table): void {
                $table->dropUnique('opp_user_emoji_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('opportunity_reactions')) {
            return;
        }

        if (! $this->indexExists('opportunity_reactions', 'opp_user_emoji_unique')) {
            Schema::table('opportunity_reactions', function (Blueprint $table): void {
                $table->unique(['opportunity_id', 'user_id', 'emoji'], 'opp_user_emoji_unique');
            });
        }

        if ($this->indexExists('opportunity_reactions', 'opp_user_single_reaction_unique')) {
            Schema::table('opportunity_reactions', function (Blueprint $table): void {
                $table->dropUnique('opp_user_single_reaction_unique');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();

            $rows = DB::select(
                'select 1 from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
                [$database, $table, $index]
            );

            return ! empty($rows);
        }

        return false;
    }
};
