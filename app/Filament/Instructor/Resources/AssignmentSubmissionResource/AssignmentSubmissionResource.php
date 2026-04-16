<?php

namespace App\Filament\Instructor\Resources\AssignmentSubmissionResource;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Filament\Instructor\Resources\AssignmentSubmissionResource\Pages\EditAssignmentSubmission;
use App\Filament\Instructor\Resources\AssignmentSubmissionResource\Pages\ListAssignmentSubmissions;
use App\Models\AssignmentSubmission;
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

class AssignmentSubmissionResource extends Resource
{
    use ScopedToInstructor;

    protected static ?string $model = AssignmentSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $navigationLabel = 'Assignment Submissions';

    protected static ?int $navigationSort = 8;

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
                                    Placeholder::make('assignment.name')
                                        ->label('Assignment')
                                        ->content(fn ($record): string => (string) ($record?->assignment?->name ?? '-')),
                                    Placeholder::make('assignment_course')
                                        ->label('Course')
                                        ->content(fn ($record): string => (string) ($record?->assignment?->course?->title ?? '-')),
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
                                    ->label('Uploaded File')
                                    ->content(fn ($record): HtmlString => $record?->file_path
                                        ? new HtmlString('<a href="/storage/' . e($record->file_path) . '" target="_blank" style="color:#0e7490;text-decoration:underline;">📄 ' . e(basename($record->file_path)) . '</a>')
                                        : new HtmlString('<span style="color:#9ca3af;">No file uploaded</span>')),
                                Placeholder::make('submission_link')
                                    ->label('Link')
                                    ->content(fn ($record): HtmlString => $record?->link
                                        ? new HtmlString('<a href="' . e($record->link) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">🔗 ' . e(\Illuminate\Support\Str::limit($record->link, 50)) . '</a>')
                                        : new HtmlString('<span style="color:#9ca3af;">No link provided</span>')),
                                Placeholder::make('submission_video')
                                    ->label('Video URL')
                                    ->content(fn ($record): HtmlString => $record?->video_url
                                        ? new HtmlString('<a href="' . e($record->video_url) . '" target="_blank" rel="noopener" style="color:#0e7490;text-decoration:underline;">🎬 ' . e(\Illuminate\Support\Str::limit($record->video_url, 50)) . '</a>')
                                        : new HtmlString('<span style="color:#9ca3af;">No video provided</span>')),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make('Grading')
                    ->icon(Heroicon::OutlinedAcademicCap)
                    ->description('Update the submission status, grade, and provide feedback')
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
                            TextInput::make('grade')
                                ->label('Grade')
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
                TextColumn::make('assignment.name')
                    ->label('Assignment')
                    ->searchable(),
                TextColumn::make('assignment.course.title')
                    ->label('Course')
                    ->placeholder('Unassigned')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('due_indicator')
                    ->label('Due')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        $dueDate = $record->assignment?->due_date;
                        if (! $dueDate) {
                            return 'No due date';
                        }
                        return $dueDate->isPast()
                            ? 'Overdue: ' . $dueDate->format('Y-m-d')
                            : 'Upcoming: ' . $dueDate->format('Y-m-d');
                    })
                    ->color(fn (string $state): string => str_starts_with($state, 'Overdue') ? 'danger' : (str_starts_with($state, 'Upcoming') ? 'warning' : 'gray')),
                TextColumn::make('grade')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'assignment',
                    fn (Builder $q) => $q->whereIn('course_id', static::instructorCourseIds())
                )
            )
            ->filters([
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markGraded')
                        ->label('Mark Graded')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'Graded']))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markGradedAndNotify')
                        ->label('Mark Graded + Notify')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('message')
                                ->label('Custom message (optional)')
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $msg = trim((string) ($data['message'] ?? ''));
                            $records->each(function ($record) use ($msg): void {
                                $record->update(['status' => 'Graded']);
                                $record->user?->notify(new SubmissionGradedNotification(
                                    'assignment',
                                    (string) $record->assignment?->name,
                                    $record->grade,
                                    (string) ($msg !== '' ? $msg : ($record->feedback ?: 'Your assignment has been graded.')),
                                ));
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markChecked')
                        ->label('Mark Checked')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'Checked']))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReturned')
                        ->label('Mark Returned')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'Returned']))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignmentSubmissions::route('/'),
            'edit' => EditAssignmentSubmission::route('/{record}/edit'),
        ];
    }
}
