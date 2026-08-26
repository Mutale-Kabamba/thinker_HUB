<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use App\Services\GamificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Enrolled Students';

    public function table(Table $table): Table
    {
        /** @var Course $course */
        $course = $this->getOwnerRecord();

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

                TextColumn::make('intake.name')
                    ->label('Class / Cohort')
                    ->badge()
                    ->color('info')
                    ->placeholder('General (No cohort)')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Enrolled Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? 'Completed (' . $state->format('M d, Y') . ')' : 'In Progress'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('add_student')
                    ->label('Enrol Student(s)')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading("Enrol Student(s) into '{$course->title}'")
                    ->modalDescription('Select one or more students to enrol into this course.')
                    ->modalSubmitActionLabel('Enrol in Course')
                    ->form([
                        Select::make('user_ids')
                            ->label('Select Student(s)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Search by student name or email.')
                            ->options(function () use ($course): array {
                                $alreadyEnrolled = Enrollment::query()
                                    ->where('course_id', $course->id)
                                    ->pluck('user_id');

                                return User::query()
                                    ->where(function ($q) {
                                        $q->whereNull('role')->orWhere('role', 'student');
                                    })
                                    ->whereNotIn('id', $alreadyEnrolled)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email}) - Track: " . ($u->track ?? 'Beginner')])
                                    ->toArray();
                            }),

                        Select::make('course_intake_id')
                            ->label('Assign Class / Intake (Cohort)')
                            ->placeholder('General / No Specific Intake')
                            ->options(fn (): array => CourseIntake::query()
                                ->where('course_id', $course->id)
                                ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                ->orderBy('start_date', 'desc')
                                ->get()
                                ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                                ->toArray()
                            )
                            ->searchable(),
                    ])
                    ->action(function (array $data) use ($course): void {
                        $userIds = (array) ($data['user_ids'] ?? []);
                        $intakeId = $data['course_intake_id'] ?? null;
                        $count = 0;

                        foreach ($userIds as $userId) {
                            Enrollment::firstOrCreate([
                                'user_id' => $userId,
                                'course_id' => $course->id,
                            ], [
                                'course_intake_id' => $intakeId,
                            ]);
                            $count++;
                        }

                        Notification::make()
                            ->title("Successfully enrolled {$count} student(s) into '{$course->title}'.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('assign_intake')
                    ->label('Assign Cohort')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->modalHeading(fn (Enrollment $record): string => "Assign Intake Cohort for {$record->user?->name}")
                    ->form([
                        Select::make('course_intake_id')
                            ->label('Class / Intake')
                            ->placeholder('No specific cohort (General)')
                            ->options(fn (Enrollment $record): array => CourseIntake::query()
                                ->where('course_id', $record->course_id)
                                ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                                ->orderBy('start_date', 'desc')
                                ->get()
                                ->mapWithKeys(fn (CourseIntake $i) => [$i->id => $i->name . ($i->is_active ? ' (Active)' : '')])
                                ->toArray()
                            )
                            ->default(fn (Enrollment $record) => $record->course_intake_id)
                            ->searchable(),
                    ])
                    ->action(function (Enrollment $record, array $data): void {
                        $record->update(['course_intake_id' => $data['course_intake_id'] ?? null]);
                        $intakeName = $record->fresh()->intake?->name ?? 'General (No cohort)';
                        Notification::make()
                            ->title("Intake updated to '{$intakeName}'.")
                            ->success()
                            ->send();
                    }),

                Action::make('toggle_completion')
                    ->label(fn (Enrollment $record): string => $record->completed_at ? 'Reset Status' : 'Mark Complete')
                    ->icon(fn (Enrollment $record): string => $record->completed_at ? 'heroicon-o-arrow-path' : 'heroicon-o-check-circle')
                    ->color(fn (Enrollment $record): string => $record->completed_at ? 'gray' : 'success')
                    ->action(function (Enrollment $record): void {
                        if ($record->completed_at) {
                            $record->markAsIncomplete();
                            Certificate::query()
                                ->where('user_id', $record->user_id)
                                ->where('course_id', $record->course_id)
                                ->delete();

                            if ($record->user && $record->course) {
                                try {
                                    app(GamificationService::class)->revokeCourseCompleted($record->user, $record->course);
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            }

                            Notification::make()
                                ->title('Course completion reset and certificate revoked.')
                                ->info()
                                ->send();
                        } else {
                            $record->markAsCompleted(auth()->user());
                            if ($record->user && $record->course) {
                                $certificate = app(CertificateService::class)->issue($record->user, $record->course, force: true);
                                if ($certificate && $certificate->wasRecentlyCreated) {
                                    try {
                                        $record->user->notify(new CertificateIssuedNotification($certificate));
                                    } catch (\Throwable $e) {
                                        report($e);
                                    }
                                }

                                try {
                                    app(GamificationService::class)->awardCourseCompleted($record->user, $record->course);
                                } catch (\Throwable $e) {
                                    report($e);
                                }
                            }

                            Notification::make()
                                ->title('Course Marked Complete!')
                                ->body('Certificate and completion badge are now ready for ' . ($record->user?->name ?? 'student') . '.')
                                ->success()
                                ->send();
                        }
                    }),

                DeleteAction::make()
                    ->label('Remove from Course')
                    ->modalHeading(fn (Enrollment $record): string => "Remove {$record->user?->name} from {$record->course?->title}?")
                    ->modalDescription('Are you sure you want to remove this student from this course? This will unenrol them from the course.')
                    ->successNotificationTitle('Student removed from course successfully.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Remove Selected from Course')
                        ->modalHeading('Remove Selected Students from Course')
                        ->modalDescription('Are you sure you want to remove the selected students from this course?')
                        ->successNotificationTitle('Selected students removed from course successfully.'),
                ]),
            ]);
    }
}
