<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HubPost extends Model
{
    use HasFactory;

    protected $table = 'hub_posts';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'category',
        'excerpt',
        'content',
        'youtube_url',
        'video_id',
        'opportunity_link',
        'opportunity_deadline',
        'is_published',
        'author_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'opportunity_deadline' => 'date',
    ];

    public const TYPES = [
        'tip_trick' => 'Tip & Trick',
        'blog' => 'Short Blog',
        'opportunity' => 'Opportunity',
        'video' => 'Video',
    ];

    public const DEFAULT_CATEGORIES = [
        'Programming',
        'Career',
        'Design',
        'Technology',
        'Personal Growth',
        'Scholarships',
        'General',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (HubPost $post): void {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title, $post->id);
            }

            if (! empty($post->youtube_url)) {
                $post->video_id = static::extractYoutubeId($post->youtube_url);
            } else {
                $post->video_id = null;
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        if (empty($baseSlug)) {
            $baseSlug = 'post';
        }

        $slug = $baseSlug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function getReadingTimeAttribute(): int
    {
        $text = strip_tags(($this->excerpt ?? '').' '.($this->content ?? ''));
        $wordCount = str_word_count($text);
        $minutes = (int) ceil($wordCount / 200);

        return max(1, $minutes);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeType(Builder $query, ?string $type): Builder
    {
        if (empty($type) || $type === 'all') {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (empty($category) || $category === 'all') {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $term = '%'.mb_strtolower(trim($term)).'%';

        return $query->where(function (Builder $q) use ($term) {
            $q->whereRaw('LOWER(title) LIKE ?', [$term])
                ->orWhereRaw('LOWER(excerpt) LIKE ?', [$term])
                ->orWhereRaw('LOWER(content) LIKE ?', [$term])
                ->orWhereRaw('LOWER(category) LIKE ?', [$term]);
        });
    }

    public static function categoryOptions(): array
    {
        $dbCategories = static::query()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $merged = array_unique(array_merge(static::DEFAULT_CATEGORIES, $dbCategories));
        sort($merged);

        return array_combine($merged, $merged);
    }
}
