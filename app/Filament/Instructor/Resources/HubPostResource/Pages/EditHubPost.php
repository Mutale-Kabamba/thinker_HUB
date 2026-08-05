<?php

namespace App\Filament\Instructor\Resources\HubPostResource\Pages;

use App\Filament\Instructor\Resources\HubPostResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditHubPost extends BaseEditRecord
{
    protected static string $resource = HubPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
