<?php

namespace App\Filament\Instructor\Resources\CourseSessionResource\Pages;

use App\Filament\Instructor\Resources\CourseSessionResource\CourseSessionResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditCourseSession extends BaseEditRecord
{
    protected static string $resource = CourseSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
