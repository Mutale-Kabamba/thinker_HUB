<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin() || $actor->isInstructor();
    }

    public function view(User $actor, Enrollment $enrollment): bool
    {
        return $actor->isAdmin()
            || $actor->id === $enrollment->user_id
            || ($actor->isInstructor() && $actor->instructorCourses()->where('courses.id', $enrollment->course_id)->exists());
    }

    public function create(User $actor, ?User $owner = null): bool
    {
        return $actor->isAdmin() || $actor->isInstructor() || ($owner && $actor->id === $owner->id);
    }

    public function update(User $actor, Enrollment $enrollment): bool
    {
        return $actor->isAdmin()
            || $actor->id === $enrollment->user_id
            || ($actor->isInstructor() && $actor->instructorCourses()->where('courses.id', $enrollment->course_id)->exists());
    }

    public function delete(User $actor, Enrollment $enrollment): bool
    {
        return $actor->isAdmin()
            || $actor->id === $enrollment->user_id
            || ($actor->isInstructor() && $actor->instructorCourses()->where('courses.id', $enrollment->course_id)->exists());
    }
}
