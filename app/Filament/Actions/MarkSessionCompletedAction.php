<?php

namespace App\Filament\Actions;

use App\Models\Attendance;
use App\Models\CourseSession;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\HtmlString;

class MarkSessionCompletedAction
{
    public static function make(string $name = 'markCompleted'): Action
    {
        return Action::make($name)
            ->label('Mark Completed')
            ->icon('heroicon-m-check-circle')
            ->color('success')
            ->modalHeading(fn (CourseSession $record): string => 'Complete Session & Mark Attendance')
            ->modalDescription(function (CourseSession $record): string {
                $course = $record->course?->title ?? 'Course';
                $title = $record->title ?: 'Class Session';
                $date = $record->getEffectiveDate()->format('D, M j, Y');

                return "{$course} — {$title} ({$date})";
            })
            ->modalWidth('4xl')
            ->modalSubmitActionLabel('Save Attendance & Mark Completed')
            ->visible(fn (CourseSession $record): bool => in_array($record->status, [
                CourseSession::STATUS_SCHEDULED,
                CourseSession::STATUS_RESCHEDULED,
            ], true))
            ->fillForm(function (CourseSession $record): array {
                $service = app(AttendanceService::class);
                $service->syncSessionRoster($record);

                $attendances = Attendance::query()
                    ->with('student')
                    ->where('course_session_id', $record->id)
                    ->get()
                    ->reject(fn (Attendance $attendance) => $service->isExcludedStudent($attendance->student))
                    ->sortBy(fn (Attendance $attendance) => strtolower((string) ($attendance->student?->name ?? '')))
                    ->values()
                    ->map(fn (Attendance $attendance): array => [
                        'attendance_id' => $attendance->id,
                        'student_name' => $attendance->student?->name ?? 'Unknown Student',
                        'student_email' => $attendance->student?->email ?? '',
                        'status' => $attendance->status ?: Attendance::STATUS_PRESENT,
                        'notes' => $attendance->notes ?? '',
                    ])
                    ->all();

                return [
                    'bulk_status' => null,
                    'attendances' => $attendances,
                    'session_notes' => $record->notes ?? '',
                ];
            })
            ->steps([
                Step::make('Mark Attendance')
                    ->description('Record each student\'s attendance for this session')
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        ToggleButtons::make('bulk_status')
                            ->label('Quick Mark All Students')
                            ->helperText('Apply a single status to everyone below, then adjust individual exceptions.')
                            ->options([
                                Attendance::STATUS_PRESENT => 'All Present',
                                Attendance::STATUS_LATE => 'All Late',
                                Attendance::STATUS_APOLOGY => 'All Apology',
                                Attendance::STATUS_ABSENT => 'All Absent',
                            ])
                            ->colors([
                                Attendance::STATUS_PRESENT => 'success',
                                Attendance::STATUS_LATE => 'warning',
                                Attendance::STATUS_APOLOGY => 'info',
                                Attendance::STATUS_ABSENT => 'danger',
                            ])
                            ->icons([
                                Attendance::STATUS_PRESENT => 'heroicon-m-check-circle',
                                Attendance::STATUS_LATE => 'heroicon-m-clock',
                                Attendance::STATUS_APOLOGY => 'heroicon-m-document-text',
                                Attendance::STATUS_ABSENT => 'heroicon-m-x-circle',
                            ])
                            ->inline()
                            ->live()
                            ->visible(fn (callable $get): bool => ! empty($get('attendances')))
                            ->afterStateUpdated(function (?string $state, callable $get, callable $set): void {
                                if (! $state || ! in_array($state, Attendance::STATUSES, true)) {
                                    return;
                                }

                                $rows = $get('attendances') ?? [];
                                foreach ($rows as $key => $row) {
                                    $rows[$key]['status'] = $state;
                                }
                                $set('attendances', $rows);
                            }),

                        Placeholder::make('empty_roster_notice')
                            ->label('')
                            ->content(new HtmlString(
                                '<div style="padding:1rem;border-radius:0.75rem;border:1px dashed #cbd5e1;text-align:center;color:#64748b;font-size:0.875rem;">'
                                . 'No active enrolled students were found for this session. Proceed to the next step to mark the session completed.'
                                . '</div>'
                            ))
                            ->visible(fn (callable $get): bool => empty($get('attendances'))),

                        Repeater::make('attendances')
                            ->label('Student Roster')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsible(false)
                            ->visible(fn (callable $get): bool => ! empty($get('attendances')))
                            ->itemLabel(fn (array $state): ?string => trim(
                                ($state['student_name'] ?? 'Student')
                                . (! empty($state['student_email']) ? ' (' . $state['student_email'] . ')' : '')
                            ))
                            ->columns(12)
                            ->schema([
                                Hidden::make('attendance_id'),
                                Hidden::make('student_name'),
                                Hidden::make('student_email'),

                                ToggleButtons::make('status')
                                    ->label('Status')
                                    ->options([
                                        Attendance::STATUS_PRESENT => 'Present',
                                        Attendance::STATUS_LATE => 'Late',
                                        Attendance::STATUS_APOLOGY => 'Apology',
                                        Attendance::STATUS_ABSENT => 'Absent',
                                    ])
                                    ->colors([
                                        Attendance::STATUS_PRESENT => 'success',
                                        Attendance::STATUS_LATE => 'warning',
                                        Attendance::STATUS_APOLOGY => 'info',
                                        Attendance::STATUS_ABSENT => 'danger',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 7,
                                    ]),

                                TextInput::make('notes')
                                    ->label('Remark (optional)')
                                    ->placeholder('e.g. Arrived 10 mins late')
                                    ->maxLength(255)
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 5,
                                    ]),
                            ]),
                    ]),

                Step::make('Review & Complete')
                    ->description('Confirm attendance summary and session notes')
                    ->icon('heroicon-m-check-badge')
                    ->schema([
                        Placeholder::make('attendance_summary')
                            ->label('Attendance Summary')
                            ->content(function (callable $get): HtmlString {
                                $rows = collect($get('attendances') ?? []);
                                $total = $rows->count();
                                $present = $rows->where('status', Attendance::STATUS_PRESENT)->count();
                                $late = $rows->where('status', Attendance::STATUS_LATE)->count();
                                $apology = $rows->where('status', Attendance::STATUS_APOLOGY)->count();
                                $absent = $rows->where('status', Attendance::STATUS_ABSENT)->count();
                                $rate = $total > 0 ? (int) round((($present + $late) / $total) * 100) : 0;

                                return new HtmlString(
                                    '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:0.75rem;">'
                                    . self::summaryCard('Total', (string) $total, '#475569', '#f8fafc', '#e2e8f0')
                                    . self::summaryCard('Present', (string) $present, '#059669', '#ecfdf5', '#a7f3d0')
                                    . self::summaryCard('Late', (string) $late, '#d97706', '#fffbeb', '#fde68a')
                                    . self::summaryCard('Apology', (string) $apology, '#0284c7', '#f0f9ff', '#bae6fd')
                                    . self::summaryCard('Absent', (string) $absent, '#dc2626', '#fef2f2', '#fecaca')
                                    . self::summaryCard('Rate', $rate . '%', '#0f766e', '#f0fdfa', '#99f6e4')
                                    . '</div>'
                                );
                            }),

                        Textarea::make('session_notes')
                            ->label('Session Notes / Recap (optional)')
                            ->placeholder('Summarize topics covered, follow-up items, or homework assigned...')
                            ->rows(4),
                    ]),
            ])
            ->action(function (CourseSession $record, array $data): void {
                $service = app(AttendanceService::class);
                $rows = $data['attendances'] ?? [];

                $counts = [
                    Attendance::STATUS_PRESENT => 0,
                    Attendance::STATUS_LATE => 0,
                    Attendance::STATUS_APOLOGY => 0,
                    Attendance::STATUS_ABSENT => 0,
                ];

                foreach ($rows as $row) {
                    $attendanceId = (int) ($row['attendance_id'] ?? 0);
                    $status = (string) ($row['status'] ?? Attendance::STATUS_PRESENT);
                    $notes = isset($row['notes']) ? trim((string) $row['notes']) : null;

                    if ($attendanceId > 0 && in_array($status, Attendance::STATUSES, true)) {
                        $service->markAttendance($attendanceId, $status, $notes === '' ? null : $notes);
                        $counts[$status]++;
                    }
                }

                $record->update([
                    'status' => CourseSession::STATUS_COMPLETED,
                    'notes' => array_key_exists('session_notes', $data) && trim((string) $data['session_notes']) !== ''
                        ? trim((string) $data['session_notes'])
                        : $record->notes,
                ]);

                $totalMarked = array_sum($counts);
                $summaryText = $totalMarked > 0
                    ? "{$counts[Attendance::STATUS_PRESENT]} present, {$counts[Attendance::STATUS_LATE]} late, {$counts[Attendance::STATUS_APOLOGY]} apology, {$counts[Attendance::STATUS_ABSENT]} absent."
                    : 'Session status updated to completed.';

                Notification::make()
                    ->title('Session Marked Completed')
                    ->body($summaryText)
                    ->success()
                    ->send();
            });
    }

    private static function summaryCard(string $label, string $value, string $color, string $bg, string $border): string
    {
        return '<div style="padding:0.75rem;border-radius:0.75rem;background:' . $bg . ';border:1px solid ' . $border . ';text-align:center;">'
            . '<div style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:' . $color . ';">' . e($label) . '</div>'
            . '<div style="font-size:1.35rem;font-weight:800;color:' . $color . ';margin-top:0.2rem;">' . e($value) . '</div>'
            . '</div>';
    }
}
