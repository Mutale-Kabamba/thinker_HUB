<?php

namespace App\Filament\Contributor\Resources\HubPostResource\Pages;

use App\Filament\Contributor\Resources\HubPostResource;
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
