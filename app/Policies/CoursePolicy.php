<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->teachesCourse($user, $course->id)) {
            return true;
        }

        return $user->isEnrolledInCourse($course->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || $this->teachesCourse($user, $course->id);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin();
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
