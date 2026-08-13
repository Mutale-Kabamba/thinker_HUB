<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentService;
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
    public function showCheckout(Request $request, Course $course): View
    {
        $course->loadMissing(['instructors:id,name,email,whatsapp']);

        $selectedLevel = (string) $request->query('track', $request->query('level', Auth::user()?->track ?? 'Beginner'));
        if (! in_array($selectedLevel, ['Beginner', 'Intermediate', 'Advanced'], true)) {
            $selectedLevel = 'Beginner';
        }

        $feeAmount = $course->getNumericFeeForLevel($selectedLevel);
        if ($feeAmount <= 0) {
            $feeAmount = $course->getNumericFee();
        }
        if ($feeAmount <= 0) {
            $feeAmount = 1500.00;
        }

        return view('pages.checkout', [
            'course' => $course,
            'feeAmount' => $feeAmount,
            'selectedLevel' => $selectedLevel,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Process payment transaction via Lenco by BroadPay.
     */
    public function processPayment(Request $request, Course $course): JsonResponse|RedirectResponse
    {
        $currentUser = Auth::user();

        $rules = [
            'track' => ['required', 'in:Beginner,Intermediate,Advanced'],
            'payment_method' => ['required', 'in:mobile_money,card,demo'],
            'provider' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'card_number' => ['nullable', 'string', 'max:30'],
            'card_holder' => ['nullable', 'string', 'max:100'],
            'card_expiry' => ['nullable', 'string', 'max:10'],
            'card_cvv' => ['nullable', 'string', 'max:5'],
        ];

        if (! $currentUser) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

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
                    'status' => 'completed',
                    'reference' => $result->reference,
                    'redirect_url' => route('payment.receipt', $result->reference),
                    'message' => 'Payment approved and enrollment confirmed!',
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
