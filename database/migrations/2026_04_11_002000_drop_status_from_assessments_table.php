<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table): void {
            if (Schema::hasColumn('assessments', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table): void {
            if (! Schema::hasColumn('assessments', 'status')) {
                $table->string('status')->default('Pending')->after('score');
            }
        });
    }
};
