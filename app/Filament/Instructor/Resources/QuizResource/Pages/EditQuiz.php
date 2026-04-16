<?php

namespace App\Filament\Instructor\Resources\QuizResource\Pages;

use App\Filament\Instructor\Resources\QuizResource\QuizResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
