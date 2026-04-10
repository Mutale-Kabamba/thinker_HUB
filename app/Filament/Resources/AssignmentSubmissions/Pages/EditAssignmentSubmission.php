<?php

namespace App\Filament\Resources\AssignmentSubmissions\Pages;

use App\Filament\Resources\AssignmentSubmissions\AssignmentSubmissionResource;
use App\Notifications\SubmissionGradedNotification;
use Filament\Resources\Pages\EditRecord;

class EditAssignmentSubmission extends EditRecord
{
    protected static string $resource = AssignmentSubmissionResource::class;

    protected function afterSave(): void
    {
        $submission = $this->record;

        if ($submission?->user) {
            $submission->user->notify(new SubmissionGradedNotification(
                'assignment',
                (string) $submission->assignment?->name,
                $submission->grade,
                (string) $submission->feedback,
            ));
        }
    }
}
