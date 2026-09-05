<?php

namespace App\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;

abstract class BaseEditRecord extends EditRecord
{
    public function areFormActionsSticky(): bool
    {
        return true;
    }

    public function getFormActionsAlignment(): Alignment | string
    {
        return Alignment::End;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Save Changes')
            ->icon('heroicon-m-check-circle');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancel')
            ->icon('heroicon-m-arrow-uturn-left');
    }

    public function getSubheading(): string | Htmlable | null
    {
        if ($this->subheading) {
            return $this->subheading;
        }

        $modelLabel = static::getResource()::getModelLabel();
        $recordKey = $this->getRecord()?->getKey();

        return "Update details, configurations, and associated records for this {$modelLabel}" . ($recordKey ? " (#{$recordKey})" : '') . '.';
    }

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
