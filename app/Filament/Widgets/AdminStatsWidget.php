<?php

namespace App\Filament\Widgets;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Widgets\Widget;

class AdminStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-stats';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'registeredStudents' => User::query()->where('role', 'student')->count(),
            'assignedAssessments' => Assessment::query()->count(),
            'publishedAssignments' => Assignment::query()->count(),
            'materials' => LearningMaterial::query()->count(),
        ];
    }
}
