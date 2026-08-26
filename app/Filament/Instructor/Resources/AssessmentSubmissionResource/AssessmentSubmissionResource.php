<?php

namespace App\Filament\Instructor\Resources\AssessmentSubmissionResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\AssessmentSubmissionResource\Pages\EditAssessmentSubmission;
use App\Filament\Instructor\Resources\AssessmentSubmissionResource\Pages\ListAssessmentSubmissions;
use App\Models\AssessmentSubmission;
use App\Notifications\SubmissionGradedNotification;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class AssessmentSubmissionResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = AssessmentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'GRADING & EVALUATIONS';

    protected static ?string $navigationLabel = 'Assessment Submissions';

    protected static ?int $navigationSort = 9;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        try {
            $count = static::getEloquentQuery()
                ->whereNull('viewed_at')
                ->whereNotIn('status', ['Graded', 'Checked'])
                ->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Unviewed assessment submissions';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Submission Details')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                Grid::make(2)->schema([
                                    Placeholder::make('assessment.name')
                                        ->label('Assessment')
                                        ->content(fn ($record): string => (string) ($record?->assessment?->name ?? '-')),
                                    Placeholder::make('assessment_course')
                                        ->label('Course')
                                        ->content(fn ($record): string => (string) ($record?->assessment?->course?->title ?? '-')),
                                ]),
                                Grid::make(2)->schema([
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
                            ->schema([
                                Placeholder::make('submission_file')
                                    ->label('Uploaded File(s)')
                                    ->content(function ($record): HtmlString {
                                        $paths = $record?->all_file_paths ?? [];
                                        if (empty($paths)) {
                                            return new HtmlString('<span style="color:#9ca3af;">No file uploaded</span>');
                                        }

                                        $html = '<div style="display:flex;flex-direction:column;gap:0.4rem;">';
                                        foreach ($paths as $idx => $filePath) {
                                            $html .= '<a href="' . e(route('file.view', ['type' => 'assessment-submission', 'id' => $record->id, 'index' => $idx])) . '" target="_blank" style="color:#0e7490;text-decoration:underline;display:inline-flex;align-items:center;gap:0.3rem;"><svg style="width:1rem;height:1rem;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> ' . e(basename($filePath)) . '</a>';
                                        }
                                        $html .= '</div>';

                                        return new HtmlString($html);
                                    }),
                                Placeholder::make('submission_link')
                                    ->label('Link')
                                    ->content(fn ($record): HtmlString => $record?->link
                                        ? new HtmlString('<a href="' . e($record->link) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;display:inline-flex;align-items:center;gap:0.3rem;"><svg style="width:1rem;height:1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg> ' . e(\Illuminate\Support\Str::limit($record->link, 50)) . '</a>')
                                        : new HtmlString('<span style="color:#9ca3af;">No link provided</span>')),
                                Placeholder::make('submission_video')
                                    ->label('Video URL')
                                    ->content(fn ($record): HtmlString => $record?->video_url
                                        ? new HtmlString('<a href="' . e($record->video_url) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;display:inline-flex;align-items:center;gap:0.3rem;"><svg style="width:1rem;height:1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg> ' . e(\Illuminate\Support\Str::limit($record->video_url, 50)) . '</a>')
                                        : new HtmlString('<span style="color:#9ca3af;">No video provided</span>')),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make('Grading')
                    ->icon(Heroicon::OutlinedAcademicCap)
                    ->description('Update the submission status, score, and provide feedback')
                    ->schema([
                        Grid::make(2)->schema([
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
                        ]),
                        Textarea::make('feedback')
                            ->label('Feedback')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assessment.name')
                    ->label('Assessment')
                    ->searchable(),
                TextColumn::make('assessment.course.title')
                    ->label('Course')
                    ->placeholder('Unassigned')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('view_status')
                    ->label('Read')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record->viewed_at !== null || in_array($record->status, ['Graded', 'Checked']) ? 'Viewed' : 'New')
                    ->color(fn (string $state): string => $state === 'New' ? 'warning' : 'gray'),
                TextColumn::make('is_retake')
                    ->label('Attempt')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->is_retake ? '2nd Try' : ($record->retake_allowed ? '2nd Try Open' : '1st Try'))
                    ->color(fn ($record) => $record->is_retake ? 'info' : ($record->retake_allowed ? 'success' : 'gray')),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'assessment',
                    fn (Builder $q) => $q->whereIn('course_id', static::instructorCourseIds())
                )
            )
            ->filters([
                SelectFilter::make('view_status')
                    ->label('Read Status')
                    ->options([
                        'unviewed' => 'New / Unviewed',
                        'viewed' => 'Viewed',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['value'] ?? null) === 'unviewed') {
                            return $query->whereNull('viewed_at')->whereNotIn('status', ['Graded', 'Checked']);
                        }
                        if (($data['value'] ?? null) === 'viewed') {
                            return $query->where(fn (Builder $q) => $q->whereNotNull('viewed_at')->orWhereIn('status', ['Graded', 'Checked']));
                        }
                        return $query;
                    }),
                SelectFilter::make('status')
                    ->options([
                        'Submitted' => 'Submitted',
                        'Graded' => 'Graded',
                        'Checked' => 'Checked',
                        'Reviewed' => 'Reviewed',
                        'Returned' => 'Returned',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($record) {
                        $service = app(\App\Services\SubmissionZipService::class);

                        return $service->downloadSingleAssessmentSubmission($record);
                    }),
                \Filament\Actions\Action::make('markViewed')
                    ->label('Mark Viewed')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn ($record): bool => $record->viewed_at === null && ! in_array($record->status, ['Graded', 'Checked']))
                    ->action(function ($record): void {
                        $record->markAsViewed();
                        \Filament\Notifications\Notification::make()->title('Marked as viewed.')->success()->send();
                    }),
                \Filament\Actions\Action::make('grantRetake')
                    ->label('Grant 2nd Try')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->retake_allowed)
                    ->requiresConfirmation()
                    ->modalHeading('Grant Second Chance')
                    ->modalDescription('Allow the student to submit a second attempt. Any recorded score above 50% will be capped at the 50% passing mark.')
                    ->action(function ($record) {
                        $record->grantRetake(auth()->user());
                        \Filament\Notifications\Notification::make()->title('Second chance granted.')->success()->send();
                    }),
                \Filament\Actions\Action::make('revokeRetake')
                    ->label('Revoke 2nd Try')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => (bool) $record->retake_allowed)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->revokeRetake();
                        \Filament\Notifications\Notification::make()->title('Second chance revoked.')->info()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadZip')
                        ->label('Download Selected (ZIP)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->action(function (Collection $records) {
                            $service = app(\App\Services\SubmissionZipService::class);
                            $response = $service->downloadAssessmentsZip($records);

                            if (! $response) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No downloadable files found in selected submissions.')
                                    ->warning()
                                    ->send();

                                return null;
                            }

                            return $response;
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markViewed')
                        ->label('Mark as Viewed')
                        ->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each(fn ($record) => $record->markAsViewed()))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markGraded')
                        ->label('Mark Graded')
                        ->icon('heroicon-o-check-badge')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            $record->update(['status' => 'Graded', 'viewed_at' => $record->viewed_at ?? now()]);
                        }))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markGradedAndNotify')
                        ->label('Mark Graded + Notify')
                        ->icon('heroicon-o-bell-alert')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('message')
                                ->label('Custom message (optional)')
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $msg = trim((string) ($data['message'] ?? ''));
                            $records->each(function ($record) use ($msg): void {
                                $record->update(['status' => 'Graded', 'viewed_at' => $record->viewed_at ?? now()]);
                                try {
                                    $record->user?->notify(new SubmissionGradedNotification(
                                        'assessment',
                                        (string) $record->assessment?->name,
                                        $record->score,
                                        (string) ($msg !== '' ? $msg : ($record->feedback ?: 'Your assessment has been graded.')),
                                    ));
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markChecked')
                        ->label('Mark Checked')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            $record->update(['status' => 'Checked', 'viewed_at' => $record->viewed_at ?? now()]);
                        }))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReturned')
                        ->label('Mark Returned')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            $record->update(['status' => 'Returned', 'viewed_at' => $record->viewed_at ?? now()]);
                        }))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessmentSubmissions::route('/'),
            'edit' => EditAssessmentSubmission::route('/{record}/edit'),
        ];
    }
}
