<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('content');
            $table->string('link')->nullable()->after('file_path');
            $table->string('video_url')->nullable()->after('link');
        });
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('content');
            $table->string('link')->nullable()->after('file_path');
            $table->string('video_url')->nullable()->after('link');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'link', 'video_url']);
        });
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'link', 'video_url']);
        });
    }
};
