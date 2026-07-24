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

        return $user->isEnrolledInCourse($material->course_id)
            || $this->teachesCourse($user, $material->course_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isInstructor();
    }

    public function update(User $user, LearningMaterial $material): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $material->course_id
            && $this->teachesCourse($user, $material->course_id);
    }

    public function delete(User $user, LearningMaterial $material): bool
    {
        return $this->update($user, $material);
    }

    protected function teachesCourse(User $user, ?int $courseId): bool
    {
        if (! $courseId || ! $user->isInstructor()) {
            return false;
        }

        return $user->instructorCourses()
            ->where('courses.id', $courseId)
            ->exists();
    }
}
