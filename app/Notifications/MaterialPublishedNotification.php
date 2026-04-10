<?php

namespace App\Notifications;

use App\Models\LearningMaterial;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaterialPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly LearningMaterial $material)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
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
