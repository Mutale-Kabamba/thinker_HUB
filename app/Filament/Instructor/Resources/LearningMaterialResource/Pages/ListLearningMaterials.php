<?php

namespace App\Filament\Instructor\Resources\LearningMaterialResource\Pages;

use App\Filament\Instructor\Resources\LearningMaterialResource\LearningMaterialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLearningMaterials extends ListRecords
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
