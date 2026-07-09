<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use App\Filament\Resources\Pages\BaseCreateRecord;

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

        return CourseForm::prepareDataForSave($data);
    }

    protected function afterCreate(): void
    {
        if ($this->record->is_open_enrollment === false) {
            $this->record->selectedParticipants()->sync($this->selectedParticipantIds);
        }
    }
}
