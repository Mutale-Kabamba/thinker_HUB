<?php

namespace App\Filament\Instructor\Resources\AssignmentSubmissionResource\Pages;

use App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource;
use App\Notifications\SubmissionGradedNotification;
use Filament\Resources\Pages\EditRecord;

class EditAssignmentSubmission extends EditRecord
{
    protected static string $resource = AssignmentSubmissionResource::class;

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->user && in_array($record->status, ['Graded', 'Checked', 'Returned'])) {
            $record->user->notify(new SubmissionGradedNotification(
                'assignment',
                (string) $record->assignment?->name,
                $record->grade,
                (string) ($record->feedback ?: 'Your assignment has been graded.'),
            ));
        }
    }
}
