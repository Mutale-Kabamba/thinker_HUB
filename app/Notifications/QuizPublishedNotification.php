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

    public string $courseName;

    public function __construct(
        private readonly Quiz $quiz,
        ?string $courseName = null,
    ) {
        $this->courseName = $courseName ?? $quiz->course?->title ?? 'your course';
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
            ->subject('New Quiz Published: '.$this->quiz->title)
            ->markdown('emails.quiz-published', [
                'quiz' => $this->quiz,
                'courseName' => $this->courseName,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New quiz available')
            ->body($this->quiz->title.' is now available for '.$this->courseName)
            ->actions([
                Action::make('view')
                    ->label('Take quiz')
                    ->url('/learn/quizzes')
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
