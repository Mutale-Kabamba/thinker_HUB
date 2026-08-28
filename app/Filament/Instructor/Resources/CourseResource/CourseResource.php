<?php

namespace App\Filament\Instructor\Resources\CourseResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\CourseResource\Pages\EditCourse;
use App\Filament\Instructor\Resources\CourseResource\Pages\ListCourses;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'ACADEMICS & CONTENT';

    protected static ?string $navigationLabel = 'My Courses';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                // Mobile Card View Structure (Stacked & Clean)
                Stack::make([
                    Split::make([
                        ImageColumn::make('image_path')
                            ->disk('public')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&background=0d9488&color=ffffff')
                            ->grow(false),
                        Stack::make([
                            TextColumn::make('title')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('code')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        TextColumn::make('is_active')
                            ->badge()
                            ->state(fn ($record) => $record->is_active ? 'Active' : 'Draft')
                            ->color(fn ($record) => $record->is_active ? 'success' : 'gray')
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('enrollments_count')
                            ->label('Students')
                            ->counts('enrollments')
                            ->badge()
                            ->color('primary')
                            ->size('xs'),
                        TextColumn::make('is_open_enrollment')
                            ->badge()
                            ->formatStateUsing(fn (?bool $state): string => $state === false ? 'Locked' : 'Open')
                            ->color(fn (?bool $state): string => $state === false ? 'gray' : 'success')
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('title')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('code')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('enrollments_count')
                    ->label('Students')
                    ->counts('enrollments')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('is_open_enrollment')
                    ->label('Enrollment')
                    ->badge()
                    ->formatStateUsing(fn (?bool $state): string => $state === false ? 'Locked' : 'Open')
                    ->color(fn (?bool $state): string => $state === false ? 'gray' : 'success')
                    ->visibleFrom('md'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('is_open_enrollment')
                    ->label('Enrollment Mode')
                    ->options([
                        '1' => 'Open',
                        '0' => 'Locked',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        if ($value === '0') {
                            return $query->where('is_open_enrollment', false);
                        }

                        return $query->where(function (Builder $innerQuery): void {
                            $innerQuery
                                ->where('is_open_enrollment', true)
                                ->orWhereNull('is_open_enrollment');
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('id', static::instructorCourseIds()));
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Courses\Schemas\CourseForm::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Courses\RelationManagers\StudentsRelationManager::class,
            \App\Filament\Resources\Courses\RelationManagers\IntakesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
