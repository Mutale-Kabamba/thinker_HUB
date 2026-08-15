<?php

namespace App\Filament\Resources\CourseGamificationRules\Pages;

use App\Filament\Resources\CourseGamificationRules\CourseGamificationRuleResource;
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
