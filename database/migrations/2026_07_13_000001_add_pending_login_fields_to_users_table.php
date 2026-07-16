<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('pending_login_password')->nullable()->after('password');
            $table->string('pending_login_token', 80)->nullable()->unique()->after('pending_login_password');
            $table->timestamp('pending_login_token_expires_at')->nullable()->after('pending_login_token');
            $table->timestamp('pending_login_token_used_at')->nullable()->after('pending_login_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['pending_login_token']);
            $table->dropColumn([
                'pending_login_password',
                'pending_login_token',
                'pending_login_token_expires_at',
                'pending_login_token_used_at',
            ]);
        });
    }
};
