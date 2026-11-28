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
        try {
            $stats = $this->approvedReviews()
                ->whereNotNull('rating')
                ->selectRaw('AVG(rating) as avg_rating, COUNT(rating) as rating_count')
                ->first();

            $totalReviewCount = $this->approvedReviews()->count();

            $avg = $stats && $stats->avg_rating !== null ? round((float) $stats->avg_rating, 2) : 0.00;
            $count = $totalReviewCount;

            if ($this instanceof \App\Models\Course) {
                $courseRatingSum = 0.0;
                $courseRatingCount = 0;

                if (\Illuminate\Support\Facades\Schema::hasTable('course_ratings')) {
                    $crStats = \App\Models\CourseRating::query()
                        ->where('course_id', $this->id)
                        ->whereNotNull('rating')
                        ->selectRaw('SUM(rating) as sum_rating, COUNT(rating) as rating_count')
                        ->first();

                    $courseRatingSum = (float) ($crStats?->sum_rating ?? 0);
                    $courseRatingCount = (int) ($crStats?->rating_count ?? 0);
                }

                $reviewRatedCount = (int) ($stats?->rating_count ?? 0);
                $reviewSum = $reviewRatedCount > 0 ? ((float) $stats->avg_rating * $reviewRatedCount) : 0.0;
                $combinedRatedCount = $reviewRatedCount + $courseRatingCount;
                $combinedAvg = $combinedRatedCount > 0
                    ? round(($reviewSum + $courseRatingSum) / $combinedRatedCount, 2)
                    : 0.00;
                $combinedReviewCount = $totalReviewCount + $courseRatingCount;

                $this->updateQuietly([
                    'average_rating' => $combinedAvg,
                    'review_count' => $combinedReviewCount,
                ]);
            } elseif ($this instanceof \App\Models\User) {
                $this->updateQuietly([
                    'instructor_rating' => $avg,
                    'instructor_review_count' => $count,
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
