<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Resources\Opportunities\OpportunityResource;
use Filament\Actions\DeleteAction;

class EditOpportunity extends BaseEditRecord
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
