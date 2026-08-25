<?php

namespace App\Filament\Instructor\Resources\CourseIntakeResource\Pages;

use App\Filament\Instructor\Resources\CourseIntakeResource\CourseIntakeResource;
use App\Models\CourseIntake;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseIntake extends CreateRecord
{
    protected static string $resource = CourseIntakeResource::class;

    protected function afterCreate(): void
    {
        /** @var CourseIntake $record */
        $record = $this->record;

        if ($record->is_active || $record->status === CourseIntake::STATUS_ACTIVE) {
            $record->activate();
        }
    }
}
