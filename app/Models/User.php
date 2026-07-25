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
        'track',
        'profile_photo_path',
        'proficiency',
        'occupation',
        'bio',
        'whatsapp',
        'linkedin_url',
        'facebook_url',
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

    /**
     * Total XP across all award transactions. Correctness first: summed
     * live from xp_transactions (indexed on user_id), no cached counter.
     */
    public function xpTotal(): int
    {
        return (int) $this->xpTransactions()->sum('points');
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
            ->where('is_active', true)
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
     * Course completion rule for certificates: enrolled + 100% progress on
     * gradable content (quizzes, assignments, assessments). Kept as the
     * single completion gate used by observers and gamification; certificate
     * issuing additionally enforces attendance in CertificateService.
     */
    public function hasCompletedCourse(Course $course): bool
    {
        return $this->courseProgress($course)['complete'];
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

    public function isEnrolledInCourse(int $courseId): bool
    {
        return $this->courses()->where('courses.id', $courseId)->exists();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return PublicDiskPath::url($this->profile_photo_path);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'student' => ! $this->isAdmin() && ! $this->isInstructor(),
            'instructor' => $this->isInstructor() && $this->is_active,
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
}
