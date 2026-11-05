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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'unique_code')) {
                $table->string('unique_code', 30)->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'age')) {
                $table->unsignedTinyInteger('age')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'school')) {
                $table->string('school', 255)->nullable()->after('track');
            }
            if (! Schema::hasColumn('users', 'grade')) {
                $table->string('grade', 50)->nullable()->after('school');
            }
            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('whatsapp');
            }
        });

        if (Schema::hasTable('parent_kids')) {
            Schema::table('parent_kids', function (Blueprint $table) {
                if (! Schema::hasColumn('parent_kids', 'age')) {
                    $table->unsignedTinyInteger('age')->nullable()->after('relationship');
                }
                if (! Schema::hasColumn('parent_kids', 'school')) {
                    $table->string('school', 255)->nullable()->after('age');
                }
                if (! Schema::hasColumn('parent_kids', 'grade')) {
                    $table->string('grade', 50)->nullable()->after('school');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            foreach (['unique_code', 'age', 'school', 'grade', 'address'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $cols[] = $col;
                }
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        if (Schema::hasTable('parent_kids')) {
            Schema::table('parent_kids', function (Blueprint $table) {
                $cols = [];
                foreach (['age', 'school', 'grade'] as $col) {
                    if (Schema::hasColumn('parent_kids', $col)) {
                        $cols[] = $col;
                    }
                }
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
