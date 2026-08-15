<?php

namespace App\Models;

use App\Notifications\QueuedVerifyEmail;
use App\Support\PublicDiskPath;
use App\Services\CertificateService;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'firebase_uid',
        'password',
        'role',
        'is_active',
        'email_verified_at',
        'track',
        'profile_photo_path',
        'proficiency',
        'occupation',
        'bio',
        'whatsapp',
        'linkedin_url',
        'facebook_url',
        'github_url',
        'instagram_url',
        'company',
        'portfolio_url',
        'specialty',
        'lifetime_xp',
        'spendable_coins',
        'current_streak',
        'last_activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'lifetime_xp' => 'integer',
            'spendable_coins' => 'integer',
            'current_streak' => 'integer',
            'last_activity_at' => 'datetime',
            'pending_login_token_expires_at' => 'datetime',
            'pending_login_token_used_at' => 'datetime',
        ];
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Whether this account was created / authenticated via Google (Firebase).
     * Google has already verified the email address for these accounts,
     * so they must never be shown the email-verification wall.
     */
    public function isGoogleAccount(): bool
    {
        return filled($this->firebase_uid);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badge')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    public function claimRequests(): HasMany
    {
        return $this->hasMany(ClaimRequest::class);
    }

    /**
     * Total XP across all award transactions.
     */
    public function xpTotal(): int
    {
        if ($this->lifetime_xp > 0) {
            return (int) $this->lifetime_xp;
        }

        return (int) ($this->xpTransactions()->sum('amount_xp') ?: $this->xpTransactions()->sum('points'));
    }

    /**
     * Current Rank Tier details (rank_name, multiplier).
     *
     * @return array{rank_name: string, multiplier: float}
     */
    public function rankTier(): array
    {
        return app(\App\Services\GamificationService::class)->calculateUserRank($this->lifetime_xp ?? 0);
    }

    public function rankName(): string
    {
        return $this->rankTier()['rank_name'];
    }

    public function rankMultiplier(): float
    {
        return (float) $this->rankTier()['multiplier'];
    }

    /**
     * Structured course progress for certificate eligibility: the student must
     * be enrolled and must finish every gradable activity visible to them —
     * a passed attempt for every active quiz, a submission for every visible
     * assignment, and a submission for every visible (personal) assessment.
     * A course with no gradable content at all is NOT complete; progress
     * tracking starts once the instructor adds activities.
     *
     * @return array{enrolled: bool, quizzes: array{total: int, done: int}, assignments: array{total: int, done: int}, assessments: array{total: int, done: int}, items_total: int, items_done: int, has_content: bool, complete: bool}
     */
    public function courseProgress(Course $course): array
    {
        $enrolled = $this->enrollments()
            ->where('course_id', $course->id)
            ->exists();

        $activeQuizIds = $course->quizzes()
            ->released()
            ->pluck('id');

        $quizzesDone = $activeQuizIds->isEmpty()
            ? 0
            : QuizAttempt::query()
                ->where('user_id', $this->id)
                ->whereIn('quiz_id', $activeQuizIds)
                ->where('passed', true)
                ->distinct()
                ->count('quiz_id');

        $assignmentIds = $course->assignments()
            ->visibleTo($this)
            ->released()
            ->pluck('id');

        $assignmentsDone = $assignmentIds->isEmpty()
            ? 0
            : AssignmentSubmission::query()
                ->where('user_id', $this->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->distinct()
                ->count('assignment_id');

        $assessmentIds = $course->assessments()
            ->visibleTo($this)
            ->released()
            ->pluck('id');

        $assessmentsDone = $assessmentIds->isEmpty()
            ? 0
            : AssessmentSubmission::query()
                ->where('user_id', $this->id)
                ->whereIn('assessment_id', $assessmentIds)
                ->distinct()
                ->count('assessment_id');

        $itemsTotal = $activeQuizIds->count() + $assignmentIds->count() + $assessmentIds->count();
        $itemsDone = $quizzesDone + $assignmentsDone + $assessmentsDone;

        return [
            'enrolled' => $enrolled,
            'quizzes' => ['total' => $activeQuizIds->count(), 'done' => $quizzesDone],
            'assignments' => ['total' => $assignmentIds->count(), 'done' => $assignmentsDone],
            'assessments' => ['total' => $assessmentIds->count(), 'done' => $assessmentsDone],
            'items_total' => $itemsTotal,
            'items_done' => $itemsDone,
            'has_content' => $itemsTotal > 0,
            'complete' => $enrolled && $itemsTotal > 0 && $itemsDone === $itemsTotal,
        ];
    }

    /**
     * Attendance summary for certificate eligibility: of the attendance rows
     * marked for this student in this course's sessions, present + late count
     * as attended and at least 75% must be attended. A course without any
     * sessions passes vacuously (nothing to attend yet); a course with
     * sessions but no rows marked for the student scores 0%.
     *
     * @return array{sessions_exist: bool, marked: int, attended: int, percent: int|null, ok: bool}
     */
    public function courseAttendance(Course $course): array
    {
        $sessionIds = $course->sessions()->pluck('id');

        if ($sessionIds->isEmpty()) {
            return [
                'sessions_exist' => false,
                'marked' => 0,
                'attended' => 0,
                'percent' => null,
                'ok' => true,
            ];
        }

        $rows = Attendance::query()
            ->where('user_id', $this->id)
            ->whereIn('course_session_id', $sessionIds);

        $marked = (clone $rows)->count();
        $attended = (clone $rows)
            ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])
            ->count();

        $percent = $marked === 0 ? 0 : (int) round($attended / $marked * 100);

        return [
            'sessions_exist' => true,
            'marked' => $marked,
            'attended' => $attended,
            'percent' => $percent,
            'ok' => $percent >= 75,
        ];
    }

    /**
     * Course completion rule: enrolled + signed off as completed by instructor
     * (enrollment->completed_at != null).
     */
    public function hasCompletedCourse(Course $course): bool
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $this->id)
            ->where('course_id', $course->id)
            ->first();

        return (bool) ($enrollment && $enrollment->completed_at !== null);
    }

    /**
     * Full certificate eligibility (progress + attendance), delegated to
     * CertificateService so the rule lives in exactly one place.
     */
    public function isCertificateEligible(Course $course): bool
    {
        return app(CertificateService::class)->eligibility($this, $course)['eligible'];
    }

    public function hasBookmarked(Model $model): bool
    {
        return $this->bookmarks()
            ->where('bookmarkable_type', $model->getMorphClass())
            ->where('bookmarkable_id', $model->getKey())
            ->exists();
    }

    /**
     * Toggle a bookmark on/off for the given model.
     * Returns true when the model is now bookmarked.
     */
    public function toggleBookmark(Model $model): bool
    {
        $existing = $this->bookmarks()
            ->where('bookmarkable_type', $model->getMorphClass())
            ->where('bookmarkable_id', $model->getKey())
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        try {
            $this->bookmarks()->create([
                'bookmarkable_type' => $model->getMorphClass(),
                'bookmarkable_id' => $model->getKey(),
            ]);
        } catch (QueryException $e) {
            // Unique constraint hit (concurrent toggle) — treat as bookmarked.
            report($e);
        }

        return true;
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')->withTimestamps();
    }

    public function targetedAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'target_user_id');
    }

    public function targetedMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'target_user_id');
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function assessmentSubmissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function instructorCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_instructor')->withTimestamps();
    }

    public function instructorApplication(): HasOne
    {
        return $this->hasOne(InstructorApplication::class);
    }

    public function courseSessions(): HasMany
    {
        return $this->hasMany(CourseSession::class, 'student_id');
    }

    public function chatRooms(): BelongsToMany
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_user')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * Accepted friends (in either direction).
     *
     * @return Collection<int, User>
     */
    public function friends(): Collection
    {
        $sent = Friendship::query()
            ->where('user_id', $this->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');

        $received = Friendship::query()
            ->where('friend_id', $this->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $ids = $sent->merge($received)->unique();

        return User::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    public function isFriendsWith(int $userId): bool
    {
        return Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($q) use ($userId): void {
                $q->where(function ($w) use ($userId): void {
                    $w->where('user_id', $this->id)->where('friend_id', $userId);
                })->orWhere(function ($w) use ($userId): void {
                    $w->where('user_id', $userId)->where('friend_id', $this->id);
                });
            })
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isBlogger(): bool
    {
        return $this->role === 'blogger';
    }

    public function isResearcher(): bool
    {
        return $this->role === 'researcher';
    }

    public function isEmployer(): bool
    {
        return $this->role === 'employer';
    }

    public function canSubmitType(string $type): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->is_active) {
            return false;
        }

        if ($this->isInstructor()) {
            // Dual-role (student + approved active instructor) automatically unlocks all contributor submissions
            if ($this->hasDualRole()) {
                return in_array($type, ['blog', 'tip_trick', 'opportunity', 'video'], true);
            }

            return in_array($type, ['tip_trick', 'video'], true);
        }

        return match ($type) {
            'blog' => $this->isBlogger(),
            'tip_trick' => $this->isResearcher() || $this->isInstructor(),
            'opportunity' => $this->isEmployer(),
            'video' => $this->isInstructor(),
            default => false,
        };
    }

    public function isEnrolledInCourse(int $courseId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->courses()->where('courses.id', $courseId)->exists();
    }

    /**
     * Get the user's first name for chat displays.
     */
    public function getFirstNameAttribute(): string
    {
        $name = trim($this->name ?? '');

        if ($name === '') {
            return 'Student';
        }

        $parts = preg_split('/\s+/', $name);

        return $parts[0] ?? $name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return PublicDiskPath::url($this->profile_photo_path);
    }

    /**
     * Whether this account operates with dual-role privileges (e.g. Student + Instructor).
     */
    public function hasDualRole(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->is_active) {
            return false;
        }

        if ($this->isInstructor()) {
            return $this->enrollments()->exists()
                || $this->instructorApplication()->where('status', 'approved')->exists();
        }

        if ($this->role === 'student' || empty($this->role)) {
            return $this->instructorApplication()->where('status', 'approved')->exists()
                || $this->instructorCourses()->exists();
        }

        if (in_array($this->role, ['blogger', 'researcher', 'employer'], true)) {
            return $this->enrollments()->exists()
                || $this->instructorApplication()->where('status', 'approved')->exists()
                || $this->instructorCourses()->exists();
        }

        return false;
    }

    public function isContributor(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Dedicated contributor roles or dual-role approved instructors
        return in_array($this->role, ['blogger', 'researcher', 'employer'], true)
            || ($this->isInstructor() && $this->is_active && $this->hasDualRole());
    }

    public function isStudent(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Students and dual-role instructors can access student learning features
        if ($this->role === 'student' || empty($this->role)) {
            return true;
        }

        if ($this->isInstructor()) {
            return $this->hasDualRole();
        }

        return $this->enrollments()->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active && ! $this->isAdmin()) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'instructor' => $this->isInstructor() || $this->isAdmin(),
            'contributor' => $this->isContributor() || $this->isAdmin(),
            'student' => $this->isStudent() || $this->isAdmin(),
            default => false,
        };
    }


    public function sendEmailVerificationNotification(?string $signerName = null): void
    {
        $resolvedSigner = $signerName;

        if ($resolvedSigner === null) {
            $sender = auth()->user();

            if ($sender instanceof self && in_array($sender->role, ['admin', 'instructor'], true)) {
                $resolvedSigner = $sender->name;
            }
        }

        $this->notify(new QueuedVerifyEmail($resolvedSigner));
    }

    /**
     * Get a deterministic, accessible chat color palette for this user.
     *
     * @return array{accent: string, name_color: string, name_color_dark: string, bg_light: string, bg_dark: string, border_light: string, border_dark: string, text_light: string, text_dark: string, chip_bg: string, gradient: string}
     */
    public function chatColorPalette(): array
    {
        $palettes = [
            [
                'accent' => '#0d9488', // Teal
                'name_color' => '#0f766e',
                'name_color_dark' => '#2dd4bf',
                'bg_light' => '#f0fdfa',
                'bg_dark' => 'rgba(13, 148, 136, 0.16)',
                'border_light' => '#99f6e4',
                'border_dark' => 'rgba(45, 212, 191, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#ccfbf1',
                'gradient' => 'linear-gradient(135deg, #0f766e, #0ea5e9)',
            ],
            [
                'accent' => '#0284c7', // Sky Blue
                'name_color' => '#0369a1',
                'name_color_dark' => '#38bdf8',
                'bg_light' => '#f0f9ff',
                'bg_dark' => 'rgba(2, 132, 199, 0.16)',
                'border_light' => '#bae6fd',
                'border_dark' => 'rgba(56, 189, 248, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#e0f2fe',
                'gradient' => 'linear-gradient(135deg, #0284c7, #6366f1)',
            ],
            [
                'accent' => '#6366f1', // Indigo
                'name_color' => '#4f46e5',
                'name_color_dark' => '#818cf8',
                'bg_light' => '#eef2ff',
                'bg_dark' => 'rgba(99, 102, 241, 0.16)',
                'border_light' => '#c7d2fe',
                'border_dark' => 'rgba(129, 140, 248, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#e0e7ff',
                'gradient' => 'linear-gradient(135deg, #6366f1, #a855f7)',
            ],
            [
                'accent' => '#9333ea', // Purple
                'name_color' => '#7e22ce',
                'name_color_dark' => '#c084fc',
                'bg_light' => '#faf5ff',
                'bg_dark' => 'rgba(147, 51, 234, 0.16)',
                'border_light' => '#e9d5ff',
                'border_dark' => 'rgba(192, 132, 252, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#f3e8ff',
                'gradient' => 'linear-gradient(135deg, #9333ea, #ec4899)',
            ],
            [
                'accent' => '#e11d48', // Rose
                'name_color' => '#be123c',
                'name_color_dark' => '#fb7185',
                'bg_light' => '#fff1f2',
                'bg_dark' => 'rgba(225, 29, 72, 0.16)',
                'border_light' => '#fecdd3',
                'border_dark' => 'rgba(251, 113, 133, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#ffe4e6',
                'gradient' => 'linear-gradient(135deg, #e11d48, #f97316)',
            ],
            [
                'accent' => '#ea580c', // Orange
                'name_color' => '#c2410c',
                'name_color_dark' => '#fb923c',
                'bg_light' => '#fff7ed',
                'bg_dark' => 'rgba(234, 88, 12, 0.16)',
                'border_light' => '#fed7aa',
                'border_dark' => 'rgba(251, 146, 60, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#ffedd5',
                'gradient' => 'linear-gradient(135deg, #ea580c, #eab308)',
            ],
            [
                'accent' => '#d97706', // Amber
                'name_color' => '#b45309',
                'name_color_dark' => '#fbbf24',
                'bg_light' => '#fffbeb',
                'bg_dark' => 'rgba(217, 119, 6, 0.16)',
                'border_light' => '#fde68a',
                'border_dark' => 'rgba(251, 191, 36, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#fef3c7',
                'gradient' => 'linear-gradient(135deg, #d97706, #10b981)',
            ],
            [
                'accent' => '#16a34a', // Emerald Green
                'name_color' => '#15803d',
                'name_color_dark' => '#4ade80',
                'bg_light' => '#f0fdf4',
                'bg_dark' => 'rgba(22, 163, 74, 0.16)',
                'border_light' => '#bbf7d0',
                'border_dark' => 'rgba(74, 222, 128, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#dcfce7',
                'gradient' => 'linear-gradient(135deg, #16a34a, #06b6d4)',
            ],
            [
                'accent' => '#0891b2', // Cyan
                'name_color' => '#0e7490',
                'name_color_dark' => '#22d3ee',
                'bg_light' => '#ecfeff',
                'bg_dark' => 'rgba(8, 145, 178, 0.16)',
                'border_light' => '#a5f3fc',
                'border_dark' => 'rgba(34, 211, 238, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#cffafe',
                'gradient' => 'linear-gradient(135deg, #0891b2, #3b82f6)',
            ],
            [
                'accent' => '#db2777', // Pink
                'name_color' => '#be185d',
                'name_color_dark' => '#f472b6',
                'bg_light' => '#fdf2f8',
                'bg_dark' => 'rgba(219, 39, 119, 0.16)',
                'border_light' => '#fbcfe8',
                'border_dark' => 'rgba(244, 114, 182, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#fce7f3',
                'gradient' => 'linear-gradient(135deg, #db2777, #8b5cf6)',
            ],
            [
                'accent' => '#7c3aed', // Violet
                'name_color' => '#6d28d9',
                'name_color_dark' => '#a78bfa',
                'bg_light' => '#f5f3ff',
                'bg_dark' => 'rgba(124, 58, 237, 0.16)',
                'border_light' => '#ddd6fe',
                'border_dark' => 'rgba(167, 139, 250, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#ede9fe',
                'gradient' => 'linear-gradient(135deg, #7c3aed, #ec4899)',
            ],
            [
                'accent' => '#059669', // Emerald
                'name_color' => '#047857',
                'name_color_dark' => '#34d399',
                'bg_light' => '#ecfdf5',
                'bg_dark' => 'rgba(5, 150, 105, 0.16)',
                'border_light' => '#a7f3d0',
                'border_dark' => 'rgba(52, 211, 153, 0.35)',
                'text_light' => '#0f172a',
                'text_dark' => '#f8fafc',
                'chip_bg' => '#d1fae5',
                'gradient' => 'linear-gradient(135deg, #059669, #0284c7)',
            ],
        ];

        $index = $this->id ? ($this->id % count($palettes)) : (abs(crc32($this->name ?? 'default')) % count($palettes));

        return $palettes[$index];
    }
}
