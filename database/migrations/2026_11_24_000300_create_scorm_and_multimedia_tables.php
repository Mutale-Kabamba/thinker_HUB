<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('scorm_packages')) {
            Schema::create('scorm_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->string('title');
                $table->string('standard', 24)->default('scorm_12')->index(); // scorm_12, scorm_2004, xapi
                $table->string('version', 40)->nullable();
                $table->string('storage_disk', 40)->default('public');
                $table->string('storage_path');
                $table->string('entry_point');
                $table->string('manifest_identifier')->nullable();
                $table->json('manifest_meta')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('scorm_attempts')) {
            Schema::create('scorm_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scorm_package_id')->constrained('scorm_packages')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->unsignedInteger('attempt_number')->default(1);
                $table->string('lesson_status', 40)->default('not attempted')->index();
                $table->decimal('score_raw', 8, 2)->nullable();
                $table->decimal('score_min', 8, 2)->nullable();
                $table->decimal('score_max', 8, 2)->nullable();
                $table->decimal('score_scaled', 6, 4)->nullable();
                $table->string('lesson_location', 255)->nullable();
                $table->longText('suspend_data')->nullable();
                $table->string('session_time', 64)->nullable();
                $table->string('total_time', 64)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['scorm_package_id', 'user_id']);
                $table->index(['user_id', 'lesson_status']);
            });
        }

        if (! Schema::hasTable('scorm_tracking')) {
            Schema::create('scorm_tracking', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scorm_attempt_id')->constrained('scorm_attempts')->cascadeOnDelete();
                $table->string('element', 190)->index();
                $table->longText('value')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('resource_downloads')) {
            Schema::create('resource_downloads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
                $table->foreignId('learning_material_id')->nullable()->constrained('learning_materials')->nullOnDelete();
                $table->string('file_name');
                $table->string('file_path', 500)->nullable();
                $table->timestamp('downloaded_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'course_id']);
            });
        }

        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {
                if (! Schema::hasColumn('lessons', 'low_bandwidth_video_url')) {
                    $table->string('low_bandwidth_video_url', 500)->nullable();
                }
                if (! Schema::hasColumn('lessons', 'video_quality_variants')) {
                    $table->json('video_quality_variants')->nullable();
                }
                if (! Schema::hasColumn('lessons', 'scorm_package_id')) {
                    $table->foreignId('scorm_package_id')->nullable()->constrained('scorm_packages')->nullOnDelete();
                }
                if (! Schema::hasColumn('lessons', 'resource_files')) {
                    $table->json('resource_files')->nullable();
                }
            });
        }

        if (Schema::hasTable('quiz_questions')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                if (! Schema::hasColumn('quiz_questions', 'branching_rules')) {
                    $table->json('branching_rules')->nullable();
                }
                if (! Schema::hasColumn('quiz_questions', 'instant_feedback')) {
                    $table->text('instant_feedback')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quiz_questions')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                foreach (['instant_feedback', 'branching_rules'] as $col) {
                    if (Schema::hasColumn('quiz_questions', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {
                if (Schema::hasColumn('lessons', 'scorm_package_id')) {
                    $table->dropConstrainedForeignId('scorm_package_id');
                }
                foreach (['resource_files', 'video_quality_variants', 'low_bandwidth_video_url'] as $col) {
                    if (Schema::hasColumn('lessons', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('resource_downloads');
        Schema::dropIfExists('scorm_tracking');
        Schema::dropIfExists('scorm_attempts');
        Schema::dropIfExists('scorm_packages');
    }
};
