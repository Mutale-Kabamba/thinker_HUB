<?php

namespace App\Filament\Instructor\Resources\ResourceVideoResource\Pages;

use App\Filament\Instructor\Resources\ResourceVideoResource\ResourceVideoResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Resources\ResourceVideos\Concerns\HandlesVideoUpload;
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
