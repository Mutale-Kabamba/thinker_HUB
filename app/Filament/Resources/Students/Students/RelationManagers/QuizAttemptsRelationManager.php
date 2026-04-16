<?php

namespace App\Filament\Resources\Students\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizAttemptsRelationManager extends RelationManager
{
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
                TextColumn::make('quiz.assessment.course.title')
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
            ->defaultSort('started_at', 'desc');
    }
}
