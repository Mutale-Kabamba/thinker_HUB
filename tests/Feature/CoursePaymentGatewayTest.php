<?php

namespace Tests\Feature;

use App\Mail\PaymentReceiptMail;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CoursePaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

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
        $response->assertSee('Card (Visa/MC)');
    }

    public function test_student_can_simulate_mobile_money_payment_and_activate_enrollment(): void
    {
        Mail::fake();

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

        $this->assertAuthenticated();

        $student = User::where('email', 'mwila@example.com')->first();
        $this->assertNotNull($student);
        $this->assertTrue((bool) $student->is_active);
        $this->assertEquals('student', $student->role);
        $this->assertEquals('Intermediate', $student->track);

        $payment = Payment::where('user_id', $student->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals('mobile_money', $payment->payment_method);
        $this->assertEquals('airtel', $payment->provider);
        $this->assertEquals(1200.00, (float) $payment->amount);

        $response->assertRedirect(route('payment.receipt', $payment->reference));

        Mail::assertSent(PaymentReceiptMail::class, function (PaymentReceiptMail $mail) use ($student) {
            return $mail->hasTo($student->email);
        });
    }

    public function test_student_can_simulate_card_payment_and_activate_enrollment(): void
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
        ]);

        $student = User::where('email', 'chileshe@example.com')->first();
        $this->assertNotNull($student);
        $this->assertTrue((bool) $student->is_active);

        $payment = Payment::where('user_id', $student->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('card', $payment->payment_method);

        $response->assertRedirect(route('payment.receipt', $payment->reference));
    }

    public function test_authenticated_student_can_pay_for_course_and_view_receipt(): void
    {
        Mail::fake();

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
            'payment_method' => 'demo',
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
}
