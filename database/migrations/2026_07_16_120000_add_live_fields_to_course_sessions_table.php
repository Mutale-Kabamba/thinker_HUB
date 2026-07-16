<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_sessions', function (Blueprint $table): void {
            $table->string('live_provider')->default('jitsi')->after('status');
            $table->string('live_room_code')->nullable()->after('live_provider');
            $table->timestamp('live_started_at')->nullable()->after('live_room_code');
            $table->timestamp('live_ended_at')->nullable()->after('live_started_at');
            $table->json('live_metadata')->nullable()->after('live_ended_at');

            $table->index('live_room_code');
            $table->index('live_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('course_sessions', function (Blueprint $table): void {
            $table->dropIndex(['live_room_code']);
            $table->dropIndex(['live_started_at']);
            $table->dropColumn([
                'live_provider',
                'live_room_code',
                'live_started_at',
                'live_ended_at',
                'live_metadata',
            ]);
        });
    }
};
