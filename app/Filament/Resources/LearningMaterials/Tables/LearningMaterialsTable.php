<?php

namespace App\Filament\Resources\LearningMaterials\Tables;

use App\Models\LearningMaterial;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LearningMaterialsTable
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
                                ->searchable(),
                        ]),
                        TextColumn::make('category')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Curriculum' => 'primary',
                                'Study Material' => 'success',
                                'Quiz Preps' => 'warning',
                                'Answer Kits' => 'info',
                                'Project Guides' => 'indigo',
                                'Cheat Sheets' => 'purple',
                                'Practice Exercises' => 'teal',
                                'Past Papers' => 'amber',
                                'Rules' => 'danger',
                                'General Notices' => 'gray',
                                'Supplementary Resources' => 'cyan',
                                default => 'gray',
                            })
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('material_type')
                            ->badge()
                            ->color('gray')
                            ->size('xs'),
                        TextColumn::make('scope')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'all' => 'success',
                                'level' => 'info',
                                'personal' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'all' => 'All Students',
                                'level' => 'Level',
                                'personal' => 'Personal',
                                default => ucfirst($state),
                            })
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->visibleFrom('md'),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Curriculum' => 'primary',
                        'Study Material' => 'success',
                        'Quiz Preps' => 'warning',
                        'Answer Kits' => 'info',
                        'Project Guides' => 'indigo',
                        'Cheat Sheets' => 'purple',
                        'Practice Exercises' => 'teal',
                        'Past Papers' => 'amber',
                        'Rules' => 'danger',
                        'General Notices' => 'gray',
                        'Supplementary Resources' => 'cyan',
                        default => 'gray',
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('material_type')
                    ->label('Type')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('scope')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'success',
                        'level' => 'info',
                        'personal' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'All Students',
                        'level' => 'Level',
                        'personal' => 'Personal',
                        default => ucfirst($state),
                    })
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('target_track')
                    ->label('Track')
                    ->placeholder('—')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('targetUser.name')
                    ->label('Student')
                    ->placeholder('—')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options(LearningMaterial::categoryOptions()),
                SelectFilter::make('material_type')
                    ->label('Type')
                    ->options([
                        'Document' => 'Document',
                        'Image' => 'Image',
                        'Video' => 'Video',
                        'Link' => 'Link',
                    ]),
                SelectFilter::make('scope')
                    ->options([
                        'all' => 'All Students',
                        'level' => 'Level',
                        'personal' => 'Personal',
                    ]),
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
