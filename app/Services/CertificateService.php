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
     * Idempotently issue a certificate for a completed course.
     * Returns null when the student is not eligible; returns the existing
     * certificate when one was already issued.
     */
    public function issue(User $user, Course $course): ?Certificate
    {
        if (! $user->hasCompletedCourse($course)) {
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
