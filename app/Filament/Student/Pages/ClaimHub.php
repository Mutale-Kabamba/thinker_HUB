<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class ClaimHub extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static string|\UnitEnum|null $navigationGroup = 'GROWTH & SOCIAL';

    protected static ?string $navigationLabel = 'Claim Hub';

    protected static ?int $navigationSort = 15;

    protected static ?string $title = 'Claim Hub & Rewards Store';

    protected static ?string $slug = 'claim-hub';

    protected string $view = 'filament.student.pages.claim-hub';
}
