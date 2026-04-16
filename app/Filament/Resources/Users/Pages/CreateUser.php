<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['role'] ?? '') === 'instructor') {
            $data['track'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if (! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();

                Log::info('Verification email sent to admin-created user.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send verification email to admin-created user.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
