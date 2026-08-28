<?php

namespace App\Filament\Resources\HubPosts\Tables;

use App\Models\HubPost;
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

class HubPostsTable
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
                        TextColumn::make('title')
                            ->weight('bold')
                            ->size('sm')
                            ->searchable(),
                        IconColumn::make('is_published')
                            ->label('Published')
                            ->boolean()
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => HubPost::TYPES[$state] ?? ucfirst($state))
                            ->color(fn (string $state): string => match ($state) {
                                'tip_trick' => 'info',
                                'blog' => 'primary',
                                'opportunity' => 'success',
                                'video' => 'danger',
                                default => 'gray',
                            })
                            ->size('xs'),
                        TextColumn::make('created_at')
                            ->label('Created')
                            ->dateTime('M j, Y')
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
                    ->grow()
                    ->wrap()
                    ->weight('bold')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => HubPost::TYPES[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'tip_trick' => 'info',
                        'blog' => 'primary',
                        'opportunity' => 'success',
                        'video' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('category')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(HubPost::TYPES),

                SelectFilter::make('category')
                    ->options(HubPost::categoryOptions()),

                TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('approve')
                        ->label('Approve & Publish')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->visible(fn (HubPost $record): bool => ! $record->is_published)
                        ->action(fn (HubPost $record) => $record->update(['is_published' => true])),

                    \Filament\Actions\Action::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-m-x-circle')
                        ->color('warning')
                        ->visible(fn (HubPost $record): bool => $record->is_published && (auth()->user()?->isAdmin() ?? false))
                        ->action(fn (HubPost $record) => $record->update(['is_published' => false])),

                    EditAction::make()->icon('heroicon-m-pencil-square'),
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
