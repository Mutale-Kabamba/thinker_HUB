<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.student.pages.settings';
}
