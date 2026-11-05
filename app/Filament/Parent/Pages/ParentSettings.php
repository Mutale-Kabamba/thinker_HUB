<?php

namespace App\Filament\Parent\Pages;

use Filament\Pages\Page;

class ParentSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'SYSTEM';

    protected static ?string $navigationLabel = 'Parent Settings';

    protected static ?string $title = 'Parent Settings & Preferences';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.parent.pages.parent-settings';
}
