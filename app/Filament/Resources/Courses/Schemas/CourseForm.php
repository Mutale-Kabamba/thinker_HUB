<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('overview')
                    ->helperText('High-level introduction shown in the course details modal.')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('timeline')
                    ->helperText('Example: 4 Weeks (approx. 4-5 hours per week)')
                    ->columnSpanFull(),
                Textarea::make('fees')
                    ->helperText('Add fee details, one item per line. Example: Beginner - One-on-One: K350 (6 Weeks)')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('requirements')
                    ->helperText('Add each requirement on a new line.')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('level_progression')
                    ->helperText('Add level progression points, one level per line.')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('key_outcome')
                    ->helperText('Summarize expected learning outcome after completion.')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
