<?php

namespace App\Filament\Actions;

use App\Models\Attendance;
use App\Models\CourseSession;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;
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
            ->modalHeading('Complete Session')
            ->modalDescription(function (CourseSession $record): string {
                $title = $record->title ?: ($record->course?->title ?? 'Class Session');
                $date = $record->getEffectiveDate()->format('D, M j, Y');

                return "{$title} · {$date}";
            })
            ->modalWidth('2xl')
            ->modalSubmitActionLabel('Complete Session')
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
                Step::make('Attendance')
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        ToggleButtons::make('bulk_status')
                            ->label('Mark all')
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
                            ->grouped()
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
                            ->hiddenLabel()
                            ->content(new HtmlString(
                                '<div style="padding:0.85rem;border-radius:0.5rem;border:1px dashed #cbd5e1;text-align:center;color:#64748b;font-size:0.85rem;">'
                                . 'No active students enrolled for this session.'
                                . '</div>'
                            ))
                            ->visible(fn (callable $get): bool => empty($get('attendances'))),

                        Repeater::make('attendances')
                            ->hiddenLabel()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->compact()
                            ->visible(fn (callable $get): bool => ! empty($get('attendances')))
                            ->table([
                                TableColumn::make('Student'),
                                TableColumn::make('Status')->width('260px'),
                            ])
                            ->schema([
                                Hidden::make('attendance_id'),
                                Hidden::make('student_name'),
                                Hidden::make('notes'),

                                Placeholder::make('student_display')
                                    ->hiddenLabel()
                                    ->content(fn (callable $get): string => (string) ($get('student_name') ?: 'Student')),

                                ToggleButtons::make('status')
                                    ->hiddenLabel()
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
                                    ->grouped()
                                    ->required(),
                            ]),
                    ]),

                Step::make('Confirm')
                    ->icon('heroicon-m-check')
                    ->schema([
                        Placeholder::make('attendance_summary')
                            ->hiddenLabel()
                            ->content(function (callable $get): HtmlString {
                                $rows = collect($get('attendances') ?? []);
                                $total = $rows->count();
                                $present = $rows->where('status', Attendance::STATUS_PRESENT)->count();
                                $late = $rows->where('status', Attendance::STATUS_LATE)->count();
                                $apology = $rows->where('status', Attendance::STATUS_APOLOGY)->count();
                                $absent = $rows->where('status', Attendance::STATUS_ABSENT)->count();
                                $rate = $total > 0 ? (int) round((($present + $late) / $total) * 100) : 0;

                                return new HtmlString(
                                    '<div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">'
                                    . self::statPill('Present', (string) $present, '#059669', '#ecfdf5')
                                    . self::statPill('Late', (string) $late, '#d97706', '#fffbeb')
                                    . self::statPill('Apology', (string) $apology, '#0284c7', '#f0f9ff')
                                    . self::statPill('Absent', (string) $absent, '#dc2626', '#fef2f2')
                                    . self::statPill('Rate', $rate . '%', '#0f766e', '#f0fdfa')
                                    . '</div>'
                                );
                            }),

                        Textarea::make('session_notes')
                            ->label('Session Notes (optional)')
                            ->placeholder('Brief recap or remarks...')
                            ->rows(2),
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
                    $notes = array_key_exists('notes', $row) ? trim((string) $row['notes']) : null;

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
                    ? "{$counts[Attendance::STATUS_PRESENT]} present · {$counts[Attendance::STATUS_LATE]} late · {$counts[Attendance::STATUS_APOLOGY]} apology · {$counts[Attendance::STATUS_ABSENT]} absent"
                    : 'Session status updated to completed.';

                Notification::make()
                    ->title('Session Completed')
                    ->body($summaryText)
                    ->success()
                    ->send();
            });
    }

    private static function statPill(string $label, string $value, string $color, string $bg): string
    {
        return '<span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.35rem 0.7rem;border-radius:999px;background:' . $bg . ';color:' . $color . ';font-size:0.8rem;font-weight:600;">'
            . '<span>' . e($label) . '</span>'
            . '<strong style="font-weight:800;">' . e($value) . '</strong>'
            . '</span>';
    }
}
