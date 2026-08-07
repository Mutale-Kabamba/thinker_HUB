<?php

namespace App\Filament\Contributor\Resources\HubPostResource\Pages;

use App\Filament\Contributor\Resources\HubPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHubPosts extends ListRecords
{
    protected static string $resource = HubPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
