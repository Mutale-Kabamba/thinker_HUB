<?php

namespace App\Filament\Resources\ResourceComments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResourceCommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'commentable', 'parent']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->label('Comment')
                    ->wrap()
                    ->limit(120)
                    ->searchable(),
                TextColumn::make('commentable_type')
                    ->label('On')
                    ->formatStateUsing(fn (?string $state): string => match (class_basename((string) $state)) {
                        'ResourceVideo' => 'Video',
                        'LearningMaterial' => 'Lesson',
                        'Opportunity' => 'Opportunity',
                        default => class_basename((string) $state),
                    })
                    ->badge(),
                TextColumn::make('parent_id')
                    ->label('Type')
                    ->formatStateUsing(fn ($state): string => $state ? 'Reply' : 'Comment')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'gray' : 'info'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('commentable_type')
                    ->label('Resource type')
                    ->options([
                        \App\Models\ResourceVideo::class => 'Video',
                        \App\Models\LearningMaterial::class => 'Lesson',
                        \App\Models\Opportunity::class => 'Opportunity',
                    ]),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
