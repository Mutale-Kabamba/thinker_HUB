<?php

namespace App\Filament\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(45)
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Promo Code' => 'warning',
                        'Job' => 'success',
                        'Reading Material' => 'info',
                        'Scholarship' => 'primary',
                        'Event' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('provider')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->placeholder('No expiry')
                    ->badge()
                    ->color(fn (?string $state, Opportunity $record): string => $record->is_expired ? 'danger' : 'gray')
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(array_combine(Opportunity::TYPES, Opportunity::TYPES)),
                TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
