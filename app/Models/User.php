<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
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
        'password',
        'role',
        'is_active',
        'track',
        'proficiency',
        'occupation',
        'whatsapp',
        'linkedin_url',
        'facebook_url',
        'profile_photo_path',
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

    public function instructorApplication(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InstructorApplication::class);
    }

    public function courseSessions(): HasMany
    {
        return $this->hasMany(CourseSession::class, 'student_id');
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

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isAdmin(),
            'student' => ! $this->isAdmin() && ! $this->isInstructor(),
            'instructor' => $this->isInstructor() && $this->is_active,
            default => false,
        };
    }
}
