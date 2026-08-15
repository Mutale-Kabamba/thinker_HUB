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
        Schema::table('courses', function (Blueprint $table): void {
            if (! Schema::hasColumn('courses', 'gamification_settings')) {
                $table->json('gamification_settings')->nullable()->after('is_open_enrollment');
            }
        });

        Schema::table('claim_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('claim_items', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('id')->constrained('courses')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_items', function (Blueprint $table): void {
            if (Schema::hasColumn('claim_items', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }
        });

        Schema::table('courses', function (Blueprint $table): void {
            if (Schema::hasColumn('courses', 'gamification_settings')) {
                $table->dropColumn('gamification_settings');
            }
        });
    }
};
