<?php

namespace App\Filament\Instructor\Resources\AssessmentResource\Pages;

use App\Filament\Instructor\Resources\AssessmentResource\AssessmentResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditAssessment extends BaseEditRecord
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
