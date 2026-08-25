<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Course;
use App\Models\CourseIntake;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IntakesRelationManager extends RelationManager
{
    protected static string $relationship = 'intakes';

    protected static ?string $title = 'Intakes & Classes (Cohorts)';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Intake / Class Name')
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
                    ->placeholder('Open-ended'),
                TextColumn::make('next_intake_start_date')
                    ->label('Next Intake Starts')
                    ->date('M j, Y')
                    ->placeholder('Not set'),
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
                    ->label('Enrolled Students')
                    ->counts('enrollments')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->defaultSort('start_date', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('New Intake / Class')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Create Intake / Class')
                    ->form([
                        TextInput::make('name')
                            ->label('Intake Name')
                            ->placeholder('e.g. Intake 1 - Jan 2026, Cohort Alpha')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->afterOrEqual('start_date'),
                        DatePicker::make('next_intake_start_date')
                            ->label('Next Intake Start Date')
                            ->helperText('When the subsequent cohort/intake will start.'),
                        DatePicker::make('registration_deadline')
                            ->label('Registration Deadline'),
                        Select::make('status')
                            ->options(CourseIntake::STATUSES)
                            ->default(CourseIntake::STATUS_UPCOMING)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Set as Current Active Intake')
                            ->helperText('If enabled, this becomes the live active intake for new enrollments.')
                            ->default(false),
                        TextInput::make('max_capacity')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Optional capacity limit'),
                        Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->after(function (CourseIntake $record, array $data): void {
                        if (! empty($data['is_active']) || ($data['status'] ?? '') === CourseIntake::STATUS_ACTIVE) {
                            $record->activate();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->label('Intake Name')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->afterOrEqual('start_date'),
                        DatePicker::make('next_intake_start_date')
                            ->label('Next Intake Start Date'),
                        DatePicker::make('registration_deadline')
                            ->label('Registration Deadline'),
                        Select::make('status')
                            ->options(CourseIntake::STATUSES)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Set as Current Active Intake'),
                        TextInput::make('max_capacity')
                            ->numeric()
                            ->minValue(1),
                        Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->after(function (CourseIntake $record, array $data): void {
                        if (! empty($data['is_active'])) {
                            $record->activate();
                        }
                    }),

                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (CourseIntake $record): bool => ! $record->is_active && ! $record->isArchived())
                    ->requiresConfirmation()
                    ->modalHeading('Activate Intake')
                    ->modalDescription('Make this the current active intake for this course? Any other currently active intake will be marked as completed.')
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
                    ->modalDescription('Archive this intake? Its enrollment and class history will be safely preserved in historical archive.')
                    ->action(function (CourseIntake $record): void {
                        $record->archive();
                        Notification::make()
                            ->title("Intake '{$record->name}' has been archived.")
                            ->warning()
                            ->send();
                    }),

                Action::make('archiveAndStartNext')
                    ->label('Archive & Launch Next')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn (CourseIntake $record): bool => $record->is_active || $record->status === CourseIntake::STATUS_ACTIVE)
                    ->modalHeading('Archive Current Intake & Launch New Intake')
                    ->modalDescription('This will archive the current intake (preserving all students and class records in history) and start the next intake on a clean blank slate.')
                    ->form([
                        TextInput::make('new_intake_name')
                            ->label('New Intake / Class Name')
                            ->placeholder('e.g. Intake 2 - March 2026')
                            ->required(),
                        DatePicker::make('new_start_date')
                            ->label('New Intake Start Date')
                            ->default(fn (CourseIntake $record) => $record->next_intake_start_date ?? now()->toDateString())
                            ->required(),
                        DatePicker::make('new_end_date')
                            ->label('New Intake End Date'),
                        DatePicker::make('new_next_intake_start_date')
                            ->label('Subsequent Next Intake Start Date')
                            ->helperText('Expected start date for the cohort after this one.'),
                    ])
                    ->action(function (CourseIntake $record, array $data): void {
                        // 1. Archive the current active intake
                        $record->archive();

                        // 2. Create and activate the new intake on a blank slate
                        $course = $record->course;
                        $newIntake = CourseIntake::create([
                            'course_id' => $course->id,
                            'name' => $data['new_intake_name'],
                            'start_date' => $data['new_start_date'],
                            'end_date' => $data['new_end_date'] ?? null,
                            'next_intake_start_date' => $data['new_next_intake_start_date'] ?? null,
                            'status' => CourseIntake::STATUS_ACTIVE,
                            'is_active' => true,
                        ]);

                        $newIntake->activate();

                        Notification::make()
                            ->title("Intake '{$record->name}' archived. New intake '{$newIntake->name}' launched on a blank slate!")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
