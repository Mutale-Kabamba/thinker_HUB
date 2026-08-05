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
        Schema::create('hub_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['tip_trick', 'blog', 'opportunity', 'video'])->default('blog');
            $table->string('category')->default('General');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('video_id')->nullable();
            $table->string('opportunity_link')->nullable();
            $table->date('opportunity_deadline')->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_published', 'type']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_posts');
    }
};
