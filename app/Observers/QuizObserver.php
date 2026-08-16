<?php

namespace App\Observers;

use App\Models\Quiz;
use App\Models\User;
use App\Notifications\QuizPublishedNotification;
use Illuminate\Support\Facades\DB;

class QuizObserver
{
    public function created(Quiz $quiz): void
    {
        $this->handleRelease($quiz);
    }

    public function updated(Quiz $quiz): void
    {
        $this->handleRelease($quiz);
    }

    /**
     * If the quiz is released, notify enrolled students once.
     */
    public function handleRelease(Quiz $quiz): void
    {
        if (! $quiz->isReleased() || ! $quiz->course_id) {
            return;
        }

        try {
            $enrolledStudents = User::query()
                ->where(function ($q): void {
                    $q->whereNull('role')->orWhere('role', 'student');
                })
                ->whereHas('enrollments', fn ($q) => $q->where('course_id', $quiz->course_id))
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            foreach ($enrolledStudents as $student) {
                // Check if notification already exists to prevent duplicate notifications
                $alreadyNotified = DB::table('notifications')
                    ->where('notifiable_type', $student->getMorphClass())
                    ->where('notifiable_id', $student->id)
                    ->where('type', QuizPublishedNotification::class)
                    ->where('data', 'like', '%"title":"New quiz available"%')
                    ->where('data', 'like', '%' . addcslashes($quiz->title, '%_\\') . '%')
                    ->exists();

                if (! $alreadyNotified) {
                    try {
                        $student->notify(new QuizPublishedNotification($quiz));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
