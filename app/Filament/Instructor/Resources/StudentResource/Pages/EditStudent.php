<?php

namespace App\Filament\Instructor\Resources\StudentResource\Pages;

use App\Filament\Instructor\Resources\StudentResource\StudentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
