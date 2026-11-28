<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Strictly additive multi-tenant / global program scoping migration.
     * Existing records retain tenant_id = NULL and operate identically as global records.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('country_code', 10)->nullable()->index();
                $table->string('country_name', 120)->nullable();
                $table->string('region_label', 120)->nullable();
                $table->string('timezone', 64)->default('UTC');
                $table->string('currency', 10)->default('USD');
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        $tables = [
            'users',
            'courses',
            'course_intakes',
            'enrollments',
            'lesson_progress',
            'assessments',
            'assessment_submissions',
            'quizzes',
            'quiz_attempts',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('tenant_id')
                        ->nullable()
                        ->constrained('tenants')
                        ->nullOnDelete();
                    $table->index('tenant_id');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'quiz_attempts',
            'quizzes',
            'assessment_submissions',
            'assessments',
            'lesson_progress',
            'enrollments',
            'course_intakes',
            'courses',
            'users',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('tenant_id');
                });
            }
        }

        Schema::dropIfExists('tenants');
    }
};
