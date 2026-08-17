<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Review author

            // Polymorphic Columns (Nullable for General Platform Reviews)
            $table->nullableMorphs('reviewable');

            $table->unsignedTinyInteger('rating'); // 1 to 5 stars
            $table->string('title')->nullable();
            $table->text('comment')->nullable();

            $table->boolean('is_approved')->default(true);
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_verified')->default(false); // e.g. Student enrolled in course

            $table->timestamps();

            // Indexes for fast lookup
            $table->index(['reviewable_type', 'reviewable_id', 'is_approved']);
            $table->unique(['user_id', 'reviewable_type', 'reviewable_id'], 'unique_user_review');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
