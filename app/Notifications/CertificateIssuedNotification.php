<?php

namespace App\Notifications;

use App\Models\Certificate;
use App\Notifications\Concerns\ResolvesMailPersonalization;
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
        $courseTitle = $this->certificate->course?->title ?? 'your course';

        return FilamentNotification::make()
            ->title('Certificate Issued!')
            ->body('Congratulations! You earned a certificate for completing '.$courseTitle.'.')
            ->actions([
                Action::make('view')
                    ->label('View certificate')
                    ->url('/learn/certificates'),
            ])
            ->getDatabaseMessage();
    }
}
