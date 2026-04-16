<?php

namespace App\Filament\Resources\Students\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssessmentSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'assessmentSubmissions';

    protected static ?string $title = 'Assessment Submissions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assessment.name')
                    ->label('Assessment')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('assessment.course.title')
                    ->label('Course'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Graded' => 'success',
                        'Submitted' => 'warning',
                        'Returned' => 'info',
                        'Checked' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('score')
                    ->numeric()
                    ->suffix('/100')
                    ->placeholder('—'),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
