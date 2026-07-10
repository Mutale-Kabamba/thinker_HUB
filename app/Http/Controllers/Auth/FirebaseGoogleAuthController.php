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
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;
use Throwable;

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

        $serviceAccount = $this->resolveServiceAccountCredentials(
            (string) config('services.firebase.credentials'),
            (string) config('services.firebase.credentials_json'),
            (string) config('services.firebase.credentials_json_base64'),
        );

        if ($serviceAccount === null) {
            return response()->json([
                'message' => 'Firebase server credentials are not configured correctly. Set FIREBASE_CREDENTIALS to a valid JSON file path, or provide FIREBASE_CREDENTIALS_JSON / FIREBASE_CREDENTIALS_JSON_BASE64 in your environment.',
            ], 500);
        }

        try {
            $firebaseAuth = (new Factory())
                ->withServiceAccount($serviceAccount)
                ->createAuth();
        } catch (Throwable) {
            return response()->json([
                'message' => 'Firebase server credentials are not configured correctly. Check FIREBASE_CREDENTIALS / FIREBASE_CREDENTIALS_JSON values on this environment.',
            ], 500);
        }

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
            $courseId = $data['course_id'] ?? null;
            $track = $data['track'] ?? null;
            $acceptedTerms = (bool) ($data['accept_terms'] ?? false);
            $acceptedRequirements = (bool) ($data['accept_requirements'] ?? false);

            if (! $courseId || ! $track || ! $acceptedTerms || ! $acceptedRequirements) {
                return response()->json([
                    'message' => 'Complete enrollment fields (Course, Level, and agreements) before continuing with Google.',
                ], 422);
            }

            $user = User::create([
                'name' => $name !== '' ? $name : Str::before($email, '@'),
                'email' => $email,
                'firebase_uid' => $uid,
                'password' => Str::random(64),
                'role' => 'student',
                'track' => $track,
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

        // Social-authenticated accounts should bypass email verification prompts.
        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => $request->session()->pull('url.intended', route('dashboard', absolute: false)),
            'message' => 'Signed in successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>|string|null
     */
    private function resolveServiceAccountCredentials(
        string $configuredPath,
        string $credentialsJson,
        string $credentialsJsonBase64,
    ): array|string|null
    {
        $inlineJson = $this->parseServiceAccountJson($credentialsJson);

        if ($inlineJson !== null) {
            return $inlineJson;
        }

        $decodedJson = base64_decode(trim($credentialsJsonBase64), true);

        if (is_string($decodedJson)) {
            $decodedCredentials = $this->parseServiceAccountJson($decodedJson);

            if ($decodedCredentials !== null) {
                return $decodedCredentials;
            }
        }

        // Support passing raw JSON in FIREBASE_CREDENTIALS for platforms that do not expose files.
        $configuredJson = $this->parseServiceAccountJson($configuredPath);

        if ($configuredJson !== null) {
            return $configuredJson;
        }

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

    /**
     * @return array<string, mixed>|null
     */
    private function parseServiceAccountJson(string $json): ?array
    {
        $trimmed = trim($json);

        if ($trimmed === '' || ! str_starts_with($trimmed, '{')) {
            return null;
        }

        $decoded = json_decode($trimmed, true);

        if (! is_array($decoded) || ($decoded['type'] ?? null) !== 'service_account') {
            return null;
        }

        return $decoded;
    }
}
