<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_session_attendances')) {
            Schema::drop('live_session_attendances');
        }

        if (! Schema::hasTable('course_sessions')) {
            return;
        }

        Schema::table('course_sessions', function (Blueprint $table): void {
            if (Schema::hasColumn('course_sessions', 'live_room_code')) {
                $table->dropIndex(['live_room_code']);
            }

            if (Schema::hasColumn('course_sessions', 'live_started_at')) {
                $table->dropIndex(['live_started_at']);
            }

            $columnsToDrop = [];

            foreach (['live_provider', 'live_room_code', 'live_started_at', 'live_ended_at', 'live_metadata'] as $column) {
                if (Schema::hasColumn('course_sessions', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('course_sessions')) {
            Schema::table('course_sessions', function (Blueprint $table): void {
                if (! Schema::hasColumn('course_sessions', 'live_provider')) {
                    $table->string('live_provider')->default('jitsi')->after('status');
                }

                if (! Schema::hasColumn('course_sessions', 'live_room_code')) {
                    $table->string('live_room_code')->nullable()->after('live_provider');
                }

                if (! Schema::hasColumn('course_sessions', 'live_started_at')) {
                    $table->timestamp('live_started_at')->nullable()->after('live_room_code');
                }

                if (! Schema::hasColumn('course_sessions', 'live_ended_at')) {
                    $table->timestamp('live_ended_at')->nullable()->after('live_started_at');
                }

                if (! Schema::hasColumn('course_sessions', 'live_metadata')) {
                    $table->json('live_metadata')->nullable()->after('live_ended_at');
                }

                if (Schema::hasColumn('course_sessions', 'live_room_code')) {
                    $table->index('live_room_code');
                }

                if (Schema::hasColumn('course_sessions', 'live_started_at')) {
                    $table->index('live_started_at');
                }
            });
        }

        if (! Schema::hasTable('live_session_attendances')) {
            Schema::create('live_session_attendances', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('course_session_id')->constrained('course_sessions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->nullable();
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('left_at')->nullable();
                $table->timestamp('last_heartbeat_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['course_session_id', 'user_id']);
                $table->index(['course_session_id', 'left_at']);
            });
        }
    }
};
