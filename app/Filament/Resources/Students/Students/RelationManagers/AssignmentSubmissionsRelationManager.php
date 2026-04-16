<?php

namespace App\Filament\Resources\Students\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentSubmissionsRelationManager extends RelationManager
{
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
                        'Checked' => 'primary',
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
            ->defaultSort('submitted_at', 'desc');
    }
}
