<?php

namespace App\Filament\Resources\InstructorApplications\Pages;

use App\Filament\Resources\InstructorApplications\InstructorApplicationResource;
use App\Models\InstructorApplication;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditInstructorApplication extends EditRecord
{
    protected static string $resource = InstructorApplicationResource::class;

    protected function afterSave(): void
    {
        /** @var InstructorApplication $application */
        $application = $this->record;

        if ($application->status === 'approved' && $application->user_id === null) {
            $existingUser = User::query()->where('email', $application->email)->first();

            if ($existingUser) {
                $existingUser->update(['role' => 'instructor']);
                $application->update([
                    'user_id' => $existingUser->id,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
            } else {
                $user = User::query()->create([
                    'name' => $application->name,
                    'email' => $application->email,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'instructor',
                    'email_verified_at' => now(),
                ]);

                $application->update([
                    'user_id' => $user->id,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
            }

            Notification::make()
                ->title('Instructor account created')
                ->body("Instructor account for {$application->name} has been created.")
                ->success()
                ->send();
        } elseif ($application->status === 'rejected') {
            $application->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            Notification::make()
                ->title('Application rejected')
                ->body("Application from {$application->name} has been rejected.")
                ->warning()
                ->send();
        }
    }
}
