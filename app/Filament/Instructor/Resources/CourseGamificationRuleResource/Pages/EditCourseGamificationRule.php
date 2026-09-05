<?php

namespace App\Filament\Instructor\Resources\CourseGamificationRuleResource\Pages;

use App\Filament\Instructor\Resources\CourseGamificationRuleResource\CourseGamificationRuleResource;
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
