<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\PaymentApprovalMessage;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required_without:login', 'nullable', 'string'],
            'login' => ['required_without:email', 'nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim((string) ($this->input('email') ?: $this->input('login')));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        $candidateUser = User::findKidByCodeOrEmail($loginInput);
        if ($candidateUser) {
            $authenticated = Auth::attempt(['id' => $candidateUser->id, 'password' => $password], $remember);
        } else {
            $authenticated = Auth::attempt(['email' => $loginInput, 'password' => $password], $remember);
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if ($user && $user->role === 'student' && ! $user->is_active) {
            Auth::logout();

            $message = $user instanceof User
                ? PaymentApprovalMessage::forUser($user)
                : 'Online Payment Coming Soon. For this paid course, the registration team will reach out soon.';

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $loginInput = (string) ($this->input('email') ?: $this->input('login'));
        return Str::transliterate(Str::lower($loginInput).'|'.$this->ip());
    }
}
