<?php

namespace App\Filament\Instructor\Resources\CourseIntakeResource\Pages;

use App\Filament\Instructor\Resources\CourseIntakeResource\CourseIntakeResource;
use App\Models\CourseIntake;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseIntake extends EditRecord
{
    protected static string $resource = CourseIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var CourseIntake $record */
        $record = $this->record;

        if ($record->is_active) {
            $record->activate();
        }
    }
}
