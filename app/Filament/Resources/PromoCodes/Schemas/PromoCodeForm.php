<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Models\Course;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Code Settings')
                    ->schema([
                        TextInput::make('code')
                            ->label('Promo Code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))
                            ->placeholder('e.g. WELCOME20, SCHOLAR100')
                            ->helperText('Case-insensitive coupon code entered by students at checkout.'),
                        Textarea::make('description')
                            ->label('Description / Internal Note')
                            ->maxLength(255)
                            ->placeholder('e.g. 20% launch discount for all cohort courses')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Code is Active')
                            ->default(true)
                            ->helperText('Deactivating instantly blocks redemption at checkout regardless of dates.'),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make('Discount Configuration')
                    ->schema([
                        Select::make('discount_type')
                            ->label('Discount Type')
                            ->options([
                                'percentage' => 'Percentage Discount (%)',
                                'fixed' => 'Fixed Amount Discount (ZMW)',
                            ])
                            ->required()
                            ->default('percentage')
                            ->live(),
                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->prefix(fn (callable $get): string => $get('discount_type') === 'percentage' ? '%' : 'K')
                            ->helperText(fn (callable $get): string => $get('discount_type') === 'percentage'
                                ? 'e.g. 20 for 20% off, 100 for 100% free enrollment'
                                : 'e.g. 250 for K250 off the course fee'),
                        TextInput::make('max_discount_amount')
                            ->label('Maximum Discount Cap (ZMW)')
                            ->numeric()
                            ->minValue(1)
                            ->prefix('K')
                            ->placeholder('e.g. 500')
                            ->helperText('Optional upper monetary limit for percentage discounts (leave empty for no cap).')
                            ->visible(fn (callable $get): bool => $get('discount_type') === 'percentage'),
                        TextInput::make('min_order_amount')
                            ->label('Minimum Course Fee Required (ZMW)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('K')
                            ->placeholder('e.g. 300')
                            ->helperText('Code only applies if original course price is at least this amount.'),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),

                Section::make('Restrictions & Usage Limits')
                    ->schema([
                        Select::make('course_id')
                            ->label('Course Scope')
                            ->relationship('course', 'title')
                            ->placeholder('All Courses (Global Promo Code)')
                            ->searchable()
                            ->preload()
                            ->helperText('Restrict this promo code to a specific course or leave blank for all paid courses.'),
                        TextInput::make('max_uses')
                            ->label('Total Redemptions Limit')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Unlimited')
                            ->helperText('Maximum total times this code can be redeemed across all users.'),
                        TextInput::make('max_uses_per_user')
                            ->label('Max Uses Per Student')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->helperText('Maximum times a single student email or user ID can use this code.'),
                        DateTimePicker::make('starts_at')
                            ->label('Valid From')
                            ->helperText('Optional start date/time when code becomes active.'),
                        DateTimePicker::make('expires_at')
                            ->label('Valid Until / Expiration')
                            ->helperText('Optional expiration date/time after which code becomes invalid.'),
                    ])
                    ->columns(['default' => 1, 'md' => 2]),
            ]);
    }
}
