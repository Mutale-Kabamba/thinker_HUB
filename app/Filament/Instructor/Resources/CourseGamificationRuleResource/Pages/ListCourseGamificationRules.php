<?php

namespace App\Filament\Instructor\Resources\CourseGamificationRuleResource\Pages;

use App\Filament\Instructor\Resources\CourseGamificationRuleResource\CourseGamificationRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourseGamificationRules extends ListRecords
{
    protected static string $resource = CourseGamificationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
