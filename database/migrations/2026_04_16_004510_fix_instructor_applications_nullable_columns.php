<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->text('course_concept_note')->nullable()->change();
            $table->text('proposed_curriculum')->nullable()->change();
        });

        // Drop the unique constraint on email so users can reapply after rejection
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->string('email')->unique()->change();
        });

        Schema::table('instructor_applications', function (Blueprint $table) {
            $table->text('course_concept_note')->nullable(false)->change();
            $table->text('proposed_curriculum')->nullable(false)->change();
        });
    }
};
