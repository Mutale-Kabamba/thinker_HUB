<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function view(User $actor, User $subject): bool
    {
        return $actor->isAdmin() || $actor->id === $subject->id;
    }

    public function update(User $actor, User $subject): bool
    {
        return $actor->isAdmin() || $actor->id === $subject->id;
    }
}
