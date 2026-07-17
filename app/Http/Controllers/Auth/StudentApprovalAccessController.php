<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class StudentApprovalAccessController extends Controller
{
    public function google(Request $request, User $user, string $hash): RedirectResponse
    {
        if (! hash_equals(sha1((string) $user->email), $hash)) {
            abort(403);
        }

        if ($user->role !== 'student' || ! $user->is_active || blank($user->firebase_uid)) {
            return redirect()->route('login')->with('status', 'This sign-in link is no longer valid.');
        }

        return redirect()
            ->route('login', ['email' => $user->email])
            ->with('status', 'Your account is active. Continue with Google sign-in to access your dashboard.');
    }

    public function manual(User $user, string $token): RedirectResponse
    {
        $expiresAt = $user->pending_login_token_expires_at;

        if (
            blank($user->pending_login_password)
            || blank($user->pending_login_token)
            || ! hash_equals((string) $user->pending_login_token, $token)
            || ! $expiresAt
            || now()->greaterThan($expiresAt)
            || $user->pending_login_token_used_at !== null
        ) {
            return redirect()->route('login')->with('status', 'This login prefill link has expired.');
        }

        try {
            $password = Crypt::decryptString((string) $user->pending_login_password);
        } catch (\Throwable) {
            return redirect()->route('login')->with('status', 'Unable to prepare login details. Please reset your password.');
        }

        $user->forceFill([
            'pending_login_token_used_at' => now(),
        ])->save();

        return redirect()
            ->route('login', ['email' => $user->email])
            ->with('prefill_password', $password)
            ->with('status', 'Your login details were prepared. Sign in to continue.');
    }
}
