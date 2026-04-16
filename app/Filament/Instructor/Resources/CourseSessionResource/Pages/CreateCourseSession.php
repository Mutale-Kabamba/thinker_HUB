<?php

namespace App\Filament\Instructor\Resources\CourseSessionResource\Pages;

use App\Filament\Instructor\Resources\CourseSessionResource\CourseSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseSession extends CreateRecord
{
    protected static string $resource = CourseSessionResource::class;
}
