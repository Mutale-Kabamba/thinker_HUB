<?php

namespace App\Filament\Instructor\Concerns;

trait ScopedToInstructor
{
    protected static function instructorCourseIds(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return \App\Models\Course::query()
            ->where('instructor_id', $user->id)
            ->orWhere('course_by', $user->id)
            ->orWhereHas('instructors', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id')
            ->merge($user->instructorCourses()->pluck('courses.id'))
            ->unique()
            ->all();
    }

    protected static function instructorCourseOptions(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return \App\Models\Course::query()
            ->where(function ($query) use ($user) {
                $query->where('instructor_id', $user->id)
                    ->orWhere('course_by', $user->id)
                    ->orWhereHas('instructors', fn ($q) => $q->where('users.id', $user->id));
            })
            ->where('is_active', true)
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }
}
