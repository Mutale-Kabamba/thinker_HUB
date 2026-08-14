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
     * When a student submits an assignment or assessment:
     * 1. Award on-time & early submission XP and evaluate punctuality badges.
     * 2. Re-check course completion & certificate eligibility.
     */
    public function created(AssignmentSubmission|AssessmentSubmission $submission): void
    {
        try {
            $user = $submission->user;

            if (! $user) {
                return;
            }

            try {
                app(GamificationService::class)->awardSubmission($user, $submission);
            } catch (\Throwable $e) {
                report($e);
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

    /**
     * When an instructor updates the grade or score of a submission:
     * Award passing grade, distinction, and perfect score XP & badges.
     */
    public function updated(AssignmentSubmission|AssessmentSubmission $submission): void
    {
        try {
            $user = $submission->user;

            if (! $user) {
                return;
            }

            $isGraded = ($submission instanceof AssignmentSubmission && $submission->wasChanged('grade') && filled($submission->grade))
                || ($submission instanceof AssessmentSubmission && $submission->wasChanged('score') && filled($submission->score));

            if ($isGraded) {
                app(GamificationService::class)->awardGradedSubmission($user, $submission);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
