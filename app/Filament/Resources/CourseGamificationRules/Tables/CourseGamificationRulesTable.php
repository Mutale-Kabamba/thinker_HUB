<?php

namespace App\Filament\Resources\CourseGamificationRules\Tables;

use App\Models\CourseGamificationRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourseGamificationRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course Scope')
                    ->placeholder('🌟 Global Platform Default Matrix')
                    ->badge()
                    ->color(fn ($state) => $state ? 'primary' : 'warning')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Rule Set Name')
                    ->searchable()
                    ->placeholder('Standard Rules'),

                TextColumn::make('rules_count')
                    ->label('Configured Activities')
                    ->getStateUsing(fn (CourseGamificationRule $record): int => is_array($record->rules) ? count($record->rules) : count(CourseGamificationRule::getDefaultMatrix()))
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
