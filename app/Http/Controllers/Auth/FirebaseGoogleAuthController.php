<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;

class FirebaseGoogleAuthController extends Controller
{
    /**
     * Handle Firebase Google sign-in by verifying ID token server-side.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
            'course_id' => ['nullable', 'integer'],
            'track' => ['nullable', 'in:Beginner,Intermediate,Advanced'],
            'accept_terms' => ['nullable', 'boolean'],
            'accept_requirements' => ['nullable', 'boolean'],
        ]);

        $credentialsPath = $this->resolveCredentialsPath((string) config('services.firebase.credentials'));

        if ($credentialsPath === null) {
            return response()->json([
                'message' => 'Firebase server credentials are not configured correctly. Set FIREBASE_CREDENTIALS to a valid service account JSON path, e.g. storage/app/firebase-service-account.json.',
            ], 500);
        }

        $firebaseAuth = (new Factory())
            ->withServiceAccount($credentialsPath)
            ->createAuth();

        try {
            $verifiedToken = $firebaseAuth->verifyIdToken($data['id_token'], true);
        } catch (FailedToVerifyToken) {
            return response()->json([
                'message' => 'Could not verify Google sign-in token. Please try again.',
            ], 422);
        }

        $claims = $verifiedToken->claims();
        $uid = (string) ($claims->get('sub') ?? '');
        $email = strtolower((string) ($claims->get('email') ?? ''));
        $name = trim((string) ($claims->get('name') ?? ''));

        if ($uid === '' || $email === '') {
            return response()->json([
                'message' => 'Google account is missing required profile data (email/uid).',
            ], 422);
        }

        $user = User::query()
            ->where('firebase_uid', $uid)
            ->orWhere('email', $email)
            ->first();

        $created = false;

        if (! $user) {
            $user = User::create([
                'name' => $name !== '' ? $name : Str::before($email, '@'),
                'email' => $email,
                'firebase_uid' => $uid,
                'password' => Str::random(64),
                'role' => 'student',
                'track' => $data['track'] ?? 'Beginner',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $created = true;
        } else {
            $updateData = [];

            if (! $user->firebase_uid) {
                $updateData['firebase_uid'] = $uid;
            }

            if (! $user->email_verified_at) {
                $updateData['email_verified_at'] = now();
            }

            if ($name !== '' && ($user->name === '' || str_contains($user->name, '@'))) {
                $updateData['name'] = $name;
            }

            if ($updateData !== []) {
                $user->forceFill($updateData)->save();
            }
        }

        if ($user->role === 'student' && $created && isset($data['course_id'])) {
            $course = Course::query()
                ->whereKey((int) $data['course_id'])
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query
                        ->where('is_open_enrollment', true)
                        ->orWhereNull('is_open_enrollment');
                })
                ->first();

            if ($course) {
                Enrollment::firstOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);
            }
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => $request->session()->pull('url.intended', route('dashboard', absolute: false)),
            'message' => 'Signed in successfully.',
        ]);
    }

    private function resolveCredentialsPath(string $configuredPath): ?string
    {
        $candidates = [];

        $trimmed = trim($configuredPath);

        if ($trimmed !== '') {
            $candidates[] = $trimmed;
            $candidates[] = base_path($trimmed);
            $candidates[] = storage_path($trimmed);
        }

        $candidates[] = storage_path('app/firebase-service-account.json');
        $candidates[] = storage_path('app/firebase-admin.json');
        $candidates[] = base_path('firebase-service-account.json');

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
