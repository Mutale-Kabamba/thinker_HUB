<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'SYSTEM';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.student.pages.settings';
}
