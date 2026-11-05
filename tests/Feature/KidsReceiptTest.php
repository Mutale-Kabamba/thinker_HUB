<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KidsReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_receipt_for_kids_course(): void
    {
        $course = Course::create([
            'title' => 'Scratch Coding for Kids',
            'code' => 'KID-SCRATCH',
            'price' => 1500,
            'is_active' => true,
            'is_for_kids' => true,
        ]);

        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $kid = User::factory()->create([
            'role' => 'kid',
            'unique_code' => 'KID-9999',
        ]);

        $payment = Payment::create([
            'user_id' => $parent->id,
            'course_id' => $course->id,
            'reference' => 'TH-TEST-KID-RECEIPT',
            'amount' => 1500,
            'payment_method' => 'mobile_money',
            'status' => 'completed',
            'metadata' => [
                'is_kids_course' => true,
                'kid_id' => $kid->id,
                'kid_unique_code' => 'KID-9999',
                'kids_data' => [
                    'kid_name' => 'Junior Doe',
                ],
            ],
        ]);

        $response = $this->actingAs($parent)->get(route('payment.receipt', $payment->reference));

        $response->assertStatus(200);
        $response->assertSee('Payment Success');
        $response->assertSee('KID-9999');
    }
}
