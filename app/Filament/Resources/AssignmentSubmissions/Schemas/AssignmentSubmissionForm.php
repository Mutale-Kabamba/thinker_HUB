<?php

namespace App\Filament\Resources\AssignmentSubmissions\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
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
                Placeholder::make('submission_file')
                    ->label('Uploaded File')
                    ->content(fn ($record): string => $record?->file_path ? '<a href="' . asset('storage/' . $record->file_path) . '" target="_blank" style="color:#0e7490;text-decoration:underline;">View / Download</a>' : 'None')
                    ->html(),
                Placeholder::make('submission_link')
                    ->label('Link')
                    ->content(fn ($record): string => $record?->link ? '<a href="' . e($record->link) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">' . e(\Illuminate\Support\Str::limit($record->link, 60)) . '</a>' : 'None')
                    ->html(),
                Placeholder::make('submission_video')
                    ->label('Video URL')
                    ->content(fn ($record): string => $record?->video_url ? '<a href="' . e($record->video_url) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">' . e(\Illuminate\Support\Str::limit($record->video_url, 60)) . '</a>' : 'None')
                    ->html(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Submitted' => 'Submitted',
                        'Graded' => 'Graded',
                        'Checked' => 'Checked',
                        'Returned' => 'Returned',
                    ])
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
