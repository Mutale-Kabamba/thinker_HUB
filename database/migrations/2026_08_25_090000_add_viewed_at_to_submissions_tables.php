<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                if (! Schema::hasColumn('assignment_submissions', 'viewed_at')) {
                    $table->timestamp('viewed_at')->nullable()->after('submitted_at');
                }
            });
        }

        if (Schema::hasTable('assessment_submissions')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                if (! Schema::hasColumn('assessment_submissions', 'viewed_at')) {
                    $table->timestamp('viewed_at')->nullable()->after('submitted_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                if (Schema::hasColumn('assignment_submissions', 'viewed_at')) {
                    $table->dropColumn('viewed_at');
                }
            });
        }

        if (Schema::hasTable('assessment_submissions')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                if (Schema::hasColumn('assessment_submissions', 'viewed_at')) {
                    $table->dropColumn('viewed_at');
                }
            });
        }
    }
};
