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
        // 1. Add is_for_kids to courses table
        if (! Schema::hasColumn('courses', 'is_for_kids')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->boolean('is_for_kids')->default(false)->after('is_open_enrollment');
                $table->index('is_for_kids');
            });
        }

        // 2. Add is_kid flag to users table
        if (! Schema::hasColumn('users', 'is_kid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_kid')->default(false)->after('role');
                $table->index('is_kid');
            });
        }

        // 3. Create parent_kids relationship table
        if (! Schema::hasTable('parent_kids')) {
            Schema::create('parent_kids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('kid_id')->constrained('users')->cascadeOnDelete();
                $table->string('relationship', 50)->default('Parent'); // Mother, Father, Guardian, Parent
                $table->unsignedTinyInteger('age')->nullable();
                $table->string('school', 255)->nullable();
                $table->string('grade', 50)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['parent_id', 'kid_id']);
                $table->index('parent_id');
                $table->index('kid_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_kids');

        if (Schema::hasColumn('users', 'is_kid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['is_kid']);
                $table->dropColumn('is_kid');
            });
        }

        if (Schema::hasColumn('courses', 'is_for_kids')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropIndex(['is_for_kids']);
                $table->dropColumn('is_for_kids');
            });
        }
    }
};
