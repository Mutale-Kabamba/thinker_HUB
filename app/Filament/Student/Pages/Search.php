<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\LearningMaterial;
use Filament\Pages\Page;

class Search extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 6;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.student.pages.search';

    public string $query = '';

    public array $results = [
        'courses' => [],
        'assignments' => [],
        'materials' => [],
        'assessments' => [],
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
                'assignments' => [],
                'materials' => [],
                'assessments' => [],
            ];

            return;
        }

        $enrolledCourseIds = $user->courses()->pluck('courses.id');

        $this->results['courses'] = Course::query()
            ->whereIn('id', $enrolledCourseIds)
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['title', 'code'])
            ->toArray();

        $this->results['assignments'] = Assignment::query()
            ->visibleTo($user)
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['name', 'due_date'])
            ->map(fn (Assignment $a): array => [
                'name' => $a->name,
                'due' => $a->due_date?->format('Y-m-d') ?? 'No due date',
            ])
            ->toArray();

        $this->results['materials'] = LearningMaterial::query()
            ->visibleTo($user)
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('file_name', 'like', "%{$term}%")
                ->orWhere('material_type', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['title', 'material_type'])
            ->toArray();

        $this->results['assessments'] = Assessment::query()
            ->visibleTo($user)
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereRaw('CAST(score as CHAR) like ?', ["%{$term}%"]))
            ->limit(8)
            ->get(['name', 'score', 'due_date'])
            ->map(fn (Assessment $assessment): array => [
                'name' => $assessment->name ?: 'Assessment',
                'score' => $assessment->score,
                'due_date' => $assessment->due_date?->format('Y-m-d') ?? '-',
            ])
            ->toArray();
    }
}
