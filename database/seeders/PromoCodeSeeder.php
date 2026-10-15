<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PromoCode::updateOrCreate(
            ['code' => 'WELCOME20'],
            [
                'description' => '20% off any paid course for new learners.',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_discount_amount' => null,
                'min_order_amount' => null,
                'max_uses' => 500,
                'max_uses_per_user' => 1,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(6),
            ]
        );

        PromoCode::updateOrCreate(
            ['code' => 'SCHOLAR100'],
            [
                'description' => '100% Full Scholarship free enrollment code.',
                'discount_type' => 'percentage',
                'discount_value' => 100,
                'max_discount_amount' => null,
                'min_order_amount' => null,
                'max_uses' => 100,
                'max_uses_per_user' => 1,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addYear(),
            ]
        );

        PromoCode::updateOrCreate(
            ['code' => 'SAVE200'],
            [
                'description' => 'Fixed ZMW 200 off on courses valued at ZMW 400 or higher.',
                'discount_type' => 'fixed',
                'discount_value' => 200,
                'max_discount_amount' => null,
                'min_order_amount' => 400,
                'max_uses' => 200,
                'max_uses_per_user' => 1,
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(3),
            ]
        );
    }
}
