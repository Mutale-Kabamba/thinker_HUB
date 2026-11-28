<?php

namespace App\Filament\Parent\Pages;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\XpTransaction;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Overview extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Overview & Hub';

    protected static ?string $title = 'Parents Portal • Kids Learning Hub';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'KIDS MANAGEMENT';

    protected string $view = 'filament.parent.pages.overview';

    public ?int $selectedKidId = null;

    public array $stats = [];

    public array $kidsList = [];

    public array $recentActivities = [];

    public array $upcomingSessions = [];

    public function selectKid(?int $kidId): void
    {
        $this->selectedKidId = $kidId;
        $this->loadData();
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        /** @var Collection<int, User> $kids */
        $kids = $parent->kids()->with(['enrollments.course', 'enrollments.intake'])->get();

        $allKidIds = $kids->pluck('id')->all();

        // Target kid IDs (either selected kid or all kids)
        $activeKidIds = $this->selectedKidId !== null && in_array($this->selectedKidId, $allKidIds, true)
            ? [$this->selectedKidId]
            : $allKidIds;

        // 1. Compile Kids List with metrics
        $this->kidsList = $kids->map(function (User $kid): array {
            $enrollments = $kid->enrollments;
            $coursesCount = $enrollments->count();
            $quizzesDone = QuizAttempt::query()->where('user_id', $kid->id)->whereNotNull('completed_at')->count();
            $assignmentsDone = AssignmentSubmission::query()->where('user_id', $kid->id)->whereNotNull('grade')->count();

            return [
                'id' => $kid->id,
                'name' => $kid->name,
                'email' => $kid->email,
                'track' => $kid->track ?? 'Beginner',
                'relationship' => $kid->pivot->relationship ?? 'Parent',
                'avatar' => $kid->getFilamentAvatarUrl(),
                'xp' => (int) ($kid->lifetime_xp ?? 0),
                'streak' => (int) ($kid->current_streak ?? 0),
                'courses_count' => $coursesCount,
                'tasks_completed' => $quizzesDone + $assignmentsDone,
                'courses' => $enrollments->map(fn ($e): array => [
                    'id' => $e->course?->id,
                    'title' => $e->course?->title ?? 'Course',
                    'is_for_kids' => (bool) ($e->course?->is_for_kids),
                    'code' => $e->course?->code,
                ])->all(),
            ];
        })->all();

        // 2. High-level stats
        $totalKids = count($kids);
        $totalCoursesEnrolled = Enrollment::query()->whereIn('user_id', $activeKidIds)->distinct('course_id')->count('course_id');
        $totalQuizzesPassed = QuizAttempt::query()->whereIn('user_id', $activeKidIds)->where('passed', true)->count();
        $totalAssignmentsSubmitted = AssignmentSubmission::query()->whereIn('user_id', $activeKidIds)->whereNotNull('submitted_at')->count();
        $totalXpEarned = (int) User::query()->whereIn('id', $activeKidIds)->sum('lifetime_xp');

        $this->stats = [
            'total_kids' => $totalKids,
            'active_courses' => $totalCoursesEnrolled,
            'quizzes_passed' => $totalQuizzesPassed,
            'assignments_submitted' => $totalAssignmentsSubmitted,
            'total_xp' => $totalXpEarned,
            'selected_kid_name' => $this->selectedKidId ? ($kids->firstWhere('id', $this->selectedKidId)?->name ?? 'All Kids') : 'All Kids',
        ];

        // 3. Consolidated Real-time Recent Activities Feed (latest 20 items)
        $activities = collect();

        // Quizzes
        $quizzes = QuizAttempt::query()
            ->with(['quiz.course', 'user'])
            ->whereIn('user_id', $activeKidIds)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(10)
            ->get();

        foreach ($quizzes as $q) {
            $pct = $q->percentage !== null
                ? (float) $q->percentage
                : ($q->total_points > 0 ? round(($q->score / $q->total_points) * 100) : (float) $q->score);

            $activities->push([
                'type' => 'quiz',
                'kid_id' => $q->user_id,
                'kid_name' => $q->user?->name ?? 'Child',
                'title' => $q->quiz?->title ?? 'Quiz Evaluation',
                'course' => $q->quiz?->course?->title ?? 'Kids Course',
                'score_label' => $pct.'%',
                'passed' => (bool) $q->passed,
                'status_label' => $q->passed ? 'Passed' : 'Needs Practice',
                'feedback' => null,
                'timestamp' => $q->completed_at ?? $q->created_at,
                'time_ago' => ($q->completed_at ?? $q->created_at)?->diffForHumans() ?? 'Recently',
            ]);
        }

        // Assignments
        $assignments = AssignmentSubmission::query()
            ->with(['assignment.course', 'user'])
            ->whereIn('user_id', $activeKidIds)
            ->whereNotNull('grade')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        foreach ($assignments as $a) {
            $grade = is_numeric($a->grade) ? round((float) $a->grade).'%' : (string) $a->grade;
            $passed = is_numeric($a->grade) ? ((float) $a->grade >= 50) : true;

            $activities->push([
                'type' => 'assignment',
                'kid_id' => $a->user_id,
                'kid_name' => $a->user?->name ?? 'Child',
                'title' => $a->assignment?->name ?? 'Project Assignment',
                'course' => $a->assignment?->course?->title ?? 'Kids Course',
                'score_label' => $grade,
                'passed' => $passed,
                'status_label' => $passed ? 'Graded' : 'Reviewed',
                'feedback' => $a->feedback,
                'timestamp' => $a->updated_at ?? $a->submitted_at ?? $a->created_at,
                'time_ago' => ($a->updated_at ?? $a->submitted_at ?? $a->created_at)?->diffForHumans() ?? 'Recently',
            ]);
        }

        // Badges / Gamification rewards
        $xpTransactions = XpTransaction::query()
            ->with('user')
            ->whereIn('user_id', $activeKidIds)
            ->latest('created_at')
            ->limit(10)
            ->get();

        foreach ($xpTransactions as $tx) {
            $activities->push([
                'type' => 'xp',
                'kid_id' => $tx->user_id,
                'kid_name' => $tx->user?->name ?? 'Child',
                'title' => $tx->description ?: ucwords(str_replace('_', ' ', (string) $tx->activity_type)),
                'course' => 'Achievement & XP',
                'score_label' => '+'.((int) ($tx->amount_xp ?: $tx->points)).' XP',
                'passed' => true,
                'status_label' => 'Unlocked',
                'feedback' => null,
                'timestamp' => $tx->created_at,
                'time_ago' => $tx->created_at?->diffForHumans() ?? 'Recently',
            ]);
        }

        $this->recentActivities = $activities
            ->sortByDesc('timestamp')
            ->take(15)
            ->values()
            ->all();

        // 4. Upcoming Sessions for kids
        $kidCourseIds = Enrollment::query()->whereIn('user_id', $activeKidIds)->pluck('course_id')->unique()->all();

        $this->upcomingSessions = CourseSession::query()
            ->whereIn('course_id', $kidCourseIds)
            ->where('scheduled_at', '>=', Carbon::now()->subHours(2))
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get()
            ->map(fn (CourseSession $s): array => [
                'id' => $s->id,
                'title' => $s->title,
                'course' => $s->course?->title ?? 'Course Session',
                'date' => $s->scheduled_at?->format('M d, Y · g:i A'),
                'time_ago' => $s->scheduled_at?->diffForHumans() ?? 'Upcoming',
                'instructor' => $s->instructor?->name ?? 'Instructor',
                'type' => $s->session_type ?? 'Live Class',
            ])->all();
    }
}
