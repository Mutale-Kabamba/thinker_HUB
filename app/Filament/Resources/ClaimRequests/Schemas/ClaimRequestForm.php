<?php

namespace App\Filament\Resources\ClaimRequests\Schemas;

use App\Models\ClaimRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClaimRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),

                Select::make('claim_item_id')
                    ->relationship('claimItem', 'title')
                    ->disabled(),

                TextInput::make('coins_spent')
                    ->numeric()
                    ->disabled(),

                Select::make('status')
                    ->options(ClaimRequest::STATUSES)
                    ->required(),

                TextInput::make('phone_number')
                    ->label('Phone / WhatsApp')
                    ->tel(),

                Textarea::make('delivery_notes')
                    ->label('Student Delivery Notes')
                    ->rows(3)
                    ->disabled(),

                Textarea::make('admin_remarks')
                    ->label('Admin Remarks & Transaction ID')
                    ->rows(3)
                    ->placeholder('Enter airtime ref, shipment tracking, or rejection reason...'),

                DateTimePicker::make('fulfilled_at')
                    ->label('Fulfilled At'),
            ]);
    }
}
