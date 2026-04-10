<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Models\User;
use App\Notifications\AssignmentAssignedNotification;

class AssignmentObserver
{
    public function created(Assignment $assignment): void
    {
        $users = User::query()->where('role', 'student');

        if ($assignment->scope === 'personal' && $assignment->target_user_id) {
            $target = User::query()->find($assignment->target_user_id);
            if ($target) {
                $target->notify(new AssignmentAssignedNotification($assignment));
            }

            return;
        }

        if ($assignment->scope === 'level' && $assignment->target_track) {
            $users->where('track', $assignment->target_track);
        }

        if ($assignment->course_id) {
            $users->whereHas('courses', fn ($query) => $query->where('courses.id', $assignment->course_id));
        }

        $users->get()->each(fn (User $user) => $user->notify(new AssignmentAssignedNotification($assignment)));
    }
}
