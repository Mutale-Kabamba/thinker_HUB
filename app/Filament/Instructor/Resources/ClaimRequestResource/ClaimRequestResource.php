<?php

namespace App\Filament\Instructor\Resources\ClaimRequestResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\ClaimRequestResource\Pages\ListClaimRequests;
use App\Filament\Instructor\Resources\ClaimRequestResource\Pages\ViewClaimRequest;
use App\Models\ClaimItem;
use App\Models\ClaimRequest;
use App\Models\Course;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClaimRequestResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = ClaimRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|\UnitEnum|null $navigationGroup = 'REWARDS & CLAIMS';

    protected static ?string $navigationLabel = 'Student Claim Requests';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\ClaimRequests\Schemas\ClaimRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
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

                TextColumn::make('claimItem.course.title')
                    ->label('Course')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

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
                SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(fn (): array => Course::query()
                        ->whereIn('id', static::instructorCourseIds())
                        ->pluck('title', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'])
                        ? $query->whereHas('claimItem', fn (Builder $q) => $q->where('course_id', $data['value']))
                        : $query
                    ),

                SelectFilter::make('status')
                    ->options(ClaimRequest::STATUSES),

                SelectFilter::make('category')
                    ->label('Item Category')
                    ->options(ClaimItem::CATEGORIES)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'])
                        ? $query->whereHas('claimItem', fn (Builder $q) => $q->where('category', $data['value']))
                        : $query
                    ),
            ])
            ->recordActions([
                Action::make('fulfill')
                    ->label('Fulfill & Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (ClaimRequest $record): bool => ! in_array($record->status, [ClaimRequest::STATUS_FULFILLED, ClaimRequest::STATUS_REJECTED], true))
                    ->form([
                        TextInput::make('admin_remarks')
                            ->label('Fulfillment Reference / Airtime TXN ID')
                            ->placeholder('e.g. Sent via MTN Mobile Money TXN #984321 / Handed merch to student')
                            ->maxLength(255),
                    ])
                    ->action(function (ClaimRequest $record, array $data): void {
                        $record->update([
                            'status' => ClaimRequest::STATUS_FULFILLED,
                            'fulfilled_at' => now(),
                            'admin_remarks' => $data['admin_remarks'] ?? $record->admin_remarks,
                        ]);

                        Notification::make()
                            ->title('Claim Fulfilled')
                            ->body("The claim for {$record->claimItem->title} has been marked as fulfilled.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject & Refund')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ClaimRequest $record): bool => ! in_array($record->status, [ClaimRequest::STATUS_FULFILLED, ClaimRequest::STATUS_REJECTED], true))
                    ->requiresConfirmation()
                    ->modalHeading('Reject Claim and Refund Thinker Coins')
                    ->modalDescription('This will refund the student their spent Thinker Coins and restore 1 stock quantity back to the reward item.')
                    ->form([
                        Textarea::make('admin_remarks')
                            ->label('Reason for Rejection')
                            ->required()
                            ->placeholder('e.g. Invalid phone number provided / Item temporarily unavailable')
                            ->rows(3),
                    ])
                    ->action(function (ClaimRequest $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            /** @var User $student */
                            $student = User::query()->where('id', $record->user_id)->lockForUpdate()->first();
                            /** @var ClaimItem $item */
                            $item = ClaimItem::query()->where('id', $record->claim_item_id)->lockForUpdate()->first();

                            // Refund coins
                            if ($student) {
                                $student->increment('spendable_coins', $record->coins_spent);
                            }

                            // Restore stock
                            if ($item && ! $item->isUnlimited()) {
                                $item->increment('stock_quantity');
                            }

                            // Update request
                            $record->update([
                                'status' => ClaimRequest::STATUS_REJECTED,
                                'admin_remarks' => $data['admin_remarks'],
                            ]);
                        });

                        Notification::make()
                            ->title('Claim Rejected & Coins Refunded')
                            ->body("Refunded {$record->coins_spent} TC to {$record->user->name}.")
                            ->warning()
                            ->send();
                    }),

                ViewAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('claimItem', fn (Builder $q) => $q->whereIn('course_id', static::instructorCourseIds())));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClaimRequests::route('/'),
            'view' => ViewClaimRequest::route('/{record}'),
        ];
    }
}
