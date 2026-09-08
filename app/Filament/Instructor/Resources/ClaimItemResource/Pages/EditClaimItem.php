<?php

namespace App\Filament\Instructor\Resources\ClaimItemResource\Pages;

use App\Filament\Instructor\Resources\ClaimItemResource\ClaimItemResource;
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
