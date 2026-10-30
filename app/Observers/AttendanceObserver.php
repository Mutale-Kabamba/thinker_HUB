<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\CourseSession;
use App\Services\GamificationService;

class AttendanceObserver
{
    public function created(Attendance $attendance): void
    {
        $this->handle($attendance);
    }

    public function updated(Attendance $attendance): void
    {
        $this->handle($attendance);
        $this->syncSessionCompleted($attendance);
    }

    private function handle(Attendance $attendance): void
    {
        try {
            if ($attendance->status === Attendance::STATUS_PRESENT && $attendance->student) {
                app(GamificationService::class)->awardAttendance($attendance->student, $attendance);
            }

            if (in_array($attendance->status, [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE], true) && $attendance->student) {
                $course = $attendance->session?->course;
                if ($course && $attendance->student->hasCompletedCourse($course)) {
                    $certificate = app(\App\Services\CertificateService::class)->issue($attendance->student, $course);
                    if ($certificate && $certificate->wasRecentlyCreated) {
                        $attendance->student->notify(new \App\Notifications\CertificateIssuedNotification($certificate));
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function syncSessionCompleted(Attendance $attendance): void
    {
        try {
            if ($attendance->wasChanged('status') && $attendance->course_session_id) {
                CourseSession::query()
                    ->whereKey($attendance->course_session_id)
                    ->where('status', '!=', CourseSession::STATUS_COMPLETED)
                    ->update(['status' => CourseSession::STATUS_COMPLETED]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
