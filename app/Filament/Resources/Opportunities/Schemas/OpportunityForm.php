<?php

namespace App\Filament\Resources\Opportunities\Schemas;

use App\Models\Opportunity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
