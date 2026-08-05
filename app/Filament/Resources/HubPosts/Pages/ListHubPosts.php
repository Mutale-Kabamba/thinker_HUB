<?php

namespace App\Filament\Resources\HubPosts\Pages;

use App\Filament\Resources\HubPosts\HubPostResource;
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
