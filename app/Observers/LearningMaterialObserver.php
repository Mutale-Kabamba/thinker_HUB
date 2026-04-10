<?php

namespace App\Observers;

use App\Models\LearningMaterial;
use App\Models\User;
use App\Notifications\MaterialPublishedNotification;

class LearningMaterialObserver
{
    public function created(LearningMaterial $material): void
    {
        $users = User::query()->where('role', 'student');

        if ($material->scope === 'personal' && $material->target_user_id) {
            $target = User::query()->find($material->target_user_id);
            if ($target) {
                $target->notify(new MaterialPublishedNotification($material));
            }

            return;
        }

        if ($material->scope === 'level' && $material->target_track) {
            $users->where('track', $material->target_track);
        }

        if ($material->course_id) {
            $users->whereHas('courses', fn ($query) => $query->where('courses.id', $material->course_id));
        }

        $users->get()->each(fn (User $user) => $user->notify(new MaterialPublishedNotification($material)));
    }
}
