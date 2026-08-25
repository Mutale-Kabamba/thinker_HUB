<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class CourseIntake extends Model
{
    use HasFactory;

    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_UPCOMING => 'Upcoming',
        self::STATUS_ACTIVE => 'Active / In Session',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    protected $fillable = [
        'course_id',
        'name',
        'start_date',
        'end_date',
        'next_intake_start_date',
        'registration_deadline',
        'status',
        'is_active',
        'max_capacity',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_intake_start_date' => 'date',
            'registration_deadline' => 'date',
            'is_active' => 'boolean',
            'max_capacity' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'course_intake_id');
    }

    public function students(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(User::class, Enrollment::class, 'course_intake_id', 'id', 'id', 'user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CourseSession::class, 'course_intake_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'course_intake_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'course_intake_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'course_intake_id');
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'course_intake_id');
    }

    public function resourceVideos(): HasMany
    {
        return $this->hasMany(ResourceVideo::class, 'course_intake_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_active', true)
                ->orWhere('status', self::STATUS_ACTIVE);
        })->where('status', '!=', self::STATUS_ARCHIVED);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UPCOMING)
            ->where('is_active', false);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED)
            ->orWhereNotNull('archived_at');
    }

    public function activate(): void
    {
        // Deactivate other active intakes for this course
        static::query()
            ->where('course_id', $this->course_id)
            ->whereKeyNot($this->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'status' => self::STATUS_COMPLETED,
            ]);

        $this->update([
            'is_active' => true,
            'status' => self::STATUS_ACTIVE,
            'archived_at' => null,
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'is_active' => false,
            'status' => self::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED || $this->archived_at !== null;
    }

    public function formattedDateRange(): ?string
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('M j, Y') . ' – ' . $this->end_date->format('M j, Y');
        }

        if ($this->start_date) {
            return 'Starts ' . $this->start_date->format('M j, Y');
        }

        if ($this->end_date) {
            return 'Ends ' . $this->end_date->format('M j, Y');
        }

        return null;
    }

    public function formattedNextIntake(): ?string
    {
        if ($this->next_intake_start_date) {
            return $this->next_intake_start_date->format('M j, Y');
        }

        return null;
    }
}
