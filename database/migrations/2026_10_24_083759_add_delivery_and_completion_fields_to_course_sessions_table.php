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
        Schema::table('course_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('course_sessions', 'delivery_mode')) {
                $table->string('delivery_mode')->default('live_virtual')->after('type'); // 'live_virtual', 'live_physical', 'video', 'read'
            }
            if (! Schema::hasColumn('course_sessions', 'meeting_link')) {
                $table->text('meeting_link')->nullable()->after('delivery_mode');
            }
            if (! Schema::hasColumn('course_sessions', 'physical_address')) {
                $table->text('physical_address')->nullable()->after('meeting_link');
            }
            if (! Schema::hasColumn('course_sessions', 'video_url')) {
                $table->text('video_url')->nullable()->after('physical_address');
            }
            if (! Schema::hasColumn('course_sessions', 'video_file_path')) {
                $table->string('video_file_path')->nullable()->after('video_url');
            }
            if (! Schema::hasColumn('course_sessions', 'reading_file_path')) {
                $table->string('reading_file_path')->nullable()->after('video_file_path');
            }
            if (! Schema::hasColumn('course_sessions', 'reading_content')) {
                $table->longText('reading_content')->nullable()->after('reading_file_path');
            }
            if (! Schema::hasColumn('course_sessions', 'required_duration_minutes')) {
                $table->unsignedInteger('required_duration_minutes')->nullable()->after('reading_content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_sessions', function (Blueprint $table) {
            $cols = [
                'delivery_mode',
                'meeting_link',
                'physical_address',
                'video_url',
                'video_file_path',
                'reading_file_path',
                'reading_content',
                'required_duration_minutes',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('course_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
