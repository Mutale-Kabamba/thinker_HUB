<?php

namespace App\Observers;

use App\Models\QuizAttempt;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;

class QuizAttemptObserver
{
    /**
     * Auto-issue a certificate when an attempt transitions to passed:
     * attempts are created in-progress and graded later via $attempt->update()
     * (TakeQuiz / Quiz::gradeAttempt), so a false→true `passed` change marks
     * the moment a quiz is passed. If this was the student's last remaining
     * active quiz in the course, issue (idempotently) and notify once.
     */
    public function updated(QuizAttempt $attempt): void
    {
        if (! $attempt->wasChanged('passed') || ! $attempt->passed) {
            return;
        }

        try {
            $course = $attempt->quiz?->course;

            if (! $course) {
                return;
            }

            $user = $attempt->user;

            if (! $user) {
                return;
            }

            // Includes the enrollment check; false when other active
            // quizzes in the course are still unpassed.
            if (! $user->hasCompletedCourse($course)) {
                return;
            }

            $certificate = app(CertificateService::class)->issue($user, $course);

            // Pre-existing certificate (e.g. claimed manually) — don't re-notify.
            if (! $certificate || ! $certificate->wasRecentlyCreated) {
                return;
            }

            try {
                $user->notify(new CertificateIssuedNotification($certificate));
            } catch (\Throwable $e) {
                report($e);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
