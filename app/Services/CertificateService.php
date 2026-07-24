<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Full certificate eligibility rule: enrolled + 100% progress (every
     * active quiz passed, every visible assignment and assessment submitted;
     * zero-content courses are locked until the instructor adds activities)
     * + attendance of at least 75% (present/late) whenever the course has
     * sessions. Returns the verdict with human-readable lock reasons.
     *
     * @return array{eligible: bool, progress: array<string, mixed>, attendance: array<string, mixed>, reasons: list<string>}
     */
    public function eligibility(User $user, Course $course): array
    {
        $progress = $user->courseProgress($course);
        $attendance = $user->courseAttendance($course);

        $reasons = [];

        if (! $progress['enrolled']) {
            $reasons[] = 'Enroll in the course first';
        } elseif (! $progress['has_content']) {
            $reasons[] = 'No gradable content yet — the certificate unlocks once the instructor adds activities';
        } elseif (! $progress['complete']) {
            $reasons[] = "Complete all activities ({$progress['items_done']}/{$progress['items_total']})";
        }

        if (! $attendance['ok']) {
            $reasons[] = "Reach 75% attendance (currently {$attendance['percent']}%)";
        }

        return [
            'eligible' => $progress['complete'] && $attendance['ok'],
            'progress' => $progress,
            'attendance' => $attendance,
            'reasons' => $reasons,
        ];
    }

    /**
     * Idempotently issue a certificate for a completed course.
     * Already-issued certificates are always returned (grandfathered), even
     * if the student no longer meets the rules. Returns null when the
     * student is not eligible.
     */
    public function issue(User $user, Course $course): ?Certificate
    {
        $existing = Certificate::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        if (! $this->eligibility($user, $course)['eligible']) {
            return null;
        }

        try {
            return Certificate::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ],
                [
                    'verification_code' => $this->generateVerificationCode(),
                    'issued_at' => now(),
                ],
            );
        } catch (QueryException $e) {
            // Concurrent issue hit the unique(user_id, course_id) constraint —
            // the other request's row wins.
            report($e);

            return Certificate::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
        }
    }

    private function generateVerificationCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (Certificate::query()->where('verification_code', $code)->exists());

        return $code;
    }
}
