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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

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
                IconColumn::make('is_active')
                    ->boolean(),
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
