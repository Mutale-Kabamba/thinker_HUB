<?php

namespace App\Filament\Resources\LearningMaterials\Tables;

use App\Models\LearningMaterial;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LearningMaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
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
                    ->sortable(),
                TextColumn::make('material_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
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
                    ->sortable(),
                TextColumn::make('target_track')
                    ->label('Track')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('targetUser.name')
                    ->label('Student')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
