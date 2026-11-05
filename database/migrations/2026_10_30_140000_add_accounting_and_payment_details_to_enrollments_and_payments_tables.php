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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('payment_status', 30)->default('paid')->after('completed_by_user_id');
            $table->boolean('is_free')->default(false)->after('payment_status');
            $table->decimal('fee_amount', 10, 2)->default(0.00)->after('is_free');
            $table->string('payment_method', 50)->nullable()->after('fee_amount');
            $table->string('transaction_reference', 100)->nullable()->after('payment_method');
            $table->text('payment_notes')->nullable()->after('transaction_reference');
            $table->foreignId('registered_by_user_id')->nullable()->after('payment_notes')->constrained('users')->nullOnDelete();
            $table->string('registration_type', 40)->default('direct_checkout')->after('registered_by_user_id');
            $table->timestamp('paid_at')->nullable()->after('registration_type');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('registered_by_user_id')->nullable()->after('metadata')->constrained('users')->nullOnDelete();
            $table->string('registration_type', 40)->default('direct_checkout')->after('registered_by_user_id');
            $table->boolean('is_free')->default(false)->after('registration_type');
            $table->text('payment_notes')->nullable()->after('is_free');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['registered_by_user_id']);
            $table->dropColumn([
                'payment_status',
                'is_free',
                'fee_amount',
                'payment_method',
                'transaction_reference',
                'payment_notes',
                'registered_by_user_id',
                'registration_type',
                'paid_at',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['registered_by_user_id']);
            $table->dropColumn([
                'registered_by_user_id',
                'registration_type',
                'is_free',
                'payment_notes',
            ]);
        });
    }
};
