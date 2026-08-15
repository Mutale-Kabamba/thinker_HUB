<?php

namespace Database\Seeders;

use App\Models\ClaimItem;
use Illuminate\Database\Seeder;

class ClaimItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Data Bundles & Airtime
            [
                'title' => '1GB High-Speed Mobile Data Bundle',
                'description' => 'Direct data top-up valid for 7 days on MTN, Airtel, or Zamtel networks.',
                'category' => ClaimItem::CATEGORY_DATA,
                'coin_cost' => 150,
                'stock_quantity' => -1, // unlimited
                'is_active' => true,
            ],
            [
                'title' => '3GB Study Pack Data Bundle',
                'description' => '30-day mobile data package for learning, video streaming, and research.',
                'category' => ClaimItem::CATEGORY_DATA,
                'coin_cost' => 350,
                'stock_quantity' => -1,
                'is_active' => true,
            ],
            [
                'title' => 'K50 Mobile Airtime Voucher',
                'description' => 'Instant electronic airtime recharge pin delivered to your phone.',
                'category' => ClaimItem::CATEGORY_DATA,
                'coin_cost' => 200,
                'stock_quantity' => -1,
                'is_active' => true,
            ],

            // Merch & Swag
            [
                'title' => 'Thinker HUB Premium Developer Hoodie',
                'description' => 'High-quality heavyweight fleece hoodie with embroidered Thinker HUB insignia. Available in S, M, L, XL.',
                'category' => ClaimItem::CATEGORY_MERCH,
                'coin_cost' => 1200,
                'stock_quantity' => 25,
                'is_active' => true,
            ],
            [
                'title' => 'Thinker HUB Tech T-Shirt',
                'description' => '100% combed cotton branded tee with minimalist futuristic code motif.',
                'category' => ClaimItem::CATEGORY_MERCH,
                'coin_cost' => 600,
                'stock_quantity' => 50,
                'is_active' => true,
            ],
            [
                'title' => 'Holographic Developer Sticker Pack',
                'description' => 'Set of 10 weather-proof die-cut stickers for your laptop, notebook, or water bottle.',
                'category' => ClaimItem::CATEGORY_MERCH,
                'coin_cost' => 100,
                'stock_quantity' => 100,
                'is_active' => true,
            ],

            // Vouchers
            [
                'title' => 'K150 Bookshop & Learning Voucher',
                'description' => 'Redeemable for programming books, stationery, or study supplies.',
                'category' => ClaimItem::CATEGORY_VOUCHER,
                'coin_cost' => 750,
                'stock_quantity' => 15,
                'is_active' => true,
            ],
            [
                'title' => 'Fast-Track Course Access Voucher',
                'description' => 'Free 100% coupon code for any premium short course or bootcamp module.',
                'category' => ClaimItem::CATEGORY_VOUCHER,
                'coin_cost' => 900,
                'stock_quantity' => 30,
                'is_active' => true,
            ],

            // Perks
            [
                'title' => '1-on-1 Senior Tech Mentorship Session (45 min)',
                'description' => 'Private video consultation with an industry tech lead for code review, career advice, and portfolio feedback.',
                'category' => ClaimItem::CATEGORY_PERK,
                'coin_cost' => 1500,
                'stock_quantity' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'CV & LinkedIn Profile Professional Review',
                'description' => 'In-depth resume optimization, project highlights, and recruiter-ready LinkedIn overhaul.',
                'category' => ClaimItem::CATEGORY_PERK,
                'coin_cost' => 800,
                'stock_quantity' => 20,
                'is_active' => true,
            ],
        ];

        $firstCourse = \App\Models\Course::first();

        if ($firstCourse) {
            $items[] = [
                'course_id' => $firstCourse->id,
                'title' => "1-on-1 Private Office Hours: {$firstCourse->title}",
                'description' => 'Direct 30-minute private consultation with the course instructor.',
                'category' => ClaimItem::CATEGORY_PERK,
                'coin_cost' => 450,
                'stock_quantity' => 5,
                'is_active' => true,
            ];
        }

        foreach ($items as $data) {
            ClaimItem::firstOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
