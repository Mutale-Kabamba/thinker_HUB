<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use App\Services\GamificationService;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->recalculate($review);

        try {
            $user = $review->user;
            if ($user && $user->isStudent()) {
                $targetLabel = match (true) {
                    $review->reviewable instanceof Course => 'course (' . $review->reviewable->title . ')',
                    $review->reviewable instanceof User => 'instructor (' . $review->reviewable->name . ')',
                    default => 'platform',
                };

                app(GamificationService::class)->awardPoints(
                    user: $user,
                    activityType: 'course_rating',
                    subject: $review,
                    baseXp: 10,
                    baseCoins: 3,
                    description: "Left a review for {$targetLabel}"
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function saved(Review $review): void
    {
        $this->recalculate($review);
    }

    public function deleted(Review $review): void
    {
        $this->recalculate($review);
    }

    protected function recalculate(Review $review): void
    {
        if ($review->reviewable && method_exists($review->reviewable, 'updateCachedRating')) {
            $review->reviewable->updateCachedRating();
        }
    }
}
