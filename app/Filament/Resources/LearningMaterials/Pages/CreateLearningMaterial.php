<?php

namespace App\Filament\Resources\LearningMaterials\Pages;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Filament\Resources\Pages\BaseCreateRecord;

class CreateLearningMaterial extends BaseCreateRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create');
    }
}
