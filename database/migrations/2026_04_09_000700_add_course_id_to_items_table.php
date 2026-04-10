<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
