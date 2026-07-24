<?php

namespace App\Notifications;

use App\Models\Course;
use Filament\Actions\Action;
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
            ->title('You were enrolled in a course')
            ->body('You are now enrolled in '.($this->course->title ?? 'a new course').'.')
            ->actions([
                Action::make('view')
                    ->label('View my courses')
                    ->url('/learn/courses'),
            ])
            ->getDatabaseMessage();
    }
}
