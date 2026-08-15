<?php

namespace App\Filament\Instructor\Resources\CourseGamificationRuleResource\Pages;

use App\Filament\Instructor\Resources\CourseGamificationRuleResource\CourseGamificationRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseGamificationRule extends EditRecord
{
    protected static string $resource = CourseGamificationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            DeleteAction::make(),
        ];
    }
}
