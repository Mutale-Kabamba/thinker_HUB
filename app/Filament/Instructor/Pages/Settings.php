<?php

namespace App\Filament\Instructor\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMUNITY & SYSTEM';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.instructor.pages.settings';
}
use Filament\Forms\Components\Textarea; // or RichEditor

Textarea::make('bio')
    ->label('About / Bio')
    ->rows(5)
    ->placeholder('Write a brief description about yourself, your background, and teaching experience...')
    ->columnSpanFull();