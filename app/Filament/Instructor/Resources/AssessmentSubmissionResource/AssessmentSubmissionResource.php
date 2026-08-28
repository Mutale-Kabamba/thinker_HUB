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
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
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

    protected static ?int $navigationSort = 7;

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
                Grid::make(1)
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
                                        ->content(fn ($record): string => (string) ($record?->submitted_at?->toDayDateTimeString() ?? '-')),
                                ]),
                            ]),
                        Section::make('Submission Content')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                Placeholder::make('notes')
                                    ->label('Student Notes / Text')
                                    ->content(fn ($record): string => (string) ($record?->notes ?? 'None provided.')),
                                Placeholder::make('link')
                                    ->label('External Project Link')
                                    ->content(fn ($record): HtmlString => new HtmlString(
                                        $record?->link
                                            ? '<a href="' . e($record->link) . '" target="_blank" class="text-teal-600 dark:text-teal-400 underline font-medium break-all">' . e($record->link) . ' &rarr;</a>'
                                            : '<span class="text-gray-400">None provided</span>'
                                    )),
                                Placeholder::make('video_url')
                                    ->label('Loom / Video Walkthrough')
                                    ->content(fn ($record): HtmlString => new HtmlString(
                                        $record?->video_url
                                            ? '<a href="' . e($record->video_url) . '" target="_blank" class="text-teal-600 dark:text-teal-400 underline font-medium break-all">' . e($record->video_url) . ' &rarr;</a>'
                                            : '<span class="text-gray-400">None provided</span>'
                                    )),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Grading & Evaluation')
                    ->icon(Heroicon::OutlinedCheckBadge)
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
            ->contentGrid([
                'default' => 1,
                'md' => null,
            ])
            ->columns([
                // Mobile Card View Structure (Stacked & Clean)
                Stack::make([
                    Split::make([
                        Stack::make([
                            TextColumn::make('assessment.name')
                                ->label('Assessment')
                                ->weight('bold')
                                ->size('sm')
                                ->placeholder(fn ($record) => 'Assessment #' . $record->assessment_id)
                                ->searchable(),
                            TextColumn::make('user.name')
                                ->label('Student')
                                ->size('xs')
                                ->color('gray')
                                ->searchable(),
                        ]),
                        TextColumn::make('status')
                            ->badge()
                            ->grow(false),
                    ]),
                    Split::make([
                        TextColumn::make('score')
                            ->formatStateUsing(fn ($state) => $state !== null ? "Score: {$state}%" : 'Ungraded')
                            ->badge()
                            ->color(fn ($state) => $state !== null ? 'success' : 'gray')
                            ->size('xs'),
                        TextColumn::make('submitted_at')
                            ->dateTime('M j, Y')
                            ->size('xs')
                            ->color('gray'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('assessment.name')
                    ->label('Assessment')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('assessment.course.title')
                    ->label('Course')
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('view_status')
                    ->label('Read')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record->viewed_at !== null || in_array($record->status, ['Graded', 'Checked']) ? 'Viewed' : 'New')
                    ->color(fn (string $state): string => $state === 'New' ? 'warning' : 'gray')
                    ->visibleFrom('md'),
                TextColumn::make('is_retake')
                    ->label('Attempt')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->is_retake ? '2nd Try' : ($record->retake_allowed ? '2nd Try Open' : '1st Try'))
                    ->color(fn ($record) => $record->is_retake ? 'info' : ($record->retake_allowed ? 'success' : 'gray'))
                    ->visibleFrom('md'),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('md'),
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
                \Filament\Actions\ActionGroup::make([
                    EditAction::make()->icon('heroicon-m-pencil-square'),
                    \Filament\Actions\Action::make('download')
                        ->label('Download')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('primary')
                        ->action(function ($record) {
                            $service = app(\App\Services\SubmissionZipService::class);

                            return $service->downloadSingleAssessmentSubmission($record);
                        }),
                    \Filament\Actions\Action::make('markViewed')
                        ->label('Mark Viewed')
                        ->icon('heroicon-m-eye')
                        ->color('gray')
                        ->visible(fn ($record): bool => $record->viewed_at === null && ! in_array($record->status, ['Graded', 'Checked']))
                        ->action(function ($record): void {
                            $record->markAsViewed();
                            \Filament\Notifications\Notification::make()->title('Marked as viewed.')->success()->send();
                        }),
                    \Filament\Actions\Action::make('grantRetake')
                        ->label('Grant 2nd Try')
                        ->icon('heroicon-m-arrow-path')
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
                        ->icon('heroicon-m-x-mark')
                        ->color('danger')
                        ->visible(fn ($record) => (bool) $record->retake_allowed)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->revokeRetake();
                            \Filament\Notifications\Notification::make()->title('Second chance revoked.')->info()->send();
                        }),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
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
