<?php

namespace App\Filament\Resources\CourseGamificationRules\Pages;

use App\Filament\Resources\CourseGamificationRules\CourseGamificationRuleResource;
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
