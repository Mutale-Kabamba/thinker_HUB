<?php

namespace App\Filament\Resources\ClaimRequests;

use App\Filament\Resources\ClaimRequests\Pages\ListClaimRequests;
use App\Filament\Resources\ClaimRequests\Pages\ViewClaimRequest;
use App\Filament\Resources\ClaimRequests\Schemas\ClaimRequestForm;
use App\Filament\Resources\ClaimRequests\Tables\ClaimRequestsTable;
use App\Models\ClaimRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClaimRequestResource extends Resource
{
    protected static ?string $model = ClaimRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'Claim Requests';

    protected static ?string $modelLabel = 'Claim Request';

    protected static ?string $pluralModelLabel = 'Claim Requests';

    protected static ?string $slug = 'claim-requests';

    protected static string|UnitEnum|null $navigationGroup = 'REWARDS & GAMIFICATION';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ClaimRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClaimRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClaimRequests::route('/'),
            'view' => ViewClaimRequest::route('/{record}'),
        ];
    }
}
