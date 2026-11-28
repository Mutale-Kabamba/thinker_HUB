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
        protected BroadPayGateway $gateway,
        protected PromoCodeService $promoCodeService,
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

            $isKidsCourse = (bool) ($course->is_for_kids || ! empty($validated['kid_name']) || $request->input('is_for_kids') === '1');
            if ($isKidsCourse && ! $course->is_for_kids) {
                $course->update(['is_for_kids' => true]);
            }
            $kidsData = null;

            if ($isKidsCourse) {
                $existingKidId = $validated['existing_kid_id'] ?? $validated['kid_id'] ?? $request->input('existing_kid_id', $request->input('kid_id'));
                $existingKid = null;
                if ($existingKidId) {
                    $existingKid = User::find($existingKidId);
                }

                $kidsData = [
                    'existing_kid_id' => $existingKid?->id,
                    'kid_name' => $existingKid ? $existingKid->name : ($validated['kid_name'] ?? null),
                    'kid_age' => $existingKid ? (int) $existingKid->age : (int) ($validated['kid_age'] ?? 0),
                    'kid_school' => $existingKid ? $existingKid->school : ($validated['kid_school'] ?? null),
                    'kid_grade' => $existingKid ? $existingKid->grade : ($validated['kid_grade'] ?? null),
                    'relationship' => $validated['relationship'] ?? 'Parent',
                    'kid_password' => $validated['kid_password'] ?? null,
                    'parent_name' => $currentUser ? $currentUser->name : ($validated['parent_name'] ?? null),
                    'parent_email' => $currentUser ? $currentUser->email : ($validated['parent_email'] ?? null),
                    'parent_password' => $validated['parent_password'] ?? null,
                    'parent_phone' => $validated['parent_phone'] ?? ($currentUser?->phone_number),
                    'parent_occupation' => $validated['parent_occupation'] ?? ($currentUser?->occupation),
                    'parent_company' => $validated['parent_company'] ?? ($currentUser?->company),
                    'parent_address' => $validated['parent_address'] ?? ($currentUser?->address),
                ];

                if ($currentUser) {
                    $userId = $currentUser->id;
                    $customerName = $currentUser->name;
                } else {
                    $customerName = $validated['parent_name'];
                    $guestData = [
                        'name' => $validated['parent_name'],
                        'email' => $validated['parent_email'],
                        'password' => $validated['parent_password'] ?? null,
                        'role' => 'parent',
                        'default_portal' => 'parent',
                        'is_kid' => false,
                        'track' => $validated['track'] ?? 'Beginner',
                        'phone_number' => $validated['parent_phone'] ?? null,
                        'occupation' => $validated['parent_occupation'] ?? null,
                        'company' => $validated['parent_company'] ?? null,
                        'address' => $validated['parent_address'] ?? null,
                    ];
                }
                $enrollmentId = null;
            } else {
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
            }

            // 2. Compute Fee
            $track = $validated['track'] ?? null;
            $mode = $validated['mode'] ?? null;
            $feeAmount = $course->getNumericFeeForOption($track, $mode);
            if ($feeAmount <= 0) {
                $feeAmount = $course->getNumericFee();
            }
            if ($feeAmount <= 0) {
                $feeAmount = 1500.00;
            }

            $originalFee = $feeAmount;
            $discountAmount = 0.00;
            $promoCodeModel = null;
            $promoCodeString = ! empty($validated['promo_code']) ? strtoupper(trim((string) $validated['promo_code'])) : null;

            if ($promoCodeString) {
                $promoValidation = $this->promoCodeService->validateCode(
                    code: $promoCodeString,
                    course: $course,
                    amount: $originalFee,
                    user: $currentUser,
                    email: $validated['email'] ?? null
                );

                if (! $promoValidation['valid']) {
                    return PaymentResult::failed(
                        reference: '',
                        message: $promoValidation['message'] ?? 'The provided promo code is invalid or expired.'
                    );
                }

                $promoCodeModel = $promoValidation['promo_code'];
                $discountAmount = $promoValidation['discount_amount'];
                $feeAmount = $promoValidation['final_amount'];
            }

            // 3. Generate unique payment reference
            $reference = 'TH-PAY-' . date('Y') . '-' . strtoupper(Str::random(6));

            // If 100% discount, bypass payment gateway directly
            if ($feeAmount <= 0.00) {
                $payment = Payment::create([
                    'user_id' => $userId,
                    'course_id' => $course->id,
                    'promo_code_id' => $promoCodeModel?->id,
                    'enrollment_id' => $enrollmentId,
                    'amount' => 0.00,
                    'original_amount' => $originalFee,
                    'discount_amount' => $discountAmount,
                    'currency' => config('broadpay.currency', 'ZMW'),
                    'payment_method' => 'promo_code',
                    'provider' => 'promo',
                    'phone_number' => $validated['phone_number'] ?? null,
                    'reference' => $reference,
                    'status' => 'completed',
                    'paid_at' => now(),
                    'registration_type' => 'direct_checkout',
                    'is_free' => true,
                    'metadata' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'gateway' => 'Promo Code Free Enrollment',
                        'track' => $validated['track'] ?? 'Beginner',
                        'mode' => $validated['mode'] ?? 'group',
                        'promo_code' => $promoCodeModel?->code,
                        'discount_amount' => $discountAmount,
                        'original_amount' => $originalFee,
                        'initiated_at' => now()->toIso8601String(),
                        'guest_data' => $guestData,
                        'is_kids_course' => $isKidsCourse,
                        'kids_data' => $kidsData,
                    ],
                ]);

                $this->fulfillPayment($payment);

                return PaymentResult::completed(
                    reference: $reference,
                    message: 'Course enrollment successfully confirmed via promo code!'
                );
            }

            $paymentMethod = $validated['payment_method'] ?? 'mobile_money';
            $provider = $validated['provider'] ?? ($paymentMethod === 'mobile_money' ? 'airtel' : 'visa');

            // 4. Initialize Payment Record (user_id is nullable for guests until paid)
            $payment = Payment::create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'promo_code_id' => $promoCodeModel?->id,
                'enrollment_id' => $enrollmentId,
                'amount' => $feeAmount,
                'original_amount' => $originalFee,
                'discount_amount' => $discountAmount,
                'currency' => config('broadpay.currency', 'ZMW'),
                'payment_method' => $paymentMethod,
                'provider' => $provider,
                'phone_number' => $validated['phone_number'] ?? ($validated['parent_phone'] ?? null),
                'reference' => $reference,
                'status' => 'pending',
                'registration_type' => 'direct_checkout',
                'is_free' => false,
                'metadata' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'gateway' => 'Lenco by BroadPay',
                    'track' => $validated['track'],
                    'mode' => $validated['mode'] ?? 'group',
                    'promo_code' => $promoCodeModel?->code,
                    'discount_amount' => $discountAmount,
                    'original_amount' => $originalFee,
                    'initiated_at' => now()->toIso8601String(),
                    'guest_data' => $guestData,
                    'is_kids_course' => $isKidsCourse,
                    'kids_data' => $kidsData,
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
        $isKidsCourse = (bool) ($payment->metadata['is_kids_course'] ?? ($course?->is_for_kids ?? false));
        $kidsData = $payment->metadata['kids_data'] ?? null;

        // Auto-reconstruct kidsData from metadata/guestData/payment user if missing for a kids course
        if ($isKidsCourse && ! $kidsData) {
            $parentName = $payment->metadata['parent_name'] ?? ($guestData['name'] ?? ($payment->user?->name ?? 'Parent'));
            $parentEmail = $payment->metadata['parent_email'] ?? ($guestData['email'] ?? ($payment->user?->email ?? null));
            $kidName = $payment->metadata['kid_name'] ?? 'Child';

            $kidsData = [
                'parent_name' => $parentName,
                'parent_email' => $parentEmail,
                'parent_password' => $payment->metadata['parent_password'] ?? ($guestData['password'] ?? null),
                'parent_phone' => $payment->metadata['parent_phone'] ?? ($guestData['phone_number'] ?? ($payment->phone ?? null)),
                'parent_occupation' => $payment->metadata['parent_occupation'] ?? ($guestData['occupation'] ?? null),
                'parent_company' => $payment->metadata['parent_company'] ?? ($guestData['company'] ?? null),
                'parent_address' => $payment->metadata['parent_address'] ?? ($guestData['address'] ?? null),
                'kid_name' => $kidName,
                'kid_age' => (int) ($payment->metadata['kid_age'] ?? 8),
                'kid_school' => $payment->metadata['kid_school'] ?? 'Primary School',
                'kid_grade' => $payment->metadata['kid_grade'] ?? 'Grade 1',
                'relationship' => $payment->metadata['relationship'] ?? 'Parent',
                'kid_password' => $payment->metadata['kid_password'] ?? Str::random(10),
            ];
        }

        if ($isKidsCourse && $kidsData) {
            // ── KIDS COURSE FLOW: CREATE/RESOLVE PARENT & KID ACCOUNTS ──
            $parent = $payment->user;
            $parentEmail = $kidsData['parent_email'] ?? ($guestData['email'] ?? null);

            if (! $parent && $parentEmail) {
                $parent = User::where('email', $parentEmail)->first();
                if (! $parent) {
                    $parent = User::create([
                        'name' => $kidsData['parent_name'] ?? ($guestData['name'] ?? 'Parent'),
                        'email' => $parentEmail,
                        'password' => $kidsData['parent_password'] ?? ($guestData['password'] ?? Str::random(12)),
                        'role' => 'parent',
                        'default_portal' => 'parent',
                        'is_kid' => false,
                        'phone_number' => $kidsData['parent_phone'] ?? ($guestData['phone_number'] ?? null),
                        'whatsapp' => $kidsData['parent_phone'] ?? ($guestData['phone_number'] ?? null),
                        'occupation' => $kidsData['parent_occupation'] ?? null,
                        'company' => $kidsData['parent_company'] ?? null,
                        'employer' => $kidsData['parent_company'] ?? null,
                        'address' => $kidsData['parent_address'] ?? null,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);
                } else {
                    $updates = ['is_active' => true, 'is_kid' => false];
                    if (! empty($kidsData['parent_name']) && (empty($parent->name) || in_array($parent->name, ['Student', 'Parent', 'User'], true))) {
                        $updates['name'] = $kidsData['parent_name'];
                    }
                    if (! empty($kidsData['parent_occupation'])) $updates['occupation'] = $kidsData['parent_occupation'];
                    if (! empty($kidsData['parent_company'])) {
                        $updates['company'] = $kidsData['parent_company'];
                        $updates['employer'] = $kidsData['parent_company'];
                    }
                    if (! empty($kidsData['parent_address'])) $updates['address'] = $kidsData['parent_address'];
                    if (! empty($kidsData['parent_phone'])) {
                        $updates['phone_number'] = $kidsData['parent_phone'];
                        $updates['whatsapp'] = $kidsData['parent_phone'];
                    }
                    if ($parent->role !== 'admin') {
                        $updates['role'] = 'parent';
                        $updates['default_portal'] = 'parent';
                    }
                    $parent->update($updates);
                }
            } elseif ($parent) {
                $updates = ['is_active' => true, 'is_kid' => false];
                if (! empty($kidsData['parent_name']) && (empty($parent->name) || in_array($parent->name, ['Student', 'Parent', 'User'], true))) {
                    $updates['name'] = $kidsData['parent_name'];
                }
                if (! empty($kidsData['parent_occupation'])) $updates['occupation'] = $kidsData['parent_occupation'];
                if (! empty($kidsData['parent_company'])) {
                    $updates['company'] = $kidsData['parent_company'];
                    $updates['employer'] = $kidsData['parent_company'];
                }
                if (! empty($kidsData['parent_address'])) $updates['address'] = $kidsData['parent_address'];
                if (! empty($kidsData['parent_phone'])) {
                    $updates['phone_number'] = $kidsData['parent_phone'];
                    $updates['whatsapp'] = $kidsData['parent_phone'];
                }
                if ($parent->role !== 'admin') {
                    $updates['role'] = 'parent';
                    $updates['default_portal'] = 'parent';
                }
                $parent->update($updates);
            }

            $payment->user_id = $parent?->id;

            // 2. Resolve or Create Kid User Account
            $kidName = $kidsData['kid_name'] ?? 'Child';
            $kidAge = (int) ($kidsData['kid_age'] ?? 0);
            $kidSchool = $kidsData['kid_school'] ?? null;
            $kidGrade = $kidsData['kid_grade'] ?? null;
            $relationship = $kidsData['relationship'] ?? 'Guardian';
            $plainKidPassword = $kidsData['kid_password'] ?? null;
            $existingKidId = $kidsData['existing_kid_id'] ?? null;

            $kid = null;
            if ($existingKidId) {
                $kid = $parent
                    ? $parent->kids()->where('users.id', $existingKidId)->where('users.is_kid', true)->first()
                    : User::where('is_kid', true)->where('role', 'student')->find($existingKidId);
            }

            if (! $kid) {
                $uniqueCode = User::generateKidUniqueCode();
                $kidEmail = strtolower($uniqueCode) . '@kids.thinkerhub.ac.zm';

                $kid = User::create([
                    'name' => $kidName,
                    'email' => $kidEmail,
                    'unique_code' => $uniqueCode,
                    'password' => $plainKidPassword ?: Str::random(10), // User model has hashed cast
                    'is_kid' => true,
                    'role' => 'student',
                    'default_portal' => 'student',
                    'age' => $kidAge,
                    'school' => $kidSchool,
                    'grade' => $kidGrade,
                    'track' => $payment->metadata['track'] ?? 'Beginner',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            } else {
                $uniqueCode = $kid->unique_code;
            }

            // 3. Link Kid to Parent
            if ($parent) {
                $parent->kids()->syncWithoutDetaching([
                    $kid->id => [
                        'relationship' => $relationship,
                        'age' => $kidAge ?: $kid->age,
                        'school' => $kidSchool ?: $kid->school,
                        'grade' => $kidGrade ?: $kid->grade,
                        'notes' => 'Enrolled via checkout for ' . ($course?->title ?? 'Kids Course'),
                    ],
                ]);
            }

            // 4. Enroll the Kid into the Course
            if ($course) {
                $activeIntakeId = $course->activeIntake?->id;
                $enrollment = Enrollment::firstOrCreate(
                    [
                        'user_id' => $kid->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'course_intake_id' => $activeIntakeId,
                        'registration_type' => 'direct_checkout',
                        'payment_status' => ((float) $payment->amount <= 0.0 ? 'free' : 'paid'),
                        'is_free' => ((float) $payment->amount <= 0.0),
                        'fee_amount' => (float) $payment->amount,
                        'payment_method' => $payment->payment_method,
                        'transaction_reference' => $payment->reference,
                        'paid_at' => $payment->paid_at ?? now(),
                    ]
                );
                $enrollment->update([
                    'course_intake_id' => $enrollment->course_intake_id ?: $activeIntakeId,
                    'registration_type' => $enrollment->registration_type ?: 'direct_checkout',
                    'payment_status' => ((float) $payment->amount <= 0.0 ? 'free' : 'paid'),
                    'is_free' => ((float) $payment->amount <= 0.0),
                    'fee_amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_reference' => $payment->reference,
                    'paid_at' => $payment->paid_at ?? now(),
                ]);
                $payment->enrollment_id = $enrollment->id;
            }

            // Ensure parent itself is NOT enrolled in the kids course
            if ($parent && $course) {
                Enrollment::where('user_id', $parent->id)->where('course_id', $course->id)->delete();
            }

            // 5. Auto-login or refresh Parent in session
            if ($parent) {
                if (! Auth::check()) {
                    Auth::login($parent);
                } else {
                    Auth::setUser($parent->fresh());
                }
            }

            $metadata = array_merge($payment->metadata ?? [], [
                'fulfillment_dispatched_at' => now()->toIso8601String(),
                'webhook_data' => $gatewayData,
                'kid_id' => $kid->id,
                'kid_unique_code' => $uniqueCode,
                'parent_id' => $parent?->id,
            ]);

            $payment->update([
                'user_id' => $payment->user_id,
                'enrollment_id' => $payment->enrollment_id,
                'status' => 'completed',
                'paid_at' => $payment->paid_at ?? now(),
                'metadata' => $metadata,
            ]);

            // 6. Record Promo Code Usage if applicable
            try {
                $promo = $payment->promoCode ?: ($payment->promo_code_id ? \App\Models\PromoCode::find($payment->promo_code_id) : null);
                if (! $promo && ! empty($payment->metadata['promo_code'])) {
                    $promo = \App\Models\PromoCode::where('code', $payment->metadata['promo_code'])->first();
                }

                if ($promo && $course) {
                    $origAmt = (float) ($payment->original_amount ?? ($payment->metadata['original_amount'] ?? $payment->amount));
                    $discAmt = (float) ($payment->discount_amount ?? ($payment->metadata['discount_amount'] ?? 0.00));
                    $finalAmt = (float) $payment->amount;

                    $this->promoCodeService->recordUsage(
                        promoCode: $promo,
                        course: $course,
                        payment: $payment,
                        originalAmount: $origAmt,
                        discountAmount: $discAmt,
                        finalAmount: $finalAmt,
                        user: $parent,
                        email: $parent?->email
                    );
                }
            } catch (Throwable $e) {
                Log::error('Failed to record promo code usage for kids enrollment: ' . $e->getMessage());
            }

            // 7. Dispatch Kid Account Credentials Mail to Parent for newly created child
            try {
                if ($parent && ! empty($parent->email) && ! empty($plainKidPassword) && empty($existingKidId)) {
                    Mail::to($parent->email)->send(new \App\Mail\KidAccountCredentialsMail(
                        parent: $parent,
                        kid: $kid,
                        course: $course,
                        uniqueCode: $uniqueCode,
                        relationship: $relationship,
                        plainPassword: $plainKidPassword,
                    ));
                }
            } catch (Throwable $e) {
                Log::error('Failed to dispatch KidAccountCredentialsMail: ' . $e->getMessage(), [
                    'payment_id' => $payment->id,
                    'parent_id' => $parent?->id,
                    'kid_id' => $kid->id,
                ]);
            }

            // 8. Send Payment Receipt to Parent
            try {
                if ($parent && ! empty($parent->email)) {
                    Mail::to($parent->email)->send(new PaymentReceiptMail($parent, $course, $payment));
                }
            } catch (Throwable $e) {
                Log::error('Failed to dispatch payment receipt email: ' . $e->getMessage(), [
                    'payment_id' => $payment->id,
                    'email' => $parent?->email,
                ]);
            }

            // 9. Send Admin Notification about kid enrollment
            try {
                $adminEmail = config('mail.admin_alert_to', 'thinkerhub@oristudiozm.com');
                if ($course) {
                    Mail::to($adminEmail)->send(new NewStudentRegistrationAlertMail($kid, $course, false));
                }
            } catch (Throwable $e) {
                Log::error('Failed to notify admin about kid enrollment: ' . $e->getMessage(), [
                    'payment_id' => $payment->id,
                ]);
            }

            return;
        }

        // 1. If guest payment, create user account now that payment has succeeded
        if (! $student && $guestData) {
            $student = User::where('email', $guestData['email'])->first();

            $targetRole = ($isKidsCourse || ($guestData['role'] ?? '') === 'parent') ? 'parent' : 'student';
            $targetPortal = ($isKidsCourse || ($guestData['default_portal'] ?? '') === 'parent') ? 'parent' : 'student';
            $targetIsKid = $isKidsCourse ? false : (bool) ($guestData['is_kid'] ?? false);

            if (! $student) {
                $student = User::create([
                    'name' => $guestData['name'],
                    'email' => $guestData['email'],
                    'password' => $guestData['password'],
                    'track' => $guestData['track'] ?? 'Beginner',
                    'role' => $targetRole,
                    'default_portal' => $targetPortal,
                    'is_kid' => $targetIsKid,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            } else {
                $updates = [
                    'is_active' => true,
                    'email_verified_at' => $student->email_verified_at ?? now(),
                    'track' => $student->track ?: ($guestData['track'] ?? 'Beginner'),
                ];
                if ($isKidsCourse && $student->role !== 'admin') {
                    $updates['role'] = 'parent';
                    $updates['default_portal'] = 'parent';
                    $updates['is_kid'] = false;
                }
                $student->update($updates);
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
                    'registration_type' => 'direct_checkout',
                    'payment_status' => ((float) $payment->amount <= 0.0 ? 'free' : 'paid'),
                    'is_free' => ((float) $payment->amount <= 0.0),
                    'fee_amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_reference' => $payment->reference,
                    'paid_at' => $payment->paid_at ?? now(),
                ]
            );

            $enrollment->update([
                'course_intake_id' => $enrollment->course_intake_id ?: $activeIntakeId,
                'registration_type' => $enrollment->registration_type ?: 'direct_checkout',
                'payment_status' => ((float) $payment->amount <= 0.0 ? 'free' : 'paid'),
                'is_free' => ((float) $payment->amount <= 0.0),
                'fee_amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'transaction_reference' => $payment->reference,
                'paid_at' => $payment->paid_at ?? now(),
            ]);

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

        // Record promo code redemption if promo code was applied
        try {
            $promo = $payment->promoCode ?: ($payment->promo_code_id ? \App\Models\PromoCode::find($payment->promo_code_id) : null);
            if (! $promo && ! empty($payment->metadata['promo_code'])) {
                $promo = \App\Models\PromoCode::where('code', $payment->metadata['promo_code'])->first();
            }

            if ($promo && $course) {
                $origAmt = (float) ($payment->original_amount ?? ($payment->metadata['original_amount'] ?? $payment->amount));
                $discAmt = (float) ($payment->discount_amount ?? ($payment->metadata['discount_amount'] ?? 0.00));
                $finalAmt = (float) $payment->amount;

                $this->promoCodeService->recordUsage(
                    promoCode: $promo,
                    course: $course,
                    payment: $payment,
                    originalAmount: $origAmt,
                    discountAmount: $discAmt,
                    finalAmount: $finalAmt,
                    user: $student,
                    email: $student?->email ?? ($payment->metadata['guest_data']['email'] ?? null)
                );
            }
        } catch (Throwable $e) {
            Log::error('Failed to record promo code usage: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
            ]);
        }

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
