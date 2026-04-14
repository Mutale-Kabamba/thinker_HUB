<?php

namespace App\Filament\Instructor\Pages;

use App\Models\Assessment;
use App\Models\Course;
use Filament\Pages\Page;

class InstructorOverview extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Instructor Dashboard';

    protected string $view = 'filament.instructor.pages.overview';

    public array $courses = [];

    public int $totalStudents = 0;

    public int $totalAssessments = 0;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $instructorCourses = $user->instructorCourses()->withCount('enrollments')->get();

        $this->courses = $instructorCourses->map(fn (Course $course) => [
            'id' => $course->id,
            'title' => $course->title,
            'code' => $course->code,
            'students' => $course->enrollments_count ?? 0,
            'is_active' => $course->is_active,
        ])->toArray();

        $this->totalStudents = $instructorCourses->sum('enrollments_count');

        $courseIds = $instructorCourses->pluck('id')->toArray();
        $this->totalAssessments = Assessment::query()->whereIn('course_id', $courseIds)->count();
    }
}
