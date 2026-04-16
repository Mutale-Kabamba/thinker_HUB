<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Events\Registered;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;

        if (! $user->hasVerifiedEmail()) {
            event(new Registered($user));
        }
    }
}
