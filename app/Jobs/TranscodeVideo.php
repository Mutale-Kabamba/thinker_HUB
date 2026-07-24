<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Transcode an uploaded video to a web-friendly H.264 MP4 (720p cap,
 * +faststart) and extract its duration. Degrades gracefully by design:
 *   - FFmpeg missing        → keep original, status 'skipped' (served as-is)
 *   - non-local disk (s3)   → keep original, status 'skipped'
 *   - missing source file   → status 'failed' + error
 *   - transcode error       → status 'failed' + error, reported
 * No exception escapes handle().
 */
class TranscodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        public readonly int $mediaId,
    ) {}

    public function handle(): void
    {
        $media = Media::query()->find($this->mediaId);

        if (! $media) {
            return;
        }

        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            $media->update([
                'status' => 'failed',
                'error' => "Uploaded file not found on disk [{$media->disk}]: {$media->path}",
            ]);

            return;
        }

        $ffmpeg = $this->ffmpegBinary();

        if ($ffmpeg === null) {
            $media->update([
                'status' => 'skipped',
                'error' => 'FFmpeg is not available on this machine; the original upload is served as-is.',
            ]);

            return;
        }

        if ((config("filesystems.disks.{$media->disk}.driver") ?? 'local') !== 'local') {
            $media->update([
                'status' => 'skipped',
                'error' => 'Transcoding is only supported on local disks; the original upload is served as-is.',
            ]);

            return;
        }

        $media->update(['status' => 'processing', 'error' => null]);

        $originalPath = $media->path;

        try {
            $input = $disk->path($originalPath);
            $newPath = $this->transcodedPath($originalPath);
            $output = $disk->path($newPath);

            // Cap at 720p without upscaling, even dimensions (-2), faststart.
            $process = new Process([
                $ffmpeg, '-y', '-i', $input,
                '-vf', 'scale=-2:min(720,ih)',
                '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '23',
                '-c:a', 'aac',
                '-movflags', '+faststart',
                $output,
            ]);
            $process->setTimeout($this->timeout);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new \RuntimeException(Str::limit(trim($process->getErrorOutput()) ?: 'ffmpeg exited non-zero', 900));
            }

            $media->update([
                'path' => $newPath,
                'mime_type' => 'video/mp4',
                'size_bytes' => (int) (filesize($output) ?: 0),
                'duration_seconds' => $this->probeDuration($ffmpeg, $output),
                'status' => 'ready',
                'error' => null,
            ]);

            if ($newPath !== $originalPath) {
                $disk->delete($originalPath);
            }
        } catch (Throwable $e) {
            report($e);

            $media->update([
                'status' => 'failed',
                'error' => Str::limit($e->getMessage(), 900),
            ]);
        }
    }

    /**
     * Executable FFmpeg binary, or null when it cannot be run (missing,
     * not on PATH, blocked). Verified by actually invoking `-version`.
     */
    private function ffmpegBinary(): ?string
    {
        $binary = (string) config('videos.ffmpeg_path', 'ffmpeg');

        try {
            $process = new Process([$binary, '-version']);
            $process->setTimeout(15);
            $process->run();

            return $process->isSuccessful() ? $binary : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Duration in seconds, parsed from `ffmpeg -i` output (no ffprobe
     * dependency — the -i banner is printed by every ffmpeg build).
     */
    private function probeDuration(string $ffmpeg, string $file): ?int
    {
        try {
            $process = new Process([$ffmpeg, '-i', $file]);
            $process->setTimeout(30);
            $process->run();

            $output = $process->getOutput().$process->getErrorOutput();

            if (preg_match('/Duration:\s*(\d+):(\d+):(\d+)/', $output, $m)) {
                return ((int) $m[1] * 3600) + ((int) $m[2] * 60) + (int) $m[3];
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    /**
     * Target path for the transcoded file: same directory, .mp4 extension.
     */
    private function transcodedPath(string $path): string
    {
        $directory = trim(dirname($path), '.');
        $name = pathinfo($path, PATHINFO_FILENAME).'.mp4';

        return ($directory !== '' ? $directory.'/' : '').$name;
    }
}
