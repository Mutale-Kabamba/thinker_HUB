<?php

namespace Tests\Feature;

use App\Mail\PaymentReceiptMail;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CoursePaymentGatewayTest extends TestCase
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
                    'id' => 'LENCO-MM-123456',
                    'status' => 'pending',
                ],
            ], 200),
            '*/collections/card' => Http::response([
                'status' => true,
                'message' => 'Card processed successfully',
                'data' => [
                    'id' => 'LENCO-CARD-123456',
                    'status' => 'successful',
                ],
            ], 200),
            '*/collections/verify/*' => Http::response([
                'status' => true,
                'message' => 'Pending authorization',
                'data' => [
                    'status' => 'pending',
                ],
            ], 200),
            '*/collections/*' => Http::response([
                'status' => true,
                'message' => 'Pending authorization',
                'data' => [
                    'status' => 'pending',
                ],
            ], 200),
        ]);
    }

    public function test_checkout_screen_can_be_rendered_for_course(): void
    {
        $course = Course::create([
            'title' => 'Web Development Bootcamp',
            'code' => 'WEB101',
            'fees' => '1500',
            'is_active' => true,
        ]);

        $response = $this->get(route('checkout.show', $course));

        $response->assertStatus(200);
        $response->assertSee('Web Development Bootcamp');
        $response->assertSee('WEB101');
        $response->assertSee('Mobile Money');
        $response->assertSee('Debit / Credit Card');
        $response->assertSee('Airtel Money');
        $response->assertSee('MTN MoMo');
        $response->assertSee('Zamtel Kwacha');
    }

    public function test_student_can_initiate_mobile_money_payment_and_poll_status(): void
    {
        $course = Course::create([
            'title' => 'Python Programming',
            'code' => 'PY101',
            'fees' => '1200',
            'is_active' => true,
        ]);

        $response = $this->post(route('checkout.process', $course), [
            'name' => 'Mwila Tembo',
            'email' => 'mwila@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'track' => 'Intermediate',
            'payment_method' => 'mobile_money',
            'provider' => 'airtel',
            'phone_number' => '0977264054',
        ]);

        // Account is NOT created yet because payment is still pending
        $this->assertGuest();
        $this->assertNull(User::where('email', 'mwila@example.com')->first());

        $payment = Payment::where('reference', 'LIKE', 'TH-PAY-%')->first();
        $this->assertNotNull($payment);
        $this->assertNull($payment->user_id);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('mobile_money', $payment->payment_method);
        $this->assertEquals('airtel', $payment->provider);
        $this->assertEquals(1200.00, (float) $payment->amount);
        $this->assertEquals('mwila@example.com', $payment->metadata['guest_data']['email']);

        $response->assertRedirect(route('payment.receipt', $payment->reference));

        // Test status check endpoint returns status
        $statusResponse = $this->getJson(route('payment.status', $payment->reference));
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'found' => true,
            'status' => 'pending',
            'is_completed' => false,
            'reference' => $payment->reference,
        ]);
    }

    public function test_account_is_created_only_when_payment_completes(): void
    {
        Mail::fake();

        $course = Course::create([
            'title' => 'AI Engineering',
            'code' => 'AI301',
            'fees' => '2500',
            'is_active' => true,
        ]);

        // 1. Guest initiates checkout
        $this->post(route('checkout.process', $course), [
            'name' => 'Lupiya Banda',
            'email' => 'lupiya@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'track' => 'Advanced',
            'payment_method' => 'mobile_money',
            'provider' => 'mtn',
            'phone_number' => '0966554433',
        ]);

        $this->assertEquals(0, User::where('email', 'lupiya@example.com')->count());

        $payment = Payment::where('reference', 'LIKE', 'TH-PAY-%')->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertNull($payment->user_id);

        // 2. Webhook confirms payment
        $webhookResponse = $this->postJson(route('payment.webhook.broadpay'), [
            'event' => 'collection.successful',
            'data' => [
                'reference' => $payment->reference,
                'status' => 'successful',
                'amount' => 2500.00,
            ],
        ]);

        $webhookResponse->assertStatus(200);
        $webhookResponse->assertJson(['success' => true]);

        // 3. User account is NOW created and enrollment confirmed
        $student = User::where('email', 'lupiya@example.com')->first();
        $this->assertNotNull($student);
        $this->assertTrue((bool) $student->is_active);
        $this->assertEquals('student', $student->role);
        $this->assertEquals('Advanced', $student->track);
        $this->assertNotNull($student->email_verified_at);

        $payment->refresh();
        $this->assertEquals($student->id, $payment->user_id);
        $this->assertEquals('completed', $payment->status);
        $this->assertNotNull($payment->enrollment_id);

        $enrollment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment);
    }

    public function test_paid_course_registration_redirects_to_checkout_without_creating_user(): void
    {
        $course = Course::create([
            'title' => 'Fullstack Mastery',
            'code' => 'FS401',
            'fees' => '3000',
            'is_active' => true,
        ]);

        $response = $this->post('/register', [
            'name' => 'Kondwani Phiri',
            'email' => 'kondwani@example.com',
            'course_id' => $course->id,
            'track' => 'Intermediate',
            'accept_terms' => true,
            'accept_requirements' => true,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // User is NOT created yet
        $this->assertNull(User::where('email', 'kondwani@example.com')->first());
        $this->assertGuest();

        $response->assertRedirect(route('checkout.show', [$course, 'track' => 'Intermediate']));
    }

    public function test_broadpay_webhook_fulfills_payment_and_sends_receipt(): void
    {
        Mail::fake();

        $student = User::factory()->create([
            'name' => 'Chanda Musonda',
            'email' => 'chanda@example.com',
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Flutter Mobile Development',
            'code' => 'FLUT101',
            'fees' => '1800',
            'is_active' => true,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $reference = 'TH-PAY-2026-TEST01';
        $payment = Payment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'amount' => 1800.00,
            'currency' => 'ZMW',
            'payment_method' => 'mobile_money',
            'provider' => 'mtn',
            'phone_number' => '0966123456',
            'reference' => $reference,
            'status' => 'pending',
        ]);

        $webhookPayload = [
            'event' => 'collection.successful',
            'data' => [
                'reference' => $reference,
                'status' => 'successful',
                'amount' => 1800.00,
                'currency' => 'ZMW',
                'transaction_id' => 'BP-TX-998877',
            ],
        ];

        $response = $this->postJson(route('payment.webhook.broadpay'), $webhookPayload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
        $this->assertNotNull($payment->paid_at);

        Mail::assertSent(PaymentReceiptMail::class, function (PaymentReceiptMail $mail) use ($student) {
            return $mail->hasTo($student->email);
        });

        // Verify status endpoint now reflects completion
        $statusResponse = $this->getJson(route('payment.status', $reference));
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'found' => true,
            'status' => 'completed',
            'is_completed' => true,
            'reference' => $reference,
        ]);
    }

    public function test_student_can_process_card_payment_and_activate_enrollment(): void
    {
        Mail::fake();

        $course = Course::create([
            'title' => 'Data Science Essentials',
            'code' => 'DS201',
            'fees' => '2000',
            'is_active' => true,
        ]);

        $response = $this->post(route('checkout.process', $course), [
            'name' => 'Chileshe Mwape',
            'email' => 'chileshe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'track' => 'Advanced',
            'payment_method' => 'card',
            'provider' => 'visa',
            'card_number' => '4242 4242 4242 4242',
            'card_holder' => 'Chileshe Mwape',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

        $student = User::where('email', 'chileshe@example.com')->first();
        $this->assertNotNull($student);
        $this->assertTrue((bool) $student->is_active);

        $payment = Payment::where('user_id', $student->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('card', $payment->payment_method);
        $this->assertEquals('completed', $payment->status);

        $response->assertRedirect(route('payment.receipt', $payment->reference));

        Mail::assertSent(PaymentReceiptMail::class);
    }

    public function test_authenticated_student_can_pay_for_course_and_view_receipt(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Cloud Computing',
            'code' => 'CC301',
            'fees' => '1800',
            'is_active' => true,
        ]);

        $response = $this->actingAs($student)->post(route('checkout.process', $course), [
            'track' => 'Beginner',
            'payment_method' => 'card',
            'card_number' => '4242 4242 4242 4242',
            'card_holder' => $student->name,
            'card_expiry' => '05/29',
            'card_cvv' => '456',
        ]);

        $payment = Payment::where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($payment);

        $response->assertRedirect(route('payment.receipt', $payment->reference));

        $receiptResponse = $this->get(route('payment.receipt', $payment->reference));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee($payment->reference);
        $receiptResponse->assertSee('Cloud Computing');
        $receiptResponse->assertSee('PAID &amp; VERIFIED', false);
    }

    public function test_checkout_screen_displays_only_selected_level_and_exact_level_fee(): void
    {
        $course = Course::create([
            'title' => 'Cybersecurity Essentials',
            'code' => 'SEC201',
            'fees' => json_encode([
                ['level' => 'Beginner', 'amount' => '1000'],
                ['level' => 'Intermediate', 'amount' => '1600'],
                ['level' => 'Advanced', 'amount' => '2200'],
            ]),
            'is_active' => true,
        ]);

        // 1. Level = Intermediate
        $response = $this->get(route('checkout.show', [$course, 'track' => 'Intermediate']));
        $response->assertStatus(200);
        $response->assertSee('SEC201');
        $response->assertSee('1,600.00');

        // 2. Level = Advanced
        $responseAdv = $this->get(route('checkout.show', [$course, 'track' => 'Advanced']));
        $responseAdv->assertStatus(200);
        $responseAdv->assertSee('2,200.00');
    }

    public function test_course_model_parses_level_fees_across_different_formats(): void
    {
        // Format A: JSON array of objects
        $courseA = new Course([
            'fees' => json_encode([
                ['level' => 'Beginner', 'amount' => '1100'],
                ['level' => 'Intermediate', 'amount' => '1700'],
                ['level' => 'Advanced', 'amount' => '2300'],
            ]),
        ]);
        $this->assertEquals(1100.00, $courseA->getNumericFeeForLevel('Beginner'));
        $this->assertEquals(1700.00, $courseA->getNumericFeeForLevel('Intermediate'));
        $this->assertEquals(2300.00, $courseA->getNumericFeeForLevel('Advanced'));

        // Format B: Text lines
        $courseB = new Course([
            'fees' => "Beginner: 1200\nIntermediate: 1800\nAdvanced: 2400",
        ]);
        $this->assertEquals(1200.00, $courseB->getNumericFeeForLevel('Beginner'));
        $this->assertEquals(1800.00, $courseB->getNumericFeeForLevel('Intermediate'));
        $this->assertEquals(2400.00, $courseB->getNumericFeeForLevel('Advanced'));

        // Format C: Flat fee fallback
        $courseC = new Course([
            'fees' => '1500',
        ]);
        $this->assertEquals(1500.00, $courseC->getNumericFeeForLevel('Beginner'));
        $this->assertEquals(1500.00, $courseC->getNumericFeeForLevel('Advanced'));
    }

    public function test_course_model_extracts_fee_options_with_modes_and_levels(): void
    {
        $course = new Course([
            'title' => 'Web Mastery',
            'code' => 'WEB101',
            'fees' => json_encode([
                'group' => [
                    ['level' => 'Beginner', 'amount' => '350', 'duration' => '8 Weeks'],
                    ['level' => 'Intermediate', 'amount' => '550', 'duration' => '8 Weeks'],
                ],
                'one_on_one' => [
                    ['level' => 'Beginner', 'amount' => '650', 'duration' => '8 Weeks'],
                    ['level' => 'Advanced', 'amount' => '950', 'duration' => '8 Weeks'],
                ],
            ]),
        ]);

        $this->assertTrue($course->hasMultipleFeeOptions());
        $options = $course->getFeeOptions();
        $this->assertCount(4, $options);

        $this->assertEquals(350.00, $course->getNumericFeeForOption('Beginner', 'group'));
        $this->assertEquals(550.00, $course->getNumericFeeForOption('Intermediate', 'group'));
        $this->assertEquals(650.00, $course->getNumericFeeForOption('Beginner', 'one_on_one'));
        $this->assertEquals(950.00, $course->getNumericFeeForOption('Advanced', 'one_on_one'));
    }

    public function test_checkout_resolves_mode_and_level_and_records_in_payment(): void
    {
        $course = Course::create([
            'title' => 'Full-Stack Mastery',
            'code' => 'FS101',
            'fees' => json_encode([
                ['category' => 'Group', 'level' => 'Beginner', 'amount' => '350'],
                ['category' => 'One-On-One', 'level' => 'Advanced', 'amount' => '950'],
            ]),
            'is_active' => true,
        ]);

        // Checkout page with One-On-One Advanced
        $response = $this->get(route('checkout.show', [
            $course,
            'track' => 'Advanced',
            'mode' => 'one_on_one',
        ]));

        $response->assertStatus(200);
        $response->assertSee('950.00');
        $response->assertSee('Full-Stack Mastery');

        // Submit checkout with One-On-One Advanced
        $postResponse = $this->post(route('checkout.process', $course), [
            'name' => 'Kabwe Chisanga',
            'email' => 'kabwe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'track' => 'Advanced',
            'mode' => 'one_on_one',
            'payment_method' => 'mobile_money',
            'provider' => 'airtel',
            'phone_number' => '0977112233',
        ]);

        $payment = Payment::where('reference', 'LIKE', 'TH-PAY-%')->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(950.00, (float) $payment->amount);
        $this->assertEquals('Advanced', $payment->metadata['track']);
        $this->assertEquals('one_on_one', $payment->metadata['mode']);
    }

    public function test_courses_page_renders_course_selection_modal(): void
    {
        Course::create([
            'title' => 'Graphic Design Masterclass',
            'code' => 'DSGN101',
            'fees' => json_encode([
                ['category' => 'Group', 'level' => 'Beginner', 'amount' => '400'],
                ['category' => 'One-On-One', 'level' => 'Beginner', 'amount' => '700'],
            ]),
            'is_active' => true,
        ]);

        $response = $this->get(route('landing.courses'));
        $response->assertStatus(200);
        $response->assertSee('course-option-modal');
        $response->assertSee('Graphic Design Masterclass');
    }
}
