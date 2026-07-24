<?php

namespace App\Filament\Resources\ResourceVideos\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Resources\ResourceVideos\Concerns\HandlesVideoUpload;
use App\Filament\Resources\ResourceVideos\ResourceVideoResource;
use Filament\Actions\DeleteAction;

class EditResourceVideo extends BaseEditRecord
{
    use HandlesVideoUpload;

    protected static string $resource = ResourceVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
