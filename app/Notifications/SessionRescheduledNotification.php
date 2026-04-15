<?php

namespace App\Notifications;

use App\Models\CourseSession;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionRescheduledNotification extends Notification
{
    public function __construct(
        private readonly CourseSession $session,
        private readonly string $courseName,
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
            ->subject('📅 Session Rescheduled: '.$this->courseName)
            ->markdown('emails.session-rescheduled', [
                'session' => $this->session,
                'courseName' => $this->courseName,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'session_rescheduled',
            'title' => 'Session rescheduled',
            'message' => $this->courseName.': moved to '.$this->session->rescheduled_date->format('D, M j')
                .' at '.$this->session->rescheduled_start_time,
            'session_id' => $this->session->id,
            'course_id' => $this->session->course_id,
            'url' => '/learn/schedule',
        ];
    }
}
