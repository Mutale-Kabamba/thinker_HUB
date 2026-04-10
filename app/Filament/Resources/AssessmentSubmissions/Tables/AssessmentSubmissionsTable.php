<?php

namespace App\Filament\Resources\AssessmentSubmissions\Tables;

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

class AssessmentSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assessment.id')
                    ->label('Assessment')
                    ->formatStateUsing(fn ($state): string => 'Assessment #'.(string) $state)
                    ->sortable(),
                TextColumn::make('assessment.course.title')
                    ->label('Course')
                    ->placeholder('Unassigned')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable()
                    ->badge(),
                TextColumn::make('review_indicator')
                    ->label('Review')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        if ($record->status !== 'Submitted') {
                            return 'Reviewed';
                        }

                        if ($record->submitted_at && $record->submitted_at->lt(now()->subDays(7))) {
                            return 'Overdue review';
                        }

                        return 'Pending review';
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Overdue review' => 'danger',
                            'Pending review' => 'warning',
                            default => 'success',
                        };
                    }),
                TextColumn::make('score')
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

                        return $query->whereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery->where('course_id', $data['value']));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
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
                                        'assessment',
                                        'Assessment #'.(string) $record->assessment?->id,
                                        $record->score,
                                        (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your assessment has been reviewed.')),
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
                                        'assessment',
                                        'Assessment #'.(string) $record->assessment?->id,
                                        $record->score,
                                        (string) ($customMessage !== '' ? $customMessage : ($record->feedback ?: 'Your assessment has been returned with feedback.')),
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
