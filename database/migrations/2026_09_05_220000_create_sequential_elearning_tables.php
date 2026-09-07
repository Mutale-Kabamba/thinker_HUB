<?php

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create course_sections table
        if (! Schema::hasTable('course_sections')) {
            Schema::create('course_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedInteger('order_column')->default(0);
                $table->timestamps();

                $table->index(['course_id', 'order_column']);
            });
        }

        // 2. Add slug to courses if missing and backfill
        if (Schema::hasTable('courses') && ! Schema::hasColumn('courses', 'slug')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('slug')->nullable()->unique()->after('title');
            });

            // Backfill slugs
            $courses = Course::all();
            foreach ($courses as $course) {
                $baseSlug = Str::slug($course->title ?: $course->code ?: 'course-'.$course->id);
                $slug = $baseSlug;
                $counter = 1;
                while (Course::where('slug', $slug)->where('id', '!=', $course->id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $course->updateQuietly(['slug' => $slug]);
            }
        }

        // 3. Add progress_percentage to enrollments if missing
        if (Schema::hasTable('enrollments') && ! Schema::hasColumn('enrollments', 'progress_percentage')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->unsignedInteger('progress_percentage')->default(0)->after('course_intake_id');
            });
        }

        // 4. Enhance lessons table with sequential e-learning schema
        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {
                if (! Schema::hasColumn('lessons', 'course_section_id')) {
                    $table->foreignId('course_section_id')->nullable()->constrained('course_sections')->nullOnDelete()->after('course_id');
                }
                if (! Schema::hasColumn('lessons', 'slug')) {
                    $table->string('slug')->nullable()->after('title');
                }
                if (! Schema::hasColumn('lessons', 'type')) {
                    $table->string('type')->default('video')->after('slug');
                }
                if (! Schema::hasColumn('lessons', 'reading_body')) {
                    $table->longText('reading_body')->nullable()->after('type');
                }
                if (! Schema::hasColumn('lessons', 'content_type')) {
                    $table->string('content_type')->nullable()->after('reading_body');
                }
                if (! Schema::hasColumn('lessons', 'content_id')) {
                    $table->unsignedBigInteger('content_id')->nullable()->after('content_type');
                }
                if (! Schema::hasColumn('lessons', 'min_watch_percentage')) {
                    $table->unsignedInteger('min_watch_percentage')->default(90)->after('content_id');
                }
                if (! Schema::hasColumn('lessons', 'is_free_preview')) {
                    $table->boolean('is_free_preview')->default(false)->after('min_watch_percentage');
                }
                if (! Schema::hasColumn('lessons', 'order_column')) {
                    $table->unsignedInteger('order_column')->default(0)->after('is_free_preview');
                }
            });

            // Backfill slugs and order_column from sort_order
            $existingLessons = Lesson::all();
            foreach ($existingLessons as $lesson) {
                $slug = $lesson->slug ?: Str::slug($lesson->title ?: 'lesson-'.$lesson->id);
                $order = $lesson->order_column ?: ($lesson->sort_order ?? 0);
                $lesson->updateQuietly([
                    'slug' => $slug,
                    'order_column' => $order,
                    'type' => $lesson->type ?: 'video',
                ]);
            }
        }

        // 5. Create lesson_progress table
        if (! Schema::hasTable('lesson_progress')) {
            Schema::create('lesson_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
                $table->boolean('is_unlocked')->default(false);
                $table->boolean('is_completed')->default(false);
                $table->unsignedInteger('last_watched_second')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'lesson_id']);
                $table->index(['user_id', 'course_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');

        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {
                $dropColumns = [];
                foreach ([
                    'order_column', 'is_free_preview', 'min_watch_percentage',
                    'content_id', 'content_type', 'reading_body', 'type', 'slug', 'course_section_id'
                ] as $col) {
                    if (Schema::hasColumn('lessons', $col)) {
                        $dropColumns[] = $col;
                    }
                }
                if (! empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('enrollments') && Schema::hasColumn('enrollments', 'progress_percentage')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropColumn('progress_percentage');
            });
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'slug')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }

        Schema::dropIfExists('course_sections');
    }
};
