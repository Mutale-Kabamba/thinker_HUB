<?php

namespace App\Filament\Resources\ClaimItems\Tables;

use App\Models\ClaimItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClaimItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

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
                    ->sortable(),

                TextColumn::make('coin_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state): string => '🪙 '.number_format((int) $state).' TC')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->formatStateUsing(fn ($state): string => (int) $state < 0 ? 'Unlimited' : ((int) $state === 0 ? 'Out of Stock' : (string) $state))
                    ->badge()
                    ->color(fn ($state): string => (int) $state === 0 ? 'danger' : ((int) $state < 0 ? 'success' : 'info'))
                    ->sortable(),

                TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('General Platform')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
