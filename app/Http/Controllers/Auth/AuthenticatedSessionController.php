<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Course;
use App\Support\PaymentApprovalMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $courses = Course::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->with(['instructors:id,name,email,whatsapp'])
            ->get(['id', 'title', 'code', 'fees', 'is_open_enrollment'])
            ->map(function (Course $course): Course {
                $requiresPayment = $course->requiresPaymentApproval();

                $course->setAttribute('requires_payment_approval', $requiresPayment);
                $course->setAttribute(
                    'payment_contact_message',
                    $requiresPayment ? PaymentApprovalMessage::forCourse($course) : ''
                );

                return $course;
            });

        return view('auth.login', [
            'courses' => $courses,
            'prefilledEmail' => $request->string('email')->toString(),
            'prefilledPassword' => (string) $request->session()->get('prefill_password', ''),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
