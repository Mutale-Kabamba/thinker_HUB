<?php

namespace App\Filament\Pages;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Pages\Page;

class Search extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 90;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.search';

    public string $query = '';

    public array $results = [
        'students' => [],
        'courses' => [],
        'assignments' => [],
        'assessments' => [],
        'materials' => [],
        'enrollments' => [],
        'assignment_submissions' => [],
        'assessment_submissions' => [],
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
        $term = trim($this->query);

        if ($term === '') {
            $this->results = [
                'students' => [],
                'courses' => [],
                'assignments' => [],
                'assessments' => [],
                'materials' => [],
                'enrollments' => [],
                'assignment_submissions' => [],
                'assessment_submissions' => [],
            ];

            return;
        }

        $this->results['students'] = User::query()
            ->where('role', 'student')
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'name', 'email'])
            ->toArray();

        $this->results['courses'] = Course::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'code'])
            ->toArray();

        $this->results['assignments'] = Assignment::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'name', 'scope'])
            ->toArray();

        $this->results['assessments'] = Assessment::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")->orWhereRaw('CAST(score as CHAR) like ?', ["%{$term}%"]))
            ->limit(8)
            ->get(['id', 'name', 'score'])
            ->toArray();

        $this->results['materials'] = LearningMaterial::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('file_name', 'like', "%{$term}%")->orWhere('material_type', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'material_type'])
            ->toArray();

        $this->results['enrollments'] = Enrollment::query()
            ->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%"))
            ->orWhereHas('course', fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->with(['user:id,name', 'course:id,title,code'])
            ->limit(8)
            ->get()
            ->map(fn (Enrollment $e): array => [
                'id' => $e->id,
                'student' => $e->user?->name ?? 'Unknown',
                'course' => $e->course?->code.' - '.$e->course?->title,
                'course_id' => $e->course_id,
            ])
            ->toArray();

        $this->results['assignment_submissions'] = AssignmentSubmission::query()
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$term}%"))
                ->orWhereHas('assignment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with(['user:id,name', 'assignment:id,name'])
            ->limit(8)
            ->get()
            ->map(fn (AssignmentSubmission $s): array => [
                'id' => $s->id,
                'student' => $s->user?->name ?? 'Unknown',
                'assignment' => $s->assignment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'grade' => $s->grade,
            ])
            ->toArray();

        $this->results['assessment_submissions'] = AssessmentSubmission::query()
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$term}%"))
                ->orWhereHas('assessment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with(['user:id,name', 'assessment:id,name'])
            ->limit(8)
            ->get()
            ->map(fn (AssessmentSubmission $s): array => [
                'id' => $s->id,
                'student' => $s->user?->name ?? 'Unknown',
                'assessment' => $s->assessment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'score' => $s->score,
            ])
            ->toArray();
    }
}
