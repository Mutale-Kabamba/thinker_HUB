<?php

namespace App\Filament\Instructor\Resources\ResourceVideoResource\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Instructor\Resources\ResourceVideoResource\ResourceVideoResource;
use Filament\Actions\DeleteAction;

class EditResourceVideo extends BaseEditRecord
{
    protected static string $resource = ResourceVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
