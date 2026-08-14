<?php

namespace App\Filament\Contributor\Resources;

use App\Filament\Contributor\Resources\HubPostResource\Pages\CreateHubPost;
use App\Filament\Contributor\Resources\HubPostResource\Pages\EditHubPost;
use App\Filament\Contributor\Resources\HubPostResource\Pages\ListHubPosts;
use App\Filament\Resources\HubPosts\Schemas\HubPostForm;
use App\Filament\Resources\HubPosts\Tables\HubPostsTable;
use App\Models\HubPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class HubPostResource extends Resource
{
    protected static ?string $model = HubPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'CONTRIBUTIONS';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        $role = auth()->user()?->role;
        return match ($role) {
            'blogger' => 'My Blog Posts & Articles',
            'researcher' => 'My Research & Insights',
            'employer' => 'My Job Opportunities',
            default => 'My Contributions',
        };
    }

    public static function getModelLabel(): string
    {
        $role = auth()->user()?->role;
        return match ($role) {
            'blogger' => 'Blog Post',
            'researcher' => 'Research Insight',
            'employer' => 'Opportunity',
            default => 'Contribution',
        };
    }

    public static function getPluralModelLabel(): string
    {
        $role = auth()->user()?->role;
        return match ($role) {
            'blogger' => 'Blog Posts',
            'researcher' => 'Research Insights',
            'employer' => 'Opportunities',
            default => 'Contributions',
        };
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('author_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return HubPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HubPostsTable::configure($table);
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
