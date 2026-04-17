<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('proficiency')->nullable()->after('track');
            $table->string('occupation')->nullable()->after('proficiency');
            $table->string('whatsapp')->nullable()->after('occupation');
            $table->string('linkedin_url')->nullable()->after('whatsapp');
            $table->string('facebook_url')->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['proficiency', 'occupation', 'whatsapp', 'linkedin_url', 'facebook_url']);
        });
    }
};
