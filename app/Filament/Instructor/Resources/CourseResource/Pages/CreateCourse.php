<?php

namespace App\Filament\Instructor\Resources\CourseResource\Pages;

use App\Filament\Instructor\Resources\CourseResource\CourseResource;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use App\Filament\Resources\Pages\BaseCreateRecord;
use Filament\Notifications\Notification;

class CreateCourse extends BaseCreateRecord
{
    protected static string $resource = CourseResource::class;

    /**
     * @var array<int>
     */
    protected array $selectedParticipantIds = [];

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedParticipantIds = array_values(array_map('intval', $data['selected_participant_ids'] ?? []));
        unset($data['selected_participant_ids']);

        // Instructors cannot self-activate courses; active status requires Admin approval
        $data['is_active'] = false;

        if (empty($data['course_by']) && auth()->check()) {
            $data['course_by'] = auth()->user()->name;
        }

        return CourseForm::prepareDataForSave($data);
    }

    protected function afterCreate(): void
    {
        $instructorId = auth()->id();
        if ($instructorId) {
            $this->record->instructors()->syncWithoutDetaching([$instructorId]);
        }

        if ($this->record->is_open_enrollment === false) {
            $this->record->selectedParticipants()->sync($this->selectedParticipantIds);
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Course Submitted for Review')
            ->body('Your course has been created successfully. It will become active and published once reviewed and approved by an Administrator.');
    }
}
