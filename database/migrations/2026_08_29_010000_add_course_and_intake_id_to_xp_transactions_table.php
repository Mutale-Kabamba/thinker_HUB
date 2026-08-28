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
        Schema::table('xp_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('xp_transactions', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('user_id')->constrained('courses')->nullOnDelete();
            }
            if (! Schema::hasColumn('xp_transactions', 'course_intake_id')) {
                $table->foreignId('course_intake_id')->nullable()->after('course_id')->constrained('course_intakes')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xp_transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('xp_transactions', 'course_intake_id')) {
                $table->dropForeign(['course_intake_id']);
                $table->dropColumn('course_intake_id');
            }
            if (Schema::hasColumn('xp_transactions', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }
        });
    }
};
