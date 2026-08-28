<?php

namespace App\Filament\Resources\CourseGamificationRules\Tables;

use App\Models\CourseGamificationRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourseGamificationRulesTable
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
                                ->label('Rule Set Name')
                                ->weight('bold')
                                ->size('sm')
                                ->placeholder('Standard Rules')
                                ->searchable(),
                            TextColumn::make('course.title')
                                ->label('Course Scope')
                                ->placeholder('🌟 Global Platform Matrix')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        IconColumn::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('rules_count')
                            ->label('Configured Activities')
                            ->getStateUsing(fn (CourseGamificationRule $record): int => is_array($record->rules) ? count($record->rules) : count(CourseGamificationRule::getDefaultMatrix()))
                            ->formatStateUsing(fn ($state) => "{$state} Activities")
                            ->badge()
                            ->color('info')
                            ->size('xs'),
                        TextColumn::make('updated_at')
                            ->label('Updated')
                            ->dateTime('M d, Y')
                            ->size('xs')
                            ->color('gray'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('course.title')
                    ->label('Course Scope')
                    ->placeholder('🌟 Global Platform Default Matrix')
                    ->badge()
                    ->color(fn ($state) => $state ? 'primary' : 'warning')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),

                TextColumn::make('name')
                    ->label('Rule Set Name')
                    ->searchable()
                    ->placeholder('Standard Rules')
                    ->visibleFrom('md'),

                TextColumn::make('rules_count')
                    ->label('Configured Activities')
                    ->getStateUsing(fn (CourseGamificationRule $record): int => is_array($record->rules) ? count($record->rules) : count(CourseGamificationRule::getDefaultMatrix()))
                    ->badge()
                    ->color('info')
                    ->visibleFrom('md'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->defaultSort('course_id', 'asc')
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()->icon('heroicon-m-trash'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
