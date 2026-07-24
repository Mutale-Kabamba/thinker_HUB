<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Media extends Model
{
    use HasFactory;

    /**
     * Status lifecycle: pending → processing → ready. 'skipped' means the
     * original upload is served as-is (no FFmpeg / non-local disk),
     * 'failed' means the transcode errored (see `error`).
     */
    public const STATUSES = ['pending', 'processing', 'ready', 'failed', 'skipped'];

    protected $table = 'media';

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'duration_seconds',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Deleting a Media row always removes its stored file.
        static::deleting(function (Media $media): void {
            try {
                Storage::disk($media->disk)->delete($media->path);
            } catch (Throwable $e) {
                report($e);
            }
        });
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Playable states: transcoded (ready) or served as the original upload
     * (skipped — FFmpeg unavailable / non-local disk).
     */
    public function isPlayable(): bool
    {
        return in_array($this->status, ['ready', 'skipped'], true);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
