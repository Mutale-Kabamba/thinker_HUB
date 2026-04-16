<?php

namespace App\Filament\Instructor\Resources\StudentResource\StudentResource\RelationManagers;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentsRelationManager extends RelationManager
{
    use ScopedToInstructor;

    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Course Enrollments';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable(),
                TextColumn::make('course.code')
                    ->label('Code'),
                TextColumn::make('created_at')
                    ->label('Enrolled At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereIn('course_id', static::instructorCourseIds())
            )
            ->defaultSort('created_at', 'desc');
    }
}
