<?php

namespace App\Notifications;

use App\Models\Course;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseEnrolledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Course $course,
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
            ->subject('Enrollment Confirmed: '.$this->course->title.' — think.er HUB')
            ->view('emails.course-enrolled', [
                'course' => $this->course,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Enrolled in '.$this->course->title)
            ->body('You are now enrolled. Explore course materials and your schedule.')
            ->actions([
                Action::make('view')
                    ->label('Go to course')
                    ->url('/learn/courses'),
            ])
            ->getDatabaseMessage();
    }
}
