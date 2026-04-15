<?php

namespace App\Notifications;

use App\Models\CourseSession;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RescheduleRequestNotification extends Notification
{
    public function __construct(
        private readonly CourseSession $session,
        private readonly string $studentName,
        private readonly string $reason,
        private readonly ?string $preferredDate,
        private readonly ?string $preferredTime,
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
            ->subject('📅 Reschedule Request from '.$this->studentName)
            ->markdown('emails.reschedule-request', [
                'session' => $this->session,
                'studentName' => $this->studentName,
                'reason' => $this->reason,
                'preferredDate' => $this->preferredDate,
                'preferredTime' => $this->preferredTime,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $courseName = $this->session->course->title ?? 'Course';

        return [
            'type' => 'reschedule_request',
            'title' => 'Reschedule request from '.$this->studentName,
            'message' => $courseName.' ('.$this->session->session_date->format('M j').'): '.$this->reason,
            'session_id' => $this->session->id,
            'course_id' => $this->session->course_id,
            'student_name' => $this->studentName,
            'reason' => $this->reason,
            'preferred_date' => $this->preferredDate,
            'preferred_time' => $this->preferredTime,
            'url' => $notifiable->role === 'instructor' ? '/teach/schedule' : '/admin/course-sessions',
        ];
    }
}
