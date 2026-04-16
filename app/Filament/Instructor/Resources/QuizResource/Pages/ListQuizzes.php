<?php

namespace App\Filament\Instructor\Resources\QuizResource\Pages;

use App\Filament\Actions\ImportQuizzesAction;
use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\QuizResource\QuizResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuizzes extends ListRecords
{
    use ScopedToInstructor;

    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportQuizzesAction::makeForCourses(static::instructorCourseIds()),
        ];
    }
}
