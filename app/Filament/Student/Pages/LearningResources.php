<?php

namespace App\Filament\Student\Pages;

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

        if (! $video || ! $video->embed_url) {
            return;
        }

        $this->playerSource = 'youtube';
        $this->playerUrl = $video->embed_url . '?autoplay=1&rel=0';
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
            $this->playerUrl = $embed . '?autoplay=1&rel=0';
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

    private function loadVideos(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

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
            ->map(function (LearningMaterial $item): array {
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
            ->map(fn (ResourceVideo $video): array => [
                'id' => $video->id,
                'title' => $video->title,
                'course' => $video->course?->title ?? 'General',
                'category' => $video->category,
                'description' => $video->description,
                'source' => 'youtube',
                'embed_url' => $video->embed_url,
                'file_url' => null,
                'thumbnail' => $video->thumbnail_url,
                'created_at' => $video->created_at?->format('M d, Y'),
                'record_type' => 'video',
            ])
            ->filter(fn (array $v): bool => filled($v['embed_url']))
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
            ->map(fn (ResourceVideo $video): array => [
                'id' => $video->id,
                'title' => $video->title,
                'description' => $video->description,
                'category' => $video->category,
                'channel' => $video->channel_name,
                'embed_url' => $video->embed_url,
                'thumbnail' => $video->thumbnail_url,
            ])
            ->filter(fn (array $v): bool => filled($v['embed_url']))
            ->values()
            ->all();

        $this->generalCategories = collect(ResourceVideo::CATEGORIES)
            ->reject(fn (string $category): bool => $category === 'Recorded Lessons')
            ->values()
            ->all();
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
