<?php

namespace App\Filament\Resources\CourseGamificationRules\Pages;

use App\Filament\Resources\CourseGamificationRules\CourseGamificationRuleResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditCourseGamificationRule extends BaseEditRecord
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
