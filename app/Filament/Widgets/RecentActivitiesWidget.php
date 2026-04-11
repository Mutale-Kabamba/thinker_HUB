<?php

namespace App\Filament\Widgets;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Widgets\Widget;

class RecentActivitiesWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activities';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $activities = collect();

        User::query()->latest()->limit(5)->get()->each(function (User $user) use ($activities): void {
            $activities->push([
                'event' => 'Student Registered',
                'meta' => $user->name,
                'time' => $user->created_at,
            ]);
        });

        Assignment::query()->latest()->limit(5)->get()->each(function (Assignment $assignment) use ($activities): void {
            $activities->push([
                'event' => 'Assignment Published',
                'meta' => $assignment->name,
                'time' => $assignment->created_at,
            ]);
        });

        LearningMaterial::query()->latest()->limit(5)->get()->each(function (LearningMaterial $material) use ($activities): void {
            $activities->push([
                'event' => 'Material Uploaded',
                'meta' => $material->title,
                'time' => $material->created_at,
            ]);
        });

        Assessment::query()->latest()->limit(5)->get()->each(function (Assessment $assessment) use ($activities): void {
            $activities->push([
                'event' => 'Assessment Updated',
                'meta' => $assessment->name ?: 'Assessment #'.$assessment->id,
                'time' => $assessment->updated_at,
            ]);
        });

        return [
            'activities' => $activities
                ->sortByDesc('time')
                ->take(10)
                ->values(),
        ];
    }
}
