<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($assessment->user_id !== $user->id || ! $assessment->course_id) {
            return false;
        }

        return $user->isEnrolledInCourse($assessment->course_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($assessment->user_id !== $user->id || ! $assessment->course_id) {
            return false;
        }

        return $user->isEnrolledInCourse($assessment->course_id);
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }
}
