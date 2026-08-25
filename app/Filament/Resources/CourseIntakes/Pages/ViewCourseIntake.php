<?php

namespace App\Filament\Resources\CourseIntakes\Pages;

use App\Filament\Resources\CourseIntakes\CourseIntakeResource;
use App\Models\CourseIntake;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCourseIntake extends ViewRecord
{
    protected static string $resource = CourseIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('activate')
                ->label('Activate Intake')
                ->icon('heroicon-o-play')
                ->color('success')
                ->visible(fn (): bool => ! $this->record->is_active && ! $this->record->isArchived())
                ->requiresConfirmation()
                ->modalHeading('Activate Intake')
                ->modalDescription('Make this the live active intake? Any previously active intake will be completed.')
                ->action(function (): void {
                    /** @var CourseIntake $record */
                    $record = $this->record;
                    $record->activate();

                    Notification::make()
                        ->title("Intake '{$record->name}' is now active.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['is_active', 'status']);
                }),

            Action::make('archive')
                ->label('Archive Intake')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->visible(fn (): bool => ! $this->record->isArchived())
                ->requiresConfirmation()
                ->modalHeading('Archive Intake')
                ->modalDescription('Archive this intake? Its enrollment and class history will remain safely preserved.')
                ->action(function (): void {
                    /** @var CourseIntake $record */
                    $record = $this->record;
                    $record->archive();

                    Notification::make()
                        ->title("Intake '{$record->name}' has been archived.")
                        ->warning()
                        ->send();

                    $this->refreshFormData(['is_active', 'status', 'archived_at']);
                }),

            Action::make('archive_and_launch_next')
                ->label('Archive & Start Next Cohort')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (): bool => $this->record->is_active && ! $this->record->isArchived())
                ->form([
                    TextInput::make('next_name')
                        ->label('New Cohort / Intake Name')
                        ->placeholder('e.g. Intake 2 - April 2026, Cohort Beta')
                        ->required(),
                    DatePicker::make('next_start_date')
                        ->label('New Cohort Start Date')
                        ->default(fn () => $this->record->next_intake_start_date ?? now()->toDateString())
                        ->required(),
                    DatePicker::make('next_end_date')
                        ->label('New Cohort End Date'),
                    DatePicker::make('subsequent_intake_start_date')
                        ->label('Subsequent Intake Start Date'),
                ])
                ->modalHeading('Archive Current Cohort & Start Next')
                ->modalDescription('This will archive the current intake, preserving all student records, and immediately launch the new cohort as active on a blank slate.')
                ->action(function (array $data): void {
                    /** @var CourseIntake $record */
                    $record = $this->record;
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

                    $this->redirect(CourseIntakeResource::getUrl('view', ['record' => $newIntake->id]));
                }),

            DeleteAction::make(),
        ];
    }
}
