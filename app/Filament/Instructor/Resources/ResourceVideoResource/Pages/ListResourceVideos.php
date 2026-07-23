<?php

namespace App\Filament\Instructor\Resources\ResourceVideoResource\Pages;

use App\Filament\Instructor\Resources\ResourceVideoResource\ResourceVideoResource;
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
