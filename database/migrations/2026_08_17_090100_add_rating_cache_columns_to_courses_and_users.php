<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(0.00);
            }
            if (! Schema::hasColumn('courses', 'review_count')) {
                $table->unsignedInteger('review_count')->default(0);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'instructor_rating')) {
                $table->decimal('instructor_rating', 3, 2)->default(0.00);
            }
            if (! Schema::hasColumn('users', 'instructor_review_count')) {
                $table->unsignedInteger('instructor_review_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'average_rating')) {
                $table->dropColumn('average_rating');
            }
            if (Schema::hasColumn('courses', 'review_count')) {
                $table->dropColumn('review_count');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'instructor_rating')) {
                $table->dropColumn('instructor_rating');
            }
            if (Schema::hasColumn('users', 'instructor_review_count')) {
                $table->dropColumn('instructor_review_count');
            }
        });
    }
};
