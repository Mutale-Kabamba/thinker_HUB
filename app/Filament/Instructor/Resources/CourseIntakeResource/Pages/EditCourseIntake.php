<?php

namespace App\Filament\Instructor\Resources\CourseIntakeResource\Pages;

use App\Filament\Instructor\Resources\CourseIntakeResource\CourseIntakeResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use App\Models\CourseIntake;
use Filament\Actions\DeleteAction;

class EditCourseIntake extends BaseEditRecord
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
