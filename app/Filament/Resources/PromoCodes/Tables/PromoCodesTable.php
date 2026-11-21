<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use App\Models\PromoCode;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Promo code copied')
                    ->weight('bold')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('discount_display')
                    ->label('Discount')
                    ->state(fn (PromoCode $record): string => $record->formattedValue())
                    ->badge()
                    ->color(fn (PromoCode $record): string => $record->isPercentage() && (float) $record->discount_value >= 100 ? 'success' : 'info'),

                TextColumn::make('course.title')
                    ->label('Applicable Course')
                    ->placeholder('All Courses (Global)')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('usages_summary')
                    ->label('Redemptions')
                    ->state(function (PromoCode $record): string {
                        $count = $record->usages()->count();
                        $max = $record->max_uses !== null ? (string) $record->max_uses : '∞';

                        return "{$count} / {$max}";
                    })
                    ->badge()
                    ->color(function (PromoCode $record): string {
                        if ($record->max_uses !== null && $record->usages()->count() >= $record->max_uses) {
                            return 'danger';
                        }

                        return 'gray';
                    }),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->placeholder('Never')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->color(fn (PromoCode $record): string => $record->expires_at && $record->expires_at->isPast() ? 'danger' : 'gray'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),

                SelectFilter::make('discount_type')
                    ->label('Discount Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ]),

                SelectFilter::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
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
