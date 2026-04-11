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
        'name',
        'description',
        'file_path',
        'target_track',
        'target_level',
        'target_user_id',
        'date_given',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'date_given' => 'date',
            'due_date' => 'date',
        ];
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
                $builder->whereNull('target_level');

                if ($userTrack !== '') {
                    $builder
                        ->orWhere('target_level', $userTrack)
                        ->orWhere('target_track', $userTrack);
                }
            })
            ->where(function (Builder $builder) use ($user): void {
                $builder->whereNull('target_user_id')
                    ->orWhere('target_user_id', $user->id);
            });
    }
}
