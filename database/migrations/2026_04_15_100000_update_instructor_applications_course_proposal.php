<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->enum('proposal_type', ['new', 'existing'])->default('existing')->after('cv_path');
            $table->text('motivation_note')->nullable()->after('proposal_type');
            $table->text('competence_note')->nullable()->after('motivation_note');
            $table->string('roadmap_path')->nullable()->after('competence_note');
            $table->string('proposed_course_name')->nullable()->after('roadmap_path');
            $table->string('teaching_location')->nullable()->after('proposed_course_name');
            $table->string('full_roadmap_path')->nullable()->after('teaching_location');
            $table->string('curriculum_path')->nullable()->after('full_roadmap_path');
        });
    }

    public function down(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->dropColumn([
                'proposal_type',
                'motivation_note',
                'competence_note',
                'roadmap_path',
                'proposed_course_name',
                'teaching_location',
                'full_roadmap_path',
                'curriculum_path',
            ]);
        });
    }
};
