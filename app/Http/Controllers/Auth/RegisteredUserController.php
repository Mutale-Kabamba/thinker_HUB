<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        return view('auth.register', [
            'courses' => Course::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title', 'code']),
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
                Rule::exists('courses', 'id')->where('is_active', true),
            ],
            'track' => ['required', 'in:Beginner,Intermediate,Advanced'],
            'accept_terms' => ['accepted'],
            'accept_requirements' => ['accepted'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'track' => $request->string('track')->toString() ?: 'Beginner',
            'role' => 'student',
            'password' => Hash::make($request->password),
        ]);

        Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => (int) $request->integer('course_id'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
