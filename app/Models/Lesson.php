<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'youtube_url',
        'video_url',
        'duration_seconds',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
        return static::extractYoutubeId($this->youtube_url ?: $this->video_url);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;

        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }
}
