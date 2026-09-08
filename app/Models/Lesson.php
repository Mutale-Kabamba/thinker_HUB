<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_free_preview' => 'boolean',
            'duration_seconds' => 'integer',
            'min_watch_percentage' => 'integer',
            'sort_order' => 'integer',
            'order_column' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Lesson $lesson) {
            if (empty($lesson->course_id) && ! empty($lesson->course_section_id)) {
                $lesson->course_id = $lesson->section?->course_id;
            }
            if (empty($lesson->slug) && ! empty($lesson->title)) {
                $lesson->slug = Str::slug($lesson->title);
            }
            if (empty($lesson->order_column) && ! empty($lesson->sort_order)) {
                $lesson->order_column = $lesson->sort_order;
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function content(): MorphTo
    {
        return $this->morphTo();
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function progressForUser(?int $userId = null): HasOne
    {
        $userId = $userId ?? \Illuminate\Support\Facades\Auth::id() ?? 0;

        return $this->hasOne(LessonProgress::class)->where('user_id', $userId);
    }

    /**
     * Extract the 11-character YouTube video ID from various URL formats.
     */
    public static function extractYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        // youtu.be/<id>
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        // youtube.com/watch?v=<id>, /embed/<id>, /shorts/<id>, /live/<id>
        if (preg_match('~(?:v=|/embed/|/shorts/|/live/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        // Bare 11-char id
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', trim($url))) {
            return trim($url);
        }

        return null;
    }

    public function getYoutubeIdAttribute(): ?string
    {
        $url = $this->video_url ?: $this->youtube_url;

        return static::extractYoutubeId($url);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;

        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (! empty($this->attributes['video_url'])) {
            return $this->attributes['video_url'];
        }

        if (! empty($this->attributes['youtube_url'])) {
            return $this->attributes['youtube_url'];
        }

        if ($this->content) {
            return $this->content->video_url ?? $this->content->youtube_url ?? null;
        }

        return null;
    }
}
