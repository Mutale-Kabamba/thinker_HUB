<?php

namespace App\Observers;

use App\Models\Assessment;
use App\Models\User;
use App\Notifications\AssessmentAssignedNotification;
use Illuminate\Support\Facades\DB;

class AssessmentObserver
{
    private function notifyUser(User $user, Assessment $assessment): void
    {
        // Prevent duplicate notifications
        $alreadyNotified = DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('type', AssessmentAssignedNotification::class)
            ->where('data', 'like', '%"title":"New assessment assigned"%')
            ->where('data', 'like', '%' . addcslashes($assessment->name, '%_\\') . '%')
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        try {
            $user->notify(new AssessmentAssignedNotification($assessment));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function created(Assessment $assessment): void
    {
        $this->handleRelease($assessment);
    }

    public function updated(Assessment $assessment): void
    {
        $this->handleRelease($assessment);
    }

    public function handleRelease(Assessment $assessment): void
    {
        if (! $assessment->isReleased()) {
            return;
        }

        // If targeted to a single student
        if ($assessment->user_id) {
            $target = User::query()->find($assessment->user_id);

            if ($target) {
                $this->notifyUser($target, $assessment);
            }

            return;
        }

        // Target cohort/track
        $users = User::query()->where(function ($query): void {
            $query->whereNull('role')->orWhere('role', 'student');
        });

        if ($assessment->course_id) {
            $users->whereHas('enrollments', fn ($query) => $query->where('course_id', $assessment->course_id));
        }

        $targetLevel = trim((string) $assessment->target_level);

        if ($targetLevel !== '') {
            $users->where(function ($query) use ($targetLevel): void {
                $query->where('track', $targetLevel)->orWhereNull('track');
            });
        }

        $users->whereNotNull('email')->where('email', '!=', '')->get()->each(fn (User $user) => $this->notifyUser($user, $assessment));
    }
}
