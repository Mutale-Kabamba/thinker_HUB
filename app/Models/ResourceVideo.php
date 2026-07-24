<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ResourceVideo extends Model
{
    use HasFactory;

    /**
     * Curated categories used for general learning videos.
     *
     * @var array<int, string>
     */
    public const CATEGORIES = [
        'Recorded Lessons',
        'Programming',
        'Web Development',
        'Mobile Development',
        'Cloud & DevOps',
        'Cybersecurity',
        'AI & Machine Learning',
        'Data Science',
        'Design',
        'UI/UX',
        'Career',
        'Interview Prep',
        'Certifications',
        'Entrepreneurship',
        'Productivity',
        'Business',
        'Soft Skills',
        'General',
    ];

    protected $fillable = [
        'title',
        'description',
        'youtube_url',
        'is_recorded_lesson',
        'course_id',
        'target_level',
        'category',
        'channel_name',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_recorded_lesson' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Local video uploads attached to this video. A present Media row IS the
     * "video_type" marker — no extra column on resource_videos: upload-branch
     * records store an empty youtube_url plus a Media row; YouTube records
     * keep a youtube_url and no Media row.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Whether this video uses a local file upload rather than YouTube.
     */
    public function hasLocalVideo(): bool
    {
        return $this->media()->exists();
    }

    /**
     * The playable local upload (transcoded, or original when transcoding
     * was skipped), if any.
     */
    public function playableLocalVideo(): ?Media
    {
        return $this->media()
            ->whereIn('status', ['ready', 'skipped'])
            ->latest()
            ->first();
    }

    /**
     * Best playback URL: the local file when a playable upload exists,
     * otherwise the YouTube embed URL (YouTube behaviour untouched).
     */
    public function videoUrl(): ?string
    {
        return $this->playableLocalVideo()?->url() ?? $this->embed_url;
    }

    protected static function booted(): void
    {
        // Deleting the video removes its Media rows; each Media deletion
        // removes its stored file from the disk.
        static::deleting(function (ResourceVideo $video): void {
            $video->media()->get()->each->delete();
        });
    }

    /**
     * Extract the YouTube video id from any common YouTube URL format.
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
        return static::extractYoutubeId($this->youtube_url);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;

        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $id = $this->youtube_id;

        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ResourceComment::class, 'commentable');
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    /**
     * @return array<string, string>
     */
    public static function categoryOptions(): array
    {
        return array_combine(self::CATEGORIES, self::CATEGORIES);
    }
}
