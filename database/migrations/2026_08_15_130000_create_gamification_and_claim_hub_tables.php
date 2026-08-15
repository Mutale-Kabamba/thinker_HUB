<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update users table with gamification counters
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'lifetime_xp')) {
                $table->unsignedInteger('lifetime_xp')->default(0)->after('email');
            }
            if (! Schema::hasColumn('users', 'spendable_coins')) {
                $table->unsignedInteger('spendable_coins')->default(0)->after('lifetime_xp');
            }
            if (! Schema::hasColumn('users', 'current_streak')) {
                $table->unsignedInteger('current_streak')->default(0)->after('spendable_coins');
            }
            if (! Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('current_streak');
            }
        });

        // 2. Update xp_transactions table with new fields
        Schema::table('xp_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('xp_transactions', 'amount_xp')) {
                $table->integer('amount_xp')->default(0)->after('user_id');
            }
            if (! Schema::hasColumn('xp_transactions', 'amount_coins')) {
                $table->integer('amount_coins')->default(0)->after('amount_xp');
            }
            if (! Schema::hasColumn('xp_transactions', 'activity_type')) {
                $table->string('activity_type')->nullable()->after('amount_coins');
            }
            if (! Schema::hasColumn('xp_transactions', 'subject_type')) {
                $table->string('subject_type')->nullable()->after('activity_type');
            }
            if (! Schema::hasColumn('xp_transactions', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            }
        });

        // Populate existing xp_transactions amount_xp & activity_type from legacy points & source
        DB::table('xp_transactions')->where('amount_xp', 0)->update([
            'amount_xp' => DB::raw('points'),
            'activity_type' => DB::raw('source'),
            'subject_id' => DB::raw('source_id'),
        ]);

        // Sync existing users lifetime_xp from transactions
        $usersWithXp = DB::table('xp_transactions')
            ->select('user_id', DB::raw('SUM(points) as total_xp'))
            ->groupBy('user_id')
            ->get();

        foreach ($usersWithXp as $row) {
            DB::table('users')
                ->where('id', $row->user_id)
                ->update([
                    'lifetime_xp' => max(0, (int) $row->total_xp),
                ]);
        }

        // 3. Create claim_items table
        if (! Schema::hasTable('claim_items')) {
            Schema::create('claim_items', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category'); // data, merch, voucher, perk
                $table->unsignedInteger('coin_cost');
                $table->integer('stock_quantity')->default(-1); // -1 = unlimited
                $table->string('image_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 4. Create claim_requests table
        if (! Schema::hasTable('claim_requests')) {
            Schema::create('claim_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('claim_item_id')->constrained('claim_items')->cascadeOnDelete();
                $table->unsignedInteger('coins_spent');
                $table->string('status')->default('pending'); // pending, approved, fulfilled, rejected
                $table->string('phone_number')->nullable();
                $table->text('delivery_notes')->nullable();
                $table->text('admin_remarks')->nullable();
                $table->timestamp('fulfilled_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_requests');
        Schema::dropIfExists('claim_items');

        Schema::table('xp_transactions', function (Blueprint $table): void {
            $table->dropColumn([
                'amount_xp',
                'amount_coins',
                'activity_type',
                'subject_type',
                'subject_id',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'lifetime_xp',
                'spendable_coins',
                'current_streak',
                'last_activity_at',
            ]);
        });
    }
};
