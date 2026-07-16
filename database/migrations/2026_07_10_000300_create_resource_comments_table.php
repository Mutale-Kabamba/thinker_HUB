<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('resource_comments')) {
            // Recover gracefully if a prior deploy created the table but failed before adding this index.
            if (! $this->indexExists('resource_comments', 'rc_comments_cmp_parent_idx')) {
                Schema::table('resource_comments', function (Blueprint $table) {
                    $table->index(['commentable_type', 'commentable_id', 'parent_id'], 'rc_comments_cmp_parent_idx');
                });
            }

            return;
        }

        Schema::create('resource_comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('resource_comments')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id', 'parent_id'], 'rc_comments_cmp_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_comments');
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
