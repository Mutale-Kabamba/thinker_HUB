<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Resources\Assessments\AssessmentResource;
use App\Models\Assessment;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateAssessment extends CreateRecord
{
    protected static string $resource = AssessmentResource::class;

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $baseData = [
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'course_id' => $data['course_id'] ?? null,
            'target_level' => $data['target_level'] ?? null,
            'date_given' => $data['date_given'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'score' => null,
        ];

        if (($data['user_id'] ?? null) !== 'all') {
            return Assessment::query()->create([
                ...$baseData,
                'user_id' => (int) $data['user_id'],
            ]);
        }

        $recipients = User::query()
            ->where(function ($query): void {
                $query->whereNull('role')->orWhere('role', '!=', 'admin');
            })
            ->where('track', $data['target_level'] ?? null)
            ->whereHas('courses', fn ($query) => $query->where('courses.id', $data['course_id'] ?? null))
            ->get(['id']);

        if ($recipients->isEmpty()) {
            throw ValidationException::withMessages([
                'user_id' => 'No students found for the selected course and level.',
            ]);
        }

        $created = null;

        foreach ($recipients as $recipient) {
            $record = Assessment::query()->create([
                ...$baseData,
                'user_id' => $recipient->id,
            ]);

            if ($created === null) {
                $created = $record;
            }
        }

        /** @var Model $created */
        return $created;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create');
    }
}
