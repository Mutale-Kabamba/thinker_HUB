<?php

namespace App\Filament\Resources\CourseSessions\Pages;

use App\Filament\Resources\CourseSessions\CourseSessionResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditCourseSession extends BaseEditRecord
{
    protected static string $resource = CourseSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
