<?php

namespace App\Filament\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                // Mobile Card View Structure (Stacked & Clean)
                Stack::make([
                    Split::make([
                        Stack::make([
                            TextColumn::make('title')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('provider')
                                ->placeholder('No provider')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        IconColumn::make('is_published')
                            ->label('Published')
                            ->boolean()
                            ->grow(false),
                    ]),
                    Split::make([
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
                            ->size('xs'),
                        TextColumn::make('expires_at')
                            ->label('Expires')
                            ->date('M d, Y')
                            ->placeholder('No expiry')
                            ->badge()
                            ->color(fn (?string $state, Opportunity $record): string => $record->is_expired ? 'danger' : 'gray')
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('title')
                    ->searchable()
                    ->limit(45)
                    ->sortable()
                    ->visibleFrom('md'),
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
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('provider')
                    ->placeholder('—')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->placeholder('No expiry')
                    ->badge()
                    ->color(fn (?string $state, Opportunity $record): string => $record->is_expired ? 'danger' : 'gray')
                    ->sortable()
                    ->visibleFrom('md'),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('md'),
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
