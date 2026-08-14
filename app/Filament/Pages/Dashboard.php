<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Admin Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getHeading(): string
    {
        return 'Admin Dashboard';
    }

    public function getSubheading(): ?string
    {
        return 'Thinker HUB Administration & Global Platform Oversight';
    }
}
