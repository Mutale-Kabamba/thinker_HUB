<?php

namespace App\Filament\Resources\ResourceVideos;

use App\Filament\Resources\ResourceVideos\Pages\CreateResourceVideo;
use App\Filament\Resources\ResourceVideos\Pages\EditResourceVideo;
use App\Filament\Resources\ResourceVideos\Pages\ListResourceVideos;
use App\Filament\Resources\ResourceVideos\Schemas\ResourceVideoForm;
use App\Filament\Resources\ResourceVideos\Tables\ResourceVideosTable;
use App\Models\ResourceVideo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ResourceVideoResource extends Resource
{
    protected static ?string $model = ResourceVideo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static string|UnitEnum|null $navigationGroup = 'ACADEMICS & CONTENT';

    protected static ?string $navigationLabel = 'Videos';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ResourceVideoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResourceVideosTable::configure($table);
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
            'index' => ListResourceVideos::route('/'),
            'create' => CreateResourceVideo::route('/create'),
            'edit' => EditResourceVideo::route('/{record}/edit'),
        ];
    }
}
