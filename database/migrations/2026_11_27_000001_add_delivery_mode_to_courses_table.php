<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('courses', 'delivery_mode')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->string('delivery_mode', 32)->nullable()->after('offering_mode')->index();
            });
        }

        // Backfill existing courses safely without overriding explicit values
        $lessonCourseIds = Schema::hasTable('lessons')
            ? DB::table('lessons')->distinct()->pluck('course_id')->filter()->all()
            : [];
        $scormCourseIds = Schema::hasTable('scorm_packages')
            ? DB::table('scorm_packages')->whereNotNull('course_id')->distinct()->pluck('course_id')->filter()->all()
            : [];
        $sessionCourseIds = Schema::hasTable('course_sessions')
            ? DB::table('course_sessions')->distinct()->pluck('course_id')->filter()->all()
            : [];

        $classroomIds = array_values(array_unique(array_merge($lessonCourseIds, $scormCourseIds)));
        $hybridIds = array_values(array_intersect($classroomIds, $sessionCourseIds));
        $selfPacedIds = array_values(array_diff($classroomIds, $hybridIds));

        if (! empty($hybridIds)) {
            DB::table('courses')->whereNull('delivery_mode')->whereIn('id', $hybridIds)->update(['delivery_mode' => 'hybrid']);
        }
        if (! empty($selfPacedIds)) {
            DB::table('courses')->whereNull('delivery_mode')->whereIn('id', $selfPacedIds)->update(['delivery_mode' => 'self_paced']);
        }

        DB::table('courses')->whereNull('delivery_mode')->update(['delivery_mode' => 'cohort']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('courses', 'delivery_mode')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->dropColumn('delivery_mode');
            });
        }
    }
};
