<?php

namespace App\Filament\Resources\Assignments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('scope')
                    ->required()
                    ->default('all'),
                TextInput::make('target_track'),
                Select::make('target_user_id')
                    ->relationship('targetUser', 'name'),
                DatePicker::make('due_date'),
                Select::make('course_id')
                    ->relationship('course', 'title'),
            ]);
    }
}
