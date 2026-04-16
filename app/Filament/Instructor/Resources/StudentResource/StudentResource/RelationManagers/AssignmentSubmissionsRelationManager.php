<?php

namespace App\Filament\Instructor\Resources\StudentResource\StudentResource\RelationManagers;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentSubmissionsRelationManager extends RelationManager
{
    use ScopedToInstructor;

    protected static string $relationship = 'assignmentSubmissions';

    protected static ?string $title = 'Assignment Submissions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assignment.name')
                    ->label('Assignment')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('assignment.course.title')
                    ->label('Course'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Graded' => 'success',
                        'Submitted' => 'warning',
                        'Returned' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('grade')
                    ->numeric()
                    ->suffix('/100')
                    ->placeholder('—'),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'assignment',
                    fn (Builder $q) => $q->whereIn('course_id', static::instructorCourseIds())
                )
            )
            ->defaultSort('submitted_at', 'desc');
    }
}
