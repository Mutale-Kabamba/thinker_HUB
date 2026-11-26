<?php

namespace App\Notifications;

use App\Models\Certificate;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Certificate $certificate,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $courseTitle = $this->certificate->course?->title ?? 'your course';

        return (new MailMessage)
            ->subject('Certificate Awarded: '.$courseTitle.' — think.er HUB')
            ->view('emails.certificate-issued', [
                'certificate' => $this->certificate,
                'notifiable' => $notifiable,
            ]);
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
