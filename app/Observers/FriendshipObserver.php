<?php

namespace App\Observers;

use App\Models\Friendship;
use App\Services\GamificationService;

class FriendshipObserver
{
    public function created(Friendship $friendship): void
    {
        $this->handle($friendship);
    }

    public function updated(Friendship $friendship): void
    {
        $this->handle($friendship);
    }

    private function handle(Friendship $friendship): void
    {
        try {
            if ($friendship->status === 'accepted') {
                $gamification = app(GamificationService::class);

                if ($friendship->requester) {
                    $gamification->awardFriendship($friendship->requester, $friendship);
                }

                if ($friendship->recipient) {
                    $gamification->awardFriendship($friendship->recipient, $friendship);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
