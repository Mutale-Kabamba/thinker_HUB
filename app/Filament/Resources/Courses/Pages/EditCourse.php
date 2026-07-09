<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    /**
     * @var array<int>
     */
    protected array $selectedParticipantIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = CourseForm::prepareDataForFill($data);
        $data['selected_participant_ids'] = $this->record->selectedParticipants()->pluck('users.id')->map(fn ($id): int => (int) $id)->all();

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedParticipantIds = array_values(array_map('intval', $data['selected_participant_ids'] ?? []));
        unset($data['selected_participant_ids']);

        return CourseForm::prepareDataForSave($data);
    }

    protected function afterSave(): void
    {
        if ($this->record->is_open_enrollment === false) {
            $this->record->selectedParticipants()->sync($this->selectedParticipantIds);
        }
    }
}
