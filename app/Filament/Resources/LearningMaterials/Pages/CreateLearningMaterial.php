<?php

namespace App\Filament\Resources\LearningMaterials\Pages;

use App\Filament\Resources\LearningMaterials\LearningMaterialResource;
use App\Filament\Resources\Pages\BaseCreateRecord;
use Filament\Notifications\Notification;

class CreateLearningMaterial extends BaseCreateRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Material created successfully')
            ->body('Material uploaded successfully and is currently processing in the background.');
    }
}
