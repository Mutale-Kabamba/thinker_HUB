<?php

namespace App\Policies;

use App\Models\LearningMaterial;
use App\Models\User;

class LearningMaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LearningMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $material->course_id) {
            return false;
        }

        return $user->isEnrolledInCourse($material->course_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, LearningMaterial $material): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, LearningMaterial $material): bool
    {
        return $user->isAdmin();
    }
}
