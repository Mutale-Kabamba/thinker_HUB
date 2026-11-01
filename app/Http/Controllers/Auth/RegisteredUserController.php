<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewStudentRegistrationAlertMail;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Support\PaymentApprovalMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
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
        $hasOpenEnrollmentColumn = Schema::hasColumn('courses', 'is_open_enrollment');
        $columns = ['id', 'title', 'code', 'fees', 'timeline'];

        if ($hasOpenEnrollmentColumn) {
            $columns[] = 'is_open_enrollment';
        }

        $courses = Course::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->with(['instructors:id,name,email,whatsapp'])
            ->get($columns)
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
        // Case: Parent account registration linking an existing registered child by unique code
        // Skips kid info and skips payment because admin registered the child manually (already paid)
        if ($request->input('account_type') === 'parent' || $request->filled('kid_unique_code')) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'kid_unique_code' => ['required', 'string', 'max:50'],
                'relationship' => ['required', 'string', 'max:50'],
                'phone_number' => ['nullable', 'string', 'max:30'],
                'occupation' => ['nullable', 'string', 'max:150'],
                'company' => ['nullable', 'string', 'max:150'],
                'address' => ['nullable', 'string', 'max:255'],
                'accept_terms' => ['accepted'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $kid = User::findKidByCodeOrEmail($request->kid_unique_code);

            if (! $kid) {
                throw ValidationException::withMessages([
                    'kid_unique_code' => 'No enrolled child was found matching code "' . $request->kid_unique_code . '". Please verify the unique code provided by the administrator.',
                ]);
            }

            $kid->update(['is_kid' => true]);

            $parent = User::create([
                'name' => trim((string) $request->name),
                'email' => strtolower(trim((string) $request->email)),
                'password' => $request->password,
                'role' => 'parent',
                'default_portal' => 'parent',
                'is_kid' => false,
                'is_active' => true,
                'phone_number' => $request->phone_number ? trim((string) $request->phone_number) : null,
                'whatsapp' => $request->phone_number ? trim((string) $request->phone_number) : null,
                'occupation' => $request->occupation ? trim((string) $request->occupation) : null,
                'company' => $request->company ? trim((string) $request->company) : null,
                'employer' => $request->company ? trim((string) $request->company) : null,
                'address' => $request->address ? trim((string) $request->address) : null,
                'email_verified_at' => now(),
            ]);

            $parent->kids()->syncWithoutDetaching([
                $kid->id => [
                    'relationship' => trim((string) $request->relationship),
                    'school' => $kid->school,
                    'grade' => $kid->grade,
                    'age' => $kid->age,
                    'notes' => 'Linked by parent upon registration using code ' . $kid->unique_code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            Auth::login($parent);

            return redirect()->route('filament.parent.pages.manage-kids')
                ->with('status', 'Welcome to Thinker HUB! Your parent account has been created and successfully linked to ' . $kid->name . '.');
        }

        $hasOpenEnrollmentColumn = Schema::hasColumn('courses', 'is_open_enrollment');

        $courseExistsRule = Rule::exists('courses', 'id')
            ->where('is_active', true);

        if ($hasOpenEnrollmentColumn) {
            $courseExistsRule = $courseExistsRule->where(fn ($query) => $query
                ->where('is_open_enrollment', true)
                ->orWhereNull('is_open_enrollment'));
        }

        $validLevels = array_keys(\App\Filament\Resources\Courses\Schemas\CourseForm::getLevelOptions());

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'course_id' => [
                'required',
                $courseExistsRule,
            ],
            'track' => ['required', 'string', 'in:'.implode(',', $validLevels)],
            'mode' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other,male,female,other'],
            'nrc_passport' => ['nullable', 'string', 'max:50'],
            'accept_terms' => ['accepted'],
            'accept_requirements' => ['accepted'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $courseQuery = Course::query()
            ->whereKey((int) $request->integer('course_id'))
            ->where('is_active', true);

        if ($hasOpenEnrollmentColumn) {
            $courseQuery->where(fn ($query) => $query
                ->where('is_open_enrollment', true)
                ->orWhereNull('is_open_enrollment'));
        }

        $course = $courseQuery->firstOrFail();

        $selectedTrack = $request->string('track')->toString() ?: 'Beginner';
        $selectedMode = $request->string('mode')->toString() ?: null;
        $checkoutParams = array_filter([
            'course' => $course,
            'track' => $selectedTrack,
            'mode' => $selectedMode,
        ], fn ($v) => $v !== null && $v !== '');

        if ($course->is_for_kids) {
            $request->session()->put('pending_registration', [
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'gender' => $request->gender ? ucfirst(strtolower($request->gender)) : null,
                'nrc_passport' => $request->nrc_passport ? trim((string) $request->nrc_passport) : null,
                'password' => $request->string('password')->toString(),
                'track' => $selectedTrack,
                'mode' => $selectedMode,
                'course_id' => $course->id,
            ]);

            return redirect()->route('checkout.show', $checkoutParams)
                ->with('status', 'Complete your child details and enrollment to activate parent and learner accounts.');
        }

        $requiresPaymentApproval = $course->requiresPaymentApproval();

        if ($requiresPaymentApproval) {
            $request->session()->put('pending_registration', [
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'gender' => $request->gender ? ucfirst(strtolower($request->gender)) : null,
                'nrc_passport' => $request->nrc_passport ? trim((string) $request->nrc_passport) : null,
                'password' => $request->string('password')->toString(),
                'track' => $selectedTrack,
                'mode' => $selectedMode,
                'course_id' => $course->id,
            ]);

            return redirect()->route('checkout.show', $checkoutParams)
                ->with('status', 'Complete your course enrollment payment to activate your account.');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender ? ucfirst(strtolower($request->gender)) : null,
            'nrc_passport' => $request->nrc_passport ? trim((string) $request->nrc_passport) : null,
            'track' => $request->string('track')->toString() ?: 'Beginner',
            'role' => 'student',
            'is_active' => true,
            'password' => Hash::make($request->password),
        ]);

        Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $this->notifyAdminsAboutNewStudent($user, $course, false);

        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                Log::error('Failed to send registration verification email.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $exception->getMessage(),
                ]);
            }
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
            Mail::to($recipients)->queue(new NewStudentRegistrationAlertMail(
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
