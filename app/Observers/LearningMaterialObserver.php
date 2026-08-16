<?php

namespace App\Observers;

use App\Models\LearningMaterial;
use App\Models\User;
use App\Notifications\MaterialPublishedNotification;
use Illuminate\Support\Facades\DB;

class LearningMaterialObserver
{
    private function notifyUser(User $user, LearningMaterial $material): void
    {
        // Prevent duplicate notifications
        $alreadyNotified = DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('type', MaterialPublishedNotification::class)
            ->where('data', 'like', '%"title":"New material available"%')
            ->where('data', 'like', '%' . addcslashes($material->title, '%_\\') . '%')
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        try {
            $user->notify(new MaterialPublishedNotification($material));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function created(LearningMaterial $material): void
    {
        if ($material->scope === 'personal' && $material->target_user_id) {
            $target = User::query()->find($material->target_user_id);
            if ($target) {
                $this->notifyUser($target, $material);
            }

            return;
        }

        $users = User::query()->where(function ($query): void {
            $query->whereNull('role')->orWhere('role', 'student');
        });

        if ($material->course_id) {
            $users->whereHas('enrollments', fn ($query) => $query->where('course_id', $material->course_id));
        }

        if ($material->scope === 'level' && $material->target_track) {
            $users->where(function ($query) use ($material): void {
                $query->where('track', $material->target_track)->orWhereNull('track');
            });
        }

        $users->whereNotNull('email')->where('email', '!=', '')->get()->each(fn (User $user) => $this->notifyUser($user, $material));
    }
}
