<?php

namespace App\Filament\Instructor\Resources\CourseGamificationRuleResource\Pages;

use App\Filament\Instructor\Resources\CourseGamificationRuleResource\CourseGamificationRuleResource;
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
