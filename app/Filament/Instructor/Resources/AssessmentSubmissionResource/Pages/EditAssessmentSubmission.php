<?php

namespace App\Filament\Instructor\Resources\AssessmentSubmissionResource\Pages;

use App\Filament\Instructor\Resources\AssessmentSubmissionResource\AssessmentSubmissionResource;
use App\Notifications\SubmissionGradedNotification;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditAssessmentSubmission extends BaseEditRecord
{
    protected static string $resource = AssessmentSubmissionResource::class;

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->user && in_array($record->status, ['Graded', 'Checked', 'Returned'])) {
            $record->user->notify(new SubmissionGradedNotification(
                'assessment',
                (string) $record->assessment?->name,
                $record->score,
                (string) ($record->feedback ?: 'Your assessment has been graded.'),
            ));
        }
    }
}
