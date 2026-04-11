<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\LearningMaterial;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Overview extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Overview';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.student.pages.overview';

    public array $stats = [];

    public array $materials = [];

    public array $quickLinks = [];

    public array $calendar = [];

    public array $upcoming = [];

    public array $assignmentSummary = [];

    public array $assessmentSummary = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $today = Carbon::today();

        $visibleAssignments = Assignment::query()
            ->visibleTo($user)
            ->get();

        $submittedCount = AssessmentSubmission::query()->where('user_id', $user->id)->count();
        $assignmentCount = max($visibleAssignments->count(), 1);
        $completion = (int) round(($submittedCount / $assignmentCount) * 100);

        $assignmentSubmissions = AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('assignment_id');

        $assessmentRecords = Assessment::query()
            ->with('course')
            ->visibleTo($user)
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->latest('id')
            ->get();

        $assessmentSubmissions = AssessmentSubmission::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('assessment_id');

        $visibleMaterials = LearningMaterial::query()
            ->visibleTo($user)
            ->latest()
            ->get();

        $nextDueItem = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->greaterThanOrEqualTo($today))
            ->sortBy('due_date')
            ->first();

        $overdueCount = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->lt($today))
            ->count();

        $this->stats = [
            'greeting' => 'Hello '.$user->name,
            'course' => $user->courses()->orderBy('courses.title')->value('courses.title') ?: 'No course selected',
            'track' => $user->track,
            'submissions' => $submittedCount,
            'assignments' => $visibleAssignments->count(),
            'materials' => $visibleMaterials->count(),
            'next_due' => $nextDueItem?->due_date?->format('Y-m-d') ?: 'No due dates',
            'overdue' => $overdueCount,
            'completion' => max(0, min(100, $completion)),
        ];

        $this->quickLinks = [
            ['label' => 'Overview', 'section' => 'overview'],
            ['label' => 'Assignments', 'section' => 'assignments'],
            ['label' => 'Assessments', 'section' => 'assessments'],
            ['label' => 'Materials', 'section' => 'materials'],
        ];

        $this->materials = $visibleMaterials
            ->take(8)
            ->map(fn (LearningMaterial $item): array => [
                'name' => $item->file_name ?: $item->title,
                'type' => $item->material_type,
                'course' => $item->course?->title ?? 'Unassigned course',
            ])
            ->values()
            ->all();

        $this->upcoming = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->greaterThanOrEqualTo($today))
            ->sortBy('due_date')
            ->take(3)
            ->map(fn (Assignment $item): array => [
                'name' => $item->name,
                'due' => $item->due_date?->format('Y-m-d') ?: '-',
                'status' => $assignmentSubmissions->get($item->id)?->status ?? 'Not submitted',
            ])
            ->values()
            ->all();

        $submittedAssignmentsCount = $assignmentSubmissions->count();
        $pendingAssignmentsCount = max($visibleAssignments->count() - $submittedAssignmentsCount, 0);

        $this->assignmentSummary = [
            'total' => $visibleAssignments->count(),
            'submitted' => $submittedAssignmentsCount,
            'pending' => $pendingAssignmentsCount,
            'next_due' => $nextDueItem?->due_date?->format('Y-m-d') ?: 'No due dates',
        ];

        $scoredAssessments = $assessmentSubmissions
            ->pluck('score')
            ->filter(fn ($value): bool => is_numeric($value));

        $averageScore = $scoredAssessments->isNotEmpty()
            ? round($scoredAssessments->avg(), 1)
            : null;

        $this->assessmentSummary = [
            'total' => $assessmentRecords->count(),
            'submitted' => $assessmentSubmissions->count(),
            'average_score' => $averageScore,
            'items' => $assessmentRecords
                ->take(4)
                ->map(fn (Assessment $item): array => [
                        'name' => $item->name ?: 'Assessment',
                    'course' => $item->course?->title ?? 'Unassigned course',
                        'due_date' => $item->due_date?->format('Y-m-d') ?? '-',
                    'score' => $assessmentSubmissions->get($item->id)?->score ?? '-',
                    'submission_status' => $assessmentSubmissions->get($item->id)?->status ?? 'Not submitted',
                ])
                ->values()
                ->all(),
        ];

        $monthStart = $today->copy()->startOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $dueMap = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->isSameMonth($today))
            ->groupBy(fn (Assignment $item): string => $item->due_date->format('Y-m-d'));

        $days = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day);
            $key = $date->format('Y-m-d');
            $days[] = [
                'day' => $day,
                'is_today' => $date->isToday(),
                'has_due' => $dueMap->has($key),
            ];
        }

        $this->calendar = [
            'month' => $today->format('F Y'),
            'days' => $days,
        ];
    }
}
