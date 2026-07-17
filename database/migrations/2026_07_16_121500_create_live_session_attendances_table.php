<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('live_session_attendances');
    }
};
