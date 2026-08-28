<?php

namespace App\Filament\Resources\ResourceComments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResourceCommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'commentable', 'parent']))
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                // Mobile Card View Structure (Stacked & Clean)
                Stack::make([
                    Split::make([
                        TextColumn::make('user.name')
                            ->label('Author')
                            ->weight('bold')
                            ->size('sm')
                            ->searchable(),
                        TextColumn::make('created_at')
                            ->dateTime('M d, Y')
                            ->size('xs')
                            ->color('gray'),
                    ]),
                    TextColumn::make('body')
                        ->label('Comment')
                        ->wrap()
                        ->limit(100)
                        ->size('xs')
                        ->color('gray')
                        ->searchable(),
                    Split::make([
                        TextColumn::make('commentable_type')
                            ->label('On')
                            ->formatStateUsing(fn (?string $state): string => match (class_basename((string) $state)) {
                                'ResourceVideo' => 'Video',
                                'LearningMaterial' => 'Lesson',
                                'Opportunity' => 'Opportunity',
                                default => class_basename((string) $state),
                            })
                            ->badge()
                            ->size('xs'),
                        TextColumn::make('parent_id')
                            ->label('Type')
                            ->formatStateUsing(fn ($state): string => $state ? 'Reply' : 'Comment')
                            ->badge()
                            ->color(fn ($state): string => $state ? 'gray' : 'info')
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('body')
                    ->label('Comment')
                    ->wrap()
                    ->limit(120)
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('commentable_type')
                    ->label('On')
                    ->formatStateUsing(fn (?string $state): string => match (class_basename((string) $state)) {
                        'ResourceVideo' => 'Video',
                        'LearningMaterial' => 'Lesson',
                        'Opportunity' => 'Opportunity',
                        default => class_basename((string) $state),
                    })
                    ->badge()
                    ->visibleFrom('md'),
                TextColumn::make('parent_id')
                    ->label('Type')
                    ->formatStateUsing(fn ($state): string => $state ? 'Reply' : 'Comment')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'gray' : 'info')
                    ->visibleFrom('md'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('md'),
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
