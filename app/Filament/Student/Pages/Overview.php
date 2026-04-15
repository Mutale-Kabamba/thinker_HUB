<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\CourseSession;
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

    public array $calendarEvents = [];

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

        $this->loadCalendar(Carbon::today());
    }

    public function navigateCalendar(int $year, int $month): void
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $this->loadCalendar($date);
    }

    protected function loadCalendar(Carbon $reference): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $monthStart = $reference->copy()->startOfMonth();
        $monthEnd = $reference->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $today = Carbon::today();

        $visibleAssignments = Assignment::query()
            ->with('course')
            ->visibleTo($user)
            ->get();

        $assessmentRecords = Assessment::query()
            ->with('course')
            ->visibleTo($user)
            ->get();

        $assignmentSubmissions = AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('assignment_id');

        $assessmentSubmissions = AssessmentSubmission::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('assessment_id');

        $assignmentDueMap = $visibleAssignments
            ->filter(fn (Assignment $a): bool => (bool) $a->due_date && $a->due_date->between($monthStart, $monthEnd))
            ->groupBy(fn (Assignment $a): string => $a->due_date->format('Y-m-d'));

        $assessmentDueMap = $assessmentRecords
            ->filter(fn (Assessment $a): bool => (bool) $a->due_date && $a->due_date->between($monthStart, $monthEnd))
            ->groupBy(fn (Assessment $a): string => $a->due_date->format('Y-m-d'));

        $courseIds = $user->courses()->pluck('courses.id')->all();
        $sessions = CourseSession::query()
            ->with('course')
            ->where(function ($q) use ($courseIds, $user) {
                $q->whereIn('course_id', $courseIds)
                    ->where(function ($q2) use ($user) {
                        $q2->where('type', 'group')
                            ->orWhere('student_id', $user->id);
                    });
            })
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->get();

        $sessionDateMap = [];
        foreach ($sessions as $s) {
            $effectiveDate = $s->getEffectiveDate();
            if ($effectiveDate->between($monthStart, $monthEnd)) {
                $key = $effectiveDate->format('Y-m-d');
                $sessionDateMap[$key][] = $s;
            }
        }

        $days = [];
        $events = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day);
            $key = $date->format('Y-m-d');

            $dayAssignments = $assignmentDueMap->get($key, collect());
            $dayAssessments = $assessmentDueMap->get($key, collect());
            $daySessions = $sessionDateMap[$key] ?? [];
            $hasItems = $dayAssignments->isNotEmpty() || $dayAssessments->isNotEmpty() || count($daySessions) > 0;

            $sessionNames = collect($daySessions)->map(fn ($s) => ($s->title ?: $s->course?->title ?? 'Session').' @ '.Carbon::parse($s->getEffectiveStartTime())->format('g:i A'));

            $days[] = [
                'day' => $day,
                'date' => $key,
                'is_today' => $date->isToday(),
                'is_past' => $date->lt($today),
                'has_due' => $hasItems,
                'assignment_count' => $dayAssignments->count(),
                'assessment_count' => $dayAssessments->count(),
                'session_count' => count($daySessions),
                'due_names' => $dayAssignments->pluck('name')->merge($dayAssessments->pluck('name'))->merge($sessionNames)->filter()->values()->all(),
            ];

            if ($hasItems) {
                $items = [];

                foreach ($dayAssignments as $a) {
                    $sub = $assignmentSubmissions->get($a->id);
                    $items[] = [
                        'type' => 'Assignment',
                        'name' => $a->name,
                        'course' => $a->course?->title ?? 'Unassigned',
                        'status' => $sub?->status ?? 'Not submitted',
                        'grade' => $sub?->grade,
                    ];
                }

                foreach ($dayAssessments as $a) {
                    $sub = $assessmentSubmissions->get($a->id);
                    $items[] = [
                        'type' => 'Assessment',
                        'name' => $a->name ?: 'Assessment',
                        'course' => $a->course?->title ?? 'Unassigned',
                        'status' => $sub?->status ?? 'Not submitted',
                        'grade' => $sub?->score,
                    ];
                }

                foreach ($daySessions as $s) {
                    $items[] = [
                        'type' => 'Session',
                        'name' => $s->title ?: ($s->course?->title ?? 'Session'),
                        'course' => $s->course?->title ?? 'Unassigned',
                        'status' => ucfirst($s->status),
                        'grade' => null,
                        'time' => Carbon::parse($s->getEffectiveStartTime())->format('g:i A').' – '.Carbon::parse($s->getEffectiveEndTime())->format('g:i A'),
                        'session_type' => $s->type === 'one_on_one' ? 'One-On-One' : 'Group',
                    ];
                }

                $events[$key] = $items;
            }
        }

        $this->calendar = [
            'month' => $reference->format('F Y'),
            'month_num' => (int) $reference->format('m'),
            'year' => (int) $reference->format('Y'),
            'start_day' => $monthStart->dayOfWeek,
            'days' => $days,
            'prev' => ['year' => (int) $reference->copy()->subMonth()->format('Y'), 'month' => (int) $reference->copy()->subMonth()->format('m')],
            'next' => ['year' => (int) $reference->copy()->addMonth()->format('Y'), 'month' => (int) $reference->copy()->addMonth()->format('m')],
        ];

        $this->calendarEvents = $events;
    }
}
