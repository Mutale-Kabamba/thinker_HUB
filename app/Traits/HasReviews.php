<?php

namespace App\Traits;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReviews
{
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function approvedReviews(): MorphMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    public function updateCachedRating(): void
    {
        $stats = $this->approvedReviews()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        $avg = $stats && $stats->avg_rating !== null ? round((float) $stats->avg_rating, 2) : 0.00;
        $count = $stats && $stats->count !== null ? (int) $stats->count : 0;

        if ($this instanceof \App\Models\Course) {
            $this->updateQuietly([
                'average_rating' => $avg,
                'review_count' => $count,
            ]);
        } elseif ($this instanceof \App\Models\User) {
            $this->updateQuietly([
                'instructor_rating' => $avg,
                'instructor_review_count' => $count,
            ]);
        }
    }
}
