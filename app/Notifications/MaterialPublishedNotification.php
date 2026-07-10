<?php

namespace App\Notifications;

use App\Models\LearningMaterial;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialPublishedNotification extends Notification
{
    use ResolvesMailPersonalization;

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
            ->subject('New Material: '.$this->material->title)
            ->markdown('emails.material-published', [
                'material' => $this->material,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'material_published',
            'title' => 'New material available',
            'message' => $this->material->title,
            'material_id' => $this->material->id,
            'course_id' => $this->material->course_id,
            'url' => '/learn/materials',
        ];
    }
}
