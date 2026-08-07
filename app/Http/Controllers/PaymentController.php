<?php

namespace App\Http\Controllers;

use App\Mail\NewStudentRegistrationAlertMail;
use App\Mail\PaymentReceiptMail;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Show the interactive checkout / simulated payment gateway for a course.
     */
    public function showCheckout(Request $request, Course $course): View
    {
        $course->loadMissing(['instructors:id,name,email,whatsapp']);

        $feeAmount = $course->getNumericFee();
        if ($feeAmount <= 0) {
            $feeAmount = 1500.00; // Standard nominal fee in ZMW if not configured
        }

        return view('pages.checkout', [
            'course' => $course,
            'feeAmount' => $feeAmount,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Process simulated payment transaction and activate enrollment.
     */
    public function processPayment(Request $request, Course $course): JsonResponse|RedirectResponse
    {
        $currentUser = Auth::user();

        $rules = [
            'track' => ['required', 'in:Beginner,Intermediate,Advanced'],
            'payment_method' => ['required', 'in:mobile_money,card,bank_transfer,demo'],
            'provider' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'card_number' => ['nullable', 'string', 'max:30'],
            'card_holder' => ['nullable', 'string', 'max:100'],
        ];

        if (! $currentUser) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        // Resolve or create student user
        if ($currentUser) {
            $student = $currentUser;
            if (empty($student->track)) {
                $student->update(['track' => $validated['track']]);
            }
        } else {
            $student = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'track' => $validated['track'],
                'role' => 'student',
                'is_active' => true, // Instant activation via payment
                'email_verified_at' => now(), // Automatically verified upon payment
                'password' => Hash::make($validated['password']),
            ]);

            Auth::login($student);
        }

        // Activate student status
        if (! $student->is_active) {
            $student->update(['is_active' => true]);
        }

        // Create or get enrollment
        $enrollment = Enrollment::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $feeAmount = $course->getNumericFee();
        if ($feeAmount <= 0) {
            $feeAmount = 1500.00;
        }

        // Generate clean transaction reference code
        $reference = 'TH-PAY-' . date('Y') . '-' . strtoupper(Str::random(6));

        // Create Payment record
        $payment = Payment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_id' => $enrollment->id,
            'amount' => $feeAmount,
            'currency' => 'ZMW',
            'payment_method' => $validated['payment_method'],
            'provider' => $validated['provider'] ?? ($validated['payment_method'] === 'mobile_money' ? 'Airtel/MTN' : 'Visa'),
            'phone_number' => $validated['phone_number'] ?? null,
            'reference' => $reference,
            'status' => 'completed',
            'paid_at' => now(),
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'gateway' => 'Thinker HUB Interactive Simulator v2.0',
                'simulated_at' => now()->toIso8601String(),
                'track' => $validated['track'],
            ],
        ]);

        // Send payment receipt to student
        try {
            Mail::to($student->email)->send(new PaymentReceiptMail($student, $course, $payment));
        } catch (\Throwable $e) {
            Log::error('Failed to send payment receipt email: ' . $e->getMessage());
        }

        // Notify admins about successful enrollment and payment
        try {
            $adminEmail = config('mail.admin_alert_to', 'thinkerhub@oristudiozm.com');
            Mail::to($adminEmail)->send(new NewStudentRegistrationAlertMail($student, $course, false));
        } catch (\Throwable $e) {
            Log::error('Failed to notify admin about paid enrollment: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment approved and enrollment confirmed!',
                'reference' => $payment->reference,
                'redirect_url' => route('payment.receipt', $payment->reference),
            ]);
        }

        return redirect()->route('payment.receipt', $payment->reference)
            ->with('payment_success', 'Congratulations! Your enrollment payment has been confirmed.');
    }

    /**
     * Show official payment confirmation receipt.
     */
    public function showReceipt(string $reference): View
    {
        $payment = Payment::with(['user', 'course', 'enrollment'])
            ->where('reference', $reference)
            ->firstOrFail();

        return view('pages.receipt', [
            'payment' => $payment,
            'course' => $payment->course,
            'student' => $payment->user,
        ]);
    }
}
