<?php

namespace App\Filament\Resources\CourseIntakes\Pages;

use App\Filament\Resources\CourseIntakes\CourseIntakeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourseIntakes extends ListRecords
{
    protected static string $resource = CourseIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Class / Intake')
                ->icon('heroicon-o-plus'),
        ];
    }
}
