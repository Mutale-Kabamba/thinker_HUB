<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->string('course_assignment_action')->nullable()->after('preferred_course_id');
            $table->foreignId('assigned_course_id')->nullable()->after('course_assignment_action')->constrained('courses')->nullOnDelete();
            $table->boolean('prompt_course_creation')->default(false)->after('assigned_course_id');
            $table->text('course_creation_guidance')->nullable()->after('prompt_course_creation');
            $table->timestamp('prompted_at')->nullable()->after('course_creation_guidance');
        });
    }

    public function down(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->dropForeign(['assigned_course_id']);
            $table->dropColumn([
                'course_assignment_action',
                'assigned_course_id',
                'prompt_course_creation',
                'course_creation_guidance',
                'prompted_at',
            ]);
        });
    }
};
