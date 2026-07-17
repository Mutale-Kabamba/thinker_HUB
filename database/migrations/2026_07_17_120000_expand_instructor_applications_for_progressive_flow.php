<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->string('proficiency')->nullable()->after('bio');
            $table->string('occupation')->nullable()->after('proficiency');
            $table->string('whatsapp')->nullable()->after('occupation');
            $table->string('facebook_url')->nullable()->after('linkedin_url');

            $table->string('proposed_course_code')->nullable()->after('proposed_course_name');
            $table->text('proposed_course_description')->nullable()->after('proposed_course_code');
            $table->text('proposed_course_overview')->nullable()->after('proposed_course_description');
            $table->string('proposed_course_timeline')->nullable()->after('proposed_course_overview');
            $table->text('proposed_course_fees')->nullable()->after('proposed_course_timeline');
            $table->text('proposed_course_requirements')->nullable()->after('proposed_course_fees');
            $table->text('proposed_course_level_progression')->nullable()->after('proposed_course_requirements');
            $table->text('proposed_course_key_outcome')->nullable()->after('proposed_course_level_progression');
            $table->boolean('proposed_course_is_open_enrollment')->default(true)->after('proposed_course_key_outcome');
        });
    }

    public function down(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->dropColumn([
                'proficiency',
                'occupation',
                'whatsapp',
                'facebook_url',
                'proposed_course_code',
                'proposed_course_description',
                'proposed_course_overview',
                'proposed_course_timeline',
                'proposed_course_fees',
                'proposed_course_requirements',
                'proposed_course_level_progression',
                'proposed_course_key_outcome',
                'proposed_course_is_open_enrollment',
            ]);
        });
    }
};
