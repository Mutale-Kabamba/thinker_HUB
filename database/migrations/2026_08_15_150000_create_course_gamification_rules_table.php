<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_gamification_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->nullable()->unique()->constrained('courses')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->json('rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_gamification_rules');
    }
};
