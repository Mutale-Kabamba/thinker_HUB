<?php

namespace App\Filament\Resources\CourseGamificationRules\Pages;

use App\Filament\Resources\CourseGamificationRules\CourseGamificationRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseGamificationRule extends CreateRecord
{
    protected static string $resource = CourseGamificationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (is_array($data['rules'] ?? null)) {
            $data['rules'] = array_map(function ($row) {
                if (is_array($row) && isset($row['xp'])) {
                    $row['coins'] = (int) round(((float) $row['xp']) * 0.30);
                }
                return $row;
            }, $data['rules']);
        }

        return $data;
    }
}
