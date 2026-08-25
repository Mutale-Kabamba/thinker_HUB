<?php

namespace App\Livewire;

use App\Models\LearningMaterial;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MaterialReader extends Component
{
    public LearningMaterial $material;

    public bool $pointsEarned = false;

    public ?string $fileUrl = null;

    public function mount(LearningMaterial $material): void
    {
        $this->material = $material;
        $this->fileUrl = route('file.view', ['type' => 'material', 'id' => $material->id], false);

        $user = Auth::user();
        if ($user) {
            $this->pointsEarned = XpTransaction::query()
                ->where('user_id', $user->id)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('subject_type', LearningMaterial::class)
                            ->where('subject_id', $this->material->id);
                    })->orWhere(function ($q) {
                        $q->where('source', 'material_read')
                            ->where('source_id', (string) $this->material->id);
                    });
                })
                ->exists();
        }
    }

    /**
     * Handle active reading engagement reward claim.
     *
     * @param  array{activeSeconds?: int|float}  $payload
     * @param  GamificationService  $gamificationService
     * @return array<string, mixed>
     */
    public function awardReadingPoints(array $payload, GamificationService $gamificationService): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'status' => 'unauthenticated',
                'message' => 'Please sign in to earn reading points.',
            ];
        }

        if ($this->pointsEarned) {
            return [
                'status' => 'already_claimed',
                'message' => 'Reading points have already been claimed for this material.',
            ];
        }

        $activeSeconds = (int) ($payload['activeSeconds'] ?? 0);

        // Server-Side Active Time Validation:
        // Student must have actively read for at least 170 seconds (10s buffer on 180s requirement)
        if ($activeSeconds < 170) {
            return [
                'status' => 'threshold_not_met',
                'message' => 'You must actively read for at least 3 minutes (180s) to earn points.',
                'active_seconds' => $activeSeconds,
            ];
        }

        $course = $this->material?->course;
        $rule = \App\Models\CourseGamificationRule::getRuleForCourse($course, 'material_read');
        $baseXp = $rule['enabled'] ? $rule['xp'] : 5;
        $baseCoins = $rule['enabled'] ? $rule['coins'] : 2;

        $awarded = $gamificationService->awardPoints(
            user: $user,
            activityType: 'material_read',
            subject: $this->material,
            baseXp: $baseXp,
            baseCoins: $baseCoins,
            description: "Read learning material: {$this->material->title}"
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
                    ->title('Reading Points Claimed!')
                    ->body("You earned +{$baseXp} XP and +{$baseCoins} Thinker Coins for active reading.")
                    ->success()
                    ->send();
            } catch (\Throwable) {
                // Ignore if not in Filament context
            }

            return [
                'status' => 'success',
                'xp' => $baseXp,
                'coins' => $baseCoins,
                'message' => "Points Claimed! +{$baseXp} XP and +{$baseCoins} TC awarded.",
            ];
        }

        $this->pointsEarned = true;

        return [
            'status' => 'already_claimed',
            'message' => 'Points already awarded.',
        ];
    }

    #[Layout('layouts.reader')]
    public function render(): View
    {
        return view('livewire.material-reader');
    }
}
