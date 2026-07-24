<?php

namespace App\Models;

use App\Notifications\QueuedVerifyEmail;
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
use Illuminate\Support\Facades\Storage;

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

    /**
     * Course completion rule for certificates: the student must be enrolled
     * and must have a passed attempt (QuizAttempt.passed, graded as
     * percentage >= Quiz.pass_percentage) for every active quiz in the
     * course. Courses without active quizzes are complete on enrollment.
     */
    public function hasCompletedCourse(Course $course): bool
    {
        $isEnrolled = $this->enrollments()
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            return false;
        }

        $activeQuizIds = $course->quizzes()
            ->where('is_active', true)
            ->pluck('id');

        if ($activeQuizIds->isEmpty()) {
            return true;
        }

        $passedQuizIds = QuizAttempt::query()
            ->where('user_id', $this->id)
            ->whereIn('quiz_id', $activeQuizIds)
            ->where('passed', true)
            ->distinct()
            ->pluck('quiz_id');

        return $passedQuizIds->count() === $activeQuizIds->count();
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
        return $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : null;
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
