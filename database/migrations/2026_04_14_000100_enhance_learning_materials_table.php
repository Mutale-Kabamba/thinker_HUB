<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->string('category')->default('General Notices')->after('title');
            $table->text('description')->nullable()->after('category');
            $table->string('file_path')->nullable()->after('file_name');
            $table->string('video_url')->nullable()->after('link_url');
        });
    }

    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'file_path', 'video_url']);
        });
    }
};
