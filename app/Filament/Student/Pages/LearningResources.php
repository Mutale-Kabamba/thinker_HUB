<?php

namespace App\Filament\Student\Pages;

use App\Models\Bookmark;
use App\Models\CourseGamificationRule;
use App\Models\LearningMaterial;
use App\Models\Lesson;
use App\Models\ResourceVideo;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class LearningResources extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'LEARNING';

    protected static ?string $navigationLabel = 'Learning Resources';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        try {
            $newCount = ResourceVideo::query()
                ->where('created_at', '>=', now()->subDays(3))
                ->count();

            return $newCount > 0 ? '●' : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New video resources available';
    }

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

    // Active in-app player state (Livewire-driven so comments and tracking can attach).
    public bool $showPlayer = false;

    public ?string $playerSource = null; // 'youtube' | 'file' | 'processing'

    public ?string $playerUrl = null;

    public ?string $playerTitle = null;

    public ?string $commentType = null; // 'video' | 'lesson'

    public ?int $commentId = null;

    public ?string $activeVideoType = null; // 'video' | 'lesson'

    public ?int $activeVideoId = null;

    public ?string $activeYoutubeId = null;

    public ?string $activeFileUrl = null;

    public bool $activePointsEarned = false;

    public int $activeXp = 10;

    public int $activeCoins = 3;

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
        $this->activeYoutubeId = null;
        $this->activeFileUrl = null;

        if ($local) {
            $this->playerSource = 'file';
            $this->playerUrl = $local->url();
            $this->activeFileUrl = $local->url();
        } elseif ($video->hasLocalVideo()) {
            // Upload exists but is pending/processing/failed — not playable yet.
            $this->playerSource = 'processing';
            $this->playerUrl = null;
        } elseif ($video->embed_url) {
            $this->playerSource = 'youtube';
            $this->playerUrl = $video->embed_url.'?autoplay=1&rel=0';
            $this->activeYoutubeId = $video->youtube_id;
        } else {
            return;
        }

        $this->playerTitle = $video->title;
        $this->commentType = 'video';
        $this->commentId = $video->id;
        $this->activeVideoType = 'video';
        $this->activeVideoId = $video->id;

        $rule = CourseGamificationRule::getRuleForCourse($video->course, 'video_completed');
        $this->activeXp = $rule['enabled'] ? $rule['xp'] : 10;
        $this->activeCoins = $rule['enabled'] ? $rule['coins'] : 3;
        $this->activePointsEarned = $this->checkPointsEarned(ResourceVideo::class, $video->id);

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
        $this->activeYoutubeId = null;
        $this->activeFileUrl = null;

        if ($embed) {
            $this->playerSource = 'youtube';
            $this->playerUrl = $embed.'?autoplay=1&rel=0';
            $this->activeYoutubeId = Lesson::extractYoutubeId($lesson->video_url);
        } elseif ($lesson->file_path) {
            $this->playerSource = 'file';
            $this->playerUrl = route('file.view', ['type' => 'material', 'id' => $lesson->id], false);
            $this->activeFileUrl = $this->playerUrl;
        } else {
            return;
        }

        $this->playerTitle = $lesson->title;
        $this->commentType = 'lesson';
        $this->commentId = $lesson->id;
        $this->activeVideoType = 'lesson';
        $this->activeVideoId = $lesson->id;

        $rule = CourseGamificationRule::getRuleForCourse($lesson->course, 'video_completed');
        $this->activeXp = $rule['enabled'] ? $rule['xp'] : 10;
        $this->activeCoins = $rule['enabled'] ? $rule['coins'] : 3;
        $this->activePointsEarned = $this->checkPointsEarned(LearningMaterial::class, $lesson->id);

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
        $this->activeVideoType = null;
        $this->activeVideoId = null;
        $this->activeYoutubeId = null;
        $this->activeFileUrl = null;
        $this->loadVideos();
    }

    public function awardVideoCompletionPoints(array $payload, GamificationService $gamificationService): array
    {
        $user = auth()->user();
        if (! $user) {
            return [
                'status' => 'unauthenticated',
                'message' => 'Please log in to earn video completion points.',
            ];
        }

        if ($this->activePointsEarned) {
            return [
                'status' => 'already_claimed',
                'message' => 'Points have already been claimed for this video.',
            ];
        }

        $subject = match ($this->activeVideoType) {
            'video' => ResourceVideo::query()->find($this->activeVideoId),
            'lesson' => LearningMaterial::query()->find($this->activeVideoId),
            default => null,
        };

        if (! $subject) {
            return [
                'status' => 'not_found',
                'message' => 'Video resource not found.',
            ];
        }

        $actualSecondsWatched = (float) ($payload['actualSecondsWatched'] ?? 0);
        $totalDuration = (float) ($payload['duration'] ?? 0);
        $currentTime = (float) ($payload['currentTime'] ?? 0);

        if ($totalDuration <= 0) {
            return [
                'status' => 'invalid_duration',
                'message' => 'Invalid video duration.',
            ];
        }

        $watchRatio = $actualSecondsWatched / $totalDuration;

        if ($watchRatio < 0.80) {
            return [
                'status' => 'threshold_not_met',
                'message' => 'You must actively watch at least 85% of the video duration to earn points.',
                'progress' => round($watchRatio * 100, 1),
            ];
        }

        if ($currentTime > 0 && ($currentTime / $totalDuration) < 0.80) {
            return [
                'status' => 'scrubbing_detected',
                'message' => 'Playback position does not match watch progress.',
            ];
        }

        $course = $subject->course ?? null;
        $rule = CourseGamificationRule::getRuleForCourse($course, 'video_completed');
        $baseXp = $rule['enabled'] ? $rule['xp'] : 10;
        $baseCoins = $rule['enabled'] ? $rule['coins'] : 3;

        $awarded = $gamificationService->awardPoints(
            user: $user,
            activityType: 'lesson_video_completed',
            subject: $subject,
            baseXp: $baseXp,
            baseCoins: $baseCoins,
            description: "Completed video lesson: {$subject->title}"
        );

        if ($awarded) {
            $this->activePointsEarned = true;

            $this->dispatch('points-awarded', [
                'xp' => $baseXp,
                'coins' => $baseCoins,
                'message' => "+{$baseXp} XP and +{$baseCoins} Thinker Coins (TC) earned!",
            ]);

            try {
                Notification::make()
                    ->title('Points Claimed!')
                    ->body("You earned +{$baseXp} XP and +{$baseCoins} Thinker Coins for completing this video.")
                    ->success()
                    ->send();
            } catch (\Throwable) {
                // Ignore if outside Filament context
            }

            return [
                'status' => 'success',
                'xp' => $baseXp,
                'coins' => $baseCoins,
                'message' => "Congratulations! +{$baseXp} XP and +{$baseCoins} TC awarded.",
            ];
        }

        $this->activePointsEarned = true;

        return [
            'status' => 'already_claimed',
            'message' => 'Points already awarded.',
        ];
    }

    protected function checkPointsEarned(string $subjectType, int $subjectId): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return XpTransaction::query()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($subjectType, $subjectId) {
                $query->where(function ($q) use ($subjectType, $subjectId) {
                    $q->where('subject_type', $subjectType)
                        ->where('subject_id', $subjectId);
                })->orWhere(function ($q) use ($subjectId) {
                    $q->whereIn('source', ['lesson_video_completed', 'video_watched'])
                        ->where('source_id', (string) $subjectId);
                });
            })
            ->exists();
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

        $earnedTransactions = XpTransaction::query()
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereIn('activity_type', ['lesson_video_completed', 'video_watched'])
                    ->orWhereIn('source', ['lesson_video_completed', 'video_watched']);
            })
            ->get(['subject_type', 'subject_id', 'source_id']);

        $earnedKeys = $earnedTransactions
            ->map(fn ($t) => ($t->subject_type ? $t->subject_type.':'.$t->subject_id : 'id:'.$t->source_id))
            ->all();

        $isPointsEarned = fn (string $class, int $id): bool => in_array($class.':'.$id, $earnedKeys, true) || in_array('id:'.$id, $earnedKeys, true);

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
            ->map(function (LearningMaterial $item) use ($isBookmarked, $isPointsEarned): array {
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
                        ? route('file.view', ['type' => 'material', 'id' => $item->id], false)
                        : null,
                    'thumbnail' => $embed
                        ? $this->youtubeThumbnail($item->video_url)
                        : null,
                    'created_at' => $item->created_at?->format('M d, Y'),
                    'bookmarked' => $isBookmarked(LearningMaterial::class, $item->id),
                    'points_earned' => $isPointsEarned(LearningMaterial::class, $item->id),
                ];
            })
            ->values();

        // Recorded lessons from admin-managed videos, targeted by course + level + intake.
        $recordedVideoLessons = ResourceVideo::query()
            ->with('course')
            ->where('is_published', true)
            ->where('is_recorded_lesson', true)
            ->whereNotNull('course_id')
            ->whereIn('course_id', $user->courses()->pluck('courses.id'))
            ->where(function ($q) use ($user): void {
                $q->whereNull('target_level')->orWhere('target_level', $user->track);
            })
            ->where(function ($q) use ($user): void {
                $q->whereNull('course_intake_id')
                    ->orWhereIn('course_intake_id', function ($sub) use ($user) {
                        $sub->select('course_intake_id')
                            ->from('enrollments')
                            ->where('user_id', $user->id)
                            ->whereNotNull('course_intake_id');
                    });
            })
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(fn (ResourceVideo $video): array => $this->presentVideo($video) + [
                'course' => $video->course?->title ?? 'General',
                'record_type' => 'video',
                'bookmarked' => $isBookmarked(ResourceVideo::class, $video->id),
                'points_earned' => $isPointsEarned(ResourceVideo::class, $video->id),
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
                'points_earned' => $isPointsEarned(ResourceVideo::class, $video->id),
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
