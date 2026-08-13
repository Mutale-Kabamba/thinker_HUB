<?php

namespace App\Filament\Resources\HubPosts;

use App\Filament\Resources\HubPosts\Pages\CreateHubPost;
use App\Filament\Resources\HubPosts\Pages\EditHubPost;
use App\Filament\Resources\HubPosts\Pages\ListHubPosts;
use App\Filament\Resources\HubPosts\Schemas\HubPostForm;
use App\Filament\Resources\HubPosts\Tables\HubPostsTable;
use App\Models\HubPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HubPostResource extends Resource
{
    protected static ?string $model = HubPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'COMMUNITY & SYSTEM';

    protected static ?string $navigationLabel = 'Knowledge Hub';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return HubPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HubPostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHubPosts::route('/'),
            'create' => CreateHubPost::route('/create'),
            'edit' => EditHubPost::route('/{record}/edit'),
        ];
    }
}
