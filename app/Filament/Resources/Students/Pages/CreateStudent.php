<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Pages\BaseCreateRecord;
use Illuminate\Support\Facades\Log;

class CreateStudent extends BaseCreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'student';
        $data['email_verified_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->saveQuietly();

            Log::info('Admin-created student auto-verified.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }
    }
}
