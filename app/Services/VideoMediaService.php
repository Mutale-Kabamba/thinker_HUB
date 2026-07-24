<?php

namespace App\Services;

use App\Jobs\TranscodeVideo;
use App\Models\Media;
use App\Models\ResourceVideo;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Attaches locally uploaded video files to ResourceVideos as Media rows and
 * queues transcoding. One shared entry point for the Admin and Instructor
 * resource pages so both panels behave identically.
 */
class VideoMediaService
{
    /**
     * Configured upload disk (VIDEO_UPLOAD_DISK; 'public' in dev, s3-ready).
     */
    public function disk(): string
    {
        return (string) config('videos.upload_disk', 'public');
    }

    /**
     * Replace any existing local video with the newly uploaded file
     * (already stored on the upload disk by the form component), then
     * dispatch the transcode job. Old Media rows — and their files — are
     * deleted first.
     */
    public function attachLocalVideo(ResourceVideo $video, string $storedPath, ?string $originalName): Media
    {
        $this->clearLocalVideo($video);

        $disk = Storage::disk($this->disk());

        $media = $video->media()->create([
            'disk' => $this->disk(),
            'path' => $storedPath,
            'original_name' => $originalName ?? basename($storedPath),
            'mime_type' => $this->quietly(fn () => $disk->mimeType($storedPath)) ?: 'application/octet-stream',
            'size_bytes' => (int) ($this->quietly(fn () => $disk->size($storedPath)) ?? 0),
            'status' => 'pending',
        ]);

        TranscodeVideo::dispatch($media->id);

        return $media;
    }

    /**
     * Remove all local uploads (Media rows + stored files) from a video.
     */
    public function clearLocalVideo(ResourceVideo $video): void
    {
        $video->media()->get()->each->delete();
    }

    private function quietly(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return null;
        }
    }
}
