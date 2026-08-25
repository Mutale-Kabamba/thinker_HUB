<?php

namespace App\Filament\Resources\AssessmentSubmissions\Pages;

use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Notifications\SubmissionGradedNotification;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditAssessmentSubmission extends BaseEditRecord
{
    protected static string $resource = AssessmentSubmissionResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record && $this->record->viewed_at === null) {
            $this->record->markAsViewed();
        }
    }

    protected function afterSave(): void
    {
        $submission = $this->record;

        if ($submission?->user) {
            try {
                $submission->user->notify(new SubmissionGradedNotification(
                    'assessment',
                    'Assessment #'.(string) $submission->assessment?->id,
                    $submission->score,
                    (string) $submission->feedback,
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
