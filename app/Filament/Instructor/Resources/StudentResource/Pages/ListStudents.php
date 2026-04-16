<?php

namespace App\Filament\Instructor\Resources\StudentResource\Pages;

use App\Filament\Instructor\Resources\StudentResource\StudentResource;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;
}
