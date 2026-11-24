<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('utm_attributions')) {
            Schema::create('utm_attributions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->string('utm_source', 120)->nullable()->index();
                $table->string('utm_medium', 120)->nullable()->index();
                $table->string('utm_campaign', 160)->nullable()->index();
                $table->string('utm_content', 160)->nullable();
                $table->string('utm_term', 160)->nullable();
                $table->text('referrer')->nullable();
                $table->text('landing_url')->nullable();
                $table->string('session_token', 80)->nullable()->index();
                $table->timestamp('captured_at')->nullable();
                $table->timestamps();

                $table->index(['utm_source', 'utm_campaign']);
                $table->index(['tenant_id', 'utm_source']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('utm_attributions');
    }
};
