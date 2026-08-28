<?php

namespace App\Filament\Resources\InstructorApplications\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InstructorApplicationsTable
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
                            TextColumn::make('name')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('email')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('proposal_type')
                            ->label('Proposal')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'new' => 'info',
                                'existing' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'new' => 'New Course',
                                'existing' => 'Existing Course',
                                default => 'Legacy',
                            })
                            ->size('xs'),
                        TextColumn::make('created_at')
                            ->label('Applied')
                            ->dateTime('M d, Y')
                            ->size('xs')
                            ->color('gray'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('proposal_type')
                    ->label('Proposal')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'new' => 'info',
                        'existing' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'new' => 'New Course',
                        'existing' => 'Existing Course',
                        default => 'Legacy',
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('preferredCourse.title')
                    ->label('Preferred Course')
                    ->placeholder('Not specified')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not reviewed')
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
