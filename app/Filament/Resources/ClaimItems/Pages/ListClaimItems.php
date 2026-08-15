<?php

namespace App\Filament\Resources\ClaimItems\Pages;

use App\Filament\Resources\ClaimItems\ClaimItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClaimItems extends ListRecords
{
    protected static string $resource = ClaimItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
