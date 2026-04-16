<?php

namespace App\Filament\Instructor\Resources\AssessmentResource\Pages;

use App\Filament\Instructor\Resources\AssessmentResource\AssessmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssessments extends ListRecords
{
    protected static string $resource = AssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
