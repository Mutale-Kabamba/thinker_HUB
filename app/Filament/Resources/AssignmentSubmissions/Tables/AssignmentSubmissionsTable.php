<?php

namespace App\Filament\Resources\AssignmentSubmissions\Tables;

use App\Models\Course;
use App\Notifications\SubmissionGradedNotification;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AssignmentSubmissionsTable
{
    public static function configure(Table $table): Table
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
                            TextColumn::make('assignment.name')
                                ->label('Assignment')
                                ->weight('bold')
                                ->size('sm')
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
                        TextColumn::make('due_indicator')
                            ->label('Due')
                            ->badge()
                            ->getStateUsing(function ($record): string {
                                $dueDate = $record->assignment?->due_date;
                                if (! $dueDate) { return 'No due date'; }
                                $submissionTime = $record->submitted_at ?? $record->created_at;
                                if ($submissionTime) {
                                    return $submissionTime->lte($dueDate->copy()->endOfDay()) ? 'On Time' : 'Late';
                                }
                                return $dueDate->isPast() ? 'Overdue' : 'Upcoming';
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'On Time' => 'success',
                                'Late', 'Overdue' => 'danger',
                                'Upcoming' => 'warning',
                                default => 'gray',
                            })
                            ->size('xs'),
                        TextColumn::make('grade')
                            ->formatStateUsing(fn ($state) => $state !== null ? "Grade: {$state}" : 'Ungraded')
                            ->badge()
                            ->color(fn ($state) => $state !== null ? 'success' : 'gray')
                            ->size('xs'),
                    ])->extraAttributes(['class' => 'pt-2 border-t border-gray-100 dark:border-gray-800']),
                ])
                ->extraAttributes([
                    'class' => 'p-4 bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm space-y-2 md:hidden',
                ]),

                // Desktop Table Columns (Hidden on Mobile)
                TextColumn::make('assignment.name')
                    ->label('Assignment')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('assignment.course.title')
                    ->label('Course')
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->visibleFrom('md'),
                TextColumn::make('due_indicator')
                    ->label('Due')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        $dueDate = $record->assignment?->due_date;

                        if (! $dueDate) {
                            return 'No due date';
                        }

                        $submissionTime = $record->submitted_at ?? $record->created_at;

                        if ($submissionTime) {
                            if ($submissionTime->lte($dueDate->copy()->endOfDay())) {
                                return 'On Time: '.$dueDate->format('Y-m-d');
                            }

                            return 'Late: '.$dueDate->format('Y-m-d');
                        }

                        if ($dueDate->isPast()) {
                            return 'Overdue: '.$dueDate->format('Y-m-d');
                        }

                        return 'Upcoming: '.$dueDate->format('Y-m-d');
                    })
                    ->color(function (string $state): string {
                        if (str_starts_with($state, 'On Time')) {
                            return 'success';
                        }

                        if (str_starts_with($state, 'Late') || str_starts_with($state, 'Overdue')) {
                            return 'danger';
                        }

                        if (str_starts_with($state, 'Upcoming')) {
                            return 'warning';
                        }

                        return 'gray';
                    })
                    ->visibleFrom('md'),
                TextColumn::make('attachments_indicator')
                    ->label('Attachments')
                    ->getStateUsing(function ($record): string {
                        $parts = [];
                        $fileCount = count($record->all_file_paths ?? []);
                        if ($fileCount > 1) {
                            $parts[] = "{$fileCount} Files";
                        } elseif ($fileCount === 1) {
                            $parts[] = 'File';
                        }
                        if ($record->link) { $parts[] = 'Link'; }
                        if ($record->video_url) { $parts[] = 'Video'; }
                        return $parts ? implode(', ', $parts) : '-';
                    })
                    ->toggleable()
                    ->visibleFrom('md'),
                TextColumn::make('grade')
                    ->numeric()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('view_status')
                    ->label('Read')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record->viewed_at !== null || in_array($record->status, ['Graded', 'Checked']) ? 'Viewed' : 'New')
                    ->color(fn (string $state): string => $state === 'New' ? 'warning' : 'gray')
                    ->visibleFrom('md'),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
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
                SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(fn (): array => Course::query()->orderBy('title')->pluck('title', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('assignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('course_id', $data['value']));
                    }),
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

                            return $service->downloadSingleAssignmentSubmission($record);
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
                            $response = $service->downloadAssignmentsZip($records);

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
                    BulkAction::make('markChecked')
                        ->label('Mark Checked')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            $record->update(['status' => 'Checked', 'viewed_at' => $record->viewed_at ?? now()]);
                        }))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markGradedAndNotify')
                        ->label('Mark Graded + Notify')
                        ->icon('heroicon-o-bell-alert')
                        ->requiresConfirmation()
                        ->modalHeading('Mark graded and notify students')
                        ->form([
                            Textarea::make('message')
                                ->label('Custom message')
                                ->rows(3)
                                ->placeholder('Optional message sent to all selected students.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $customMessage = trim((string) ($data['message'] ?? ''));

                            $records->each(function ($record) use ($customMessage): void {
                                $record->update(['status' => 'Graded', 'viewed_at' => $record->viewed_at ?? now()]);

                                if ($record->user) {
                                    try {
                                        $record->user->notify(new SubmissionGradedNotification(
                                            'assignment',
                                            (string) $record->assignment?->name,
                                            $record->grade,
                                            (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your assignment has been graded.')),
                                        ));
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReviewed')
                        ->label('Mark Reviewed')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            $record->update(['status' => 'Reviewed', 'viewed_at' => $record->viewed_at ?? now()]);
                        }))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReturned')
                        ->label('Mark Returned')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->action(fn (Collection $records) => $records->each(function ($record) {
                            $record->update(['status' => 'Returned', 'viewed_at' => $record->viewed_at ?? now()]);
                        }))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReviewedAndNotify')
                        ->label('Mark Reviewed + Notify')
                        ->requiresConfirmation()
                        ->modalHeading('Mark reviewed and notify students')
                        ->form([
                            Textarea::make('message')
                                ->label('Custom message')
                                ->rows(3)
                                ->placeholder('Optional message sent to all selected students.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $customMessage = trim((string) ($data['message'] ?? ''));

                            $records->each(function ($record) use ($customMessage): void {
                                $record->update(['status' => 'Reviewed', 'viewed_at' => $record->viewed_at ?? now()]);

                                if ($record->user) {
                                    try {
                                        $record->user->notify(new SubmissionGradedNotification(
                                            'assignment',
                                            (string) $record->assignment?->name,
                                            $record->grade,
                                            (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your submission has been reviewed.')),
                                        ));
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReturnedAndNotify')
                        ->label('Mark Returned + Notify')
                        ->requiresConfirmation()
                        ->modalHeading('Mark returned and notify students')
                        ->form([
                            Textarea::make('message')
                                ->label('Custom message')
                                ->rows(3)
                                ->placeholder('Optional message sent to all selected students.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $customMessage = trim((string) ($data['message'] ?? ''));

                            $records->each(function ($record) use ($customMessage): void {
                                $record->update(['status' => 'Returned', 'viewed_at' => $record->viewed_at ?? now()]);

                                if ($record->user) {
                                    try {
                                        $record->user->notify(new SubmissionGradedNotification(
                                            'assignment',
                                            (string) $record->assignment?->name,
                                            $record->grade,
                                            (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your submission has been returned with feedback.')),
                                        ));
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
