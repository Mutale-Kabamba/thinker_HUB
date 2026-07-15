<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'COMMUNITY & SYSTEM';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.settings';
}
