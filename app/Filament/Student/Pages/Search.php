<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
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
        'my_assignment_submissions' => [],
        'my_assessment_submissions' => [],
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
                'my_assignment_submissions' => [],
                'my_assessment_submissions' => [],
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
            ->get(['id', 'title', 'code'])
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
            ->get(['id', 'title', 'material_type'])
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

        $this->results['my_assignment_submissions'] = AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('assignment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with('assignment:id,name')
            ->limit(8)
            ->get()
            ->map(fn (AssignmentSubmission $s): array => [
                'assignment' => $s->assignment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'grade' => $s->grade,
            ])
            ->toArray();

        $this->results['my_assessment_submissions'] = AssessmentSubmission::query()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('assessment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with('assessment:id,name')
            ->limit(8)
            ->get()
            ->map(fn (AssessmentSubmission $s): array => [
                'assessment' => $s->assessment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'score' => $s->score,
            ])
            ->toArray();
    }
}
