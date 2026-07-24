<?php

namespace App\Observers;

use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\SessionScheduledNotification;

class CourseSessionObserver
{
    private function notifyUser(User $user, CourseSession $session): void
    {
        try {
            $user->notify(new SessionScheduledNotification($session));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function created(CourseSession $session): void
    {
        try {
            // One-on-one sessions belong to a single student; only that
            // student should hear about them.
            if ($session->student_id && $session->isOneOnOne()) {
                $student = User::query()->find($session->student_id);

                if ($student) {
                    $this->notifyUser($student, $session);
                }

                return;
            }

            if (! $session->course_id) {
                return;
            }

            Enrollment::query()
                ->where('course_id', $session->course_id)
                ->with('user')
                ->chunkById(100, function ($enrollments) use ($session): void {
                    foreach ($enrollments as $enrollment) {
                        $user = $enrollment->user;

                        if (! $user || $user->id === $session->instructor_id) {
                            continue;
                        }

                        $this->notifyUser($user, $session);
                    }
                });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
