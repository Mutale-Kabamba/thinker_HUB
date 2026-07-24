<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Badge definitions are seeded here (not in a seeder) so a single
     * migrate command leaves the gamification layer fully usable;
     * insertOrIgnore keeps re-runs and shared databases safe.
     */
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description');
            $table->string('icon')->nullable();
            $table->unsignedInteger('xp_reward')->default(0);
            $table->timestamps();
        });

        Schema::create('user_badge', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'badge_id']);
        });

        Schema::create('xp_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('source');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index('user_id');
            // Idempotency key: one award per user per source per source object.
            $table->unique(['user_id', 'source', 'source_id']);
        });

        $now = now();

        DB::table('badges')->insertOrIgnore([
            [
                'key' => 'first_perfect_quiz',
                'name' => 'Perfectionist',
                'description' => 'Score 100% on a quiz for the first time.',
                'icon' => '💯',
                'xp_reward' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'streak_7',
                'name' => 'On Fire',
                'description' => 'Be active on 7 consecutive days.',
                'icon' => '🔥',
                'xp_reward' => 150,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'course_completed',
                'name' => 'Graduate',
                'description' => 'Complete your first course.',
                'icon' => '🎓',
                'xp_reward' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xp_transactions');
        Schema::dropIfExists('user_badge');
        Schema::dropIfExists('badges');
    }
};
