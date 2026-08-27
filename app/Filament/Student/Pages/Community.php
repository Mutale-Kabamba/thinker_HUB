<?php

namespace App\Filament\Student\Pages;

use App\Events\ChatMessageSent;
use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\ChatMessage;
use App\Models\ChatMessageReaction;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Friendship;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class Community extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'GROWTH & SOCIAL';

    protected static ?string $navigationLabel = 'Community';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.student.pages.community';

    public const DIRECTORY_LIMIT = 50;

    #[Url(as: 'tab')]
    public string $tab = 'chats'; // chats | results | friends | leaderboard

    public string $directorySearch = '';

    public string $resultsSearch = '';

    public string $resultsFilter = 'all'; // all | quiz | assignment | assessment

    public string $chatSearch = '';

    public string $chatFilter = 'all'; // all | groups | direct

    /** @var array<string, mixed>|null */
    public ?array $profileUser = null;

    public ?int $selectedRoomId = null;

    public ?string $selectedTaskId = null;

    public ?int $replyingToMessageId = null;

    public function selectTask(?string $taskId): void
    {
        $this->selectedTaskId = $taskId;
    }

    public int $messagesLimit = 30;

    public bool $hasMoreMessages = false;

    public string $messageBody = '';

    public $attachment = null;

    public array $attachments = [];

    public function getHeading(): ?string
    {
        if ($this->tab === 'chats' && $this->selectedRoomId) {
            return '';
        }

        return 'Community';
    }

    public function closeRoom(): void
    {
        $this->selectedRoomId = null;
        $this->replyingToMessageId = null;
    }

    public function removeAttachment(int $index): void
    {
        if (is_array($this->attachments) && isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function selectRoom(int $id): void
    {
        $this->openRoom($id);
    }

    public function mount(): void
    {
        $this->ensureCourseRooms();

        // Evaluate-on-read: the streak badge is checked lazily for the
        // viewing student (in addition to quiz-pass/certificate events) so
        // chat/submission/attendance activity can complete a streak without
        // observing four more models. Idempotent and cheap.
        $user = auth()->user();

        if ($user) {
            try {
                app(GamificationService::class)->evaluateStreak($user);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    // ------------------------------------------------------------- Gamification

    /**
     * Top 20 students by XP. When the viewer falls outside the top 20
     * (including students with no XP yet), their row is appended with their
     * overall rank so they always see where they stand.
     *
     * @return array{rows: Collection<int, array<string, mixed>>, viewer: array<string, mixed>|null}
     */
    public function getLeaderboardProperty(): array
    {
        $ranked = app(GamificationService::class)->leaderboard();
        $top = $ranked->take(20)->values();
        $viewer = null;
        $user = auth()->user();

        if ($user) {
            $mine = $ranked->firstWhere('user_id', $user->id);

            if ($mine && $mine['rank'] > 20) {
                $viewer = $mine;
            } elseif (! $mine) {
                $viewer = [
                    'rank' => $ranked->count() + 1,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'xp' => 0,
                    'badge_count' => $user->badges()->count(),
                    'badge_icons' => $user->badges()->orderBy('user_badge.earned_at')->limit(5)->pluck('icon')->filter()->values()->all(),
                ];
            }
        }

        return ['rows' => $top, 'viewer' => $viewer];
    }

    /**
     * Compact XP/badge summary chip for the viewing student.
     *
     * @return array{xp: int, badge_count: int, badge_icons: array<int, string>}
     */
    public function getMyXpProperty(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['xp' => 0, 'badge_count' => 0, 'badge_icons' => []];
        }

        return [
            'xp' => $user->xpTotal(),
            'badge_count' => $user->badges()->count(),
            'badge_icons' => $user->badges()->orderBy('user_badge.earned_at')->limit(5)->pluck('icon')->filter()->values()->all(),
        ];
    }

    /**
     * Detailed XP & Thinker Coins earning history, badges, and breakdown for the viewing student.
     *
     * @return array{
     *   total_xp: int,
     *   total_coins: int,
     *   streak: int,
     *   rank: array{rank_name: string, multiplier: float},
     *   transactions: \Illuminate\Support\Collection<int, \App\Models\XpTransaction>,
     *   earned_badges: \Illuminate\Support\Collection<int, \App\Models\Badge>,
     *   total_available_badges: int
     * }
     */
    public function getMyXpBreakdownProperty(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'total_xp' => 0,
                'total_coins' => 0,
                'streak' => 0,
                'rank' => ['rank_name' => 'Novice', 'multiplier' => 1.0],
                'transactions' => collect(),
                'earned_badges' => collect(),
                'total_available_badges' => 0,
            ];
        }

        $service = app(GamificationService::class);
        $rank = $service->calculateUserRank((int) ($user->lifetime_xp ?? 0));

        $txs = \App\Models\XpTransaction::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->take(50)
            ->get();

        $earnedBadges = $user->badges()
            ->orderBy('user_badge.earned_at', 'desc')
            ->get();

        $totalAvailableBadges = \App\Models\Badge::query()->count();

        return [
            'total_xp' => (int) ($user->lifetime_xp ?? 0),
            'total_coins' => (int) ($user->spendable_coins ?? 0),
            'streak' => (int) ($user->current_streak ?? 0),
            'rank' => $rank,
            'transactions' => $txs,
            'earned_badges' => $earnedBadges,
            'total_available_badges' => $totalAvailableBadges,
        ];
    }



    /**
     * Ensure a group room exists for each course the student is enrolled in,
     * and that the student is a member of it.
     */
    private function ensureCourseRooms(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        foreach ($user->courses()->get() as $course) {
            $groupName = $course->code ?: ($course->title.' — Group');

            $room = ChatRoom::firstOrCreate(
                ['type' => 'course', 'course_id' => $course->id],
                ['name' => $groupName],
            );

            if ($room->name !== $groupName) {
                $room->update(['name' => $groupName]);
            }

            $room->members()->syncWithoutDetaching([$user->id]);
        }
    }

    // ---------------------------------------------------------------- Friends

    public function getFriendsProperty(): Collection
    {
        return auth()->user()?->friends() ?? collect();
    }

    public function getPendingRequestsProperty(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return Friendship::query()
            ->with('requester')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    /**
     * Browsable student directory (replaces the old search-first flow):
     * every student except the viewer, classmates first — ordered by shared
     * course count DESC then name — then everyone else alphabetically.
     * Six grouped queries total, no N+1: enrollments-by-course, students,
     * XP sums, badge icons, and the viewer's friendships.
     *
     * @return array{rows: Collection<int, array<string, mixed>>, total: int, shown: int}
     */
    public function getDirectoryProperty(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['rows' => collect(), 'total' => 0, 'shown' => 0];
        }

        // Classmates: students sharing >=1 enrolled course, with course names.
        $viewerCourseIds = Enrollment::query()->where('user_id', $user->id)->pluck('course_id');

        $sharedByStudent = collect();

        if ($viewerCourseIds->isNotEmpty()) {
            $sharedByStudent = Enrollment::query()
                ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->whereIn('enrollments.course_id', $viewerCourseIds)
                ->where('enrollments.user_id', '!=', $user->id)
                ->get(['enrollments.user_id', 'courses.title'])
                ->groupBy('user_id')
                ->map(fn ($rows): array => [
                    'count' => $rows->pluck('title')->unique()->count(),
                    'courses' => $rows->pluck('title')->unique()->sort()->values()->all(),
                ]);
        }

        $students = User::query()
            ->where('role', 'student')
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $studentIds = $students->pluck('id')->all();

        $xpByUser = XpTransaction::query()
            ->whereIn('user_id', $studentIds)
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(points) as xp')
            ->pluck('xp', 'user_id');

        $badgeIcons = DB::table('user_badge')
            ->join('badges', 'badges.id', '=', 'user_badge.badge_id')
            ->whereIn('user_badge.user_id', $studentIds)
            ->orderBy('user_badge.earned_at')
            ->get(['user_badge.user_id', 'badges.icon'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('icon')->filter()->take(3)->values()->all());

        $friendshipByUser = Friendship::query()
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('friend_id', $user->id))
            ->get(['id', 'user_id', 'friend_id', 'status'])
            ->mapWithKeys(fn (Friendship $f): array => [
                ($f->user_id === $user->id ? $f->friend_id : $f->user_id) => $this->friendshipState($f, $user->id),
            ]);

        // Collection is name-sorted; sortByDesc is stable, so classmates
        // come first (shared DESC) and ties stay alphabetical.
        $rows = $students
            ->map(fn (User $s): array => [
                'id' => $s->id,
                'name' => $s->name,
                'shared_count' => $sharedByStudent[$s->id]['count'] ?? 0,
                'shared_courses' => $sharedByStudent[$s->id]['courses'] ?? [],
                'xp' => (int) ($xpByUser[$s->id] ?? 0),
                'badge_icons' => $badgeIcons[$s->id] ?? [],
                'friendship' => $friendshipByUser[$s->id] ?? ['state' => 'none', 'friendship_id' => null],
            ])
            ->sortByDesc('shared_count')
            ->values();

        $term = mb_strtolower(trim($this->directorySearch));

        if ($term !== '') {
            $rows = $rows->filter(fn (array $row): bool => str_contains(mb_strtolower($row['name']), $term))->values();
        }

        $total = $rows->count();
        $shown = min($total, self::DIRECTORY_LIMIT);

        return [
            'rows' => $rows->take(self::DIRECTORY_LIMIT)->values(),
            'total' => $total,
            'shown' => $shown,
        ];
    }

    /**
     * Open the student profile modal. Students only — any other id
     * (or a missing user) aborts quietly.
     */
    public function showProfile(int $userId): void
    {
        $viewer = auth()->user();

        if (! $viewer) {
            return;
        }

        $target = User::query()->where('role', 'student')->find($userId);

        if (! $target) {
            return;
        }

        $viewerCourseIds = Enrollment::query()->where('user_id', $viewer->id)->pluck('course_id');

        $sharedCourses = $viewerCourseIds->isEmpty()
            ? collect()
            : Course::query()
                ->whereIn('id', $viewerCourseIds)
                ->whereHas('enrollments', fn ($q) => $q->where('user_id', $target->id))
                ->orderBy('title')
                ->pluck('title');

        $friendship = Friendship::query()
            ->where(fn ($q) => $q->where('user_id', $viewer->id)->where('friend_id', $target->id))
            ->orWhere(fn ($q) => $q->where('user_id', $target->id)->where('friend_id', $viewer->id))
            ->first();

        $service = app(GamificationService::class);
        $rankTier = $service->calculateUserRank((int) ($target->lifetime_xp ?? 0));

        // Get rank on the leaderboard
        $allLeaderboard = $service->leaderboard();
        $leaderboardRow = $allLeaderboard->firstWhere('user_id', $target->id);
        $rankPosition = $leaderboardRow ? $leaderboardRow['rank'] : (
            User::query()
                ->where('role', 'student')
                ->where('is_active', true)
                ->where('lifetime_xp', '>', (int) ($target->lifetime_xp ?? 0))
                ->count() + 1
        );

        $earnedBadges = $target->badges()
            ->orderBy('user_badge.earned_at', 'desc')
            ->get();

        $recentTransactions = XpTransaction::query()
            ->where('user_id', $target->id)
            ->latest('created_at')
            ->take(50)
            ->get();

        $this->profileUser = [
            'id' => $target->id,
            'name' => $target->name,
            'role_label' => 'Student',
            'bio' => $target->bio,
            'avatar' => $target->getFilamentAvatarUrl(),
            'xp' => (int) ($target->lifetime_xp ?? $target->xpTotal()),
            'coins' => (int) ($target->spendable_coins ?? 0),
            'streak' => (int) ($target->current_streak ?? 0),
            'rank_position' => $rankPosition,
            'rank_tier' => $rankTier,
            'badges' => $earnedBadges->map(fn ($b): array => [
                'id' => $b->id,
                'key' => $b->key,
                'name' => $b->name,
                'description' => $b->description,
                'icon' => $b->icon,
                'xp_reward' => (int) ($b->xp_reward ?? 0),
                'earned_at' => $b->pivot?->earned_at ? \Illuminate\Support\Carbon::parse($b->pivot->earned_at)->diffForHumans() : 'Earned',
            ])->all(),
            'badge_count' => $earnedBadges->count(),
            'recent_transactions' => $recentTransactions->map(fn ($tx): array => [
                'id' => $tx->id,
                'activity_type' => $tx->activity_type,
                'description' => $tx->description ?: ucwords(str_replace('_', ' ', (string) $tx->activity_type)),
                'amount_xp' => (int) ($tx->amount_xp ?: $tx->points),
                'amount_coins' => (int) ($tx->amount_coins ?? 0),
                'created_at' => $tx->created_at?->diffForHumans() ?? 'Recently',
            ])->all(),
            'courses_count' => $target->courses()->count(),
            'shared_courses' => $sharedCourses->all(),
            'friendship' => $friendship ? $this->friendshipState($friendship, $viewer->id) : ['state' => 'none', 'friendship_id' => null],
            'is_self' => $target->id === $viewer->id,
        ];
    }

    public function showStudentGamification(int $userId): void
    {
        $this->showProfile($userId);
    }

    public function closeProfile(): void
    {
        $this->profileUser = null;
    }

    /**
     * Friendship state between the viewer and the other party of a
     * friendship row: friends | sent (viewer requested) | incoming.
     *
     * @return array{state: string, friendship_id: int}
     */
    private function friendshipState(Friendship $friendship, int $viewerId): array
    {
        $state = match (true) {
            $friendship->status === 'accepted' => 'friends',
            $friendship->user_id === $viewerId => 'sent',
            default => 'incoming',
        };

        return ['state' => $state, 'friendship_id' => $friendship->id];
    }

    public function sendRequest(int $userId): void
    {
        $user = auth()->user();

        if (! $user || $userId === $user->id) {
            return;
        }

        // Already friends or a request already exists in either direction.
        $exists = Friendship::query()
            ->where(function ($q) use ($user, $userId): void {
                $q->where('user_id', $user->id)->where('friend_id', $userId);
            })
            ->orWhere(function ($q) use ($user, $userId): void {
                $q->where('user_id', $userId)->where('friend_id', $user->id);
            })
            ->exists();

        if ($exists) {
            return;
        }

        Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $userId,
            'status' => 'pending',
        ]);

        $this->refreshOpenProfile();
    }

    public function acceptRequest(int $friendshipId): void
    {
        $user = auth()->user();

        $friendship = Friendship::query()
            ->where('id', $friendshipId)
            ->where('friend_id', $user?->id)
            ->where('status', 'pending')
            ->first();

        $friendship?->update(['status' => 'accepted']);

        $this->refreshOpenProfile();
    }

    public function declineRequest(int $friendshipId): void
    {
        $user = auth()->user();

        Friendship::query()
            ->where('id', $friendshipId)
            ->where('friend_id', $user?->id)
            ->where('status', 'pending')
            ->delete();

        $this->refreshOpenProfile();
    }

    public function removeFriend(int $userId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        Friendship::query()
            ->where(function ($q) use ($user, $userId): void {
                $q->where('user_id', $user->id)->where('friend_id', $userId);
            })
            ->orWhere(function ($q) use ($user, $userId): void {
                $q->where('user_id', $userId)->where('friend_id', $user->id);
            })
            ->delete();

        $this->refreshOpenProfile();
    }

    /**
     * Re-resolve the open profile modal after a friendship action so its
     * action button reflects the new state (no-op when no modal is open).
     */
    private function refreshOpenProfile(): void
    {
        if ($this->profileUser) {
            $this->showProfile($this->profileUser['id']);
        }
    }

    // -------------------------------------------------------- Results & Performance

    /**
     * Aggregated live performance results across quizzes, assignments, and assessments.
     *
     * @return array{
     *   items: Collection<int, array<string, mixed>>,
     *   stats: array<string, mixed>,
     *   total_count: int,
     *   filtered_count: int
     * }
     */
    public function getResultsProperty(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'items' => collect(),
                'stats' => [
                    'average_score' => 0,
                    'total_completed' => 0,
                    'quizzes_count' => 0,
                    'assignments_count' => 0,
                    'assessments_count' => 0,
                    'pass_rate' => 0,
                    'passed_count' => 0,
                ],
                'total_count' => 0,
                'filtered_count' => 0,
            ];
        }

        // 1. Quizzes (QuizAttempt - Latest attempt per quiz only)
        $quizzes = QuizAttempt::query()
            ->with(['quiz.course'])
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('quiz_id')
            ->map(fn ($attempts) => $attempts->first())
            ->values()
            ->map(function (QuizAttempt $attempt): array {
                $percentage = $attempt->percentage !== null
                    ? (float) $attempt->percentage
                    : ($attempt->total_points > 0 ? round(($attempt->score / $attempt->total_points) * 100, 1) : (float) ($attempt->score ?? 0));

                return [
                    'id' => 'quiz_'.$attempt->id,
                    'raw_id' => $attempt->id,
                    'type' => 'quiz',
                    'type_label' => 'Quiz',
                    'type_badge' => 'Q',
                    'title' => $attempt->quiz?->title ?? 'Quiz Evaluation',
                    'course' => $attempt->quiz?->course?->title ?? 'General Curriculum',
                    'score_display' => round($percentage).'%',
                    'numeric_score' => $percentage,
                    'status' => $attempt->passed ? 'Passed' : 'Failed',
                    'status_color' => $attempt->passed ? 'success' : 'danger',
                    'is_pass' => (bool) $attempt->passed,
                    'date' => $attempt->completed_at ?? $attempt->created_at,
                    'date_formatted' => ($attempt->completed_at ?? $attempt->created_at)?->diffForHumans() ?? 'Recently',
                    'full_date' => ($attempt->completed_at ?? $attempt->created_at)?->format('M d, Y · g:i A'),
                    'feedback' => null,
                    'link_url' => route('filament.student.pages.quizzes'),
                ];
            });

        // 2. Assignments (AssignmentSubmission - Latest submission per assignment only)
        $assignments = AssignmentSubmission::query()
            ->with(['assignment.course'])
            ->where('user_id', $user->id)
            ->whereNotNull('grade')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('assignment_id')
            ->map(fn ($subs) => $subs->first())
            ->values()
            ->map(function (AssignmentSubmission $sub): array {
                $numericGrade = is_numeric($sub->grade) ? (float) $sub->grade : 0.0;
                $passed = $numericGrade >= 50.0;

                return [
                    'id' => 'assignment_'.$sub->id,
                    'raw_id' => $sub->id,
                    'type' => 'assignment',
                    'type_label' => 'Assignment',
                    'type_badge' => 'A',
                    'title' => $sub->assignment?->name ?? 'Assignment Project',
                    'course' => $sub->assignment?->course?->title ?? 'General Curriculum',
                    'score_display' => is_numeric($sub->grade) ? round($numericGrade).'%' : (string) $sub->grade,
                    'numeric_score' => $numericGrade,
                    'status' => $passed ? 'Graded' : 'Needs Review',
                    'status_color' => $passed ? 'success' : 'warning',
                    'is_pass' => $passed,
                    'date' => $sub->updated_at ?? $sub->submitted_at ?? $sub->created_at,
                    'date_formatted' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->diffForHumans() ?? 'Recently',
                    'full_date' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->format('M d, Y · g:i A'),
                    'feedback' => $sub->feedback,
                    'link_url' => route('filament.student.pages.assignments'),
                ];
            });

        // 3. Assessments (AssessmentSubmission - Latest submission per assessment only)
        $assessments = AssessmentSubmission::query()
            ->with(['assessment.course'])
            ->where('user_id', $user->id)
            ->where(function ($q): void {
                $q->whereNotNull('score')->orWhereNotNull('raw_score');
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('assessment_id')
            ->map(fn ($subs) => $subs->first())
            ->values()
            ->map(function (AssessmentSubmission $sub): array {
                $gradeVal = $sub->score ?? $sub->raw_score;
                $numericScore = is_numeric($gradeVal) ? (float) $gradeVal : 0.0;
                $passed = $numericScore >= 50.0;

                return [
                    'id' => 'assessment_'.$sub->id,
                    'raw_id' => $sub->id,
                    'type' => 'assessment',
                    'type_label' => 'Assessment',
                    'type_badge' => 'As',
                    'title' => $sub->assessment?->name ?? 'Comprehensive Assessment',
                    'course' => $sub->assessment?->course?->title ?? 'General Curriculum',
                    'score_display' => is_numeric($gradeVal) ? round($numericScore).'%' : (string) $gradeVal,
                    'numeric_score' => $numericScore,
                    'status' => $passed ? 'Graded' : 'Needs Review',
                    'status_color' => $passed ? 'success' : 'warning',
                    'is_pass' => $passed,
                    'date' => $sub->updated_at ?? $sub->submitted_at ?? $sub->created_at,
                    'date_formatted' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->diffForHumans() ?? 'Recently',
                    'full_date' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->format('M d, Y · g:i A'),
                    'feedback' => $sub->feedback,
                    'link_url' => route('filament.student.pages.assessments'),
                ];
            });

        // 4. Cohort Academic Performance Leaderboard calculation
        $allStudents = User::query()
            ->where('role', 'student')
            ->where('is_active', true)
            ->get(['id', 'name']);

        $studentIds = $allStudents->pluck('id')->all();

        $quizScoresByUser = QuizAttempt::query()
            ->whereIn('user_id', $studentIds)
            ->whereNotNull('completed_at')
            ->get()
            ->groupBy('user_id');

        $assignmentScoresByUser = AssignmentSubmission::query()
            ->whereIn('user_id', $studentIds)
            ->whereNotNull('grade')
            ->get()
            ->groupBy('user_id');

        $assessmentScoresByUser = AssessmentSubmission::query()
            ->whereIn('user_id', $studentIds)
            ->where(fn ($q) => $q->whereNotNull('score')->orWhereNotNull('raw_score'))
            ->get()
            ->groupBy('user_id');

        $leaderboardRows = $allStudents->map(function (User $s) use ($quizScoresByUser, $assignmentScoresByUser, $assessmentScoresByUser, $user): array {
            $sQuizzes = $quizScoresByUser->get($s->id, collect())
                ->groupBy('quiz_id')
                ->map(fn ($atts) => $atts->sortByDesc(fn ($a) => $a->completed_at ?? $a->created_at)->first());
            $sAssignments = $assignmentScoresByUser->get($s->id, collect())
                ->groupBy('assignment_id')
                ->map(fn ($subs) => $subs->sortByDesc(fn ($a) => $a->updated_at ?? $a->submitted_at ?? $a->created_at)->first());
            $sAssessments = $assessmentScoresByUser->get($s->id, collect())
                ->groupBy('assessment_id')
                ->map(fn ($subs) => $subs->sortByDesc(fn ($as) => $as->updated_at ?? $as->submitted_at ?? $as->created_at)->first());

            $scores = [];
            $passedCount = 0;

            foreach ($sQuizzes as $q) {
                $pct = $q->percentage !== null
                    ? (float) $q->percentage
                    : ($q->total_points > 0 ? ($q->score / $q->total_points) * 100 : (float) ($q->score ?? 0));
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
            $passRate = $count > 0 ? round(($passedCount / $count) * 100) : 0;

            $tier = match (true) {
                $avgScore >= 90.0 => ['label' => 'Distinction', 'color' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300'],
                $avgScore >= 75.0 => ['label' => 'High Merit', 'color' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300'],
                $avgScore >= 60.0 => ['label' => 'Merit', 'color' => 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300'],
                $avgScore >= 50.0 => ['label' => 'Pass', 'color' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'],
                default => ['label' => 'In Progress', 'color' => 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300'],
            };

            return [
                'user_id' => $s->id,
                'name' => $s->name,
                'avatar' => $s->getFilamentAvatarUrl(),
                'avg_score' => $avgScore,
                'evaluations_count' => $count,
                'quizzes_count' => $sQuizzes->count(),
                'assignments_count' => $sAssignments->count(),
                'assessments_count' => $sAssessments->count(),
                'passed_count' => $passedCount,
                'pass_rate' => $passRate,
                'tier' => $tier,
                'is_self' => $s->id === $user->id,
            ];
        })
        ->filter(fn (array $r) => $r['evaluations_count'] > 0 || $r['is_self'])
        ->sortByDesc(fn (array $r) => ($r['avg_score'] * 1000) + $r['evaluations_count'])
        ->values()
        ->map(function (array $r, int $idx): array {
            $r['rank'] = $idx + 1;
            return $r;
        });

        $allRaw = $quizzes->concat($assignments)->concat($assessments);
        $totalCount = $allRaw->count();

        // Summary Stats
        $avgScore = $totalCount > 0 ? round($allRaw->avg('numeric_score'), 1) : 0;
        $passedCount = $allRaw->where('is_pass', true)->count();
        $passRate = $totalCount > 0 ? round(($passedCount / $totalCount) * 100) : 0;

        $viewerStanding = $leaderboardRows->firstWhere('is_self', true);

        $stats = [
            'average_score' => $avgScore,
            'total_completed' => $totalCount,
            'quizzes_count' => $quizzes->count(),
            'assignments_count' => $assignments->count(),
            'assessments_count' => $assessments->count(),
            'pass_rate' => $passRate,
            'passed_count' => $passedCount,
            'my_rank' => $viewerStanding['rank'] ?? 1,
            'total_ranked_students' => $leaderboardRows->count(),
        ];

        // Filter leaderboard rows if searching
        $term = mb_strtolower(trim($this->resultsSearch));
        $filteredLeaderboard = $leaderboardRows;
        if ($term !== '') {
            $filteredLeaderboard = $leaderboardRows->filter(function (array $r) use ($term): bool {
                return str_contains(mb_strtolower($r['name']), $term);
            })->values();
        }

        // 5. Recent Graded Tasks & Candidate Scores for the Score Board
        $tasksList = collect();

        // A. Quizzes (Only latest / current attempt per student)
        $allQuizzesWithAttempts = QuizAttempt::query()
            ->with(['quiz.course', 'user'])
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('quiz_id');

        foreach ($allQuizzesWithAttempts as $quizId => $attempts) {
            $firstAttempt = $attempts->first();
            $quiz = $firstAttempt->quiz;
            if (! $quiz) {
                continue;
            }

            $uniqueCandidates = $attempts
                ->groupBy('user_id')
                ->map(fn ($userAttempts) => $userAttempts->sortByDesc(fn ($att) => $att->completed_at ?? $att->created_at)->first())
                ->values();

            $candidates = $uniqueCandidates->map(function (QuizAttempt $att) use ($user): array {
                $percentage = $att->percentage !== null
                    ? (float) $att->percentage
                    : ($att->total_points > 0 ? round(($att->score / $att->total_points) * 100, 1) : (float) ($att->score ?? 0));

                return [
                    'user_id' => $att->user_id,
                    'candidate_name' => $att->user?->name ?? 'Student',
                    'candidate_avatar' => $att->user?->getFilamentAvatarUrl(),
                    'score' => round($percentage).'%',
                    'numeric_score' => $percentage,
                    'status' => $att->passed ? 'Passed' : 'Failed',
                    'status_color' => $att->passed ? 'success' : 'danger',
                    'feedback' => null,
                    'graded_at' => ($att->completed_at ?? $att->created_at)?->diffForHumans() ?? 'Recently',
                    'full_date' => ($att->completed_at ?? $att->created_at)?->format('M d, Y · g:i A'),
                    'is_self' => $att->user_id === $user->id,
                    'timestamp' => $att->completed_at ?? $att->created_at,
                ];
            })->sortByDesc('numeric_score')->values()->map(function (array $c, int $idx): array {
                $c['rank'] = $idx + 1;

                return $c;
            });

            $latestDate = $candidates->max('timestamp');

            $tasksList->push([
                'id' => 'quiz_'.$quiz->id,
                'raw_id' => $quiz->id,
                'title' => $quiz->title,
                'type' => 'quiz',
                'type_label' => 'Quiz',
                'type_badge' => 'Q',
                'course' => $quiz->course?->title ?? 'Curriculum Course',
                'candidates_count' => $candidates->count(),
                'average_score' => round($candidates->avg('numeric_score'), 1),
                'pass_rate' => $candidates->count() > 0 ? round(($candidates->where('status', 'Passed')->count() / $candidates->count()) * 100) : 0,
                'latest_date' => $latestDate,
                'latest_date_formatted' => $latestDate?->diffForHumans() ?? 'Recently',
                'publish_date' => $quiz->published_at ?? $quiz->created_at ?? $quiz->id,
                'candidates' => $candidates,
            ]);
        }

        // B. Assignments (Only latest / current submission per student)
        $allAssignmentsWithSubmissions = AssignmentSubmission::query()
            ->with(['assignment.course', 'user'])
            ->whereNotNull('grade')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('assignment_id');

        foreach ($allAssignmentsWithSubmissions as $assignmentId => $submissions) {
            $firstSub = $submissions->first();
            $assignment = $firstSub->assignment;
            if (! $assignment) {
                continue;
            }

            $uniqueCandidates = $submissions
                ->groupBy('user_id')
                ->map(fn ($userSubs) => $userSubs->sortByDesc(fn ($sub) => $sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)->first())
                ->values();

            $candidates = $uniqueCandidates->map(function (AssignmentSubmission $sub) use ($user): array {
                $numericGrade = is_numeric($sub->grade) ? (float) $sub->grade : 0.0;
                $passed = $numericGrade >= 50.0;

                return [
                    'user_id' => $sub->user_id,
                    'candidate_name' => $sub->user?->name ?? 'Student',
                    'candidate_avatar' => $sub->user?->getFilamentAvatarUrl(),
                    'score' => is_numeric($sub->grade) ? round($numericGrade).'%' : (string) $sub->grade,
                    'numeric_score' => $numericGrade,
                    'status' => $passed ? 'Graded' : 'Needs Review',
                    'status_color' => $passed ? 'success' : 'warning',
                    'feedback' => $sub->feedback,
                    'graded_at' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->diffForHumans() ?? 'Recently',
                    'full_date' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->format('M d, Y · g:i A'),
                    'is_self' => $sub->user_id === $user->id,
                    'timestamp' => $sub->updated_at ?? $sub->submitted_at ?? $sub->created_at,
                ];
            })->sortByDesc('numeric_score')->values()->map(function (array $c, int $idx): array {
                $c['rank'] = $idx + 1;

                return $c;
            });

            $latestDate = $candidates->max('timestamp');

            $tasksList->push([
                'id' => 'assignment_'.$assignment->id,
                'raw_id' => $assignment->id,
                'title' => $assignment->name,
                'type' => 'assignment',
                'type_label' => 'Assignment',
                'type_badge' => 'A',
                'course' => $assignment->course?->title ?? 'Curriculum Course',
                'candidates_count' => $candidates->count(),
                'average_score' => round($candidates->avg('numeric_score'), 1),
                'pass_rate' => $candidates->count() > 0 ? round(($candidates->where('status', 'Graded')->count() / $candidates->count()) * 100) : 0,
                'latest_date' => $latestDate,
                'latest_date_formatted' => $latestDate?->diffForHumans() ?? 'Recently',
                'publish_date' => $assignment->published_at ?? $assignment->created_at ?? $assignment->id,
                'candidates' => $candidates,
            ]);
        }

        // C. Assessments (Only latest / current submission per student)
        $allAssessmentsWithSubmissions = AssessmentSubmission::query()
            ->with(['assessment.course', 'user'])
            ->where(fn ($q) => $q->whereNotNull('score')->orWhereNotNull('raw_score'))
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('assessment_id');

        foreach ($allAssessmentsWithSubmissions as $assessmentId => $submissions) {
            $firstSub = $submissions->first();
            $assessment = $firstSub->assessment;
            if (! $assessment) {
                continue;
            }

            $uniqueCandidates = $submissions
                ->groupBy('user_id')
                ->map(fn ($userSubs) => $userSubs->sortByDesc(fn ($sub) => $sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)->first())
                ->values();

            $candidates = $uniqueCandidates->map(function (AssessmentSubmission $sub) use ($user): array {
                $gradeVal = $sub->score ?? $sub->raw_score;
                $numericScore = is_numeric($gradeVal) ? (float) $gradeVal : 0.0;
                $passed = $numericScore >= 50.0;

                return [
                    'user_id' => $sub->user_id,
                    'candidate_name' => $sub->user?->name ?? 'Student',
                    'candidate_avatar' => $sub->user?->getFilamentAvatarUrl(),
                    'score' => is_numeric($gradeVal) ? round($numericScore).'%' : (string) $gradeVal,
                    'numeric_score' => $numericScore,
                    'status' => $passed ? 'Graded' : 'Needs Review',
                    'status_color' => $passed ? 'success' : 'warning',
                    'feedback' => $sub->feedback,
                    'graded_at' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->diffForHumans() ?? 'Recently',
                    'full_date' => ($sub->updated_at ?? $sub->submitted_at ?? $sub->created_at)?->format('M d, Y · g:i A'),
                    'is_self' => $sub->user_id === $user->id,
                    'timestamp' => $sub->updated_at ?? $sub->submitted_at ?? $sub->created_at,
                ];
            })->sortByDesc('numeric_score')->values()->map(function (array $c, int $idx): array {
                $c['rank'] = $idx + 1;

                return $c;
            });

            $latestDate = $candidates->max('timestamp');

            $tasksList->push([
                'id' => 'assessment_'.$assessment->id,
                'raw_id' => $assessment->id,
                'title' => $assessment->name,
                'type' => 'assessment',
                'type_label' => 'Assessment',
                'type_badge' => 'As',
                'course' => $assessment->course?->title ?? 'Curriculum Course',
                'candidates_count' => $candidates->count(),
                'average_score' => round($candidates->avg('numeric_score'), 1),
                'pass_rate' => $candidates->count() > 0 ? round(($candidates->where('status', 'Graded')->count() / $candidates->count()) * 100) : 0,
                'latest_date' => $latestDate,
                'latest_date_formatted' => $latestDate?->diffForHumans() ?? 'Recently',
                'publish_date' => $assessment->published_at ?? $assessment->created_at ?? $assessment->id,
                'candidates' => $candidates,
            ]);
        }

        // Compute shortened titles according to time of publish (Quiz 1, Quiz 2, Assignment 1, Assignment 2, Assessment 1...)
        $quizzes = $tasksList->where('type', 'quiz')->sortBy('publish_date')->values();
        $assignments = $tasksList->where('type', 'assignment')->sortBy('publish_date')->values();
        $assessments = $tasksList->where('type', 'assessment')->sortBy('publish_date')->values();

        $quizNumberMap = [];
        foreach ($quizzes as $index => $q) {
            $quizNumberMap[$q['id']] = 'Quiz '.($index + 1);
        }

        $assignmentNumberMap = [];
        foreach ($assignments as $index => $a) {
            $assignmentNumberMap[$a['id']] = 'Assignment '.($index + 1);
        }

        $assessmentNumberMap = [];
        foreach ($assessments as $index => $as) {
            $assessmentNumberMap[$as['id']] = 'Assessment '.($index + 1);
        }

        $tasksList = $tasksList->map(function (array $task) use ($quizNumberMap, $assignmentNumberMap, $assessmentNumberMap): array {
            $task['short_title'] = match ($task['type']) {
                'quiz' => $quizNumberMap[$task['id']] ?? 'Quiz',
                'assignment' => $assignmentNumberMap[$task['id']] ?? 'Assignment',
                'assessment' => $assessmentNumberMap[$task['id']] ?? 'Assessment',
                default => $task['type_label'],
            };

            return $task;
        });

        $sortedTasks = $tasksList->sortByDesc('latest_date')->values();

        // Filter tasks by type if selected
        if ($this->resultsFilter !== 'all') {
            $sortedTasks = $sortedTasks->where('type', $this->resultsFilter)->values();
        }

        // Filter tasks list if searching
        if ($term !== '') {
            $sortedTasks = $sortedTasks->filter(function (array $t) use ($term): bool {
                return str_contains(mb_strtolower($t['title']), $term)
                    || str_contains(mb_strtolower($t['course']), $term)
                    || str_contains(mb_strtolower($t['type_label']), $term);
            })->values();
        }

        // Selected or default active task
        $activeTaskId = $this->selectedTaskId ?? ($sortedTasks->first()['id'] ?? null);
        $activeTask = $sortedTasks->firstWhere('id', $activeTaskId) ?? $sortedTasks->first();

        // Filter task candidate list if searching
        if ($activeTask && $term !== '') {
            $filteredCandidates = $activeTask['candidates']->filter(function (array $c) use ($term): bool {
                return str_contains(mb_strtolower($c['candidate_name']), $term);
            })->values();
            $activeTask['candidates'] = $filteredCandidates;
        }
        // Personal evaluation history for student records
        $filtered = $allRaw->sortByDesc('date')->values();

        if ($this->resultsFilter !== 'all') {
            $filtered = $filtered->where('type', $this->resultsFilter)->values();
        }

        if ($term !== '') {
            $filtered = $filtered->filter(function (array $item) use ($term): bool {
                return str_contains(mb_strtolower($item['title']), $term)
                    || str_contains(mb_strtolower($item['course']), $term)
                    || str_contains(mb_strtolower($item['type_label']), $term);
            })->values();
        }

        return [
            'items' => $filtered,
            'leaderboard' => $filteredLeaderboard,
            'tasks' => $sortedTasks,
            'active_task' => $activeTask,
            'stats' => $stats,
            'total_count' => $totalCount,
            'filtered_count' => $filtered->count(),
        ];
    }

    // ------------------------------------------------------------------ Chats

    public function getRoomsProperty(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        $rooms = $user->chatRooms()
            ->with(['members', 'latestMessage.user', 'course'])
            ->get()
            ->sortByDesc(fn (ChatRoom $room) => $room->latestMessage?->created_at ?? $room->created_at)
            ->values();

        if ($this->chatFilter === 'groups') {
            $rooms = $rooms->where('type', 'course')->values();
        } elseif ($this->chatFilter === 'direct') {
            $rooms = $rooms->where('type', 'direct')->values();
        }

        $search = mb_strtolower(trim($this->chatSearch));
        if ($search !== '') {
            $rooms = $rooms->filter(function (ChatRoom $room) use ($user, $search): bool {
                $name = mb_strtolower($room->displayNameFor($user));
                $course = mb_strtolower($room->course?->title ?? '');
                $lastMsg = mb_strtolower($room->latestMessage?->body ?? '');

                return str_contains($name, $search) || str_contains($course, $search) || str_contains($lastMsg, $search);
            })->values();
        }

        return $rooms;
    }

    public function openDirect(int $userId): void
    {
        $user = auth()->user();

        if (! $user || ! $user->isFriendsWith($userId)) {
            return;
        }

        $room = ChatRoom::findOrCreateDirect($user->id, $userId);
        $this->selectedRoomId = $room->id;
        $this->replyingToMessageId = null;
        $this->messagesLimit = 30;
        $this->hasMoreMessages = false;
        $this->tab = 'chats';
    }

    public function openRoom(int $roomId): void
    {
        $user = auth()->user();

        // Only open rooms the user belongs to.
        if ($user && $user->chatRooms()->where('chat_rooms.id', $roomId)->exists()) {
            $this->selectedRoomId = $roomId;
            $this->replyingToMessageId = null;
            $this->messagesLimit = 30;
            $this->hasMoreMessages = false;
        }
    }

    public function setReplyTo(int $messageId): void
    {
        if (! $this->activeRoom) {
            return;
        }

        $msg = ChatMessage::query()
            ->where('chat_room_id', $this->activeRoom->id)
            ->find($messageId);

        if ($msg) {
            $this->replyingToMessageId = $msg->id;
            $this->dispatch('focus-chat-input');
        }
    }

    public function cancelReply(): void
    {
        $this->replyingToMessageId = null;
    }

    public function getReplyingToMessageProperty(): ?ChatMessage
    {
        if (! $this->replyingToMessageId) {
            return null;
        }

        return ChatMessage::query()
            ->with('user')
            ->where('chat_room_id', $this->activeRoom?->id)
            ->find($this->replyingToMessageId);
    }

    public function toggleReaction(int $messageId, string $emoji): void
    {
        $user = auth()->user();
        if (! $user || ! $this->activeRoom) {
            return;
        }

        $allowed = ['👍', '❤️', '🔥', '👏', '🎉', '🚀', '💯', '✨', '😂', '😍', '🤩', '😎', '🤔', '💡', '🙌', '🙏', '😮', '😢', '💪', '🥳'];
        $emoji = trim($emoji);
        if (! in_array($emoji, $allowed, true) && mb_strlen($emoji) > 4) {
            return;
        }

        $message = ChatMessage::query()
            ->where('chat_room_id', $this->activeRoom->id)
            ->find($messageId);

        if (! $message) {
            return;
        }

        $existing = ChatMessageReaction::query()
            ->where('chat_message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ChatMessageReaction::create([
                'chat_message_id' => $message->id,
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        }
    }

    public function loadMoreMessages(): void
    {
        if (! $this->activeRoom) {
            return;
        }

        $totalMessages = ChatMessage::query()
            ->where('chat_room_id', $this->activeRoom->id)
            ->count();

        $this->messagesLimit += 30;
        $this->hasMoreMessages = $this->messagesLimit < $totalMessages;
    }

    public function getActiveRoomProperty(): ?ChatRoom
    {
        if (! $this->selectedRoomId) {
            return null;
        }

        $user = auth()->user();

        return $user?->chatRooms()
            ->with(['members', 'course'])
            ->where('chat_rooms.id', $this->selectedRoomId)
            ->first();
    }

    public function getMessagesProperty(): Collection
    {
        if (! $this->activeRoom) {
            return collect();
        }

        $totalMessages = ChatMessage::query()
            ->where('chat_room_id', $this->activeRoom->id)
            ->count();

        $messages = ChatMessage::query()
            ->with(['user', 'replyTo.user', 'reactions.user'])
            ->where('chat_room_id', $this->activeRoom->id)
            ->latest()
            ->limit($this->messagesLimit)
            ->get()
            ->reverse()
            ->values();

        $this->hasMoreMessages = $totalMessages > $this->messagesLimit;

        return $messages;
    }

    public function sendMessage(): void
    {
        $user = auth()->user();
        $body = trim($this->messageBody);

        if (! $user || ! $this->activeRoom) {
            return;
        }

        $uploadedFiles = [];
        if (! empty($this->attachments) && is_array($this->attachments)) {
            $uploadedFiles = $this->attachments;
        } elseif ($this->attachment) {
            $uploadedFiles = [$this->attachment];
        }

        // Nothing to send.
        if ($body === '' && empty($uploadedFiles)) {
            return;
        }

        if (mb_strlen($body) > 2000) {
            $body = mb_substr($body, 0, 2000);
        }

        $attachmentsData = [];
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        foreach ($uploadedFiles as $file) {
            if (is_object($file) && method_exists($file, 'getClientOriginalName')) {
                $name = $file->getClientOriginalName();
                $ext = strtolower($file->getClientOriginalExtension());
                $type = in_array($ext, $imageExtensions, true) ? 'image' : 'file';
                $path = $file->store('chat-attachments', 'public');

                $attachmentsData[] = [
                    'path' => $path,
                    'name' => $name,
                    'type' => $type,
                ];
            }
        }

        $first = $attachmentsData[0] ?? null;

        $message = ChatMessage::create([
            'chat_room_id' => $this->activeRoom->id,
            'user_id' => $user->id,
            'reply_to_id' => $this->replyingToMessageId,
            'body' => $body !== '' ? $body : null,
            'attachments' => ! empty($attachmentsData) ? $attachmentsData : null,
            'attachment_path' => $first['path'] ?? null,
            'attachment_name' => $first['name'] ?? null,
            'attachment_type' => $first['type'] ?? null,
        ]);

        $this->messageBody = '';
        $this->replyingToMessageId = null;
        $this->reset('attachment', 'attachments');

        if (class_exists(ChatMessageSent::class)) {
            try {
                ChatMessageSent::dispatch($message);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Livewire echo listeners: refresh when a message lands in the open room.
     * Only active once a real broadcaster (e.g. Reverb) is configured; until
     * then the message pane falls back to short polling.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        if (! $this->selectedRoomId || ! in_array(config('broadcasting.default'), ['reverb', 'pusher', 'ably'], true)) {
            return [];
        }

        return [
            "echo-private:chat-room.{$this->selectedRoomId},ChatMessageSent" => '$refresh',
        ];
    }
}
