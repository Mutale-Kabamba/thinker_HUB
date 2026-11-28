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
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'nrc_passport')) {
                $table->string('nrc_passport', 50)->nullable()->after('gender');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'gender')) {
                $columns[] = 'gender';
            }
            if (Schema::hasColumn('users', 'nrc_passport')) {
                $columns[] = 'nrc_passport';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
