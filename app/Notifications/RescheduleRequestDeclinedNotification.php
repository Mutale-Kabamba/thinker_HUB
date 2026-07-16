<?php

namespace App\Notifications;

use App\Models\CourseSession;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RescheduleRequestDeclinedNotification extends Notification
{
    use ResolvesMailPersonalization;
    public function __construct(
        private readonly CourseSession $session,
        private readonly string $courseName,
        private readonly ?string $reason = null,
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
        return (new MailMessage)
            ->subject('Reschedule Request Declined: '.$this->courseName)
            ->markdown('emails.reschedule-request-declined', [
                'session' => $this->session,
                'courseName' => $this->courseName,
                'reason' => $this->reason,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $message = 'Your reschedule request for '.$this->courseName.' was declined.';

        if (filled($this->reason)) {
            $message .= ' Reason: '.$this->reason;
        }

        return [
            'type' => 'reschedule_request_declined',
            'title' => 'Reschedule request declined',
            'message' => $message,
            'session_id' => $this->session->id,
            'course_id' => $this->session->course_id,
            'url' => '/learn/schedule',
        ];
    }
}
