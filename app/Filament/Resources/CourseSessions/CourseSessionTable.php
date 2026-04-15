<?php

namespace App\Filament\Resources\CourseSessions;

use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\SessionRescheduledNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
