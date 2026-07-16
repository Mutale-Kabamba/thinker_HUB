<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('group'); // group | one_on_one
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('scheduled'); // scheduled | completed | rescheduled | cancelled
            $table->date('rescheduled_date')->nullable();
            $table->time('rescheduled_start_time')->nullable();
            $table->time('rescheduled_end_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'session_date']);
            $table->index(['student_id', 'session_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sessions');
    }
};
