<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewStudentRegistrationAlertMail;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Support\PaymentApprovalMessage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
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

        return view('auth.register', [
            'courses' => $courses,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')
                    ->where('is_active', true)
                    ->where(fn ($query) => $query
                        ->where('is_open_enrollment', true)
                        ->orWhereNull('is_open_enrollment')),
            ],
            'track' => ['required', 'in:Beginner,Intermediate,Advanced'],
            'accept_terms' => ['accepted'],
            'accept_requirements' => ['accepted'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $course = Course::query()
            ->whereKey((int) $request->integer('course_id'))
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->where('is_open_enrollment', true)
                ->orWhereNull('is_open_enrollment'))
            ->firstOrFail();

        $requiresPaymentApproval = $course->requiresPaymentApproval();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'track' => $request->string('track')->toString() ?: 'Beginner',
            'role' => 'student',
            'is_active' => ! $requiresPaymentApproval,
            'password' => Hash::make($request->password),
        ]);

        Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        if ($requiresPaymentApproval) {
            $user->forceFill([
                'pending_login_password' => Crypt::encryptString($request->string('password')->toString()),
                'pending_login_token' => null,
                'pending_login_token_expires_at' => null,
                'pending_login_token_used_at' => null,
            ])->save();
        }

        $this->notifyAdminsAboutNewStudent($user, $course, $requiresPaymentApproval);

        event(new Registered($user));

        if ($requiresPaymentApproval) {
            return redirect()->route('login')->with('status', PaymentApprovalMessage::forCourse($course));
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function notifyAdminsAboutNewStudent(User $student, Course $course, bool $requiresPaymentApproval): void
    {
        $recipientConfig = (string) config('mail.admin_to', config('mail.contact_to', config('mail.from.address')));
        $recipients = collect(explode(',', $recipientConfig))
            ->map(static fn (string $email): string => trim($email))
            ->filter(static fn (string $email): bool => $email !== '')
            ->values()
            ->all();

        if ($recipients === []) {
            return;
        }

        $instructorContacts = $course->instructors()
            ->orderBy('name')
            ->get(['name', 'email'])
            ->map(static fn (User $instructor): string => trim($instructor->name.' <'.$instructor->email.'>'))
            ->all();

        try {
            Mail::to($recipients)->send(new NewStudentRegistrationAlertMail(
                student: $student,
                course: $course,
                requiresPaymentApproval: $requiresPaymentApproval,
                instructorContacts: $instructorContacts,
            ));
        } catch (\Throwable $exception) {
            Log::error('Failed to send new student registration alert.', [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
