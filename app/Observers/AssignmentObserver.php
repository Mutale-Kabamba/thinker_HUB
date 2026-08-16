<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Models\User;
use App\Notifications\AssignmentAssignedNotification;
use Illuminate\Support\Facades\DB;

class AssignmentObserver
{
    private function notifyUser(User $user, Assignment $assignment): void
    {
        // Prevent duplicate notifications
        $alreadyNotified = DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('type', AssignmentAssignedNotification::class)
            ->where('data', 'like', '%"title":"New assignment assigned"%')
            ->where('data', 'like', '%' . addcslashes($assignment->name, '%_\\') . '%')
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        try {
            $user->notify(new AssignmentAssignedNotification($assignment));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function created(Assignment $assignment): void
    {
        $this->handleRelease($assignment);
    }

    public function updated(Assignment $assignment): void
    {
        $this->handleRelease($assignment);
    }

    public function handleRelease(Assignment $assignment): void
    {
        if (! $assignment->isReleased()) {
            return;
        }

        if ($assignment->target_user_id) {
            $target = User::query()->find($assignment->target_user_id);

            if ($target) {
                $this->notifyUser($target, $assignment);
            }

            return;
        }

        $users = User::query()->where(function ($query): void {
            $query->whereNull('role')->orWhere('role', 'student');
        });

        if ($assignment->course_id) {
            $users->whereHas('enrollments', fn ($query) => $query->where('course_id', $assignment->course_id));
        }

        $targetLevel = trim((string) ($assignment->target_level ?: $assignment->target_track));

        if ($targetLevel !== '') {
            $users->where(function ($query) use ($targetLevel): void {
                $query->where('track', $targetLevel)->orWhereNull('track');
            });
        }

        $users->whereNotNull('email')->where('email', '!=', '')->get()->each(fn (User $user) => $this->notifyUser($user, $assignment));
    }
}
