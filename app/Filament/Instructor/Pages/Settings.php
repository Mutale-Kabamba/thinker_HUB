<?php

namespace App\Filament\Instructor\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.instructor.pages.settings';
}
