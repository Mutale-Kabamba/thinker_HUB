<?php

namespace App\Filament\Resources\CourseIntakes\RelationManagers;

use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Enrolled Students (Cohort)';

    public function table(Table $table): Table
    {
        /** @var CourseIntake $intake */
        $intake = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('user.track')
                    ->label('Level / Track')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Enrolled Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),

                IconColumn::make('completed_at')
                    ->label('Course Completed')
                    ->boolean()
                    ->getStateUsing(fn (Enrollment $record): bool => $record->completed_at !== null),
            ])
            ->headerActions([
                Action::make('add_students')
                    ->label('Add Student(s) to Intake')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading("Add Students to '{$intake->name}'")
                    ->modalDescription(function () use ($intake): string {
                        $current = $intake->enrollments()->count();
                        $capacity = $intake->max_capacity ? " (Capacity: {$current} / {$intake->max_capacity})" : " ({$current} currently enrolled)";
                        return "Select one or more students to assign or enroll into this intake cohort{$capacity}.";
                    })
                    ->modalSubmitActionLabel('Add to Intake')
                    ->form([
                        Select::make('user_ids')
                            ->label('Select Student(s)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Search by student name or email.')
                            ->options(function () use ($intake): array {
                                $alreadyInThisIntake = Enrollment::query()
                                    ->where('course_id', $intake->course_id)
                                    ->where('course_intake_id', $intake->id)
                                    ->pluck('user_id');

                                return User::query()
                                    ->where(function ($q) {
                                        $q->whereNull('role')->orWhere('role', 'student');
                                    })
                                    ->whereNotIn('id', $alreadyInThisIntake)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function (User $u) use ($intake) {
                                        $isEnrolledInCourse = Enrollment::query()
                                            ->where('course_id', $intake->course_id)
                                            ->where('user_id', $u->id)
                                            ->exists();
                                        $tag = $isEnrolledInCourse ? ' (Already in Course - Will Assign to Intake)' : '';
                                        return [$u->id => "{$u->name} ({$u->email}) - Track: " . ($u->track ?? 'Beginner') . $tag];
                                    })
                                    ->toArray();
                            }),
                    ])
                    ->action(function (array $data) use ($intake): void {
                        $userIds = (array) ($data['user_ids'] ?? []);
                        $count = 0;

                        foreach ($userIds as $userId) {
                            $enrollment = Enrollment::query()
                                ->where('user_id', $userId)
                                ->where('course_id', $intake->course_id)
                                ->first();

                            if ($enrollment) {
                                $enrollment->update([
                                    'course_intake_id' => $intake->id,
                                ]);
                            } else {
                                Enrollment::create([
                                    'user_id' => $userId,
                                    'course_id' => $intake->course_id,
                                    'course_intake_id' => $intake->id,
                                ]);
                            }
                            $count++;
                        }

                        Notification::make()
                            ->title("Successfully added {$count} student(s) to '{$intake->name}'.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('transfer_intake')
                        ->label('Move Intake')
                        ->icon('heroicon-m-arrows-right-left')
                        ->color('warning')
                        ->modalHeading('Move Student to Another Intake')
                        ->modalDescription(fn (Enrollment $record): string => "Transfer {$record->user?->name} to another intake for {$record->course?->title}.")
                        ->form([
                            Select::make('target_intake_id')
                                ->label('Target Intake')
                                ->required()
                                ->options(function () use ($intake): array {
                                    return CourseIntake::query()
                                        ->where('course_id', $intake->course_id)
                                        ->whereKeyNot($intake->id)
                                        ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                        ->orderBy('start_date', 'desc')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }),
                        ])
                        ->action(function (Enrollment $record, array $data): void {
                            $targetIntake = CourseIntake::find($data['target_intake_id']);
                            $record->update([
                                'course_intake_id' => $targetIntake?->id,
                            ]);

                            Notification::make()
                                ->title("Transferred student to '{$targetIntake?->name}'.")
                                ->success()
                                ->send();
                        }),

                    Action::make('remove_from_intake')
                        ->label('Remove from Intake')
                        ->icon('heroicon-m-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Remove Student from Intake')
                        ->modalDescription(fn (Enrollment $record): string => "Remove {$record->user?->name} from '{$intake->name}'? (Student will remain enrolled in the course without an assigned cohort).")
                        ->action(function (Enrollment $record): void {
                            $record->update([
                                'course_intake_id' => null,
                            ]);

                            Notification::make()
                                ->title('Student removed from intake cohort.')
                                ->info()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('Unenroll Course')
                        ->icon('heroicon-m-trash')
                        ->modalHeading('Unenroll Student from Course Entirely'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('move_selected_intake')
                        ->label('Move to Another Intake')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->form([
                            Select::make('target_intake_id')
                                ->label('Target Intake')
                                ->required()
                                ->options(function () use ($intake): array {
                                    return CourseIntake::query()
                                        ->where('course_id', $intake->course_id)
                                        ->whereKeyNot($intake->id)
                                        ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                        ->orderBy('start_date', 'desc')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $targetIntake = CourseIntake::find($data['target_intake_id']);
                            $records->each(fn (Enrollment $e) => $e->update(['course_intake_id' => $targetIntake?->id]));

                            Notification::make()
                                ->title("Moved {$records->count()} student(s) to '{$targetIntake?->name}'.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('remove_from_intake_bulk')
                        ->label('Remove from Intake (Keep in Course)')
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->modalHeading('Remove selected students from this intake cohort')
                        ->action(function (Collection $records): void {
                            $records->each(fn (Enrollment $e) => $e->update(['course_intake_id' => null]));

                            Notification::make()
                                ->title("Removed {$records->count()} student(s) from intake cohort.")
                                ->info()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Unenroll Selected from Course'),
                ]),
            ]);
    }
}

