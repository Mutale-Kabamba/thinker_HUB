<?php

namespace App\Filament\Resources\PromoCodes\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Resources\PromoCodes\PromoCodeResource;
use Filament\Actions\DeleteAction;

class EditPromoCode extends BaseEditRecord
{
    protected static string $resource = PromoCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
