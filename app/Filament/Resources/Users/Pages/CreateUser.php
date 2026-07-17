<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Pages\BaseCreateRecord;
use Illuminate\Support\Facades\Log;

class CreateUser extends BaseCreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['role'] ?? '') === 'student') {
            $data['email_verified_at'] = now();
        }

        if (($data['role'] ?? '') !== 'student') {
            $data['track'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->role === 'student' && ! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->saveQuietly();

            Log::info('Admin-created student auto-verified from users page.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);
        }

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
