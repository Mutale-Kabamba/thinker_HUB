<?php

namespace App\Livewire;

use App\Models\CourseGamificationRule;
use App\Models\LearningMaterial;
use App\Models\Lesson;
use App\Models\ResourceVideo;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class VideoPlayer extends Component
{
    public ?Lesson $lesson = null;

    public ?LearningMaterial $material = null;

    public ?ResourceVideo $video = null;

    public string $title = '';

    public ?string $courseTitle = null;

    public ?string $youtubeId = null;

    public ?string $fileUrl = null;

    public bool $pointsEarned = false;

    public int $initialDuration = 0;

    public int $baseXp = 10;

    public int $baseCoins = 3;

    public function mount($lesson = null, $material = null, $video = null): void
    {
        if ($lesson instanceof Lesson || (is_numeric($lesson) && $lesson > 0)) {
            $this->lesson = $lesson instanceof Lesson ? $lesson : Lesson::query()->findOrFail($lesson);
            $this->title = $this->lesson->title;
            $this->courseTitle = $this->lesson->course?->title;
            $this->youtubeId = $this->lesson->youtube_id;
            $this->initialDuration = (int) ($this->lesson->duration_seconds ?? 0);
        } elseif ($material instanceof LearningMaterial || (is_numeric($material) && $material > 0)) {
            $this->material = $material instanceof LearningMaterial ? $material : LearningMaterial::query()->findOrFail($material);
            $this->title = $this->material->title;
            $this->courseTitle = $this->material->course?->title;
            $this->youtubeId = Lesson::extractYoutubeId($this->material->video_url);
            $this->fileUrl = $this->material->file_path ? route('file.view', ['type' => 'material', 'id' => $this->material->id], false) : null;
        } elseif ($video instanceof ResourceVideo || (is_numeric($video) && $video > 0)) {
            $this->video = $video instanceof ResourceVideo ? $video : ResourceVideo::query()->findOrFail($video);
            $this->title = $this->video->title;
            $this->courseTitle = $this->video->course?->title;
            $this->youtubeId = $this->video->youtube_id;
            $this->fileUrl = $this->video->playableLocalVideo()?->url();
        }

        $subject = $this->getSubjectModel();
        $course = $subject?->course ?? null;
        $rule = CourseGamificationRule::getRuleForCourse($course, 'video_completed');
        $this->baseXp = $rule['enabled'] ? $rule['xp'] : 10;
        $this->baseCoins = $rule['enabled'] ? $rule['coins'] : 3;

        $user = Auth::user();
        if ($user && $subject) {
            $this->pointsEarned = XpTransaction::query()
                ->where('user_id', $user->id)
                ->where(function ($query) use ($subject) {
                    $query->where(function ($q) use ($subject) {
                        $q->where('subject_type', get_class($subject))
                            ->where('subject_id', $subject->getKey());
                    })->orWhere(function ($q) use ($subject) {
                        $q->whereIn('source', ['lesson_video_completed', 'video_watched'])
                            ->where('source_id', (string) $subject->getKey());
                    });
                })
                ->exists();
        }
    }

    public function getSubjectModel(): ?Model
    {
        return $this->lesson ?? $this->material ?? $this->video;
    }

    /**
     * Handle client video watch progress claim and award points if 80%+ threshold is met.
     *
     * @param  array{actualSecondsWatched?: float|int, duration?: float|int, currentTime?: float|int}  $payload
     * @param  GamificationService  $gamificationService
     * @return array<string, mixed>
     */
    public function awardVideoCompletionPoints(array $payload, GamificationService $gamificationService): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'status' => 'unauthenticated',
                'message' => 'Please log in to earn video completion points.',
            ];
        }

        if ($this->pointsEarned) {
            return [
                'status' => 'already_claimed',
                'message' => 'Points have already been claimed for this video.',
            ];
        }

        $subject = $this->getSubjectModel();
        if (! $subject) {
            return [
                'status' => 'not_found',
                'message' => 'Video resource not found.',
            ];
        }

        $actualSecondsWatched = (float) ($payload['actualSecondsWatched'] ?? 0);
        $totalDuration = (float) ($payload['duration'] ?? $this->initialDuration ?: 0);
        $currentTime = (float) ($payload['currentTime'] ?? 0);

        // Server-Side Anti-Scrubbing Safeguard:
        if ($totalDuration <= 0) {
            return [
                'status' => 'invalid_duration',
                'message' => 'Invalid video duration.',
            ];
        }

        $watchRatio = $actualSecondsWatched / $totalDuration;

        // Verify that student actively watched at least 80%
        if ($watchRatio < 0.80) {
            return [
                'status' => 'threshold_not_met',
                'message' => 'You must actively watch at least 85% of the video duration to earn points.',
                'progress' => round($watchRatio * 100, 1),
            ];
        }

        // Check that playback position is also at or beyond 80%
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
            description: "Completed video lesson: {$this->title}"
        );

        if ($awarded) {
            $this->pointsEarned = true;

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

        $this->pointsEarned = true;

        return [
            'status' => 'already_claimed',
            'message' => 'Points already awarded.',
        ];
    }

    #[Layout('layouts.guest')]
    public function render(): View
    {
        return view('livewire.video-player');
    }
}
