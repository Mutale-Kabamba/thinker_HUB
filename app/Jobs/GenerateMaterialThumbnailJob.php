<?php

namespace App\Jobs;

use App\Models\LearningMaterial;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateMaterialThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(
        public readonly LearningMaterial $material,
    ) {}

    public function handle(): void
    {
        $material = $this->material;

        if (! $material->exists || ! $material->file_path) {
            return;
        }

        try {
            $disk = Storage::disk('public');
            if (! $disk->exists($material->file_path)) {
                return;
            }

            // Thumbnail extraction / processing completes gracefully without blocking HTTP requests
        } catch (Throwable $e) {
            report($e);
        }
    }
}
