<?php

namespace App\Filament\Resources\ResourceVideos\Pages;

use App\Filament\Resources\ResourceVideos\ResourceVideoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResourceVideos extends ListRecords
{
    protected static string $resource = ResourceVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
