<?php

namespace App\Observers;

use App\Models\CourseRating;
use App\Services\GamificationService;

class CourseRatingObserver
{
    public function created(CourseRating $rating): void
    {
        $this->handle($rating);
    }

    public function updated(CourseRating $rating): void
    {
        $this->handle($rating);
    }

    public function deleted(CourseRating $rating): void
    {
        try {
            $rating->course?->updateCachedRating();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function handle(CourseRating $rating): void
    {
        try {
            $rating->course?->updateCachedRating();

            if ($rating->user) {
                app(GamificationService::class)->awardCourseRating($rating->user, $rating);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
