<?php

namespace App\Filament\Instructor\Pages;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Pages\Page;

class Search extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 9;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.instructor.pages.search';

    public string $query = '';

    public array $results = [
        'courses' => [],
        'students' => [],
        'sessions' => [],
    ];

    public function mount(): void
    {
        $this->query = (string) request()->query('q', '');
        $this->runSearch();
    }

    public function updatedQuery(): void
    {
        $this->runSearch();
    }

    protected function runSearch(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $term = trim($this->query);

        if ($term === '') {
            $this->results = [
                'courses' => [],
                'students' => [],
                'sessions' => [],
            ];

            return;
        }

        $courseIds = $user->instructorCourses()->pluck('courses.id');

        $this->results['courses'] = Course::query()
            ->whereIn('id', $courseIds)
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'code', 'is_active'])
            ->toArray();

        $this->results['students'] = User::query()
            ->where('role', 'student')
            ->whereHas('courses', fn ($q) => $q->whereIn('courses.id', $courseIds))
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'name', 'email'])
            ->toArray();

        $this->results['sessions'] = CourseSession::query()
            ->whereIn('course_id', $courseIds)
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('status', 'like', "%{$term}%")
                ->orWhereHas('course', fn ($q2) => $q2->where('title', 'like', "%{$term}%")))
            ->with(['course:id,title,code'])
            ->limit(8)
            ->get()
            ->map(fn (CourseSession $s): array => [
                'title' => $s->title ?: ($s->course->title ?? '—'),
                'course' => $s->course->code ?? '',
                'date' => $s->getEffectiveDate()->format('M d, Y'),
                'status' => ucfirst($s->status),
            ])
            ->toArray();
    }
}
