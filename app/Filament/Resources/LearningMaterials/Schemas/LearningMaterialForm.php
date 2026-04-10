<?php

namespace App\Filament\Resources\LearningMaterials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LearningMaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('material_type')
                    ->required()
                    ->default('File'),
                TextInput::make('scope')
                    ->required()
                    ->default('all'),
                TextInput::make('target_track'),
                Select::make('target_user_id')
                    ->relationship('targetUser', 'name'),
                TextInput::make('link_url')
                    ->url(),
                TextInput::make('file_name'),
                Select::make('course_id')
                    ->relationship('course', 'title'),
            ]);
    }
}
