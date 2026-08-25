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
        $tables = [
            'assignments',
            'assessments',
            'quizzes',
            'learning_materials',
            'resource_videos',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'course_intake_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('course_intake_id')
                        ->nullable()
                        ->after('course_id')
                        ->constrained('course_intakes')
                        ->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'assignments',
            'assessments',
            'quizzes',
            'learning_materials',
            'resource_videos',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'course_intake_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('course_intake_id');
                });
            }
        }
    }
};
