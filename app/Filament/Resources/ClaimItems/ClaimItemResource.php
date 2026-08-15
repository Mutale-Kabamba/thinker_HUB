<?php

namespace App\Filament\Resources\ClaimItems;

use App\Filament\Resources\ClaimItems\Pages\CreateClaimItem;
use App\Filament\Resources\ClaimItems\Pages\EditClaimItem;
use App\Filament\Resources\ClaimItems\Pages\ListClaimItems;
use App\Filament\Resources\ClaimItems\Schemas\ClaimItemForm;
use App\Filament\Resources\ClaimItems\Tables\ClaimItemsTable;
use App\Models\ClaimItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClaimItemResource extends Resource
{
    protected static ?string $model = ClaimItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $navigationLabel = 'Reward Catalog';

    protected static ?string $modelLabel = 'Reward Item';

    protected static ?string $pluralModelLabel = 'Reward Items';

    protected static ?string $slug = 'claim-items';

    protected static string|UnitEnum|null $navigationGroup = 'REWARDS & GAMIFICATION';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ClaimItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClaimItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClaimItems::route('/'),
            'create' => CreateClaimItem::route('/create'),
            'edit' => EditClaimItem::route('/{record}/edit'),
        ];
    }
}
