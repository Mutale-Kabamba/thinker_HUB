<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $assignment->course_id) {
            return false;
        }

        return $user->isEnrolledInCourse($assignment->course_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin();
    }
}
