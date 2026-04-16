<?php

namespace App\Filament\Instructor\Resources\StudentResource\Pages;

use App\Filament\Instructor\Resources\StudentResource\StudentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'student';

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if (! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();

                Log::info('Verification email sent to instructor-created student.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send verification email to student.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
