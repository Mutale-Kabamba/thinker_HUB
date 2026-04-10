<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function view(User $actor, Enrollment $enrollment): bool
    {
        return $actor->isAdmin() || $actor->id === $enrollment->user_id;
    }

    public function create(User $actor, User $owner): bool
    {
        return $actor->isAdmin() || $actor->id === $owner->id;
    }

    public function update(User $actor, Enrollment $enrollment): bool
    {
        return $actor->isAdmin() || $actor->id === $enrollment->user_id;
    }

    public function delete(User $actor, Enrollment $enrollment): bool
    {
        return $actor->isAdmin() || $actor->id === $enrollment->user_id;
    }
}
