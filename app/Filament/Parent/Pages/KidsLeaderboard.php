<?php

namespace App\Filament\Parent\Pages;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\GamificationService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class KidsLeaderboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Kids Leaderboard';

    protected static ?string $title = 'Kids Course Leaderboard';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'ACADEMICS & PROGRESS';

    protected string $view = 'filament.parent.pages.kids-leaderboard';

    #[Url(as: 'course')]
    public ?int $selectedCourseId = null;

    #[Url(as: 'intake')]
    public ?int $selectedIntakeId = null;

    public array $courses = [];

    public array $intakes = [];

    public array $leaderboardRows = [];

    public ?array $myChildHighlight = null;

    public function mount(): void
    {
        $this->loadLeaderboard();
    }

    public function updatedSelectedCourseId(): void
    {
        $this->selectedIntakeId = null;
        $this->loadLeaderboard();
    }

    public function updatedSelectedIntakeId(): void
    {
        $this->loadLeaderboard();
    }

    public function selectCourse(int $courseId): void
    {
        $this->selectedCourseId = $courseId;
        $this->selectedIntakeId = null;
        $this->loadLeaderboard();
    }

    public function loadLeaderboard(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $parentKidIds = $parent->kids()->pluck('users.id')->all();

        // 1. Get all Kids Courses that the parent's children are enrolled in
        $coursesQuery = Course::query()
            ->whereHas('enrollments', fn ($q) => $q->whereIn('user_id', $parentKidIds))
            ->with('intakes');

        $kidsCourses = $coursesQuery->get();

        // If no course explicitly selected yet, default to first available
        if (! $this->selectedCourseId && $kidsCourses->isNotEmpty()) {
            $this->selectedCourseId = $kidsCourses->first()->id;
        }

        $this->courses = $kidsCourses->map(fn (Course $c): array => [
            'id' => $c->id,
            'title' => $c->title,
            'is_for_kids' => (bool) $c->is_for_kids,
        ])->all();

        if (! $this->selectedCourseId) {
            $this->leaderboardRows = [];
            $this->myChildHighlight = null;

            return;
        }

        $selectedCourse = Course::query()->with('intakes')->find($this->selectedCourseId);

        if ($selectedCourse) {
            $this->intakes = $selectedCourse->intakes->map(fn ($i): array => [
                'id' => $i->id,
                'name' => $i->name,
            ])->all();
        }

        // 2. Query students enrolled in this kids course (and optional intake)
        $enrollmentQuery = Enrollment::query()
            ->where('course_id', $this->selectedCourseId);

        if ($this->selectedIntakeId) {
            $enrollmentQuery->where(function ($q): void {
                $q->where('course_intake_id', $this->selectedIntakeId)->orWhereNull('course_intake_id');
            });
        }

        $enrolledStudentIds = $enrollmentQuery->pluck('user_id')->unique()->all();

        if (empty($enrolledStudentIds)) {
            $this->leaderboardRows = [];
            $this->myChildHighlight = null;

            return;
        }

        $students = User::query()
            ->whereIn('id', $enrolledStudentIds)
            ->where('is_active', true)
            ->get();

        // Grouped Scores
        $quizScoresByUser = QuizAttempt::query()
            ->whereIn('user_id', $enrolledStudentIds)
            ->whereNotNull('completed_at')
            ->whereHas('quiz', fn ($q) => $q->where('course_id', $this->selectedCourseId))
            ->get()
            ->groupBy('user_id');

        $assignmentScoresByUser = AssignmentSubmission::query()
            ->whereIn('user_id', $enrolledStudentIds)
            ->whereNotNull('grade')
            ->whereHas('assignment', fn ($q) => $q->where('course_id', $this->selectedCourseId))
            ->get()
            ->groupBy('user_id');

        $assessmentScoresByUser = AssessmentSubmission::query()
            ->whereIn('user_id', $enrolledStudentIds)
            ->where(fn ($q) => $q->whereNotNull('score')->orWhereNotNull('raw_score'))
            ->whereHas('assessment', fn ($q) => $q->where('course_id', $this->selectedCourseId))
            ->get()
            ->groupBy('user_id');

        $service = app(GamificationService::class);

        $rows = $students->map(function (User $s) use ($quizScoresByUser, $assignmentScoresByUser, $assessmentScoresByUser, $parentKidIds): array {
            $sQuizzes = $quizScoresByUser->get($s->id, collect())
                ->groupBy('quiz_id')
                ->map(fn ($atts) => $atts->first());

            $sAssignments = $assignmentScoresByUser->get($s->id, collect())
                ->groupBy('assignment_id')
                ->map(fn ($subs) => $subs->first());

            $sAssessments = $assessmentScoresByUser->get($s->id, collect())
                ->groupBy('assessment_id')
                ->map(fn ($subs) => $subs->first());

            $scores = [];
            $passedCount = 0;

            foreach ($sQuizzes as $q) {
                $pct = $q->percentage !== null
                    ? (float) $q->percentage
                    : ($q->total_points > 0 ? ($q->score / $q->total_points) * 100 : (float) $q->score);
                $scores[] = $pct;
                if ($q->passed) {
                    $passedCount++;
                }
            }

            foreach ($sAssignments as $a) {
                if (is_numeric($a->grade)) {
                    $val = (float) $a->grade;
                    $scores[] = $val;
                    if ($val >= 50.0) {
                        $passedCount++;
                    }
                }
            }

            foreach ($sAssessments as $as) {
                $raw = $as->score ?? $as->raw_score;
                if (is_numeric($raw)) {
                    $val = (float) $raw;
                    $scores[] = $val;
                    if ($val >= 50.0) {
                        $passedCount++;
                    }
                }
            }

            $count = count($scores);
            $avgScore = $count > 0 ? round(array_sum($scores) / $count, 1) : 0.0;
            $courseXp = $s->getXpForCourse($this->selectedCourseId, $this->selectedIntakeId);

            // Encouraging child tiers
            $kidTier = match (true) {
                $avgScore >= 90.0 || $courseXp >= 800 => ['name' => 'Master Explorer', 'color' => 'amber'],
                $avgScore >= 75.0 || $courseXp >= 400 => ['name' => 'Super Star', 'color' => 'indigo'],
                $avgScore >= 60.0 || $courseXp >= 200 => ['name' => 'Rising Learner', 'color' => 'emerald'],
                default => ['name' => 'Junior Adventurer', 'color' => 'slate'],
            };

            $isMyChild = in_array($s->id, $parentKidIds, true);

            // For privacy in kids leaderboard: show friendly first name or display name
            $parts = explode(' ', trim($s->name));
            $displayName = $parts[0].(isset($parts[1]) ? ' '.mb_substr($parts[1], 0, 1).'.' : '');

            return [
                'user_id' => $s->id,
                'full_name' => $s->name,
                'display_name' => $displayName,
                'avatar' => $s->getFilamentAvatarUrl(),
                'avg_score' => $avgScore,
                'evaluations_count' => $count,
                'passed_count' => $passedCount,
                'course_xp' => $courseXp,
                'lifetime_xp' => (int) ($s->lifetime_xp ?? 0),
                'kid_tier' => $kidTier,
                'is_my_child' => $isMyChild,
            ];
        });

        // Sort by XP then average score
        $sorted = $rows->sortByDesc(fn (array $r) => [$r['course_xp'], $r['avg_score'], -$r['user_id']])->values();

        $ranked = $sorted->map(function (array $row, int $idx): array {
            $row['rank'] = $idx + 1;

            return $row;
        });

        $this->leaderboardRows = $ranked->all();

        // Highlight parent's first child in this list if present
        $this->myChildHighlight = $ranked->firstWhere('is_my_child', true);
    }
}
