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
        'target_level',
        'date_given',
        'publish_at',
        'due_date',
        'file_path',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'date_given' => 'date',
            'publish_at' => 'datetime',
            'due_date' => 'date',
        ];
    }

    public function scopeReleased(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereNull('publish_at')
                ->orWhere('publish_at', '<=', now());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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

        return $query->where('user_id', $user->id);
    }
}
