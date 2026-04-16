<?php

namespace App\Filament\Instructor\Resources\AssessmentResource\Pages;

use App\Filament\Instructor\Resources\AssessmentResource\AssessmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessment extends EditRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
