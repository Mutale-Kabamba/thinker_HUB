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

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }

    public function getFileIconAttribute(): string
    {
        $mime = strtolower($this->mime_type ?? '');
        $ext = strtolower(pathinfo($this->original_name ?? '', PATHINFO_EXTENSION));

        if (str_contains($mime, 'pdf') || $ext === 'pdf') {
            return 'fa-solid fa-file-pdf text-rose-600';
        }
        if (str_contains($mime, 'presentation') || str_contains($mime, 'powerpoint') || in_array($ext, ['ppt', 'pptx'], true)) {
            return 'fa-solid fa-file-powerpoint text-amber-600';
        }
        if (str_contains($mime, 'word') || str_contains($mime, 'document') || in_array($ext, ['doc', 'docx'], true)) {
            return 'fa-solid fa-file-word text-blue-600';
        }
        if (str_contains($mime, 'sheet') || str_contains($mime, 'excel') || in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
            return 'fa-solid fa-file-excel text-emerald-600';
        }
        if (str_contains($mime, 'image') || in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'], true)) {
            return 'fa-solid fa-file-image text-indigo-600';
        }
        if (str_contains($mime, 'video') || in_array($ext, ['mp4', 'mov', 'avi', 'webm'], true)) {
            return 'fa-solid fa-file-video text-purple-600';
        }

        return 'fa-solid fa-file-lines text-slate-500';
    }

    public function getIsImageAttribute(): bool
    {
        $mime = strtolower($this->mime_type ?? '');
        $ext = strtolower(pathinfo($this->original_name ?? '', PATHINFO_EXTENSION));

        return str_contains($mime, 'image') || in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'], true);
    }
}
