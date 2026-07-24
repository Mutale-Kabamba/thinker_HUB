<?php

namespace App\Notifications;

use App\Models\CourseSession;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RescheduleRequestNotification extends Notification
{
    use ResolvesMailPersonalization;

    public function __construct(
        private readonly CourseSession $session,
        private readonly int $studentId,
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
            ->subject('Reschedule Request from '.$this->studentName)
            ->markdown('emails.reschedule-request', [
                'session' => $this->session,
                'studentName' => $this->studentName,
                'reason' => $this->reason,
                'preferredDate' => $this->preferredDate,
                'preferredTime' => $this->preferredTime,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $courseName = $this->session->course->title ?? 'Course';
        $url = $notifiable->role === 'instructor' ? '/teach/schedule' : '/manage/course-sessions';
        $message = $courseName.' ('.$this->session->session_date->format('M j').'): '.$this->reason;

        // Filament bell payload merged with the legacy keys the reschedule
        // decision workflow reads from stored notification data.
        return array_merge(
            FilamentNotification::make()
                ->title('Reschedule request from '.$this->studentName)
                ->body($message)
                ->actions([
                    Action::make('review')
                        ->label('Review request')
                        ->url($url),
                ])
                ->getDatabaseMessage(),
            [
                'type' => 'reschedule_request',
                'message' => $message,
                'session_id' => $this->session->id,
                'course_id' => $this->session->course_id,
                'student_id' => $this->studentId,
                'student_name' => $this->studentName,
                'reason' => $this->reason,
                'preferred_date' => $this->preferredDate,
                'preferred_time' => $this->preferredTime,
                'url' => $url,
            ],
        );
    }
}
