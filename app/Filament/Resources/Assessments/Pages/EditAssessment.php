<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAssessment extends EditRecord
{
    protected static string $resource = AssessmentResource::class;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['user_id'] ?? null) === 'all') {
            throw ValidationException::withMessages([
                'user_id' => 'Use Create to send an assessment to all students. Editing supports a single target user only.',
            ]);
        }

        $data['user_id'] = (int) ($data['user_id'] ?? 0);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
