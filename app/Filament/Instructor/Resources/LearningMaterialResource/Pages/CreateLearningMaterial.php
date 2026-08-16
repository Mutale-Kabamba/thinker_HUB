<?php

namespace App\Filament\Instructor\Resources\LearningMaterialResource\Pages;

use App\Filament\Instructor\Resources\LearningMaterialResource\LearningMaterialResource;
use App\Filament\Resources\Pages\BaseCreateRecord;
use Filament\Notifications\Notification;

class CreateLearningMaterial extends BaseCreateRecord
{
    protected static string $resource = LearningMaterialResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Material created successfully')
            ->body('Material uploaded successfully and is currently processing in the background.');
    }
}
