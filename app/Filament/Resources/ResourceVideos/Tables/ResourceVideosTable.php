<?php

namespace App\Filament\Resources\ResourceVideos\Tables;

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

class ResourceVideosTable
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
                            TextColumn::make('course.title')
                                ->size('xs')
                                ->color('gray')
                                ->placeholder('General')
                                ->searchable(),
                        ]),
                        IconColumn::make('is_published')
                            ->label('Published')
                            ->boolean()
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('category')
                            ->badge()
                            ->color('info')
                            ->size('xs'),
                        TextColumn::make('channel_name')
                            ->placeholder('No channel')
                            ->size('xs')
                            ->color('gray'),
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
                TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('channel_name')
                    ->label('Channel')
                    ->placeholder('—')
                    ->searchable()
                    ->visibleFrom('md'),
                IconColumn::make('is_recorded_lesson')
                    ->label('Recorded Lesson')
                    ->boolean()
                    ->visibleFrom('md'),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('—')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('target_level')
                    ->label('Level')
                    ->placeholder('All')
                    ->visibleFrom('md'),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('category')
                    ->options(\App\Models\ResourceVideo::categoryOptions()),
                TernaryFilter::make('is_recorded_lesson')
                    ->label('Recorded lesson'),
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
