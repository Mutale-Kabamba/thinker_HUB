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
        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                if (! Schema::hasColumn('quiz_attempts', 'is_retake')) {
                    $table->boolean('is_retake')->default(false)->after('passed');
                }
                if (! Schema::hasColumn('quiz_attempts', 'retake_allowed')) {
                    $table->boolean('retake_allowed')->default(false)->after('is_retake');
                }
                if (! Schema::hasColumn('quiz_attempts', 'retake_granted_at')) {
                    $table->dateTime('retake_granted_at')->nullable()->after('retake_allowed');
                }
                if (! Schema::hasColumn('quiz_attempts', 'retake_granted_by')) {
                    $table->foreignId('retake_granted_by')->nullable()->constrained('users')->nullOnDelete()->after('retake_granted_at');
                }
                if (! Schema::hasColumn('quiz_attempts', 'raw_score')) {
                    $table->integer('raw_score')->nullable()->after('retake_granted_by');
                }
            });
        }

        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                if (! Schema::hasColumn('assignment_submissions', 'is_retake')) {
                    $table->boolean('is_retake')->default(false)->after('status');
                }
                if (! Schema::hasColumn('assignment_submissions', 'retake_allowed')) {
                    $table->boolean('retake_allowed')->default(false)->after('is_retake');
                }
                if (! Schema::hasColumn('assignment_submissions', 'retake_granted_at')) {
                    $table->dateTime('retake_granted_at')->nullable()->after('retake_allowed');
                }
                if (! Schema::hasColumn('assignment_submissions', 'retake_granted_by')) {
                    $table->foreignId('retake_granted_by')->nullable()->constrained('users')->nullOnDelete()->after('retake_granted_at');
                }
                if (! Schema::hasColumn('assignment_submissions', 'raw_grade')) {
                    $table->string('raw_grade')->nullable()->after('retake_granted_by');
                }
            });
        }

        if (Schema::hasTable('assessment_submissions')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                if (! Schema::hasColumn('assessment_submissions', 'is_retake')) {
                    $table->boolean('is_retake')->default(false)->after('status');
                }
                if (! Schema::hasColumn('assessment_submissions', 'retake_allowed')) {
                    $table->boolean('retake_allowed')->default(false)->after('is_retake');
                }
                if (! Schema::hasColumn('assessment_submissions', 'retake_granted_at')) {
                    $table->dateTime('retake_granted_at')->nullable()->after('retake_allowed');
                }
                if (! Schema::hasColumn('assessment_submissions', 'retake_granted_by')) {
                    $table->foreignId('retake_granted_by')->nullable()->constrained('users')->nullOnDelete()->after('retake_granted_at');
                }
                if (! Schema::hasColumn('assessment_submissions', 'raw_score')) {
                    $table->string('raw_score')->nullable()->after('retake_granted_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('retake_granted_by');
                $table->dropColumn(['is_retake', 'retake_allowed', 'retake_granted_at', 'raw_score']);
            });
        }

        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('retake_granted_by');
                $table->dropColumn(['is_retake', 'retake_allowed', 'retake_granted_at', 'raw_grade']);
            });
        }

        if (Schema::hasTable('assessment_submissions')) {
            Schema::table('assessment_submissions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('retake_granted_by');
                $table->dropColumn(['is_retake', 'retake_allowed', 'retake_granted_at', 'raw_score']);
            });
        }
    }
};
