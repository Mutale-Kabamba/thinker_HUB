<?php

namespace App\Filament\Student\Pages;

use App\Models\Bookmark;
use App\Models\LearningMaterial;
use App\Models\ResourceVideo;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;

class LearningResources extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'LEARNING';

    protected static ?string $navigationLabel = 'Learning Resources';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.student.pages.learning-resources';

    /** @var array<int, array<string, mixed>> */
    public array $courseLessons = [];

    /** @var array<int, array<string, mixed>> */
    public array $generalVideos = [];

    /** @var array<int, array<string, mixed>> */
    public array $savedItems = [];

    /** @var array<int, string> */
    public array $generalCategories = [];

    /** @var array<int, string> */
    public array $lessonCategories = [];

    #[Url(as: 'topic')]
    public string $filterCategory = '';

    #[Url(as: 'lesson_topic')]
    public string $filterLessonCategory = '';

    // Active in-app player state (Livewire-driven so comments can attach).
    public bool $showPlayer = false;

    public ?string $playerSource = null; // 'youtube' | 'file'

    public ?string $playerUrl = null;

    public ?string $playerTitle = null;

    public ?string $commentType = null; // 'video' | 'lesson'

    public ?int $commentId = null;

    public function mount(): void
    {
        $this->loadVideos();
    }

    public function updatedFilterCategory(): void
    {
        $this->loadVideos();
    }

    public function updatedFilterLessonCategory(): void
    {
        $this->loadVideos();
    }

    public function openGeneralVideo(int $id): void
    {
        $video = ResourceVideo::query()->where('is_published', true)->find($id);

        if (! $video) {
            return;
        }

        $local = $video->playableLocalVideo();

        if ($local) {
            $this->playerSource = 'file';
            $this->playerUrl = $local->url();
        } elseif ($video->hasLocalVideo()) {
            // Upload exists but is pending/processing/failed — not playable yet.
            $this->playerSource = 'processing';
            $this->playerUrl = null;
        } elseif ($video->embed_url) {
            $this->playerSource = 'youtube';
            $this->playerUrl = $video->embed_url.'?autoplay=1&rel=0';
        } else {
            return;
        }

        $this->playerTitle = $video->title;
        $this->commentType = 'video';
        $this->commentId = $video->id;
        $this->showPlayer = true;
    }

    public function openLesson(int $id): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $lesson = LearningMaterial::query()
            ->visibleTo($user)
            ->where('material_type', 'Video')
            ->find($id);

        if (! $lesson) {
            return;
        }

        $embed = $this->youtubeEmbed($lesson->video_url);

        if ($embed) {
            $this->playerSource = 'youtube';
            $this->playerUrl = $embed.'?autoplay=1&rel=0';
        } elseif ($lesson->file_path) {
            $this->playerSource = 'file';
            $this->playerUrl = Storage::disk('public')->url($lesson->file_path);
        } else {
            return;
        }

        $this->playerTitle = $lesson->title;
        $this->commentType = 'lesson';
        $this->commentId = $lesson->id;
        $this->showPlayer = true;
    }

    public function closePlayer(): void
    {
        $this->showPlayer = false;
        $this->playerSource = null;
        $this->playerUrl = null;
        $this->playerTitle = null;
        $this->commentType = null;
        $this->commentId = null;
    }

    public function toggleBookmark(string $type, int $id): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $model = match ($type) {
            'lesson' => LearningMaterial::query()->visibleTo($user)->find($id),
            'video' => ResourceVideo::query()->where('is_published', true)->find($id),
            default => null,
        };

        if (! $model) {
            return;
        }

        $user->toggleBookmark($model);

        $this->loadVideos();
    }

    private function loadVideos(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $bookmarkedKeys = $user->bookmarks()
            ->get(['bookmarkable_type', 'bookmarkable_id'])
            ->map(fn (Bookmark $b): string => $b->bookmarkable_type.':'.$b->bookmarkable_id)
            ->all();

        $isBookmarked = fn (string $class, int $id): bool => in_array($class.':'.$id, $bookmarkedKeys, true);

        // Recorded lessons per course (from learning materials tagged as Video).
        $materialLessons = LearningMaterial::query()
            ->with('course')
            ->visibleTo($user)
            ->where('material_type', 'Video')
            ->where(function ($q): void {
                $q->whereNotNull('video_url')->orWhereNotNull('file_path');
            })
            ->latest()
            ->get()
            ->map(function (LearningMaterial $item) use ($isBookmarked): array {
                $embed = $this->youtubeEmbed($item->video_url);

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'course' => $item->course?->title ?? 'General',
                    'category' => $item->category ?? 'General',
                    'description' => $item->description,
                    'source' => $embed ? 'youtube' : 'file',
                    'embed_url' => $embed,
                    'file_url' => (! $embed && $item->file_path)
                        ? Storage::disk('public')->url($item->file_path)
                        : null,
                    'thumbnail' => $embed
                        ? $this->youtubeThumbnail($item->video_url)
                        : null,
                    'created_at' => $item->created_at?->format('M d, Y'),
                    'bookmarked' => $isBookmarked(LearningMaterial::class, $item->id),
                ];
            })
            ->values();

        // Recorded lessons from admin-managed videos, targeted by course + level.
        $recordedVideoLessons = ResourceVideo::query()
            ->with('course')
            ->where('is_published', true)
            ->where('is_recorded_lesson', true)
            ->whereNotNull('course_id')
            ->whereIn('course_id', $user->courses()->pluck('courses.id'))
            ->where(function ($q) use ($user): void {
                $q->whereNull('target_level')->orWhere('target_level', $user->track);
            })
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(fn (ResourceVideo $video): array => $this->presentVideo($video) + [
                'course' => $video->course?->title ?? 'General',
                'record_type' => 'video',
                'bookmarked' => $isBookmarked(ResourceVideo::class, $video->id),
            ])
            ->filter(fn (array $v): bool => filled($v['embed_url']) || filled($v['file_url']) || $v['processing'])
            ->values();

        $combinedLessons = $materialLessons
            ->map(fn (array $lesson): array => $lesson + ['record_type' => 'lesson'])
            ->concat($recordedVideoLessons)
            ->values();

        $this->lessonCategories = $combinedLessons
            ->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($this->filterLessonCategory !== '') {
            $combinedLessons = $combinedLessons->where('category', $this->filterLessonCategory)->values();
        }

        $this->courseLessons = $combinedLessons
            ->sortByDesc('created_at')
            ->values()
            ->all();

        // Curated general videos (admin-managed ResourceVideo).
        $query = ResourceVideo::query()
            ->where('is_published', true)
            ->where('is_recorded_lesson', false);

        if ($this->filterCategory !== '') {
            $query->where('category', $this->filterCategory);
        }

        $this->generalVideos = $query
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(fn (ResourceVideo $video): array => $this->presentVideo($video) + [
                'channel' => $video->channel_name,
                'bookmarked' => $isBookmarked(ResourceVideo::class, $video->id),
            ])
            ->filter(fn (array $v): bool => filled($v['embed_url']) || filled($v['file_url']) || $v['processing'])
            ->values()
            ->all();

        $this->generalCategories = collect(ResourceVideo::CATEGORIES)
            ->reject(fn (string $category): bool => $category === 'Recorded Lessons')
            ->values()
            ->all();

        $this->loadSaved($user);
    }

    private function loadSaved($user): void
    {
        $this->savedItems = $user->bookmarks()
            ->with('bookmarkable')
            ->latest()
            ->latest('id')
            ->get()
            ->map(function (Bookmark $bookmark): ?array {
                $item = $bookmark->bookmarkable;

                if ($item instanceof LearningMaterial) {
                    return [
                        'type' => 'lesson',
                        'id' => $item->id,
                        'title' => $item->title,
                        'kind' => 'Lesson',
                        'meta' => $item->course?->title ?? 'General',
                        'saved_at' => $bookmark->created_at?->diffForHumans(),
                    ];
                }

                if ($item instanceof ResourceVideo) {
                    return [
                        'type' => 'video',
                        'id' => $item->id,
                        'title' => $item->title,
                        'kind' => 'Video',
                        'meta' => $item->category,
                        'saved_at' => $bookmark->created_at?->diffForHumans(),
                    ];
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Shared card payload for a ResourceVideo, covering both YouTube and
     * local-upload sources. 'processing' flags uploads that are not playable
     * yet (pending/processing/failed) so lists keep them with a hint.
     *
     * @return array<string, mixed>
     */
    private function presentVideo(ResourceVideo $video): array
    {
        $local = $video->playableLocalVideo();
        $hasLocal = $local !== null || $video->hasLocalVideo();

        return [
            'id' => $video->id,
            'title' => $video->title,
            'description' => $video->description,
            'category' => $video->category,
            'source' => $local ? 'file' : ($hasLocal ? 'processing' : 'youtube'),
            'embed_url' => $hasLocal ? null : $video->embed_url,
            'file_url' => $local?->url(),
            'processing' => $local === null && $hasLocal,
            'thumbnail' => $hasLocal ? null : $video->thumbnail_url,
            'created_at' => $video->created_at?->format('M d, Y'),
        ];
    }

    private function youtubeEmbed(?string $url): ?string
    {
        $id = ResourceVideo::extractYoutubeId($url);

        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    private function youtubeThumbnail(?string $url): ?string
    {
        $id = ResourceVideo::extractYoutubeId($url);

        return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : null;
    }
}
