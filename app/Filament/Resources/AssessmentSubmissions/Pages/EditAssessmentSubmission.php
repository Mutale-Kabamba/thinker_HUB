<?php

namespace App\Filament\Resources\AssessmentSubmissions\Pages;

use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Notifications\SubmissionGradedNotification;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditAssessmentSubmission extends BaseEditRecord
{
    protected static string $resource = AssessmentSubmissionResource::class;

    protected function afterSave(): void
    {
        $submission = $this->record;

        if ($submission?->user) {
            $submission->user->notify(new SubmissionGradedNotification(
                'assessment',
                'Assessment #'.(string) $submission->assessment?->id,
                $submission->score,
                (string) $submission->feedback,
            ));
        }
    }
}
