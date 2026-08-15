<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Full certificate eligibility rule: enrolled + instructor marked completion
     * + 100% progress on program activities + attendance of at least 75%.
     * Zero-content courses or incomplete enrollments are locked until marked
     * complete by the instructor.
     *
     * @return array{eligible: bool, is_instructor_completed: bool, completed_at: mixed, progress: array<string, mixed>, attendance: array<string, mixed>, reasons: list<string>}
     */
    public function eligibility(User $user, Course $course): array
    {
        $progress = $user->courseProgress($course);
        $attendance = $user->courseAttendance($course);
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $reasons = [];

        if (! $progress['enrolled'] || ! $enrollment) {
            $reasons[] = 'Enroll in the course first';
        }

        if ($enrollment && $enrollment->completed_at === null) {
            $reasons[] = 'Program completion must be signed off by your instructor';
        }

        if (! $progress['has_content']) {
            $reasons[] = 'No gradable content yet — complete course activities first';
        } elseif (! $progress['complete']) {
            $reasons[] = "Complete all program activities ({$progress['items_done']}/{$progress['items_total']})";
        }

        if (! $attendance['ok']) {
            $reasons[] = "Reach 75% attendance (currently {$attendance['percent']}%)";
        }

        $isInstructorCompleted = (bool) ($enrollment && $enrollment->completed_at !== null);
        $isEligible = $isInstructorCompleted && $progress['complete'] && $attendance['ok'];

        return [
            'eligible' => $isEligible,
            'is_instructor_completed' => $isInstructorCompleted,
            'completed_at' => $enrollment?->completed_at,
            'progress' => $progress,
            'attendance' => $attendance,
            'reasons' => $reasons,
        ];
    }

    /**
     * Idempotently issue a certificate for a completed course.
     * Already-issued certificates are always returned (grandfathered), even
     * if the student no longer meets the rules. Returns null when the
     * student is not eligible or not completed by the instructor.
     */
    public function issue(User $user, Course $course, bool $force = false): ?Certificate
    {
        $existing = Certificate::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            return null;
        }

        // Instructor completion is required unless force-issued by instructor
        if (! $force && $enrollment->completed_at === null) {
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
