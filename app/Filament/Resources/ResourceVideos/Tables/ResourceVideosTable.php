<?php

namespace App\Filament\Resources\ResourceVideos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ResourceVideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(45)
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('channel_name')
                    ->label('Channel')
                    ->placeholder('—')
                    ->searchable(),
                IconColumn::make('is_recorded_lesson')
                    ->label('Recorded Lesson')
                    ->boolean(),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('target_level')
                    ->label('Level')
                    ->placeholder('All'),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
