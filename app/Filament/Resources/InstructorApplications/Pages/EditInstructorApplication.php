<?php

namespace App\Filament\Resources\InstructorApplications\Pages;

use App\Filament\Resources\InstructorApplications\InstructorApplicationResource;
use App\Models\InstructorApplication;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInstructorApplication extends EditRecord
{
    protected static string $resource = InstructorApplicationResource::class;

    protected function afterSave(): void
    {
        /** @var InstructorApplication $application */
        $application = $this->record;

        if ($application->status === 'approved') {
            $user = $application->user;

            if ($user) {
                $user->update([
                    'role' => 'instructor',
                    'is_active' => true,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);

                // Assign the preferred course if it's an existing course application
                if ($application->proposal_type === 'existing' && $application->preferred_course_id) {
                    $user->instructorCourses()->syncWithoutDetaching([$application->preferred_course_id]);
                }
            }

            $application->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            Notification::make()
                ->title('Instructor approved')
                ->body("Instructor account for {$application->name} has been activated.")
                ->success()
                ->send();
        } elseif ($application->status === 'rejected') {
            $application->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Deactivate the user if they have no other approved applications
            $user = $application->user;
            if ($user) {
                $hasOtherApproved = InstructorApplication::query()
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $application->id)
                    ->where('status', 'approved')
                    ->exists();

                if (! $hasOtherApproved) {
                    $user->update(['is_active' => false]);
                }
            }

            Notification::make()
                ->title('Application rejected')
                ->body("Application from {$application->name} has been rejected.")
                ->warning()
                ->send();
        }
    }
}
