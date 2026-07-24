<?php

namespace App\Observers;

use App\Models\QuizAttempt;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use App\Services\GamificationService;

class QuizAttemptObserver
{
    /**
     * On a passed-transition (attempts are created in-progress and graded
     * later via $attempt->update(), so a false→true `passed` change marks
     * the moment a quiz is passed):
     *   1. award quiz XP (+ perfect-score bonus/badge, + streak check),
     *   2. if this was the student's last remaining active quiz in the
     *      course, award completion XP/badge and issue (idempotently) the
     *      certificate, notifying once.
     * All gamification calls are idempotent; failures are reported without
     * blocking the certificate flow.
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

            $gamification = app(GamificationService::class);

            try {
                $gamification->awardQuizPassed($user, $attempt);
                $gamification->evaluateStreak($user);
            } catch (\Throwable $e) {
                report($e);
            }

            // Includes the enrollment check; false when other active
            // quizzes in the course are still unpassed.
            if (! $user->hasCompletedCourse($course)) {
                return;
            }

            try {
                $gamification->awardCourseCompleted($user, $course);
            } catch (\Throwable $e) {
                report($e);
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
