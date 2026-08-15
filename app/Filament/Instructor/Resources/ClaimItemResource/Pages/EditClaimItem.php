<?php

namespace App\Filament\Instructor\Resources\ClaimItemResource\Pages;

use App\Filament\Instructor\Resources\ClaimItemResource\ClaimItemResource;
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
