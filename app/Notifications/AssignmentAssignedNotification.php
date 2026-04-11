<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Assignment $assignment)
    {
    }

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
            ->subject('New Assignment Assigned')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new assignment has been assigned to you: '.$this->assignment->name)
            ->line('Due date: '.($this->assignment->due_date?->format('Y-m-d') ?? 'No due date'))
            ->action('Open Dashboard', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_assigned',
            'title' => 'New assignment assigned',
            'message' => $this->assignment->name,
            'assignment_id' => $this->assignment->id,
            'course_id' => $this->assignment->course_id,
            'due_date' => $this->assignment->due_date?->format('Y-m-d'),
        ];
    }
}
