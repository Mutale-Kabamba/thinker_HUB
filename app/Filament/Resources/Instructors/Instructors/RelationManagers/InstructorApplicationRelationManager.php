<?php

namespace App\Filament\Resources\Instructors\Instructors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstructorApplicationRelationManager extends RelationManager
{
    protected static string $relationship = 'instructorApplication';

    protected static ?string $title = 'Instructor Application';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('proposal_type')
                    ->label('Proposal Type'),
                TextColumn::make('phone'),
                TextColumn::make('qualifications')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->qualifications),
                TextColumn::make('experience')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->experience),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->placeholder('Not reviewed'),
                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
