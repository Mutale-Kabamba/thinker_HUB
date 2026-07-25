<?php

namespace App\Filament\Instructor\Resources\LearningMaterialResource\Pages;

use App\Filament\Instructor\Resources\LearningMaterialResource\LearningMaterialResource;
use App\Support\PublicDiskPath;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditLearningMaterial extends BaseEditRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['file_path'] = PublicDiskPath::normalize($data['file_path'] ?? null);

        return $data;
    }
}
