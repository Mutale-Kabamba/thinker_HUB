<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\User;
use App\Models\UtmAttribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class UtmAttributionService
{
    public const COOKIE_NAME = 'thinker_utm';

    /**
     * Extract UTM & referrer payload from the current request (query params, session, or cookie).
     *
     * @return array<string, string|null>|null
     */
    public function extractFromRequest(?Request $request = null): ?array
    {
        $request ??= request();

        if (! $request) {
            return null;
        }

        $queryData = $this->parseQueryParameters($request);
        if ($queryData !== null) {
            return $queryData;
        }

        try {
            if ($request->hasSession() && is_array($request->session()->get(self::COOKIE_NAME))) {
                $sessionData = $request->session()->get(self::COOKIE_NAME);

                return $this->sanitizePayload($sessionData);
            }
        } catch (Throwable) {
            // Session may be unavailable in stateless contexts
        }

        $rawCookie = $request->cookie(self::COOKIE_NAME);
        if (is_string($rawCookie) && $rawCookie !== '') {
            $decoded = json_decode($rawCookie, true);
            if (is_array($decoded)) {
                return $this->sanitizePayload($decoded);
            }
        }

        return null;
    }

    /**
     * Parse UTM parameters directly from an incoming GET request if present.
     *
     * @return array<string, string|null>|null
     */
    public function parseQueryParameters(Request $request): ?array
    {
        $keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
        $found = [];

        foreach ($keys as $key) {
            $val = trim((string) $request->query($key, ''));
            $found[$key] = $val !== '' ? mb_substr($val, 0, 160) : null;
        }

        $hasAnyUtm = count(array_filter($found)) > 0;
        $referrer = trim((string) $request->headers->get('referer', ''));
        $isExternalReferrer = $referrer !== '' && ! str_contains($referrer, $request->getHost());

        if (! $hasAnyUtm && ! $isExternalReferrer) {
            return null;
        }

        return [
            'utm_source' => $found['utm_source'],
            'utm_medium' => $found['utm_medium'],
            'utm_campaign' => $found['utm_campaign'],
            'utm_content' => $found['utm_content'],
            'utm_term' => $found['utm_term'],
            'referrer' => $isExternalReferrer ? mb_substr($referrer, 0, 1000) : null,
            'landing_url' => mb_substr($request->fullUrl(), 0, 1000),
            'captured_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Persist UTM attribution for a newly registered user (non-blocking).
     */
    public function attachToUser(User $user, ?Request $request = null): ?UtmAttribution
    {
        try {
            if (! Schema::hasTable('utm_attributions')) {
                return null;
            }

            $payload = $this->extractFromRequest($request);
            if ($payload === null) {
                return null;
            }

            return UtmAttribution::create([
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'utm_source' => $payload['utm_source'] ?? null,
                'utm_medium' => $payload['utm_medium'] ?? null,
                'utm_campaign' => $payload['utm_campaign'] ?? null,
                'utm_content' => $payload['utm_content'] ?? null,
                'utm_term' => $payload['utm_term'] ?? null,
                'referrer' => $payload['referrer'] ?? null,
                'landing_url' => $payload['landing_url'] ?? null,
                'session_token' => $this->resolveSessionToken($request),
                'captured_at' => $payload['captured_at'] ?? now(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Persist or link UTM attribution when an enrollment is created (non-blocking).
     */
    public function attachToEnrollment(Enrollment $enrollment, ?Request $request = null): ?UtmAttribution
    {
        try {
            if (! Schema::hasTable('utm_attributions')) {
                return null;
            }

            $payload = $this->extractFromRequest($request);

            // Fall back to the user's most recent registration attribution if current request has no cookie
            if ($payload === null && $enrollment->user_id) {
                $existing = UtmAttribution::query()
                    ->where('user_id', $enrollment->user_id)
                    ->latest('id')
                    ->first();

                if ($existing) {
                    if ($existing->enrollment_id === null) {
                        $existing->update([
                            'enrollment_id' => $enrollment->id,
                            'tenant_id' => $enrollment->tenant_id ?? $existing->tenant_id,
                        ]);

                        return $existing;
                    }

                    $payload = [
                        'utm_source' => $existing->utm_source,
                        'utm_medium' => $existing->utm_medium,
                        'utm_campaign' => $existing->utm_campaign,
                        'utm_content' => $existing->utm_content,
                        'utm_term' => $existing->utm_term,
                        'referrer' => $existing->referrer,
                        'landing_url' => $existing->landing_url,
                        'captured_at' => $existing->captured_at?->toIso8601String() ?? now()->toIso8601String(),
                    ];
                }
            }

            if ($payload === null) {
                return null;
            }

            return UtmAttribution::create([
                'user_id' => $enrollment->user_id,
                'enrollment_id' => $enrollment->id,
                'tenant_id' => $enrollment->tenant_id ?? $enrollment->course?->tenant_id ?? $enrollment->user?->tenant_id,
                'utm_source' => $payload['utm_source'] ?? null,
                'utm_medium' => $payload['utm_medium'] ?? null,
                'utm_campaign' => $payload['utm_campaign'] ?? null,
                'utm_content' => $payload['utm_content'] ?? null,
                'utm_term' => $payload['utm_term'] ?? null,
                'referrer' => $payload['referrer'] ?? null,
                'landing_url' => $payload['landing_url'] ?? null,
                'session_token' => $this->resolveSessionToken($request),
                'captured_at' => $payload['captured_at'] ?? now(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string|null>|null
     */
    private function sanitizePayload(array $data): ?array
    {
        $clean = [
            'utm_source' => isset($data['utm_source']) && trim((string) $data['utm_source']) !== '' ? mb_substr(trim((string) $data['utm_source']), 0, 120) : null,
            'utm_medium' => isset($data['utm_medium']) && trim((string) $data['utm_medium']) !== '' ? mb_substr(trim((string) $data['utm_medium']), 0, 120) : null,
            'utm_campaign' => isset($data['utm_campaign']) && trim((string) $data['utm_campaign']) !== '' ? mb_substr(trim((string) $data['utm_campaign']), 0, 160) : null,
            'utm_content' => isset($data['utm_content']) && trim((string) $data['utm_content']) !== '' ? mb_substr(trim((string) $data['utm_content']), 0, 160) : null,
            'utm_term' => isset($data['utm_term']) && trim((string) $data['utm_term']) !== '' ? mb_substr(trim((string) $data['utm_term']), 0, 160) : null,
            'referrer' => isset($data['referrer']) && trim((string) $data['referrer']) !== '' ? mb_substr(trim((string) $data['referrer']), 0, 1000) : null,
            'landing_url' => isset($data['landing_url']) && trim((string) $data['landing_url']) !== '' ? mb_substr(trim((string) $data['landing_url']), 0, 1000) : null,
            'captured_at' => isset($data['captured_at']) ? (string) $data['captured_at'] : now()->toIso8601String(),
        ];

        $hasSignal = (bool) array_filter([
            $clean['utm_source'],
            $clean['utm_medium'],
            $clean['utm_campaign'],
            $clean['referrer'],
        ]);

        return $hasSignal ? $clean : null;
    }

    private function resolveSessionToken(?Request $request): ?string
    {
        try {
            $req = $request ?? request();
            if ($req && $req->hasSession()) {
                return mb_substr((string) $req->session()->getId(), 0, 80);
            }
        } catch (Throwable) {
            // Ignore when running outside HTTP session
        }

        return null;
    }
}
