<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            if (! Schema::hasColumn('assignments', 'target_level')) {
                $table->string('target_level')->nullable()->after('target_track');
            }

            if (! Schema::hasColumn('assignments', 'date_given')) {
                $table->date('date_given')->nullable()->after('target_user_id');
            }

            if (! Schema::hasColumn('assignments', 'file_path')) {
                $table->string('file_path')->nullable()->after('description');
            }
        });

        Schema::table('assessments', function (Blueprint $table): void {
            if (! Schema::hasColumn('assessments', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('assessments', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('assessments', 'target_level')) {
                $table->string('target_level')->nullable()->after('course_id');
            }

            if (! Schema::hasColumn('assessments', 'date_given')) {
                $table->date('date_given')->nullable()->after('description');
            }

            if (! Schema::hasColumn('assessments', 'due_date')) {
                $table->date('due_date')->nullable()->after('date_given');
            }

            if (! Schema::hasColumn('assessments', 'file_path')) {
                $table->string('file_path')->nullable()->after('due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            foreach (['target_level', 'date_given', 'file_path'] as $column) {
                if (Schema::hasColumn('assignments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('assessments', function (Blueprint $table): void {
            foreach (['name', 'description', 'target_level', 'date_given', 'due_date', 'file_path'] as $column) {
                if (Schema::hasColumn('assessments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
