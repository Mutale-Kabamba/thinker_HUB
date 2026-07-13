<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'link_url',
        'promo_code',
        'provider',
        'extra',
        'is_published',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'expires_at' => 'date',
            'extra' => 'array',
        ];
    }

    /**
     * @var array<int, string>
     */
    public const TYPES = [
        'Promo Code',
        'Job',
        'Reading Material',
        'Scholarship',
        'Event',
    ];

    /**
     * Published and not past its expiry date.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            });
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isBefore(now()->startOfDay());
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ResourceComment::class, 'commentable');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(OpportunityReaction::class);
    }
}
