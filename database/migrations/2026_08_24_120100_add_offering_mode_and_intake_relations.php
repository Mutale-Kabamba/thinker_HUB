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
        if (Schema::hasTable('courses') && ! Schema::hasColumn('courses', 'offering_mode')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('offering_mode')->default('once_off')->after('code'); // 'once_off' or 'ongoing'
            });
        }

        if (Schema::hasTable('enrollments') && ! Schema::hasColumn('enrollments', 'course_intake_id')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->foreignId('course_intake_id')->nullable()->after('course_id')->constrained('course_intakes')->nullOnDelete();
            });
        }

        if (Schema::hasTable('course_sessions') && ! Schema::hasColumn('course_sessions', 'course_intake_id')) {
            Schema::table('course_sessions', function (Blueprint $table) {
                $table->foreignId('course_intake_id')->nullable()->after('course_id')->constrained('course_intakes')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('course_sessions') && Schema::hasColumn('course_sessions', 'course_intake_id')) {
            Schema::table('course_sessions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('course_intake_id');
            });
        }

        if (Schema::hasTable('enrollments') && Schema::hasColumn('enrollments', 'course_intake_id')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('course_intake_id');
            });
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'offering_mode')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('offering_mode');
            });
        }
    }
};
