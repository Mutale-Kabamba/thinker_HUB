<?php

namespace App\Filament\Instructor\Resources\StudentResource\StudentResource\RelationManagers;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizAttemptsRelationManager extends RelationManager
{
    use ScopedToInstructor;

    protected static string $relationship = 'quizAttempts';

    protected static ?string $title = 'Quiz Attempts';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('quiz.course.title')
                    ->label('Course'),
                TextColumn::make('score')
                    ->numeric()
                    ->suffix(fn ($record): string => '/' . ($record->total_points ?? '?')),
                TextColumn::make('percentage')
                    ->suffix('%')
                    ->color(fn ($record): string => ($record->passed ?? false) ? 'success' : 'danger'),
                IconColumn::make('passed')
                    ->boolean(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('In progress'),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'quiz',
                    fn (Builder $q) => $q->whereIn('course_id', static::instructorCourseIds())
                )
            )
            ->defaultSort('started_at', 'desc');
    }
}
