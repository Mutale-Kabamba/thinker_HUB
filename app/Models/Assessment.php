<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'course_id',
        'course_intake_id',
        'target_level',
        'date_given',
        'publish_at',
        'due_date',
        'file_path',
        'file_paths',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'file_paths' => 'array',
            'date_given' => 'date',
            'publish_at' => 'datetime',
            'due_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Assessment $assessment): void {
            $paths = $assessment->file_paths;
            if (is_array($paths) && ! empty($paths)) {
                $first = reset($paths);
                if ($first && is_string($first)) {
                    $assessment->file_path = $first;
                }
            } elseif ($assessment->file_path && empty($assessment->file_paths)) {
                $assessment->file_paths = [$assessment->file_path];
            }
        });
    }

    public function getFilePathsAttribute($value): array
    {
        if ($value !== null) {
            $decoded = is_string($value) ? json_decode($value, true) : $value;
            if (is_array($decoded) && ! empty($decoded)) {
                return array_values(array_filter($decoded, fn ($p) => filled($p)));
            }
        }

        if (filled($this->file_path)) {
            return [$this->file_path];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    public function getAllFilePathsAttribute(): array
    {
        return $this->file_paths;
    }

    public function scopeReleased(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereNull('publish_at')
                ->orWhere('publish_at', '<=', now());
        });
    }

    public function isReleased(): bool
    {
        if ($this->publish_at !== null) {
            return $this->publish_at->lte(now());
        }

        return true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class, 'course_intake_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $enrolledCourseIds = $user->courses()->pluck('courses.id');
        $userTrack = trim((string) $user->track);

        return $query->where(function (Builder $q) use ($user, $enrolledCourseIds, $userTrack): void {
            $q->where('user_id', $user->id)
                ->orWhere(function (Builder $courseScope) use ($user, $enrolledCourseIds, $userTrack): void {
                    $courseScope->whereIn('course_id', $enrolledCourseIds)
                        ->where(function (Builder $userMatch) use ($user): void {
                            $userMatch->whereNull('user_id')
                                ->orWhere('user_id', $user->id);
                        })
                        ->where(function (Builder $levelMatch) use ($userTrack): void {
                            $levelMatch->whereNull('target_level');
                            if ($userTrack !== '') {
                                $levelMatch->orWhere('target_level', $userTrack);
                            }
                        })
                        ->where(function (Builder $intakeMatch) use ($user): void {
                            $intakeMatch->whereNull('course_intake_id')
                                ->orWhereIn('course_intake_id', function ($sub) use ($user) {
                                    $sub->select('course_intake_id')
                                        ->from('enrollments')
                                        ->where('user_id', $user->id)
                                        ->whereNotNull('course_intake_id');
                                });
                        });
                });
        });
    }
}
