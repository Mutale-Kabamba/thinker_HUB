<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\BuildsSearchResults;
use Filament\Pages\Page;

class Search extends Page
{
    use BuildsSearchResults;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 90;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.search';

    protected function searchSections(): array
    {
        return [
            'students' => 'searchStudents',
            'courses' => 'searchCourses',
            'assignments' => 'searchAssignments',
            'assessments' => 'searchAssessments',
            'materials' => 'searchMaterials',
            'enrollments' => 'searchEnrollments',
            'assignment_submissions' => 'searchAssignmentSubmissions',
            'assessment_submissions' => 'searchAssessmentSubmissions',
        ];
    }
}
