<?php

namespace App\Filament\Resources\ClaimItems\Tables;

use App\Models\ClaimItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClaimItemsTable
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
                        ImageColumn::make('image_path')
                            ->disk('public')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&background=f59e0b&color=ffffff')
                            ->grow(false),
                        Stack::make([
                            TextColumn::make('title')
                                ->weight('bold')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('category')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'data' => 'info',
                                    'merch' => 'success',
                                    'voucher' => 'warning',
                                    'perk' => 'primary',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => ClaimItem::CATEGORIES[$state] ?? $state)
                                ->size('xs'),
                        ]),
                        TextColumn::make('coin_cost')
                            ->label('Cost')
                            ->formatStateUsing(fn ($state): string => '🪙 '.number_format((int) $state).' TC')
                            ->badge()
                            ->color('warning')
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('stock_quantity')
                            ->label('Stock')
                            ->formatStateUsing(fn ($state): string => (int) $state < 0 ? 'Unlimited' : ((int) $state === 0 ? 'Out of Stock' : "Stock: {$state}"))
                            ->badge()
                            ->color(fn ($state): string => (int) $state === 0 ? 'danger' : ((int) $state < 0 ? 'success' : 'info'))
                            ->size('xs'),
                        TextColumn::make('course.title')
                            ->placeholder('General')
                            ->size('xs')
                            ->color('gray'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->visibleFrom('md'),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->visibleFrom('md'),

                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'data' => 'info',
                        'merch' => 'success',
                        'voucher' => 'warning',
                        'perk' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ClaimItem::CATEGORIES[$state] ?? $state)
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('coin_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state): string => '🪙 '.number_format((int) $state).' TC')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->formatStateUsing(fn ($state): string => (int) $state < 0 ? 'Unlimited' : ((int) $state === 0 ? 'Out of Stock' : (string) $state))
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 0 ? 'danger' : ((int) $state < 0 ? 'success' : 'info'))
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('General Platform')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('md'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->defaultSort('coin_cost', 'asc')
            ->filters([
                SelectFilter::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'title'),

                SelectFilter::make('category')
                    ->options(ClaimItem::CATEGORIES),

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
