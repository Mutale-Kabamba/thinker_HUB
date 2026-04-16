<?php

namespace App\Filament\Instructor\Resources\AssignmentResource\Pages;

use App\Filament\Instructor\Resources\AssignmentResource\AssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
