<?php

namespace App\Filament\Resources\ClaimRequests\Tables;

use App\Models\ClaimItem;
use App\Models\ClaimRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClaimRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('claimItem.title')
                    ->label('Reward Item')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('coins_spent')
                    ->label('Coins Spent')
                    ->formatStateUsing(fn ($state): string => '🪙 '.number_format((int) $state).' TC')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('claimItem.category')
                    ->label('Category')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'data' => 'info',
                        'merch' => 'success',
                        'voucher' => 'warning',
                        'perk' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'data' => '📶 Data & Airtime',
                        'merch' => '👕 Merch',
                        'voucher' => '🎟️ Voucher',
                        'perk' => '🚀 Perk',
                        default => $state ?? '—',
                    })
                    ->sortable(),

                TextColumn::make('claimItem.course.title')
                    ->label('Course')
                    ->placeholder('General Platform')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone_number')
                    ->label('Phone / WhatsApp')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'fulfilled' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('fulfilled_at')
                    ->label('Fulfilled')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ClaimRequest::STATUSES),

                SelectFilter::make('course_id')
                    ->label('Course')
                    ->relationship('claimItem.course', 'title'),

                SelectFilter::make('category')
                    ->label('Item Category')
                    ->options(ClaimItem::CATEGORIES)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'])
                        ? $query->whereHas('claimItem', fn (Builder $q) => $q->where('category', $data['value']))
                        : $query
                    ),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    Action::make('fulfill')
                        ->label('Fulfill & Complete')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn (ClaimRequest $record): bool => ! in_array($record->status, [ClaimRequest::STATUS_FULFILLED, ClaimRequest::STATUS_REJECTED], true))
                        ->form([
                            TextInput::make('admin_remarks')
                                ->label('Fulfillment Reference / Airtime TXN ID')
                                ->placeholder('e.g. Airtime ref #TXN-998811 or Courier tracking #')
                                ->maxLength(500),
                        ])
                        ->action(function (ClaimRequest $record, array $data): void {
                            $record->update([
                                'status' => ClaimRequest::STATUS_FULFILLED,
                                'fulfilled_at' => now(),
                                'admin_remarks' => $data['admin_remarks'] ?? $record->admin_remarks,
                            ]);

                            Notification::make()
                                ->title('Claim Request Fulfilled')
                                ->body("Reward '{$record->claimItem?->title}' marked as fulfilled for {$record->user?->name}.")
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject & Refund')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Claim & Refund Coins')
                        ->modalDescription('Are you sure you want to reject this claim? The spent Thinker Coins will be immediately refunded back to the student\'s balance.')
                        ->visible(fn (ClaimRequest $record): bool => ! in_array($record->status, [ClaimRequest::STATUS_FULFILLED, ClaimRequest::STATUS_REJECTED], true))
                        ->form([
                            Textarea::make('admin_remarks')
                                ->label('Rejection Reason (Required)')
                                ->required()
                                ->placeholder('Explain why this claim cannot be fulfilled...')
                                ->rows(3),
                        ])
                        ->action(function (ClaimRequest $record, array $data): void {
                            DB::transaction(function () use ($record, $data) {
                                $user = User::query()->where('id', $record->user_id)->lockForUpdate()->first();
                                $item = ClaimItem::query()->where('id', $record->claim_item_id)->lockForUpdate()->first();

                                // Refund coins
                                if ($user && $record->coins_spent > 0) {
                                    $user->increment('spendable_coins', $record->coins_spent);
                                }

                                // Restore stock if finite
                                if ($item && $item->stock_quantity >= 0) {
                                    $item->increment('stock_quantity');
                                }

                                $record->update([
                                    'status' => ClaimRequest::STATUS_REJECTED,
                                    'admin_remarks' => $data['admin_remarks'] ?? 'Claim rejected by administrator.',
                                ]);
                            });

                            Notification::make()
                                ->title('Claim Rejected & Coins Refunded')
                                ->body("Claim rejected and {$record->coins_spent} TC refunded to {$record->user?->name}.")
                                ->warning()
                                ->send();
                        }),

                    ViewAction::make()->icon('heroicon-m-eye'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ]);
    }
}
