<?php

namespace App\Observers;

use App\Models\Attendance;
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
    }

    private function handle(Attendance $attendance): void
    {
        try {
            if ($attendance->status === Attendance::STATUS_PRESENT && $attendance->student) {
                app(GamificationService::class)->awardAttendance($attendance->student, $attendance);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
