<?php

namespace App\Filament\Resources\ResourceComments;

use App\Filament\Resources\ResourceComments\Pages\ListResourceComments;
use App\Filament\Resources\ResourceComments\Tables\ResourceCommentsTable;
use App\Models\ResourceComment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ResourceCommentResource extends Resource
{
    protected static ?string $model = ResourceComment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'COMMUNITY & SYSTEM';

    protected static ?string $navigationLabel = 'Comments';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return ResourceCommentsTable::configure($table);
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
            'index' => ListResourceComments::route('/'),
        ];
    }
}
