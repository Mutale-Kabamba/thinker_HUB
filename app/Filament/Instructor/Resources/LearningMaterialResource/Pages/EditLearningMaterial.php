<?php

namespace App\Filament\Instructor\Resources\LearningMaterialResource\Pages;

use App\Filament\Instructor\Resources\LearningMaterialResource\LearningMaterialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningMaterial extends EditRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
