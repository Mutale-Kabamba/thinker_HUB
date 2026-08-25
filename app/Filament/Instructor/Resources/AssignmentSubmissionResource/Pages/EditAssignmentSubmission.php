<?php

namespace App\Filament\Instructor\Resources\AssignmentSubmissionResource\Pages;

use App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource;
use App\Notifications\SubmissionGradedNotification;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditAssignmentSubmission extends BaseEditRecord
{
    protected static string $resource = AssignmentSubmissionResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record && $this->record->viewed_at === null) {
            $this->record->markAsViewed();
        }
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->user && in_array($record->status, ['Graded', 'Checked', 'Returned'])) {
            try {
                $record->user->notify(new SubmissionGradedNotification(
                    'assignment',
                    (string) $record->assignment?->name,
                    $record->grade,
                    (string) ($record->feedback ?: 'Your assignment has been graded.'),
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
