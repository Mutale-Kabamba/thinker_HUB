<?php

namespace App\Filament\Resources\AssignmentSubmissions\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AssignmentSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('assignment.name')
                    ->label('Assignment')
                    ->content(fn ($record): string => (string) ($record?->assignment?->name ?? '-')),
                Placeholder::make('user.name')
                    ->label('Student')
                    ->content(fn ($record): string => (string) ($record?->user?->name ?? '-')),
                Placeholder::make('submitted_at')
                    ->label('Submitted At')
                    ->content(fn ($record): string => (string) ($record?->submitted_at?->format('Y-m-d H:i') ?? '-')),
                Textarea::make('content')
                    ->label('Submission Content')
                    ->rows(6)
                    ->disabled(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('grade')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Textarea::make('feedback')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
