<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoursePromoCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/collections/mobile-money' => Http::response([
                'status' => true,
                'message' => 'Prompt sent to customer',
                'data' => [
                    'id' => 'GATEWAY-MM-TEST',
                    'status' => 'pending',
                ],
            ], 200),
            '*/collections/card' => Http::response([
                'status' => true,
                'message' => 'Card processed successfully',
                'data' => [
                    'id' => 'GATEWAY-CARD-TEST',
                    'status' => 'successful',
                ],
            ], 200),
        ]);
    }

    public function test_can_validate_percentage_promo_code_via_api(): void
    {
        $course = Course::create([
            'title' => 'Graphic Design Masterclass',
            'code' => 'DSGN201',
            'fees' => '1000',
            'is_active' => true,
        ]);

        $promo = PromoCode::create([
            'code' => 'TEST20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'test20',
            'email' => 'student@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'code' => 'TEST20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'original_amount' => 1000,
            'discount_amount' => 200,
            'final_amount' => 800,
            'is_free' => false,
        ]);
    }

    public function test_can_validate_100_percent_free_promo_code(): void
    {
        $course = Course::create([
            'title' => 'Intro to Python',
            'code' => 'PY101',
            'fees' => '1500',
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'FULLFREE',
            'discount_type' => 'percentage',
            'discount_value' => 100,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'FULLFREE',
            'email' => 'scholar@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'code' => 'FULLFREE',
            'original_amount' => 1500,
            'discount_amount' => 1500,
            'final_amount' => 0,
            'is_free' => true,
        ]);
    }

    public function test_can_validate_fixed_amount_promo_code(): void
    {
        $course = Course::create([
            'title' => 'Fullstack Laravel',
            'code' => 'LAR101',
            'fees' => '800',
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'FLAT300',
            'discount_type' => 'fixed',
            'discount_value' => 300,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'FLAT300',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'code' => 'FLAT300',
            'original_amount' => 800,
            'discount_amount' => 300,
            'final_amount' => 500,
            'is_free' => false,
        ]);
    }

    public function test_expired_promo_code_returns_validation_error(): void
    {
        $course = Course::create([
            'title' => 'Data Science',
            'code' => 'DS101',
            'fees' => '500',
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'EXPIRED50',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'EXPIRED50',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'valid' => false,
        ]);
        $this->assertStringContainsString('expired', strtolower($response->json('message')));
    }

    public function test_inactive_promo_code_returns_validation_error(): void
    {
        $course = Course::create([
            'title' => 'Data Science',
            'code' => 'DS101',
            'fees' => '500',
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'INACTIVE10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => false,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'INACTIVE10',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'valid' => false,
        ]);
    }

    public function test_course_restricted_promo_code_rejected_for_different_course(): void
    {
        $courseA = Course::create([
            'title' => 'Course A',
            'code' => 'CRS-A',
            'fees' => '500',
            'is_active' => true,
        ]);

        $courseB = Course::create([
            'title' => 'Course B',
            'code' => 'CRS-B',
            'fees' => '500',
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'COURSE-A-ONLY',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'course_id' => $courseA->id,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $courseB), [
            'promo_code' => 'COURSE-A-ONLY',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'valid' => false,
        ]);
        $this->assertStringContainsString('only valid for', strtolower($response->json('message')));
    }

    public function test_promo_code_with_min_order_amount_rejected_if_price_too_low(): void
    {
        $course = Course::create([
            'title' => 'Mini Course',
            'code' => 'MINI101',
            'fees' => '200',
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'BIGBUY',
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'min_order_amount' => 500,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'BIGBUY',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('minimum', strtolower($response->json('message')));
    }

    public function test_max_global_uses_limit_enforced(): void
    {
        $course = Course::create([
            'title' => 'Exclusive Course',
            'code' => 'EXC101',
            'fees' => '600',
            'is_active' => true,
        ]);

        $promo = PromoCode::create([
            'code' => 'LIMIT1',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'max_uses' => 1,
            'uses_count' => 1,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'LIMIT1',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('usage limit', strtolower($response->json('message')));
    }

    public function test_max_uses_per_user_enforced_by_email(): void
    {
        $course = Course::create([
            'title' => 'Special Course',
            'code' => 'SPC101',
            'fees' => '600',
            'is_active' => true,
        ]);

        $promo = PromoCode::create([
            'code' => 'ONCEPERUSER',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'max_uses_per_user' => 1,
            'is_active' => true,
        ]);

        PromoCodeUsage::create([
            'promo_code_id' => $promo->id,
            'course_id' => $course->id,
            'user_email' => 'repeat@example.com',
            'original_amount' => 600,
            'discount_amount' => 300,
            'final_amount' => 300,
        ]);

        $response = $this->postJson(route('checkout.apply-promo', $course), [
            'promo_code' => 'ONCEPERUSER',
            'email' => 'repeat@example.com',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('already used', strtolower($response->json('message')));
    }

    public function test_checkout_with_partial_discount_records_promo_details_on_payment(): void
    {
        $course = Course::create([
            'title' => 'Mobile App Dev',
            'code' => 'APP101',
            'fees' => '1000',
            'is_active' => true,
        ]);

        $promo = PromoCode::create([
            'code' => 'DISCOUNT30',
            'discount_type' => 'percentage',
            'discount_value' => 30,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.process', $course), [
            'name' => 'Chileshe Mwape',
            'email' => 'chileshe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'track' => 'Intermediate',
            'payment_method' => 'mobile_money',
            'provider' => 'airtel',
            'phone_number' => '0977112233',
            'promo_code' => 'DISCOUNT30',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $payment = Payment::where('course_id', $course->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals($promo->id, $payment->promo_code_id);
        $this->assertEquals(1000.00, (float) $payment->original_amount);
        $this->assertEquals(300.00, (float) $payment->discount_amount);
        $this->assertEquals(700.00, (float) $payment->amount);
    }

    public function test_checkout_with_100_percent_free_code_bypasses_gateway_and_completes_instantly(): void
    {
        $course = Course::create([
            'title' => 'Full Scholarship Course',
            'code' => 'SCHOLAR1',
            'fees' => '1200',
            'is_active' => true,
        ]);

        $promo = PromoCode::create([
            'code' => 'SCHOLARSHIP',
            'discount_type' => 'percentage',
            'discount_value' => 100,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('checkout.process', $course), [
            'name' => 'Lupupa Banda',
            'email' => 'lupupa@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'track' => 'Beginner',
            'promo_code' => 'SCHOLARSHIP',
            // Notice: No payment_method or phone_number required because course is 100% free!
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_free' => true,
        ]);

        $payment = Payment::where('course_id', $course->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(Payment::STATUS_COMPLETED, $payment->status);
        $this->assertEquals(0.00, (float) $payment->amount);
        $this->assertEquals(1200.00, (float) $payment->original_amount);
        $this->assertEquals(1200.00, (float) $payment->discount_amount);
        $this->assertEquals('promo_code', $payment->payment_method);

        // Student user created and logged in
        $student = User::where('email', 'lupupa@example.com')->first();
        $this->assertNotNull($student);
        $this->assertEquals('student', $student->role);
        $this->assertAuthenticatedAs($student);

        // Enrollment created & active
        $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertFalse($enrollment->isCompleted());
        $this->assertEquals(0, (int) $enrollment->progress_percentage);

        // Promo usage recorded
        $usage = PromoCodeUsage::where('promo_code_id', $promo->id)->where('user_email', 'lupupa@example.com')->first();
        $this->assertNotNull($usage);
        $this->assertEquals(1200.00, (float) $usage->original_amount);
        $this->assertEquals(1200.00, (float) $usage->discount_amount);
        $this->assertEquals(0.00, (float) $usage->final_amount);

        // Promo code uses count incremented
        $this->assertEquals(1, $promo->fresh()->uses_count);
    }

    public function test_receipt_page_renders_discount_breakdown(): void
    {
        $course = Course::create([
            'title' => 'Web Design',
            'code' => 'WD101',
            'fees' => '1000',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Kondwani Phiri',
            'email' => 'kondwani@example.com',
            'role' => 'student',
        ]);

        $promo = PromoCode::create([
            'code' => 'SAVE40',
            'discount_type' => 'percentage',
            'discount_value' => 40,
            'is_active' => true,
        ]);

        $payment = Payment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'promo_code_id' => $promo->id,
            'amount' => 600.00,
            'original_amount' => 1000.00,
            'discount_amount' => 400.00,
            'currency' => 'ZMW',
            'status' => Payment::STATUS_COMPLETED,
            'payment_method' => 'card',
            'reference' => 'PAY-TEST-DISCOUNT-123',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('payment.receipt', $payment->reference));

        $response->assertStatus(200);
        $response->assertSee('PAY-TEST-DISCOUNT-123');
        $response->assertSee('Original Price');
        $response->assertSee('Promo Discount (SAVE40)');
        $response->assertSee('Total Payment');
        $response->assertSee('ZMW 600.00');
    }
}
