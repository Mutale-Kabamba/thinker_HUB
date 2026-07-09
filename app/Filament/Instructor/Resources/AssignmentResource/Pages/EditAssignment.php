<?php

namespace App\Filament\Instructor\Resources\AssignmentResource\Pages;

use App\Filament\Instructor\Resources\AssignmentResource\AssignmentResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditAssignment extends BaseEditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
