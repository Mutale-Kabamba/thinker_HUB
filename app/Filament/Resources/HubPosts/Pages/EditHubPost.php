<?php

namespace App\Filament\Resources\HubPosts\Pages;

use App\Filament\Resources\HubPosts\HubPostResource;
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
