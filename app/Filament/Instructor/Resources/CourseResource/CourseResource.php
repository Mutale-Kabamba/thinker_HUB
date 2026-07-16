<?php

namespace App\Filament\Instructor\Resources\CourseResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\CourseResource\Pages\ListCourses;
use App\Filament\Instructor\Resources\CourseResource\Pages\ViewCourse;
use App\Models\Course;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
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
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('enrollments_count')
                    ->label('Students')
                    ->counts('enrollments')
                    ->sortable(),
                TextColumn::make('is_open_enrollment')
                    ->label('Enrollment')
                    ->badge()
                    ->formatStateUsing(fn (?bool $state): string => $state === false ? 'Locked' : 'Open')
                    ->color(fn (?bool $state): string => $state === false ? 'gray' : 'success'),
                IconColumn::make('is_active')
                    ->boolean(),
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
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('id', static::instructorCourseIds()));
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Courses\Schemas\CourseForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'view' => ViewCourse::route('/{record}'),
        ];
    }
}
