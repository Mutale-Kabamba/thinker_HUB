<?php

namespace App\Filament\Resources\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

abstract class BaseCreateRecord extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        $panelId = Filament::getCurrentPanel()?->getId() ?? (
            str_starts_with(static::class, 'App\\Filament\\Instructor\\')
                ? 'instructor'
                : (str_starts_with(static::class, 'App\\Filament\\Student\\')
                    ? 'student'
                    : (str_starts_with(static::class, 'App\\Filament\\Contributor\\')
                        ? 'contributor'
                        : 'admin'))
        );

        if (static::getResource()::hasPage('index')) {
            return static::getResource()::getUrl('index', panel: $panelId);
        }

        return $this->getResourceUrl('index', panel: $panelId);
    }
}
