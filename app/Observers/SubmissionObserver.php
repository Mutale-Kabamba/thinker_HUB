<?php

namespace App\Observers;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use App\Services\GamificationService;

class SubmissionObserver
{
    /**
     * When a student submits an assignment or assessment, re-check course
     * completion: if this was the last outstanding gradable activity, award
     * completion XP/badge and issue (idempotently) the certificate,
     * notifying once. CertificateService::issue() additionally enforces the
     * 75% attendance rule, so the certificate only lands when both progress
     * and attendance qualify. All calls are idempotent; failures are
     * reported without blocking the submission itself.
     */
    public function created(AssignmentSubmission|AssessmentSubmission $submission): void
    {
        try {
            $user = $submission->user;

            if (! $user) {
                return;
            }

            $course = $submission instanceof AssignmentSubmission
                ? $submission->assignment?->course
                : $submission->assessment?->course;

            if (! $course) {
                return;
            }

            // Includes the enrollment check; false while other gradable
            // activities in the course are still outstanding.
            if (! $user->hasCompletedCourse($course)) {
                return;
            }

            try {
                app(GamificationService::class)->awardCourseCompleted($user, $course);
            } catch (\Throwable $e) {
                report($e);
            }

            $certificate = app(CertificateService::class)->issue($user, $course);

            // Null (attendance gate) or pre-existing certificate — don't notify.
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
