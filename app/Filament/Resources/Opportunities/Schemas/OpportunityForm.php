<?php

namespace App\Filament\Resources\Opportunities\Schemas;

use App\Models\Opportunity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->required()
                    ->options(array_combine(Opportunity::TYPES, Opportunity::TYPES))
                    ->default('Job')
                    ->live(),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('provider')
                    ->label('Provider / Company / Source')
                    ->maxLength(255),

                Section::make('Type Details')
                    ->schema([
                        TextInput::make('extra.company')
                            ->label('Company')
                            ->visible(fn (callable $get): bool => $get('type') === 'Job')
                            ->maxLength(255),
                        TextInput::make('extra.location')
                            ->label('Location')
                            ->visible(fn (callable $get): bool => in_array($get('type'), ['Job', 'Event'], true))
                            ->maxLength(255),
                        TextInput::make('extra.job_mode')
                            ->label('Job Mode (Remote/Hybrid/On-site)')
                            ->visible(fn (callable $get): bool => $get('type') === 'Job')
                            ->maxLength(255),
                        TextInput::make('extra.salary')
                            ->label('Salary / Stipend')
                            ->visible(fn (callable $get): bool => $get('type') === 'Job')
                            ->maxLength(255),
                        TextInput::make('extra.role')
                            ->label('Role / Position')
                            ->visible(fn (callable $get): bool => $get('type') === 'Job')
                            ->maxLength(255),

                        TextInput::make('extra.amount')
                            ->label('Discount / Amount')
                            ->visible(fn (callable $get): bool => $get('type') === 'Promo Code')
                            ->maxLength(255),
                        DatePicker::make('extra.valid_until')
                            ->label('Promo Valid Until')
                            ->native(false)
                            ->visible(fn (callable $get): bool => $get('type') === 'Promo Code'),

                        TextInput::make('extra.host')
                            ->label('Host / Organizer')
                            ->visible(fn (callable $get): bool => in_array($get('type'), ['Event', 'Scholarship'], true))
                            ->maxLength(255),
                        DatePicker::make('extra.event_date')
                            ->label('Event Date')
                            ->native(false)
                            ->visible(fn (callable $get): bool => $get('type') === 'Event'),

                        TextInput::make('extra.author')
                            ->label('Author')
                            ->visible(fn (callable $get): bool => $get('type') === 'Reading Material')
                            ->maxLength(255),
                        TextInput::make('extra.format')
                            ->label('Format (PDF/Article/Video)')
                            ->visible(fn (callable $get): bool => $get('type') === 'Reading Material')
                            ->maxLength(255),

                        TextInput::make('extra.eligibility')
                            ->label('Eligibility')
                            ->visible(fn (callable $get): bool => $get('type') === 'Scholarship')
                            ->maxLength(255),
                        TextInput::make('extra.value')
                            ->label('Scholarship Value')
                            ->visible(fn (callable $get): bool => $get('type') === 'Scholarship')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                TextInput::make('link_url')
                    ->label('Link URL')
                    ->url()
                    ->helperText('Apply link, course link, or reading material URL.')
                    ->maxLength(255),

                TextInput::make('promo_code')
                    ->label('Promo Code')
                    ->visible(fn (callable $get): bool => $get('type') === 'Promo Code')
                    ->maxLength(255),

                DatePicker::make('expires_at')
                    ->label('Expires On')
                    ->helperText('Leave empty for no expiry. Expired items are hidden from students automatically.')
                    ->native(false),

                Toggle::make('is_published')
                    ->label('Published (visible to students)')
                    ->default(true),
            ]);
    }
}
