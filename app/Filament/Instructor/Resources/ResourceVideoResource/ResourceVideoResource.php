<?php

namespace App\Filament\Instructor\Resources\ResourceVideoResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\ResourceVideoResource\Pages\CreateResourceVideo;
use App\Filament\Instructor\Resources\ResourceVideoResource\Pages\EditResourceVideo;
use App\Filament\Instructor\Resources\ResourceVideoResource\Pages\ListResourceVideos;
use App\Models\ResourceVideo;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('youtube_url')
                    ->label('YouTube URL')
                    ->required()
                    ->url()
                    ->helperText('Paste any YouTube link (watch, youtu.be, shorts, or embed).')
                    ->maxLength(255),

                Toggle::make('is_recorded_lesson')
                    ->label('Recorded lesson')
                    ->helperText('Turn on if this video is an official recorded lesson for enrolled students.')
                    ->default(false)
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if ($state) {
                            $set('category', 'Recorded Lessons');
                        }
                    })
                    ->live(),

                Select::make('course_id')
                    ->label('Course')
                    ->options(fn (): array => static::instructorCourseOptions())
                    ->searchable()
                    ->visible(fn (callable $get): bool => (bool) $get('is_recorded_lesson'))
                    ->required(fn (callable $get): bool => (bool) $get('is_recorded_lesson')),

                Select::make('target_level')
                    ->label('Target level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ])
                    ->helperText('Leave empty to show this recorded lesson to all levels in the selected course.')
                    ->visible(fn (callable $get): bool => (bool) $get('is_recorded_lesson')),

                Select::make('category')
                    ->label('Category')
                    ->options(ResourceVideo::categoryOptions())
                    ->default('General')
                    ->required()
                    ->searchable(),

                TextInput::make('channel_name')
                    ->label('Channel / Source')
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first.'),

                Toggle::make('is_published')
                    ->label('Published (visible to students)')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(45)
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('channel_name')
                    ->label('Channel')
                    ->placeholder('—')
                    ->searchable(),
                IconColumn::make('is_recorded_lesson')
                    ->label('Recorded Lesson')
                    ->boolean(),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('target_level')
                    ->label('Level')
                    ->placeholder('All'),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds())->orWhereNull('course_id'))
            ->filters([
                SelectFilter::make('category')
                    ->options(ResourceVideo::categoryOptions()),
                TernaryFilter::make('is_recorded_lesson')
                    ->label('Recorded lesson'),
                TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
