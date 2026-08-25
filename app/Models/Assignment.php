<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'course_intake_id',
        'name',
        'description',
        'file_path',
        'file_paths',
        'target_track',
        'target_level',
        'target_user_id',
        'date_given',
        'publish_at',
        'due_date',
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
        static::saving(function (Assignment $assignment): void {
            $paths = $assignment->file_paths;
            if (is_array($paths) && ! empty($paths)) {
                $first = reset($paths);
                if ($first && is_string($first)) {
                    $assignment->file_path = $first;
                }
            } elseif ($assignment->file_path && empty($assignment->file_paths)) {
                $assignment->file_paths = [$assignment->file_path];
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

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
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
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $enrolledCourseIds = $user->courses()->pluck('courses.id');
        $userTrack = trim((string) $user->track);

        return $query
            ->whereIn('course_id', $enrolledCourseIds)
            ->where(function (Builder $builder) use ($userTrack): void {
                $builder->where(function (Builder $q): void {
                    $q->whereNull('target_level')
                        ->whereNull('target_track');
                });

                if ($userTrack !== '') {
                    $builder
                        ->orWhere('target_level', $userTrack)
                        ->orWhere('target_track', $userTrack);
                }
            })
            ->where(function (Builder $builder) use ($user): void {
                $builder->whereNull('target_user_id')
                    ->orWhere('target_user_id', $user->id);
            })
            ->where(function (Builder $builder) use ($user): void {
                $builder->whereNull('course_intake_id')
                    ->orWhereIn('course_intake_id', function ($sub) use ($user) {
                        $sub->select('course_intake_id')
                            ->from('enrollments')
                            ->where('user_id', $user->id)
                            ->whereNotNull('course_intake_id');
                    });
            });
    }
}
