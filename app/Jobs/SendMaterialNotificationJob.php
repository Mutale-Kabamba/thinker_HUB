<?php

namespace App\Jobs;

use App\Models\LearningMaterial;
use App\Models\User;
use App\Notifications\MaterialPublishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendMaterialNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(
        public readonly LearningMaterial $material,
    ) {}

    public function handle(): void
    {
        $material = $this->material;

        if (! $material->exists) {
            return;
        }

        if ($material->scope === 'personal' && $material->target_user_id) {
            $target = User::query()->find($material->target_user_id);
            if ($target) {
                $this->notifyUser($target, $material);
            }

            return;
        }

        $users = User::query()->where(function ($query): void {
            $query->whereNull('role')->orWhere('role', 'student');
        });

        if ($material->course_id) {
            $users->whereHas('enrollments', fn ($query) => $query->where('course_id', $material->course_id));
        }

        if ($material->scope === 'level' && $material->target_track) {
            $users->where(function ($query) use ($material): void {
                $query->where('track', $material->target_track)->orWhereNull('track');
            });
        }

        $users->whereNotNull('email')
            ->where('email', '!=', '')
            ->chunkById(100, function ($recipients) use ($material): void {
                foreach ($recipients as $user) {
                    $this->notifyUser($user, $material);
                }
            });
    }

    private function notifyUser(User $user, LearningMaterial $material): void
    {
        // Prevent duplicate notifications
        $alreadyNotified = DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('type', MaterialPublishedNotification::class)
            ->where('data', 'like', '%"title":"New material available"%')
            ->where('data', 'like', '%' . addcslashes($material->title, '%_\\') . '%')
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        try {
            $user->notify(new MaterialPublishedNotification($material));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
