<?php

namespace App\Notifications;

use App\Models\Certificate;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification
{
    public function __construct(
        private readonly Certificate $certificate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Certificate issued')
            ->body('Your certificate for '.($this->certificate->course?->title ?? 'your course').' is ready to download.')
            ->actions([
                Action::make('view')
                    ->label('View certificates')
                    ->url('/learn/certificates'),
            ])
            ->getDatabaseMessage();
    }
}
