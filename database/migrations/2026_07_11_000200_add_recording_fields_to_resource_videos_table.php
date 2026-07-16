<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_videos', function (Blueprint $table) {
            $table->boolean('is_recorded_lesson')->default(false)->after('youtube_url');
            $table->foreignId('course_id')->nullable()->after('is_recorded_lesson')->constrained()->nullOnDelete();
            $table->string('target_level')->nullable()->after('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('resource_videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
            $table->dropColumn(['is_recorded_lesson', 'target_level']);
        });
    }
};
