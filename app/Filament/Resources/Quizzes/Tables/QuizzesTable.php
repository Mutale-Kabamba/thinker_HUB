<?php

namespace App\Filament\Resources\Quizzes\Tables;

use App\Filament\Actions\ExportQuizzesAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizzesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),
                TextColumn::make('time_limit_minutes')
                    ->label('Time Limit')
                    ->formatStateUsing(fn (?int $state): string => $state ? $state . ' min' : 'No limit')
                    ->sortable(),
                TextColumn::make('pass_percentage')
                    ->label('Pass %')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('publish_at')
                    ->label('Publish At')
                    ->dateTime()
                    ->placeholder('Immediate')
                    ->sortable(),
                TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->counts('attempts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square'),
                    ExportQuizzesAction::makeRowAction(),
                    \Filament\Actions\DeleteAction::make()
                        ->icon('heroicon-m-trash'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportQuizzesAction::makeBulkAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
