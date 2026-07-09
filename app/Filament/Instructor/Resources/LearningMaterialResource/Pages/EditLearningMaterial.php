<?php

namespace App\Filament\Instructor\Resources\LearningMaterialResource\Pages;

use App\Filament\Instructor\Resources\LearningMaterialResource\LearningMaterialResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditLearningMaterial extends BaseEditRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
