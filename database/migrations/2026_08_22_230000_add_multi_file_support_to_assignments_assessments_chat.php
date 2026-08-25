<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            if (! Schema::hasColumn('assignments', 'file_paths')) {
                $table->json('file_paths')->nullable()->after('file_path');
            }
        });

        Schema::table('assessments', function (Blueprint $table): void {
            if (! Schema::hasColumn('assessments', 'file_paths')) {
                $table->json('file_paths')->nullable()->after('file_path');
            }
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('assignment_submissions', 'file_paths')) {
                $table->json('file_paths')->nullable()->after('file_path');
            }
        });

        Schema::table('assessment_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('assessment_submissions', 'file_paths')) {
                $table->json('file_paths')->nullable()->after('file_path');
            }
        });

        Schema::table('chat_messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('chat_messages', 'attachments')) {
                $table->json('attachments')->nullable()->after('attachment_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            if (Schema::hasColumn('assignments', 'file_paths')) {
                $table->dropColumn('file_paths');
            }
        });

        Schema::table('assessments', function (Blueprint $table): void {
            if (Schema::hasColumn('assessments', 'file_paths')) {
                $table->dropColumn('file_paths');
            }
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            if (Schema::hasColumn('assignment_submissions', 'file_paths')) {
                $table->dropColumn('file_paths');
            }
        });

        Schema::table('assessment_submissions', function (Blueprint $table): void {
            if (Schema::hasColumn('assessment_submissions', 'file_paths')) {
                $table->dropColumn('file_paths');
            }
        });

        Schema::table('chat_messages', function (Blueprint $table): void {
            if (Schema::hasColumn('chat_messages', 'attachments')) {
                $table->dropColumn('attachments');
            }
        });
    }
};
