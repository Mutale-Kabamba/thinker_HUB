<?php

namespace App\Filament\Instructor\Pages;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Pages\AttendanceRegister as BaseAttendanceRegister;
use App\Models\Course;
use App\Models\CourseSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AttendanceRegister extends BaseAttendanceRegister
{
    use ScopedToInstructor;

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->isInstructor() || $user->isAdmin()));
    }

    protected function getBaseSessionsQuery(): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return CourseSession::query()->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return CourseSession::query();
        }

        $courseIds = static::instructorCourseIds();

        return CourseSession::query()
            ->where(function (Builder $q) use ($user, $courseIds): void {
                $q->where('instructor_id', $user->id)
                    ->orWhereIn('course_id', $courseIds);
            });
    }

    public function getAvailableCourses(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        if ($user->isAdmin()) {
            return Course::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get();
        }

        $courseIds = static::instructorCourseIds();

        return Course::query()
            ->whereIn('id', $courseIds)
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }
}
