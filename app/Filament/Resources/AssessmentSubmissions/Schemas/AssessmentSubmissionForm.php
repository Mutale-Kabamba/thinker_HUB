<?php

namespace App\Filament\Resources\AssessmentSubmissions\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class AssessmentSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Submission Details')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->description('Student submission information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('assessment.id')
                                            ->label('Assessment')
                                            ->content(fn ($record): string => ($record?->assessment?->name ?? '') . ' (#' . (string) ($record?->assessment?->id ?? '-') . ')'),
                                        Placeholder::make('assessment_course')
                                            ->label('Course')
                                            ->content(fn ($record): string => (string) ($record?->assessment?->course?->title ?? '-')),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('user.name')
                                            ->label('Student')
                                            ->content(fn ($record): string => (string) ($record?->user?->name ?? '-')),
                                        Placeholder::make('submitted_at')
                                            ->label('Submitted At')
                                            ->content(fn ($record): HtmlString|string => $record?->submitted_at
                                                ? new HtmlString('<span style="color:#059669;font-weight:500;">' . e($record->submitted_at->format('M d, Y \a\t h:i A')) . '</span>')
                                                : new HtmlString('<span style="color:#dc2626;">Not submitted</span>')),
                                    ]),
                                Textarea::make('content')
                                    ->label('Written Response')
                                    ->rows(5)
                                    ->disabled()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),

                        Section::make('Attachments')
                            ->icon(Heroicon::OutlinedPaperClip)
                            ->description('Files and links submitted by the student')
                            ->schema([
                                Placeholder::make('submission_file')
                                    ->label('Uploaded File')
                                    ->content(fn ($record): HtmlString => $record?->file_path
                                        ? new HtmlString(
                                            '<div style="display:flex;align-items:center;gap:0.5rem;">'
                                            . '<span style="font-size:1.25rem;">📄</span>'
                                            . '<a href="/storage/' . e($record->file_path) . '" target="_blank" style="color:#0e7490;text-decoration:underline;font-weight:500;">'
                                            . e(basename($record->file_path))
                                            . '</a></div>'
                                        )
                                        : new HtmlString('<span style="color:#9ca3af;">No file uploaded</span>')),
                                Placeholder::make('submission_link')
                                    ->label('Link')
                                    ->content(fn ($record): HtmlString => $record?->link
                                        ? new HtmlString(
                                            '<div style="display:flex;align-items:center;gap:0.5rem;">'
                                            . '<span style="font-size:1.25rem;">🔗</span>'
                                            . '<a href="' . e($record->link) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;font-weight:500;">'
                                            . e(\Illuminate\Support\Str::limit($record->link, 50))
                                            . '</a></div>'
                                        )
                                        : new HtmlString('<span style="color:#9ca3af;">No link provided</span>')),
                                Placeholder::make('submission_video')
                                    ->label('Video URL')
                                    ->content(fn ($record): HtmlString => $record?->video_url
                                        ? new HtmlString(
                                            '<div style="display:flex;align-items:center;gap:0.5rem;">'
                                            . '<span style="font-size:1.25rem;">🎬</span>'
                                            . '<a href="' . e($record->video_url) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;font-weight:500;">'
                                            . e(\Illuminate\Support\Str::limit($record->video_url, 50))
                                            . '</a></div>'
                                        )
                                        : new HtmlString('<span style="color:#9ca3af;">No video provided</span>')),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make('Grading')
                    ->icon(Heroicon::OutlinedAcademicCap)
                    ->description('Update the submission status, score, and provide feedback')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'Submitted' => 'Submitted',
                                        'Graded' => 'Graded',
                                        'Checked' => 'Checked',
                                        'Returned' => 'Returned',
                                    ])
                                    ->required(),
                                TextInput::make('score')
                                    ->label('Score')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('/ 100'),
                                Placeholder::make('current_status_display')
                                    ->label('Current Status')
                                    ->content(fn ($record): HtmlString => new HtmlString(
                                        '<span style="display:inline-flex;align-items:center;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.875rem;font-weight:600;'
                                        . match ($record?->status) {
                                            'Graded' => 'background:#d1fae5;color:#065f46;',
                                            'Checked' => 'background:#dbeafe;color:#1e40af;',
                                            'Returned' => 'background:#fef3c7;color:#92400e;',
                                            default => 'background:#f3f4f6;color:#374151;',
                                        }
                                        . '">' . e($record?->status ?? 'Pending') . '</span>'
                                    )),
                            ]),
                        Textarea::make('feedback')
                            ->label('Feedback')
                            ->placeholder('Provide constructive feedback for the student...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
