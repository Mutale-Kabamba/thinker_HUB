<?php

namespace App\Filament\Instructor\Resources\CourseSessionResource\Pages;

use App\Filament\Instructor\Resources\CourseSessionResource\CourseSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseSession extends EditRecord
{
    protected static string $resource = CourseSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
