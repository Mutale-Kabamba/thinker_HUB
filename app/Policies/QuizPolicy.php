<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $quiz->course_id) {
            return false;
        }

        if ($this->teachesCourse($user, $quiz->course_id)) {
            return true;
        }

        return $user->isEnrolledInCourse($quiz->course_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isInstructor();
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $quiz->course_id
            && $this->teachesCourse($user, $quiz->course_id);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
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
