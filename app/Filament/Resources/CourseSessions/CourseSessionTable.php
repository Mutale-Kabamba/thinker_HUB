<?php

namespace App\Filament\Resources\CourseSessions;

use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\RescheduleRequestDeclinedNotification;
use App\Notifications\RescheduleRequestNotification;
use App\Notifications\RescheduleRequestSubmittedNotification;
use App\Notifications\SessionRescheduledNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Notifications\DatabaseNotification;

class CourseSessionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'one_on_one' ? 'One-On-One' : 'Group')
                    ->color(fn (string $state): string => $state === 'group' ? 'success' : 'info'),
                TextColumn::make('student.name')
                    ->label('Student')
                    ->placeholder('All enrolled')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Session')
                    ->placeholder('—')
                    ->limit(30),
                TextColumn::make('session_date')
                    ->date('D, M j, Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->time('g:i A'),
                TextColumn::make('end_time')
                    ->time('g:i A'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'rescheduled' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->placeholder('—'),
            ])
            ->defaultSort('session_date', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'rescheduled' => 'Rescheduled',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'group' => 'Group',
                        'one_on_one' => 'One-On-One',
                    ]),
            ])
            ->recordActions([
                Action::make('markCompleted')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CourseSession $record): bool => $record->status === 'scheduled')
                    ->action(fn (CourseSession $record) => $record->update(['status' => 'completed'])),

                Action::make('reschedule')
                    ->label('Reschedule')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->visible(fn (CourseSession $record): bool => in_array($record->status, ['scheduled', 'rescheduled']))
                    ->form([
                        DatePicker::make('rescheduled_date')->required(),
                        TimePicker::make('rescheduled_start_time')->required()->seconds(false),
                        TimePicker::make('rescheduled_end_time')->seconds(false),
                    ])
                    ->action(function (CourseSession $record, array $data): void {
                        $record->update([
                            'status' => 'rescheduled',
                            'rescheduled_date' => $data['rescheduled_date'],
                            'rescheduled_start_time' => $data['rescheduled_start_time'],
                            'rescheduled_end_time' => $data['rescheduled_end_time'] ?? null,
                        ]);

                        $record->refresh();
                        $courseName = $record->course->title ?? 'Course';

                        if ($record->isOneOnOne() && $record->student_id) {
                            $student = User::find($record->student_id);
                            $student?->notify(new SessionRescheduledNotification($record, $courseName));
                        } else {
                            $students = User::query()
                                ->whereHas('enrollments', fn ($q) => $q->where('course_id', $record->course_id))
                                ->get();
                            foreach ($students as $student) {
                                $student->notify(new SessionRescheduledNotification($record, $courseName));
                            }
                        }
                    }),

                Action::make('reviewRescheduleRequest')
                    ->label('Review Request')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->visible(fn (CourseSession $record): bool => self::findPendingRequestNotificationForSession($record) !== null)
                    ->fillForm(function (CourseSession $record): array {
                        $notification = self::findPendingRequestNotificationForSession($record);
                        $data = $notification?->data ?? [];

                        return [
                            'decision' => 'accept',
                            'request_reason' => (string) ($data['reason'] ?? ''),
                            'request_student' => (string) ($data['student_name'] ?? 'Student'),
                            'request_preferred_date' => $data['preferred_date'] ?? null,
                            'request_preferred_time' => $data['preferred_time'] ?? null,
                            'rescheduled_date' => $data['preferred_date'] ?? null,
                            'rescheduled_start_time' => $data['preferred_time'] ?? null,
                            'rescheduled_end_time' => null,
                            'decline_reason' => '',
                        ];
                    })
                    ->form([
                        Placeholder::make('request_student')
                            ->label('Student')
                            ->content(fn ($state): string => (string) $state),
                        Placeholder::make('request_reason')
                            ->label('Reason')
                            ->content(fn ($state): string => (string) ($state ?: 'No reason provided.')),
                        Placeholder::make('request_preferred_date')
                            ->label('Preferred Date')
                            ->content(fn ($state): string => (string) ($state ?: 'Not provided')),
                        Placeholder::make('request_preferred_time')
                            ->label('Preferred Time')
                            ->content(fn ($state): string => (string) ($state ?: 'Not provided')),
                        Select::make('decision')
                            ->label('Decision')
                            ->options([
                                'accept' => 'Accept request',
                                'decline' => 'Decline request',
                            ])
                            ->default('accept')
                            ->required()
                            ->live(),
                        DatePicker::make('rescheduled_date')
                            ->label('Rescheduled Date')
                            ->visible(fn (callable $get): bool => $get('decision') === 'accept')
                            ->required(fn (callable $get): bool => $get('decision') === 'accept'),
                        TimePicker::make('rescheduled_start_time')
                            ->label('Rescheduled Start Time')
                            ->seconds(false)
                            ->visible(fn (callable $get): bool => $get('decision') === 'accept')
                            ->required(fn (callable $get): bool => $get('decision') === 'accept'),
                        TimePicker::make('rescheduled_end_time')
                            ->label('Rescheduled End Time')
                            ->seconds(false)
                            ->visible(fn (callable $get): bool => $get('decision') === 'accept'),
                        Textarea::make('decline_reason')
                            ->label('Decline Message (optional)')
                            ->rows(3)
                            ->visible(fn (callable $get): bool => $get('decision') === 'decline'),
                    ])
                    ->action(function (CourseSession $record, array $data): void {
                        $notification = self::findPendingRequestNotificationForSession($record);

                        if (! $notification) {
                            Notification::make()->title('No pending request found for this session.')->warning()->send();

                            return;
                        }

                        $requestData = $notification->data;
                        $decision = (string) ($data['decision'] ?? 'accept');

                        if ($decision === 'decline') {
                            $studentId = isset($requestData['student_id']) ? (int) $requestData['student_id'] : null;
                            $student = $studentId ? User::find($studentId) : null;

                            if ($student) {
                                $student->notify(new RescheduleRequestDeclinedNotification(
                                    session: $record,
                                    courseName: $record->course->title ?? 'Course',
                                    reason: filled($data['decline_reason'] ?? null) ? (string) $data['decline_reason'] : null,
                                ));
                            }

                            $notification->update([
                                'read_at' => now(),
                                'data' => array_merge($notification->data ?? [], ['decision_status' => 'declined']),
                            ]);

                            self::updateStudentRequestDecision($record->id, $studentId, 'declined');

                            Notification::make()->title('Reschedule request declined. Student notified.')->success()->send();

                            return;
                        }

                        $record->update([
                            'status' => 'rescheduled',
                            'rescheduled_date' => $data['rescheduled_date'],
                            'rescheduled_start_time' => $data['rescheduled_start_time'],
                            'rescheduled_end_time' => $data['rescheduled_end_time'] ?? null,
                        ]);

                        self::notifyStudentsAboutReschedule($record);

                        $notification->update([
                            'read_at' => now(),
                            'data' => array_merge($notification->data ?? [], ['decision_status' => 'accepted']),
                        ]);

                        $studentId = isset($requestData['student_id']) ? (int) $requestData['student_id'] : null;
                        self::updateStudentRequestDecision($record->id, $studentId, 'accepted');

                        Notification::make()->title('Reschedule request accepted. Session updated and students notified.')->success()->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    protected static function findPendingRequestNotificationForSession(CourseSession $record): ?DatabaseNotification
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->notifications()
            ->where('type', RescheduleRequestNotification::class)
            ->get()
            ->first(function (DatabaseNotification $notification) use ($record): bool {
                $data = $notification->data ?? [];

                return (int) ($data['session_id'] ?? 0) === $record->id
                    && empty($data['decision_status']);
            });
    }

    protected static function notifyStudentsAboutReschedule(CourseSession $record): void
    {
        $record->refresh();
        $courseName = $record->course->title ?? 'Course';

        if ($record->isOneOnOne() && $record->student_id) {
            $student = User::find($record->student_id);
            $student?->notify(new SessionRescheduledNotification($record, $courseName));

            return;
        }

        $students = User::query()
            ->whereHas('enrollments', fn ($q) => $q->where('course_id', $record->course_id))
            ->get();

        foreach ($students as $student) {
            $student->notify(new SessionRescheduledNotification($record, $courseName));
        }
    }

    protected static function updateStudentRequestDecision(int $sessionId, ?int $studentId, string $decision): void
    {
        if (! $studentId) {
            return;
        }

        $student = User::find($studentId);

        if (! $student) {
            return;
        }

        $requestNotification = $student->notifications()
            ->where('type', RescheduleRequestSubmittedNotification::class)
            ->latest()
            ->get()
            ->first(function (DatabaseNotification $notification) use ($sessionId): bool {
                $data = $notification->data ?? [];

                return (int) ($data['session_id'] ?? 0) === $sessionId
                    && in_array((string) ($data['decision_status'] ?? 'pending'), ['pending', ''], true);
            });

        if (! $requestNotification) {
            return;
        }

        $requestNotification->update([
            'read_at' => now(),
            'data' => array_merge($requestNotification->data ?? [], ['decision_status' => $decision]),
        ]);
    }
}
