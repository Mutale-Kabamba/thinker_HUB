<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PromoCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Show the checkout page for a course.
     */
    public function showCheckout(Request $request, Course $course): View|RedirectResponse
    {
        if ($course->is_open_enrollment === false) {
            return redirect()->route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)])
                ->with('error', 'Enrollment is currently locked for this course.');
        }

        $course->loadMissing(['instructors:id,name,email,whatsapp']);

        $rawTrack = (string) $request->query('track', $request->query('level', Auth::user()?->track ?? 'Beginner'));
        $selectedLevel = \App\Filament\Resources\Courses\Schemas\CourseForm::normalizeLevel($rawTrack);
        $validLevels = array_keys(\App\Filament\Resources\Courses\Schemas\CourseForm::getLevelOptions());
        if (! in_array($selectedLevel, $validLevels, true)) {
            $selectedLevel = 'Beginner';
        }

        $rawMode = strtolower(trim((string) $request->query('mode', $request->query('category', 'group'))));
        if (in_array($rawMode, ['one_on_one', 'one2one', '1_1', 'private'], true)) {
            $selectedMode = 'one_on_one';
        } elseif (in_array($rawMode, ['self_paced', 'selfpaced', 'self_study'], true)) {
            $selectedMode = 'self_paced';
        } else {
            $selectedMode = 'group';
        }

        $feeOptions = $course->getFeeOptions();
        $explicitModeRequested = $request->hasAny(['mode', 'category']);

        if (! empty($feeOptions)) {
            $matchedOption = null;

            if ($explicitModeRequested) {
                foreach ($feeOptions as $opt) {
                    if ($opt['category'] === $selectedMode && strtolower((string) $opt['level']) === strtolower($selectedLevel)) {
                        $matchedOption = $opt;
                        break;
                    }
                }
            }

            if (! $matchedOption) {
                foreach ($feeOptions as $opt) {
                    if (strtolower((string) $opt['level']) === strtolower($selectedLevel)) {
                        $matchedOption = $opt;
                        break;
                    }
                }
            }

            if (! $matchedOption) {
                $baseReq = strtolower(trim(explode('(', $selectedLevel)[0]));
                foreach ($feeOptions as $opt) {
                    $baseOpt = strtolower(trim(explode('(', (string) $opt['level'])[0]));
                    if ($baseOpt !== '' && $baseOpt === $baseReq) {
                        $matchedOption = $opt;
                        break;
                    }
                }
            }

            if (! $matchedOption) {
                $matchedOption = $feeOptions[0];
            }

            $selectedLevel = (string) $matchedOption['level'];
            $selectedMode = (string) $matchedOption['category'];
            $feeAmount = (float) $matchedOption['amount'];
        } else {
            $feeAmount = $course->getNumericFeeForOption($selectedLevel, $selectedMode);
            if ($feeAmount <= 0) {
                $feeAmount = $course->getNumericFee();
            }
            if ($feeAmount <= 0) {
                $feeAmount = 1500.00;
            }
        }

        $initialPromoCode = strtoupper(trim((string) $request->query('promo', $request->query('code', ''))));

        $existingKidId = $request->query('kid_id', $request->query('existing_kid_id'));
        $existingKid = null;
        if ($existingKidId) {
            $currentUser = Auth::user();
            if ($currentUser) {
                $existingKid = $currentUser->isAdmin()
                    ? User::where('is_kid', true)->find($existingKidId)
                    : $currentUser->kids()->where('users.id', $existingKidId)->where('users.is_kid', true)->first();
            }
        }

        return view('pages.checkout', [
            'course' => $course,
            'isForKids' => (bool) ($course->is_for_kids || $existingKid),
            'existingKid' => $existingKid,
            'feeAmount' => $feeAmount,
            'selectedLevel' => $selectedLevel,
            'selectedMode' => $selectedMode,
            'feeOptions' => $feeOptions,
            'user' => Auth::user(),
            'initialPromoCode' => $initialPromoCode,
        ]);
    }

    /**
     * Validate and preview a promo code for a course via AJAX.
     */
    public function applyPromoCode(Request $request, Course $course, PromoCodeService $promoCodeService): JsonResponse
    {
        $code = (string) $request->input('code', $request->input('promo_code', ''));
        $track = (string) $request->input('track', 'Beginner');
        $mode = (string) $request->input('mode', 'group');
        $email = $request->input('email') ? (string) $request->input('email') : (Auth::user()?->email);

        $feeAmount = $course->getNumericFeeForOption($track, $mode);
        if ($feeAmount <= 0) {
            $feeAmount = $course->getNumericFee();
        }
        if ($feeAmount <= 0) {
            $feeAmount = 1500.00;
        }

        $result = $promoCodeService->validateCode(
            code: $code,
            course: $course,
            amount: $feeAmount,
            user: Auth::user(),
            email: $email
        );

        $status = $result['valid'] ? 200 : 422;

        return response()->json([
            'valid' => $result['valid'],
            'success' => $result['valid'],
            'message' => $result['message'],
            'code' => $result['code'],
            'discount_type' => $result['discount_type'],
            'discount_value' => $result['discount_value'],
            'original_amount' => $result['original_amount'],
            'discount_amount' => $result['discount_amount'],
            'final_amount' => $result['final_amount'],
            'is_free' => $result['is_free'],
            'formatted_discount' => $result['formatted_discount'],
            'formatted_original_amount' => 'ZMW ' . number_format($result['original_amount'], 2),
            'formatted_discount_amount' => 'ZMW ' . number_format($result['discount_amount'], 2),
            'formatted_final_amount' => 'ZMW ' . number_format($result['final_amount'], 2),
        ], $status);
    }

    /**
     * Process payment transaction via Lenco by BroadPay or direct promo enrollment.
     */
    public function processPayment(Request $request, Course $course, PromoCodeService $promoCodeService): JsonResponse|RedirectResponse
    {
        if ($course->is_open_enrollment === false) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Enrollment is currently locked for this course.'], 403);
            }
            return redirect()->back()->with('error', 'Enrollment is currently locked for this course.');
        }

        $currentUser = Auth::user();
        $promoCode = $request->input('promo_code');
        $promoValidation = null;

        if (! empty($promoCode)) {
            $track = (string) $request->input('track', 'Beginner');
            $mode = (string) $request->input('mode', 'group');
            $feeAmount = $course->getNumericFeeForOption($track, $mode);
            if ($feeAmount <= 0) {
                $feeAmount = $course->getNumericFee();
            }
            if ($feeAmount <= 0) {
                $feeAmount = 1500.00;
            }

            $email = $currentUser ? $currentUser->email : (string) $request->input('email');
            $promoValidation = $promoCodeService->validateCode(
                code: (string) $promoCode,
                course: $course,
                amount: $feeAmount,
                user: $currentUser,
                email: $email
            );

            if (! $promoValidation['valid']) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $promoValidation['message'] ?? 'Invalid promo code.',
                    ], 422);
                }
                return redirect()->back()->withErrors(['promo_code' => $promoValidation['message']]);
            }
        }

        $isFree = $promoValidation && $promoValidation['is_free'];

        $validLevels = array_keys(\App\Filament\Resources\Courses\Schemas\CourseForm::getLevelOptions());
        $validLevelsRule = implode(',', $validLevels);

        $rules = [
            'track' => ['required', 'string', 'in:' . $validLevelsRule],
            'mode' => ['nullable', 'string', 'max:50'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'payment_method' => [$isFree ? 'nullable' : 'required', 'string', 'in:mobile_money,card,demo,promo_code'],
            'provider' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'card_number' => ['nullable', 'string', 'max:30'],
            'card_holder' => ['nullable', 'string', 'max:100'],
            'card_expiry' => ['nullable', 'string', 'max:10'],
            'card_cvv' => ['nullable', 'string', 'max:5'],
        ];

        $existingKidId = $request->input('existing_kid_id') ?: $request->input('kid_id');
        $existingKid = null;
        if ($existingKidId && $currentUser) {
            $existingKid = $currentUser->isAdmin()
                ? User::where('is_kid', true)->find($existingKidId)
                : $currentUser->kids()->where('users.id', $existingKidId)->where('users.is_kid', true)->first();
        }

        $isKidsCourse = (bool) ($course->is_for_kids || $request->input('is_for_kids') === '1' || $request->filled('kid_name') || $existingKid);

        if ($isKidsCourse) {
            if ($existingKid) {
                $rules['existing_kid_id'] = ['nullable', 'exists:users,id'];
                $rules['kid_id'] = ['nullable', 'exists:users,id'];
                $rules['relationship'] = ['nullable', 'string', 'max:50'];
            } else {
                $rules['kid_name'] = ['required', 'string', 'max:255'];
                $rules['kid_age'] = ['required', 'integer', 'min:3', 'max:18'];
                $rules['kid_school'] = ['required', 'string', 'max:255'];
                $rules['kid_grade'] = ['required', 'string', 'max:100'];
                $rules['relationship'] = ['required', 'string', 'max:50'];
                $rules['kid_password'] = ['required', 'string', 'min:6'];
            }

            if (! $currentUser) {
                $rules['parent_name'] = ['required', 'string', 'max:255'];
                $rules['parent_email'] = ['required', 'string', 'lowercase', 'email', 'max:255'];
                $rules['parent_password'] = ['required', 'string', 'min:6'];
                $rules['parent_phone'] = ['required', 'string', 'max:30'];
                $rules['parent_occupation'] = ['required', 'string', 'max:150'];
                $rules['parent_company'] = ['required', 'string', 'max:150'];
                $rules['parent_address'] = ['required', 'string', 'max:255'];
            } else {
                $rules['parent_phone'] = ['nullable', 'string', 'max:30'];
                $rules['parent_occupation'] = ['nullable', 'string', 'max:150'];
                $rules['parent_company'] = ['nullable', 'string', 'max:150'];
                $rules['parent_address'] = ['nullable', 'string', 'max:255'];
            }
        } else {
            if (! $currentUser) {
                $rules['name'] = ['required', 'string', 'max:255'];
                $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
                $rules['password'] = ['required', 'confirmed', Password::defaults()];
            }
        }

        $validated = $request->validate($rules);

        if ($existingKid) {
            $validated['existing_kid_id'] = $existingKid->id;
            $validated['kid_name'] = $existingKid->name;
            $validated['kid_id'] = $existingKid->id;
        }

        if ($isFree && empty($validated['payment_method'])) {
            $validated['payment_method'] = 'promo_code';
        }

        $result = $this->paymentService->processCheckout($course, $validated, $request);

        if ($request->wantsJson() || $request->ajax()) {
            if ($result->isRedirect()) {
                return response()->json([
                    'success' => true,
                    'status' => 'redirect',
                    'reference' => $result->reference,
                    'redirect_url' => $result->redirectUrl,
                    'message' => $result->message,
                ]);
            }

            if ($result->isCompleted()) {
                return response()->json([
                    'success' => true,
                    'is_free' => (bool) $isFree,
                    'status' => 'completed',
                    'reference' => $result->reference,
                    'redirect_url' => route('payment.receipt', $result->reference),
                    'message' => $isFree ? 'Free enrollment confirmed with promo code!' : 'Payment approved and enrollment confirmed!',
                ]);
            }

            if ($result->isPending()) {
                return response()->json([
                    'success' => true,
                    'status' => 'pending',
                    'reference' => $result->reference,
                    'redirect_url' => route('payment.receipt', $result->reference),
                    'message' => $result->message ?: 'USSD Prompt sent. Enter PIN on your mobile phone to approve.',
                ]);
            }

            return response()->json([
                'success' => false,
                'status' => 'failed',
                'reference' => $result->reference,
                'message' => $result->message ?: 'Payment transaction failed. Please try again.',
            ], 422);
        }

        if ($result->isRedirect()) {
            return redirect()->away($result->redirectUrl);
        }

        if ($result->isCompleted() || $result->isPending()) {
            return redirect()->route('payment.receipt', $result->reference)
                ->with('payment_success', 'Your enrollment transaction has been initiated.');
        }

        return back()->withErrors(['payment' => $result->message ?: 'Payment processing failed. Please try again.']);
    }

    /**
     * Check real-time payment status (for polling pending USSD approvals).
     */
    public function checkStatus(string $reference): JsonResponse
    {
        $status = $this->paymentService->checkStatus($reference);

        return response()->json($status);
    }

    /**
     * Handle incoming webhook from BroadPay / Lenco.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $result = $this->paymentService->processWebhook($request);

        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * Show official payment confirmation receipt.
     */
    public function showReceipt(string $reference): View
    {
        $payment = Payment::with(['user', 'course', 'enrollment'])
            ->where('reference', $reference)
            ->firstOrFail();

        $student = $payment->user;
        if (! $student && isset($payment->metadata['guest_data'])) {
            $student = (object) [
                'name' => $payment->metadata['guest_data']['name'] ?? 'Student',
                'email' => $payment->metadata['guest_data']['email'] ?? '',
                'track' => $payment->metadata['guest_data']['track'] ?? 'Beginner',
            ];
        }

        return view('pages.receipt', [
            'payment' => $payment,
            'course' => $payment->course,
            'student' => $student,
        ]);
    }
}
