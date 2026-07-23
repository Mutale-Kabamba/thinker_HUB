<?php

namespace App\Filament\Instructor\Pages;

use App\Filament\Concerns\BuildsSearchResults;
use Filament\Pages\Page;

class Search extends Page
{
    use BuildsSearchResults;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 9;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.instructor.pages.search';

    protected function searchSections(): array
    {
        return [
            'courses' => 'searchInstructorCourses',
            'students' => 'searchInstructorStudents',
            'sessions' => 'searchInstructorSessions',
        ];
    }
}
