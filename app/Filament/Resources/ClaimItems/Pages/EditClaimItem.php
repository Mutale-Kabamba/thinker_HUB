<?php

namespace App\Filament\Resources\ClaimItems\Pages;

use App\Filament\Resources\ClaimItems\ClaimItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClaimItem extends EditRecord
{
    protected static string $resource = ClaimItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
