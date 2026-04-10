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
        Schema::table('courses', function (Blueprint $table) {
            $table->text('overview')->nullable()->after('description');
            $table->string('timeline')->nullable()->after('overview');
            $table->text('fees')->nullable()->after('timeline');
            $table->text('requirements')->nullable()->after('fees');
            $table->text('key_outcome')->nullable()->after('requirements');
            $table->text('level_progression')->nullable()->after('key_outcome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'overview',
                'timeline',
                'fees',
                'requirements',
                'key_outcome',
                'level_progression',
            ]);
        });
    }
};
