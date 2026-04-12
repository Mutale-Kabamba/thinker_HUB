<?php

namespace App\Filament\Resources\AssignmentSubmissions\Tables;

use App\Models\Course;
use App\Notifications\SubmissionGradedNotification;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
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
                    ->searchable()
                    ->badge(),
                TextColumn::make('due_indicator')
                    ->label('Due')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        $dueDate = $record->assignment?->due_date;

                        if (! $dueDate) {
                            return 'No due date';
                        }

                        if ($dueDate->isPast()) {
                            return 'Overdue: '.$dueDate->format('Y-m-d');
                        }

                        return 'Upcoming: '.$dueDate->format('Y-m-d');
                    })
                    ->color(function (string $state): string {
                        if (str_starts_with($state, 'Overdue')) {
                            return 'danger';
                        }

                        if (str_starts_with($state, 'Upcoming')) {
                            return 'warning';
                        }

                        return 'gray';
                    }),
                TextColumn::make('grade')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markGraded')
                        ->label('Mark Graded')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'Graded']))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markChecked')
                        ->label('Mark Checked')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'Checked']))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markGradedAndNotify')
                        ->label('Mark Graded + Notify')
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
                                $record->update(['status' => 'Graded']);

                                if ($record->user) {
                                    $record->user->notify(new SubmissionGradedNotification(
                                        'assignment',
                                        (string) $record->assignment?->name,
                                        $record->grade,
                                        (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your assignment has been graded.')),
                                    ));
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReviewed')
                        ->label('Mark Reviewed')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'Reviewed']))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('markReturned')
                        ->label('Mark Returned')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'Returned']))
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
                                $record->update(['status' => 'Reviewed']);

                                if ($record->user) {
                                    $record->user->notify(new SubmissionGradedNotification(
                                        'assignment',
                                        (string) $record->assignment?->name,
                                        $record->grade,
                                        (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your submission has been reviewed.')),
                                    ));
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
                                $record->update(['status' => 'Returned']);

                                if ($record->user) {
                                    $record->user->notify(new SubmissionGradedNotification(
                                        'assignment',
                                        (string) $record->assignment?->name,
                                        $record->grade,
                                        (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your submission has been returned with feedback.')),
                                    ));
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
