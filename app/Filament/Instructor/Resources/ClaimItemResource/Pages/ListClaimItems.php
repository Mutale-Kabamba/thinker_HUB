<?php

namespace App\Filament\Instructor\Resources\ClaimItemResource\Pages;

use App\Filament\Instructor\Resources\ClaimItemResource\ClaimItemResource;
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
