<?php

namespace App\Filament\Resources\ClaimItems\Pages;

use App\Filament\Resources\ClaimItems\ClaimItemResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditClaimItem extends BaseEditRecord
{
    protected static string $resource = ClaimItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
