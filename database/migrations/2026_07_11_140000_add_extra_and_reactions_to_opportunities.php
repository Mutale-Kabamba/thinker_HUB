<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->json('extra')->nullable()->after('provider');
        });

        Schema::create('opportunity_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 16);
            $table->timestamps();

            $table->unique(['opportunity_id', 'user_id', 'emoji'], 'opp_user_emoji_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_reactions');

        Schema::table('opportunities', function (Blueprint $table): void {
            $table->dropColumn('extra');
        });
    }
};
