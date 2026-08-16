<?php

namespace App\Observers;

use App\Jobs\GenerateMaterialThumbnailJob;
use App\Jobs\SendMaterialNotificationJob;
use App\Models\LearningMaterial;

class LearningMaterialObserver
{
    public function created(LearningMaterial $material): void
    {
        // Offload heavy notifications and thumbnail processing to background queue
        SendMaterialNotificationJob::dispatch($material);
        GenerateMaterialThumbnailJob::dispatch($material);
    }
}

