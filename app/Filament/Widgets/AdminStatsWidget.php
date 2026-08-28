<?php

namespace App\Filament\Widgets;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Widgets\Widget;

class AdminStatsWidget extends Widget
{   
    protected static ?string $pollingInterval = null;

    protected string $view = 'filament.widgets.admin-stats';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $registeredStudents = User::query()->where('role', 'student')->count();
        $activeLearners = User::query()->where('role', 'student')->where('is_active', true)->count();
        $activeCourses = \App\Models\Course::query()->where('is_active', true)->count();
        $activeCohorts = \App\Models\CourseIntake::query()->where('is_active', true)->count();
        $assignedAssessments = Assessment::query()->count();
        $publishedAssignments = Assignment::query()->count();
        $materials = LearningMaterial::query()->count();

        return [
            'registeredStudents' => $registeredStudents,
            'activeLearners' => $activeLearners,
            'activeCourses' => $activeCourses,
            'activeCohorts' => $activeCohorts,
            'assignedAssessments' => $assignedAssessments,
            'publishedAssignments' => $publishedAssignments,
            'materials' => $materials,
            'coursesUrl' => \App\Filament\Resources\Courses\CourseResource::getUrl(),
            'studentsUrl' => \App\Filament\Resources\Students\StudentResource::getUrl(),
            'assessmentsUrl' => \App\Filament\Resources\Assessments\AssessmentResource::getUrl(),
            'assignmentsUrl' => \App\Filament\Resources\Assignments\AssignmentResource::getUrl(),
            'materialsUrl' => \App\Filament\Resources\LearningMaterials\LearningMaterialResource::getUrl(),
        ];
    }
}
