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

        return $user->instructorCourses()->pluck('courses.id')->all();
    }

    protected static function instructorCourseOptions(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $user->instructorCourses()
            ->where('is_active', true)
            ->orderBy('title')
            ->pluck('title', 'courses.id')
            ->toArray();
    }
}
