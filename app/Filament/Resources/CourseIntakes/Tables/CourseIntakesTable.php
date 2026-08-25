<?php

namespace App\Filament\Resources\CourseIntakes\Tables;

use App\Models\Course;
use App\Models\CourseIntake;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourseIntakesTable
{
    /**
     * @param  (callable(): array<string, string>)|null  $courseOptions
     */
    public static function configure(Table $table, ?callable $courseOptions = null): Table
    {
        $resolveCourseOptions = $courseOptions ?? fn (): array => Course::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (Course $c) => [$c->id => $c->title . ' (' . $c->code . ')'])
            ->toArray();

        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('name')
                    ->label('Class / Intake')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('M j, Y')
                    ->sortable()
                    ->placeholder('Ongoing'),

                TextColumn::make('next_intake_start_date')
                    ->label('Next Intake')
                    ->date('M j, Y')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CourseIntake::STATUS_ACTIVE => 'success',
                        CourseIntake::STATUS_UPCOMING => 'warning',
                        CourseIntake::STATUS_COMPLETED => 'info',
                        CourseIntake::STATUS_ARCHIVED => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => CourseIntake::STATUSES[$state] ?? ucfirst($state)),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('enrollments_count')
                    ->label('Students')
                    ->counts('enrollments')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                SelectFilter::make('course_id')
                    ->label('Filter by Course')
                    ->options($resolveCourseOptions)
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(CourseIntake::STATUSES),

                SelectFilter::make('is_active')
                    ->label('Active State')
                    ->options([
                        '1' => 'Current Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                EditAction::make(),

                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (CourseIntake $record): bool => ! $record->is_active && ! $record->isArchived())
                    ->requiresConfirmation()
                    ->modalHeading('Activate Intake')
                    ->modalDescription('Set this as the active intake? Any other currently active intake for this course will be marked as completed.')
                    ->action(function (CourseIntake $record): void {
                        $record->activate();
                        Notification::make()
                            ->title("Intake '{$record->name}' is now active.")
                            ->success()
                            ->send();
                    }),

                Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->visible(fn (CourseIntake $record): bool => ! $record->isArchived())
                    ->requiresConfirmation()
                    ->modalHeading('Archive Intake')
                    ->modalDescription('Archive this intake? Its enrollment and class history will remain preserved in historical archive.')
                    ->action(function (CourseIntake $record): void {
                        $record->archive();
                        Notification::make()
                            ->title("Intake '{$record->name}' has been archived.")
                            ->warning()
                            ->send();
                    }),

                Action::make('archive_and_launch_next')
                    ->label('Archive & Start Next')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (CourseIntake $record): bool => $record->is_active && ! $record->isArchived())
                    ->form([
                        TextInput::make('next_name')
                            ->label('New Intake Name')
                            ->placeholder('e.g. Intake 2 - April 2026, Cohort Beta')
                            ->required(),
                        DatePicker::make('next_start_date')
                            ->label('New Intake Start Date')
                            ->default(fn (CourseIntake $record) => $record->next_intake_start_date ?? now()->toDateString())
                            ->required(),
                        DatePicker::make('next_end_date')
                            ->label('New Intake End Date'),
                        DatePicker::make('subsequent_intake_start_date')
                            ->label('Subsequent (Next) Intake Start Date'),
                    ])
                    ->modalHeading('Archive Current Cohort & Start Next')
                    ->modalDescription('This will archive the current intake, preserving all student records, and immediately create the new cohort as active on a blank slate.')
                    ->action(function (CourseIntake $record, array $data): void {
                        $course = $record->course;
                        $record->archive();

                        $newIntake = CourseIntake::create([
                            'course_id' => $course->id,
                            'name' => $data['next_name'],
                            'start_date' => $data['next_start_date'],
                            'end_date' => $data['next_end_date'] ?? null,
                            'next_intake_start_date' => $data['subsequent_intake_start_date'] ?? null,
                            'status' => CourseIntake::STATUS_ACTIVE,
                            'is_active' => true,
                        ]);

                        $newIntake->activate();

                        Notification::make()
                            ->title("Previous cohort archived. '{$newIntake->name}' launched on a clean slate!")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
