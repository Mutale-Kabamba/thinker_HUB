<?php

namespace App\Filament\Instructor\Resources\ResourceVideoResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\ResourceVideoResource\Pages\CreateResourceVideo;
use App\Filament\Instructor\Resources\ResourceVideoResource\Pages\EditResourceVideo;
use App\Filament\Instructor\Resources\ResourceVideoResource\Pages\ListResourceVideos;
use App\Filament\Resources\ResourceVideos\Schemas\ResourceVideoForm;
use App\Filament\Resources\ResourceVideos\Tables\ResourceVideosTable;
use App\Models\ResourceVideo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResourceVideoResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = ResourceVideo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'ACADEMICS & CONTENT';

    protected static ?string $navigationLabel = 'Videos';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ResourceVideoForm::configure(
            $schema,
            fn (): array => static::instructorCourseOptions()
        );
    }

    public static function table(Table $table): Table
    {
        return ResourceVideosTable::configure($table)
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->whereIn('course_id', static::instructorCourseIds())
                    ->orWhereNull('course_id')
            );
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
