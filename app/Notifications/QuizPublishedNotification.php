<?php

namespace App\Notifications;

use App\Models\Quiz;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuizPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable, ResolvesMailPersonalization;

    public function __construct(private readonly Quiz $quiz) {}

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
            ->subject('New Quiz Available: '.$this->quiz->title)
            ->greeting('Hello '.$this->resolveRecipientName($notifiable).'!')
            ->line('A new quiz has been published for your course: **'.$this->quiz->title.'**.')
            ->when($this->quiz->time_limit_minutes, fn ($mail) => $mail->line('Time Limit: '.$this->quiz->time_limit_minutes.' minutes.'))
            ->line('Pass percentage requirement: '.$this->quiz->pass_percentage.'%.')
            ->action('Take Quiz', url('/learn/quizzes'))
            ->salutation("Best regards,\n".$this->resolveSignerName());
    }

    public function toArray(object $notifiable): array
    {
        $body = $this->quiz->title;

        if ($this->quiz->course) {
            $body .= ' ('.$this->quiz->course->title.')';
        }

        return FilamentNotification::make()
            ->title('New quiz available')
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label('View quizzes')
                    ->url('/learn/quizzes')
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
