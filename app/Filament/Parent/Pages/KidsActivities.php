<?php

namespace App\Filament\Parent\Pages;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\XpTransaction;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class KidsActivities extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Activities & Progress';

    protected static ?string $title = 'Kids Activities & Performance';

    protected static ?int $navigationSort = 3;

    protected static string|\UnitEnum|null $navigationGroup = 'ACADEMICS & PROGRESS';

    protected string $view = 'filament.parent.pages.kids-activities';

    #[Url(as: 'kid')]
    public ?int $selectedKidId = null;

    #[Url(as: 'course')]
    public ?int $selectedCourseId = null;

    #[Url(as: 'type')]
    public string $selectedType = 'all'; // all | quiz | assignment | assessment | badge

    public string $search = '';

    public array $kids = [];

    public array $courses = [];

    public array $metrics = [];

    public array $activities = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedSelectedKidId(): void
    {
        $this->loadData();
    }

    public function updatedSelectedCourseId(): void
    {
        $this->loadData();
    }

    public function updatedSelectedType(): void
    {
        $this->loadData();
    }

    public function updatedSearch(): void
    {
        $this->loadData();
    }

    public function filterKid(?int $kidId): void
    {
        $this->selectedKidId = $kidId;
        $this->loadData();
    }

    public function filterType(string $type): void
    {
        $this->selectedType = $type;
        $this->loadData();
    }

    public function loadData(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        /** @var Collection<int, User> $kidsCollection */
        $kidsCollection = $parent->kids()->with(['enrollments.course'])->get();

        $this->kids = $kidsCollection->map(fn (User $k): array => [
            'id' => $k->id,
            'name' => $k->name,
            'avatar' => $k->getFilamentAvatarUrl(),
        ])->all();

        $allKidIds = $kidsCollection->pluck('id')->all();

        $activeKidIds = $this->selectedKidId && in_array($this->selectedKidId, $allKidIds, true)
            ? [$this->selectedKidId]
            : $allKidIds;

        // Courses enrolled by active kids
        $enrollmentCourses = Enrollment::query()
            ->with('course')
            ->whereIn('user_id', $activeKidIds)
            ->get()
            ->pluck('course')
            ->filter()
            ->unique('id');

        $this->courses = $enrollmentCourses->map(fn (Course $c): array => [
            'id' => $c->id,
            'title' => $c->title,
            'is_for_kids' => (bool) $c->is_for_kids,
        ])->values()->all();

        // 1. Quizzes Query
        $quizQuery = QuizAttempt::query()
            ->with(['quiz.course', 'user'])
            ->whereIn('user_id', $activeKidIds)
            ->whereNotNull('completed_at');

        if ($this->selectedCourseId) {
            $quizQuery->whereHas('quiz', fn ($q) => $q->where('course_id', $this->selectedCourseId));
        }

        $allQuizzes = $quizQuery->latest('completed_at')->get();

        // 2. Assignments Query
        $assignmentQuery = AssignmentSubmission::query()
            ->with(['assignment.course', 'user'])
            ->whereIn('user_id', $activeKidIds)
            ->whereNotNull('grade');

        if ($this->selectedCourseId) {
            $assignmentQuery->whereHas('assignment', fn ($q) => $q->where('course_id', $this->selectedCourseId));
        }

        $allAssignments = $assignmentQuery->latest('updated_at')->get();

        // 3. Assessments Query
        $assessmentQuery = AssessmentSubmission::query()
            ->with(['assessment.course', 'user'])
            ->whereIn('user_id', $activeKidIds)
            ->where(fn ($q) => $q->whereNotNull('score')->orWhereNotNull('raw_score'));

        if ($this->selectedCourseId) {
            $assessmentQuery->whereHas('assessment', fn ($q) => $q->where('course_id', $this->selectedCourseId));
        }

        $allAssessments = $assessmentQuery->latest('updated_at')->get();

        // 4. Badges / XP
        $xpQuery = XpTransaction::query()
            ->with('user')
            ->whereIn('user_id', $activeKidIds);

        $allXp = $xpQuery->latest('created_at')->get();

        // Compute Metrics
        $scoreValues = [];
        $passedCount = 0;

        foreach ($allQuizzes as $q) {
            $pct = $q->percentage !== null
                ? (float) $q->percentage
                : ($q->total_points > 0 ? ($q->score / $q->total_points) * 100 : (float) $q->score);
            $scoreValues[] = $pct;
            if ($q->passed) {
                $passedCount++;
            }
        }

        foreach ($allAssignments as $a) {
            if (is_numeric($a->grade)) {
                $val = (float) $a->grade;
                $scoreValues[] = $val;
                if ($val >= 50.0) {
                    $passedCount++;
                }
            }
        }

        foreach ($allAssessments as $as) {
            $raw = $as->score ?? $as->raw_score;
            if (is_numeric($raw)) {
                $val = (float) $raw;
                $scoreValues[] = $val;
                if ($val >= 50.0) {
                    $passedCount++;
                }
            }
        }

        $totalEvaluations = count($scoreValues);
        $avgScore = $totalEvaluations > 0 ? round(array_sum($scoreValues) / $totalEvaluations, 1) : 0.0;
        $passRate = $totalEvaluations > 0 ? round(($passedCount / $totalEvaluations) * 100) : 0;

        $this->metrics = [
            'average_score' => $avgScore,
            'pass_rate' => $passRate,
            'quizzes_count' => $allQuizzes->count(),
            'quizzes_passed' => $allQuizzes->where('passed', true)->count(),
            'assignments_count' => $allAssignments->count(),
            'assessments_count' => $allAssessments->count(),
            'total_completed' => $totalEvaluations,
        ];

        // Compile Activity Feed Items
        $items = collect();

        if (in_array($this->selectedType, ['all', 'quiz'], true)) {
            foreach ($allQuizzes as $q) {
                $pct = $q->percentage !== null
                    ? (float) $q->percentage
                    : ($q->total_points > 0 ? round(($q->score / $q->total_points) * 100) : (float) $q->score);

                $items->push([
                    'id' => 'quiz_'.$q->id,
                    'type' => 'quiz',
                    'type_badge' => 'Quiz',
                    'type_color' => 'sky',
                    'kid_id' => $q->user_id,
                    'kid_name' => $q->user?->name ?? 'Child',
                    'title' => $q->quiz?->title ?? 'Quiz Evaluation',
                    'course' => $q->quiz?->course?->title ?? 'General Curriculum',
                    'score_display' => round($pct).'%',
                    'numeric_score' => $pct,
                    'passed' => (bool) $q->passed,
                    'status_label' => $q->passed ? 'Passed' : 'Needs Retake',
                    'status_color' => $q->passed ? 'emerald' : 'rose',
                    'feedback' => null,
                    'timestamp' => $q->completed_at ?? $q->created_at,
                    'formatted_date' => ($q->completed_at ?? $q->created_at)?->format('M d, Y · g:i A'),
                    'time_ago' => ($q->completed_at ?? $q->created_at)?->diffForHumans() ?? 'Recently',
                ]);
            }
        }

        if (in_array($this->selectedType, ['all', 'assignment'], true)) {
            foreach ($allAssignments as $a) {
                $num = is_numeric($a->grade) ? (float) $a->grade : 0.0;
                $passed = $num >= 50.0;

                $items->push([
                    'id' => 'assignment_'.$a->id,
                    'type' => 'assignment',
                    'type_badge' => 'Assignment',
                    'type_color' => 'indigo',
                    'kid_id' => $a->user_id,
                    'kid_name' => $a->user?->name ?? 'Child',
                    'title' => $a->assignment?->name ?? 'Project Task',
                    'course' => $a->assignment?->course?->title ?? 'General Curriculum',
                    'score_display' => is_numeric($a->grade) ? round($num).'%' : (string) $a->grade,
                    'numeric_score' => $num,
                    'passed' => $passed,
                    'status_label' => $passed ? 'Graded' : 'Needs Review',
                    'status_color' => $passed ? 'emerald' : 'amber',
                    'feedback' => $a->feedback,
                    'timestamp' => $a->updated_at ?? $a->submitted_at ?? $a->created_at,
                    'formatted_date' => ($a->updated_at ?? $a->submitted_at ?? $a->created_at)?->format('M d, Y · g:i A'),
                    'time_ago' => ($a->updated_at ?? $a->submitted_at ?? $a->created_at)?->diffForHumans() ?? 'Recently',
                ]);
            }
        }

        if (in_array($this->selectedType, ['all', 'assessment'], true)) {
            foreach ($allAssessments as $as) {
                $gradeVal = $as->score ?? $as->raw_score;
                $num = is_numeric($gradeVal) ? (float) $gradeVal : 0.0;
                $passed = $num >= 50.0;

                $items->push([
                    'id' => 'assessment_'.$as->id,
                    'type' => 'assessment',
                    'type_badge' => 'Assessment',
                    'type_color' => 'purple',
                    'kid_id' => $as->user_id,
                    'kid_name' => $as->user?->name ?? 'Child',
                    'title' => $as->assessment?->name ?? 'Comprehensive Assessment',
                    'course' => $as->assessment?->course?->title ?? 'General Curriculum',
                    'score_display' => is_numeric($gradeVal) ? round($num).'%' : (string) $gradeVal,
                    'numeric_score' => $num,
                    'passed' => $passed,
                    'status_label' => $passed ? 'Graded' : 'Needs Review',
                    'status_color' => $passed ? 'emerald' : 'amber',
                    'feedback' => $as->feedback,
                    'timestamp' => $as->updated_at ?? $as->submitted_at ?? $as->created_at,
                    'formatted_date' => ($as->updated_at ?? $as->submitted_at ?? $as->created_at)?->format('M d, Y · g:i A'),
                    'time_ago' => ($as->updated_at ?? $as->submitted_at ?? $as->created_at)?->diffForHumans() ?? 'Recently',
                ]);
            }
        }

        if (in_array($this->selectedType, ['all', 'badge'], true)) {
            foreach ($allXp as $tx) {
                $items->push([
                    'id' => 'xp_'.$tx->id,
                    'type' => 'badge',
                    'type_badge' => 'Achievement',
                    'type_color' => 'amber',
                    'kid_id' => $tx->user_id,
                    'kid_name' => $tx->user?->name ?? 'Child',
                    'title' => $tx->description ?: ucwords(str_replace('_', ' ', (string) $tx->activity_type)),
                    'course' => 'Milestones & Badges',
                    'score_display' => '+'.((int) ($tx->amount_xp ?: $tx->points)).' XP',
                    'numeric_score' => (int) ($tx->amount_xp ?: $tx->points),
                    'passed' => true,
                    'status_label' => 'Unlocked',
                    'status_color' => 'amber',
                    'feedback' => null,
                    'timestamp' => $tx->created_at,
                    'formatted_date' => $tx->created_at?->format('M d, Y · g:i A'),
                    'time_ago' => $tx->created_at?->diffForHumans() ?? 'Recently',
                ]);
            }
        }

        // Apply Search Term
        $searchTerm = mb_strtolower(trim($this->search));
        if ($searchTerm !== '') {
            $items = $items->filter(function (array $it) use ($searchTerm): bool {
                return str_contains(mb_strtolower($it['title']), $searchTerm)
                    || str_contains(mb_strtolower($it['course']), $searchTerm)
                    || str_contains(mb_strtolower($it['kid_name']), $searchTerm)
                    || str_contains(mb_strtolower((string) ($it['feedback'] ?? '')), $searchTerm);
            });
        }

        $this->activities = $items->sortByDesc('timestamp')->values()->all();
    }
}
