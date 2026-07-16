<?php

namespace App\Filament\Instructor\Resources\CourseSessionResource\Pages;

use App\Filament\Instructor\Resources\CourseSessionResource\CourseSessionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditCourseSession extends BaseEditRecord
{
    protected static string $resource = CourseSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
