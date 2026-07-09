<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add course_id column to quizzes
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('course_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // 2. Backfill course_id from existing assessment relationship
        DB::table('quizzes')
            ->whereNotNull('assessment_id')
            ->update([
                'course_id' => DB::raw(
                    '(SELECT course_id FROM assessments WHERE assessments.id = quizzes.assessment_id)'
                ),
            ]);

        // 3. Drop the old assessment_id foreign key and column
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['assessment_id']);
            $table->dropColumn('assessment_id');
        });

        // 4. Make course_id NOT NULL now that data is backfilled
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('assessment_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });
    }
};
