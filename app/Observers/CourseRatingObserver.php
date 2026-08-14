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

    private function handle(CourseRating $rating): void
    {
        try {
            if ($rating->user) {
                app(GamificationService::class)->awardCourseRating($rating->user, $rating);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
