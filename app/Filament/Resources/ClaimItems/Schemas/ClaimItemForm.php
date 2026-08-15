<?php

namespace App\Filament\Resources\ClaimItems\Schemas;

use App\Models\ClaimItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClaimItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. 5GB Monthly High-Speed Data Bundle'),

                Select::make('course_id')
                    ->label('Assigned Course')
                    ->placeholder('General Platform Reward (Available to All)')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Leave empty for general platform rewards, or select a course to tie this reward specifically to that course.'),

                Select::make('category')
                    ->options(ClaimItem::CATEGORIES)
                    ->required(),

                TextInput::make('coin_cost')
                    ->label('Cost in Thinker Coins (TC)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->placeholder('e.g. 350'),

                TextInput::make('stock_quantity')
                    ->label('Stock Quantity (-1 for unlimited)')
                    ->numeric()
                    ->default(-1)
                    ->required()
                    ->helperText('Enter -1 for infinite digital stock or exact integer count for limited physical swag.'),

                FileUpload::make('image_path')
                    ->label('Reward Image')
                    ->image()
                    ->directory('claim-items')
                    ->disk('public')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull()
                    ->placeholder('Detailed description of this reward and fulfillment details...'),

                Toggle::make('is_active')
                    ->label('Active & Available in Storefront')
                    ->default(true),
            ]);
    }
}
