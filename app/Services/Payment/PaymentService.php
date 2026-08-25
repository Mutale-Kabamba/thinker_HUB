<?php

namespace App\Services\Payment;

use App\Mail\NewStudentRegistrationAlertMail;
use App\Mail\PaymentReceiptMail;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PaymentService
{
    public function __construct(
        protected BroadPayGateway $gateway
    ) {}

    /**
     * Process course checkout pipeline.
     */
    public function processCheckout(Course $course, array $validated, Request $request): PaymentResult
    {
        return DB::transaction(function () use ($course, $validated, $request) {
            $currentUser = Auth::user();
            $guestData = null;
            $userId = null;
            $enrollmentId = null;
            $customerName = '';

            // 1. Resolve student if logged in, otherwise hold guest credentials without creating user yet
            if ($currentUser) {
                $student = $currentUser;
                if (empty($student->track)) {
                    $student->update(['track' => $validated['track']]);
                }
                $userId = $student->id;
                $customerName = $student->name;

                // Resolve Course Enrollment for existing user
                $activeIntakeId = $course->activeIntake?->id;
                $enrollment = Enrollment::firstOrCreate(
                    [
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'course_intake_id' => $activeIntakeId,
                    ]
                );

                if ($activeIntakeId && ! $enrollment->course_intake_id) {
                    $enrollment->update(['course_intake_id' => $activeIntakeId]);
                }
                $enrollmentId = $enrollment->id;
            } else {
                // DO NOT create user or enrollment in database yet
                $customerName = $validated['name'];
                $guestData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'track' => $validated['track'] ?? 'Beginner',
                    'phone_number' => $validated['phone_number'] ?? null,
                ];
            }

            // 2. Compute Fee
            $track = $validated['track'] ?? null;
            $feeAmount = $course->getNumericFeeForLevel($track);
            if ($feeAmount <= 0) {
                $feeAmount = $course->getNumericFee();
            }
            if ($feeAmount <= 0) {
                $feeAmount = 1500.00;
            }

            // 3. Generate unique payment reference
            $reference = 'TH-PAY-' . date('Y') . '-' . strtoupper(Str::random(6));

            $paymentMethod = $validated['payment_method'];
            $provider = $validated['provider'] ?? ($paymentMethod === 'mobile_money' ? 'airtel' : 'visa');

            // 4. Initialize Payment Record (user_id is nullable for guests until paid)
            $payment = Payment::create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'enrollment_id' => $enrollmentId,
                'amount' => $feeAmount,
                'currency' => config('broadpay.currency', 'ZMW'),
                'payment_method' => $paymentMethod,
                'provider' => $provider,
                'phone_number' => $validated['phone_number'] ?? null,
                'reference' => $reference,
                'status' => 'pending',
                'metadata' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'gateway' => 'Lenco by BroadPay',
                    'track' => $validated['track'],
                    'initiated_at' => now()->toIso8601String(),
                    'guest_data' => $guestData,
                ],
            ]);

            // 5. Dispatch to Gateway
            if ($paymentMethod === 'mobile_money') {
                $result = $this->gateway->initiateMobileMoney(
                    payment: $payment,
                    phone: (string) ($validated['phone_number'] ?? ''),
                    provider: $provider
                );
            } else {
                $result = $this->gateway->initiateCard(
                    payment: $payment,
                    cardDetails: [
                        'card_number' => $validated['card_number'] ?? '',
                        'card_holder' => $validated['card_holder'] ?? $customerName,
                        'card_expiry' => $validated['card_expiry'] ?? '',
                        'card_cvv' => $validated['card_cvv'] ?? '',
                    ]
                );
            }

            // 6. Update Payment Record with Gateway Response
            $paymentMetadata = array_merge($payment->metadata ?? [], [
                'gateway_reference' => $result->gatewayReference,
                'gateway_message' => $result->message,
                'gateway_data' => $result->data,
            ]);

            if ($result->isCompleted()) {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'metadata' => $paymentMetadata,
                ]);

                $this->fulfillPayment($payment);
            } elseif ($result->status === 'failed') {
                $payment->update([
                    'status' => 'failed',
                    'metadata' => $paymentMetadata,
                ]);
            } else {
                $payment->update([
                    'status' => 'pending',
                    'metadata' => $paymentMetadata,
                ]);
            }

            return $result;
        });
    }

    /**
     * Complete and fulfill a verified payment (idempotent).
     * Creates student account and course enrollment only upon verified payment completion.
     */
    public function fulfillPayment(Payment $payment, array $gatewayData = []): void
    {
        if ($payment->isCompleted() && $payment->paid_at && ! empty($payment->metadata['fulfillment_dispatched_at'])) {
            return;
        }

        $student = $payment->user;
        $course = $payment->course;
        $guestData = $payment->metadata['guest_data'] ?? null;

        // 1. If guest payment, create user account now that payment has succeeded
        if (! $student && $guestData) {
            $student = User::where('email', $guestData['email'])->first();

            if (! $student) {
                $student = User::create([
                    'name' => $guestData['name'],
                    'email' => $guestData['email'],
                    'password' => $guestData['password'],
                    'track' => $guestData['track'] ?? 'Beginner',
                    'role' => 'student',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            } else {
                $student->update([
                    'is_active' => true,
                    'email_verified_at' => $student->email_verified_at ?? now(),
                    'track' => $student->track ?: ($guestData['track'] ?? 'Beginner'),
                ]);
            }

            $payment->user_id = $student->id;

            // Auto-login newly created student in current session if request context exists
            if (! Auth::check()) {
                Auth::login($student);
            }
        }

        if ($student && ! $student->is_active) {
            $student->update(['is_active' => true]);
        }

        // 2. Resolve Enrollment
        if ($student && $course) {
            $activeIntakeId = $course->activeIntake?->id;
            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                ],
                [
                    'course_intake_id' => $activeIntakeId,
                ]
            );

            if ($activeIntakeId && ! $enrollment->course_intake_id) {
                $enrollment->update(['course_intake_id' => $activeIntakeId]);
            }
            $payment->enrollment_id = $enrollment->id;
        }

        $metadata = array_merge($payment->metadata ?? [], [
            'fulfillment_dispatched_at' => now()->toIso8601String(),
            'webhook_data' => $gatewayData,
        ]);

        $payment->update([
            'user_id' => $payment->user_id,
            'enrollment_id' => $payment->enrollment_id,
            'status' => 'completed',
            'paid_at' => $payment->paid_at ?? now(),
            'metadata' => $metadata,
        ]);

        // 3. Send payment receipt to student
        try {
            if ($student && ! empty($student->email)) {
                Mail::to($student->email)->send(new PaymentReceiptMail($student, $course, $payment));
            }
        } catch (Throwable $e) {
            Log::error('Failed to dispatch payment receipt email: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'email' => $student?->email,
            ]);
        }

        // 4. Send admin notification
        try {
            $adminEmail = config('mail.admin_alert_to', 'thinkerhub@oristudiozm.com');
            if ($student && $course) {
                Mail::to($adminEmail)->send(new NewStudentRegistrationAlertMail($student, $course, false));
            }
        } catch (Throwable $e) {
            Log::error('Failed to notify admin about paid enrollment: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
            ]);
        }
    }

    /**
     * Poll/Query payment status.
     */
    public function checkStatus(string $reference): array
    {
        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            return [
                'found' => false,
                'status' => 'not_found',
                'message' => 'Payment reference not found.',
            ];
        }

        // If pending, verify with BroadPay gateway
        if ($payment->status === 'pending') {
            $gatewayResult = $this->gateway->verifyTransaction($reference);
            if ($gatewayResult->isCompleted()) {
                $this->fulfillPayment($payment, $gatewayResult->data);
                $payment->refresh();
            } elseif ($gatewayResult->status === 'failed') {
                $payment->update(['status' => 'failed']);
            }
        }

        // Auto-login user if completed and not currently authenticated
        if ($payment->isCompleted() && $payment->user && ! Auth::check()) {
            Auth::login($payment->user);
        }

        return [
            'found' => true,
            'status' => $payment->status,
            'is_completed' => $payment->isCompleted(),
            'reference' => $payment->reference,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'redirect_url' => $payment->isCompleted() ? route('payment.receipt', $payment->reference) : null,
        ];
    }

    /**
     * Process incoming Lenco / BroadPay webhook.
     */
    public function processWebhook(Request $request): array
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('X-BroadPay-Signature') ?? $request->header('X-Lenco-Signature') ?? $request->header('X-Signature');

        if (! $this->gateway->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('BroadPay Webhook: Invalid signature received.');

            return ['success' => false, 'status' => 401, 'message' => 'Invalid webhook signature.'];
        }

        $payload = $request->json()->all();
        Log::info('BroadPay Webhook received:', $payload);

        $event = strtolower((string) ($payload['event'] ?? $payload['type'] ?? ''));
        $data = $payload['data'] ?? $payload;
        $reference = $data['reference'] ?? $data['external_reference'] ?? $data['tx_ref'] ?? null;

        if (! $reference) {
            return ['success' => false, 'status' => 400, 'message' => 'Missing transaction reference.'];
        }

        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            Log::warning('BroadPay Webhook: Payment reference not found in database: ' . $reference);

            return ['success' => false, 'status' => 404, 'message' => 'Payment reference not found.'];
        }

        $status = strtolower((string) ($data['status'] ?? $event));

        if (in_array($status, ['success', 'successful', 'completed', 'paid', 'charge.success', 'collection.successful'], true)) {
            $this->fulfillPayment($payment, $payload);

            return ['success' => true, 'status' => 200, 'message' => 'Payment fulfilled successfully.'];
        }

        if (in_array($status, ['failed', 'cancelled', 'charge.failed', 'collection.failed'], true)) {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge($payment->metadata ?? [], ['webhook_failure' => $payload]),
            ]);

            return ['success' => true, 'status' => 200, 'message' => 'Payment marked failed.'];
        }

        return ['success' => true, 'status' => 200, 'message' => 'Webhook received and recorded.'];
    }
}
