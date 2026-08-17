<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'title',
        'comment',
        'is_approved',
        'is_anonymous',
        'is_verified',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'is_anonymous' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    // --- Scopes ---

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopePlatform(Builder $query): Builder
    {
        return $query->whereNull('reviewable_type')->whereNull('reviewable_id');
    }

    public function scopePlatformOnly(Builder $query): Builder
    {
        return $query->whereNull('reviewable_type')->whereNull('reviewable_id');
    }

    public function scopeForModel(Builder $query, Model $model): Builder
    {
        return $query->where('reviewable_type', get_class($model))
            ->where('reviewable_id', $model->getKey());
    }
}
