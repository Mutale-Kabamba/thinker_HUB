<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\UtmAttributionService;

class EnrollmentUtmObserver
{
    public function created(Enrollment $enrollment): void
    {
        app(UtmAttributionService::class)->attachToEnrollment($enrollment);
    }
}
