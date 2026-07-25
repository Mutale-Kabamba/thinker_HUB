<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assignments') && ! Schema::hasColumn('assignments', 'publish_at')) {
            Schema::table('assignments', function (Blueprint $table): void {
                $table->timestamp('publish_at')->nullable()->after('date_given');
            });
        }

        if (Schema::hasTable('assessments') && ! Schema::hasColumn('assessments', 'publish_at')) {
            Schema::table('assessments', function (Blueprint $table): void {
                $table->timestamp('publish_at')->nullable()->after('date_given');
            });
        }

        if (Schema::hasTable('quizzes') && ! Schema::hasColumn('quizzes', 'publish_at')) {
            Schema::table('quizzes', function (Blueprint $table): void {
                $table->timestamp('publish_at')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'publish_at')) {
            Schema::table('assignments', function (Blueprint $table): void {
                $table->dropColumn('publish_at');
            });
        }

        if (Schema::hasTable('assessments') && Schema::hasColumn('assessments', 'publish_at')) {
            Schema::table('assessments', function (Blueprint $table): void {
                $table->dropColumn('publish_at');
            });
        }

        if (Schema::hasTable('quizzes') && Schema::hasColumn('quizzes', 'publish_at')) {
            Schema::table('quizzes', function (Blueprint $table): void {
                $table->dropColumn('publish_at');
            });
        }
    }
};
