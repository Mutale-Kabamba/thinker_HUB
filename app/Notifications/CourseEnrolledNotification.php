<?php

namespace App\Notifications;

use App\Models\Course;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class CourseEnrolledNotification extends Notification
{
    public function __construct(
        private readonly Course $course,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
