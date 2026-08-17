<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VideoPlayer extends Component
{
    public Lesson $lesson;

    public bool $pointsEarned = false;

    public ?string $youtubeId = null;

    public function mount(Lesson $lesson): void
    {
        $this->lesson = $lesson;
        $this->youtubeId = $lesson->youtube_id;

        $user = Auth::user();
        if ($user) {
            $this->pointsEarned = XpTransaction::query()
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('subject_type', Lesson::class)
                            ->where('subject_id', $this->lesson->id);
                    })->orWhere(function ($q) {
                        $q->where('source', 'lesson_video_completed')
                            ->where('source_id', (string) $this->lesson->id);
                    });
                })
                ->exists();
        }
    }

    /**
     * Handle client video watch progress claim and award points if 85% threshold is met.
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
                'message' => 'Points have already been claimed for this video lesson.',
            ];
        }

        $actualSecondsWatched = (float) ($payload['actualSecondsWatched'] ?? 0);
        $totalDuration = (float) ($payload['duration'] ?? $this->lesson->duration_seconds ?? 0);
        $currentTime = (float) ($payload['currentTime'] ?? 0);

        // Server-Side Anti-Scrubbing Safeguard:
        if ($totalDuration <= 0) {
            return [
                'status' => 'invalid_duration',
                'message' => 'Invalid video duration.',
            ];
        }

        $watchRatio = $actualSecondsWatched / $totalDuration;

        // Verify that student actively watched at least 80% (threshold rule)
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

        $course = $this->lesson?->course;
        $rule = \App\Models\CourseGamificationRule::getRuleForCourse($course, 'video_completed');
        $baseXp = $rule['enabled'] ? $rule['xp'] : 10;
        $baseCoins = $rule['enabled'] ? $rule['coins'] : 5;

        $awarded = $gamificationService->awardPoints(
            user: $user,
            activityType: 'lesson_video_completed',
            subject: $this->lesson,
            baseXp: $baseXp,
            baseCoins: $baseCoins,
            description: "Completed video lesson: {$this->lesson->title}"
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
                    ->body("You earned +{$baseXp} XP and +{$baseCoins} Thinker Coins for completing this video lesson.")
                    ->success()
                    ->send();
            } catch (\Throwable) {
                // Ignore if not in Filament context
            }

            return [
                'status' => 'success',
                'xp' => 10,
                'coins' => 5,
                'message' => 'Congratulations! +10 XP and +5 TC awarded.',
            ];
        }

        $this->pointsEarned = true;

        return [
            'status' => 'already_claimed',
            'message' => 'Points already awarded.',
        ];
    }

    public function render(): View
    {
        return view('livewire.video-player');
    }
}
