<?php

namespace App\Filament\Student\Pages;

use App\Filament\Concerns\BuildsSearchResults;
use Filament\Pages\Page;

class Search extends Page
{
    use BuildsSearchResults;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 6;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.student.pages.search';

    protected function searchSections(): array
    {
        return [
            'courses' => 'searchEnrolledCourses',
            'assignments' => 'searchVisibleAssignments',
            'materials' => 'searchVisibleMaterials',
            'assessments' => 'searchVisibleAssessments',
            'my_assignment_submissions' => 'searchMyAssignmentSubmissions',
            'my_assessment_submissions' => 'searchMyAssessmentSubmissions',
        ];
    }
}
