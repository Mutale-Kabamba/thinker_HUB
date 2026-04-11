<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Models\User;
use App\Notifications\AssignmentAssignedNotification;

class AssignmentObserver
{
    public function created(Assignment $assignment): void
    {
        if ($assignment->target_user_id) {
            $target = User::query()->find($assignment->target_user_id);

            if ($target) {
                $target->notify(new AssignmentAssignedNotification($assignment));
            }

            return;
        }

        $users = User::query()->where(function ($query): void {
            $query->whereNull('role')->orWhere('role', '!=', 'admin');
        });

        if ($assignment->course_id) {
            $users->whereHas('courses', fn ($query) => $query->where('courses.id', $assignment->course_id));
        }

        $targetLevel = trim((string) ($assignment->target_level ?: $assignment->target_track));

        if ($targetLevel !== '') {
            $users->where('track', $targetLevel);
        }

        $users->get()->each(fn (User $user) => $user->notify(new AssignmentAssignedNotification($assignment)));
    }
}
