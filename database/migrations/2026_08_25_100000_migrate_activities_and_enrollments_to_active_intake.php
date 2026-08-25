<?php

use App\Models\Course;
use App\Models\CourseIntake;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Moves all activities and items (sessions, assignments, assessments, quizzes,
     * learning materials, videos, and enrollments) to each course's current active class/intake.
     */
    public function up(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasTable('course_intakes')) {
            return;
        }

        $courses = Course::all();

        foreach ($courses as $course) {
            // Find or create the active intake for the course
            $activeIntake = CourseIntake::query()
                ->where('course_id', $course->id)
                ->where(function ($q) {
                    $q->where('is_active', true)
                        ->orWhere('status', CourseIntake::STATUS_ACTIVE);
                })
                ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                ->latest('start_date')
                ->first();

            if (! $activeIntake) {
                $activeIntake = CourseIntake::query()
                    ->where('course_id', $course->id)
                    ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                    ->orderBy('start_date')
                    ->first();
            }

            if (! $activeIntake) {
                $activeIntake = CourseIntake::create([
                    'course_id' => $course->id,
                    'name' => 'Intake 1 - ' . ($course->code ?: 'Current Cohort'),
                    'start_date' => now()->toDateString(),
                    'status' => CourseIntake::STATUS_ACTIVE,
                    'is_active' => true,
                ]);
            } else {
                $activeIntake->update([
                    'is_active' => true,
                    'status' => CourseIntake::STATUS_ACTIVE,
                ]);
            }

            $tables = [
                'course_sessions',
                'assignments',
                'assessments',
                'quizzes',
                'learning_materials',
                'resource_videos',
                'enrollments',
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'course_intake_id') && Schema::hasColumn($table, 'course_id')) {
                    DB::table($table)
                        ->where('course_id', $course->id)
                        ->update(['course_intake_id' => $activeIntake->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way data alignment migration; no destructive reversal needed.
    }
};
