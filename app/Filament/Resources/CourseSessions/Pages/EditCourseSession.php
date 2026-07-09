<?php

namespace App\Filament\Resources\CourseSessions\Pages;

use App\Filament\Resources\CourseSessions\CourseSessionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\BaseEditRecord;

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
