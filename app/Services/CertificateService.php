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
     * Certificate eligibility rule:
     * Enrolled + course completed (either 1. X/X scheduled sessions attended/completed OR 2. Instructor marks as completed)
     *
     * @return array{eligible: bool, is_instructor_completed: bool, is_all_sessions_done: bool, completed_at: mixed, progress: array<string, mixed>, sessions: array<string, mixed>, attendance: array<string, mixed>, reasons: list<string>}
     */
    public function eligibility(User $user, Course $course): array
    {
        $progress = $user->courseProgress($course);
        $sessionsProgress = $user->courseSessionsProgress($course);
        $attendance = $user->courseAttendance($course);
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $reasons = [];

        if (! $enrollment) {
            $reasons[] = 'Enroll in the course first';
        }

        $isCourseCompleted = $sessionsProgress['is_completed'];

        if (! $isCourseCompleted) {
            if ($sessionsProgress['total'] > 0) {
                $reasons[] = "Complete all scheduled sessions ({$sessionsProgress['completed']}/{$sessionsProgress['total']}) or have your instructor mark course completion";
            } else {
                $reasons[] = 'Course completion must be marked by your instructor or all sessions completed';
            }
        }

        $isEligible = (bool) $enrollment && $isCourseCompleted;

        return [
            'eligible' => $isEligible,
            'is_instructor_completed' => $sessionsProgress['is_instructor_completed'],
            'is_all_sessions_done' => $sessionsProgress['is_all_sessions_done'],
            'completed_at' => $enrollment?->completed_at,
            'progress' => $progress,
            'sessions' => $sessionsProgress,
            'attendance' => $attendance,
            'reasons' => $reasons,
        ];
    }

    /**
     * Idempotently issue a certificate for a completed course.
     * Requires the course to be completed (all scheduled sessions done or instructor marked complete).
     */
    public function issue(User $user, Course $course, bool $force = false): ?Certificate
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            return null;
        }

        // Completion is required unless force-issued by instructor
        if (! $force && ! $user->hasCompletedCourse($course)) {
            return null;
        }

        $existing = Certificate::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
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
