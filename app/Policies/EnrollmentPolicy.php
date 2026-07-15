<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function view(User $actor, Enrollment $enrollment): bool
    {
        return $actor->isAdmin() || $actor->id === $enrollment->user_id;
    }

    public function create(User $actor, ?User $owner = null): bool
    {
        return $actor->isAdmin() || ($owner && $actor->id === $owner->id);
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
