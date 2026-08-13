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
        Schema::table('hub_posts', function (Blueprint $table) {
            $table->text('code_snippet')->nullable()->after('content');
            $table->text('pro_tip')->nullable()->after('code_snippet');
            $table->json('extra')->nullable()->after('pro_tip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hub_posts', function (Blueprint $table) {
            $table->dropColumn(['code_snippet', 'pro_tip', 'extra']);
        });
    }
};
