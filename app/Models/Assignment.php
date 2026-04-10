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
        'scope',
        'target_track',
        'target_user_id',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
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

        return $query->where(function (Builder $builder) use ($user): void {
            $builder->where('scope', 'all')
                ->orWhere(function (Builder $q) use ($user): void {
                    $q->where('scope', 'level')->where('target_track', $user->track);
                })
                ->orWhere(function (Builder $q) use ($user): void {
                    $q->where('scope', 'personal')->where('target_user_id', $user->id);
                });
        })->whereIn('course_id', $enrolledCourseIds);
    }
}
