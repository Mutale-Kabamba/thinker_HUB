<?php

namespace App\Notifications;

use App\Models\LearningMaterial;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly LearningMaterial $material)
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
            ->subject('New Learning Material Available')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('New learning material has been published: '.$this->material->title)
            ->action('Open Dashboard', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'material_published',
            'title' => 'New material available',
            'message' => $this->material->title,
            'material_id' => $this->material->id,
            'course_id' => $this->material->course_id,
        ];
    }
}
