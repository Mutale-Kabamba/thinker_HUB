<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->nullable()->after('course_id')->constrained('promo_codes')->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('amount');
            $table->decimal('original_amount', 10, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['promo_code_id']);
            $table->dropColumn(['promo_code_id', 'original_amount', 'discount_amount']);
        });
    }
};
