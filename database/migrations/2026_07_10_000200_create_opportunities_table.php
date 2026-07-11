<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('Job');
            $table->text('description')->nullable();
            $table->string('link_url')->nullable();
            $table->string('promo_code')->nullable();
            $table->string('provider')->nullable();
            $table->boolean('is_published')->default(true);
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
