<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceService
{
    /**
     * Idempotently sync enrolled students into the session's attendance records.
     *
     * @return array{total_eligible: int, newly_created: int, student_ids: list<int>}
     */
    public function syncSessionRoster(CourseSession $session): array
    {
        return Attendance::syncForSession($session);
    }

    /**
     * Determine if a user is a designated test student who should be excluded from formal reports and PDF exports.
     */
    public function isExcludedStudent(?User $student): bool
    {
        return $student ? $student->isTestStudent() : false;
    }

    /**
     * Calculate comprehensive attendance metrics for a session.
     *
     * @return array{
     *     total: int,
     *     present: int,
     *     absent: int,
     *     late: int,
     *     apology: int,
     *     attended_total: int,
     *     attendance_rate: int,
     *     marked_count: int
     * }
     */
    public function getSessionAttendanceSummary(CourseSession $session): array
    {
        $attendances = Attendance::query()
            ->where('course_session_id', $session->id)
            ->with('student')
            ->get()
            ->reject(fn ($a) => $this->isExcludedStudent($a->student));

        $total = $attendances->count();
        $present = $attendances->where('status', Attendance::STATUS_PRESENT)->count();
        $absent = $attendances->where('status', Attendance::STATUS_ABSENT)->count();
        $late = $attendances->where('status', Attendance::STATUS_LATE)->count();
        $apology = $attendances->where('status', Attendance::STATUS_APOLOGY)->count();

        $attendedTotal = $present + $late;
        $attendanceRate = $total > 0 ? (int) round(($attendedTotal / $total) * 100) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'apology' => $apology,
            'attended_total' => $attendedTotal,
            'attendance_rate' => $attendanceRate,
            'marked_count' => $attendances->whereNotNull('status')->count(),
        ];
    }

    /**
     * Update attendance status and optional note for a single record.
     */
    public function markAttendance(int $attendanceId, string $status, ?string $notes = null): Attendance
    {
        $attendance = Attendance::findOrFail($attendanceId);

        if (! in_array($status, Attendance::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid attendance status [{$status}].");
        }

        $data = ['status' => $status];
        if ($notes !== null) {
            $data['notes'] = $notes;
        }

        $attendance->update($data);

        return $attendance->fresh(['student', 'session']);
    }

    /**
     * Mark all attendance records of a session with the given status.
     */
    public function markBatchAttendance(CourseSession $session, string $status): int
    {
        if (! in_array($status, Attendance::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid attendance status [{$status}].");
        }

        $attendances = Attendance::query()
            ->where('course_session_id', $session->id)
            ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            $attendance->update(['status' => $status]);
            $count++;
        }

        return $count;
    }

    /**
     * Stream an Excel-compatible CSV export for a single session attendance register.
     */
    public function exportSessionExcel(CourseSession $session): StreamedResponse
    {
        $this->syncSessionRoster($session);

        $session->loadMissing(['course', 'intake', 'instructor']);
        $summary = $this->getSessionAttendanceSummary($session);

        $records = Attendance::query()
            ->with(['student'])
            ->where('course_session_id', $session->id)
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->whereNotIn('users.email', User::TEST_STUDENT_EMAILS)
            ->where(function ($q) {
                $q->where('users.name', 'not like', '%bwalya mutale%')
                    ->orWhere('users.email', 'not like', '%zcuniversity%');
            })
            ->orderBy('users.name', 'asc')
            ->select('attendances.*')
            ->get();

        $cleanTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $session->title ?: 'Session');
        $dateStr = $session->getEffectiveDate()->format('Y-m-d');
        $filename = "Attendance_Register_{$cleanTitle}_{$dateStr}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($session, $summary, $records) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Microsoft Excel auto-encoding
            fputs($handle, "\xEF\xBB\xBF");

            // Header Section
            fputcsv($handle, ['THINKER HUB — OFFICIAL ATTENDANCE REGISTER']);
            fputcsv($handle, ['Generated At', Carbon::now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Session Metadata
            fputcsv($handle, ['SESSION INFORMATION']);
            fputcsv($handle, ['Course', $session->course?->title ?? '—', 'Code', $session->course?->code ?? '—']);
            fputcsv($handle, ['Session Title', $session->title ?: 'Class Session', 'Session Type', ucfirst($session->type ?: 'Group')]);
            fputcsv($handle, ['Class / Intake', $session->intake?->name ?? 'All Enrolled Cohorts', 'Instructor', $session->instructor?->name ?? '—']);
            fputcsv($handle, [
                'Date',
                $session->getEffectiveDate()->format('l, d M Y'),
                'Scheduled Time',
                $session->getEffectiveStartTime() . ' - ' . $session->getEffectiveEndTime(),
            ]);
            fputcsv($handle, []);

            // Summary KPI Section
            fputcsv($handle, ['ATTENDANCE SUMMARY']);
            fputcsv($handle, ['Total Registered Students', $summary['total']]);
            fputcsv($handle, ['Present', $summary['present']]);
            fputcsv($handle, ['Absent', $summary['absent']]);
            fputcsv($handle, ['Late', $summary['late']]);
            fputcsv($handle, ['Apology / Excused', $summary['apology']]);
            fputcsv($handle, ['Attendance Rate (%)', $summary['attendance_rate'] . '%']);
            fputcsv($handle, []);

            // Student Roster
            fputcsv($handle, ['STUDENT ROSTER & ATTENDANCE LOG']);
            fputcsv($handle, [
                '#',
                'Student ID',
                'Student Name',
                'Student Email',
                'Status',
                'Notes / Remarks',
                'Marked At',
            ]);

            $index = 1;
            foreach ($records as $record) {
                $student = $record->student;
                $statusLabel = match ($record->status) {
                    Attendance::STATUS_PRESENT => 'Present',
                    Attendance::STATUS_ABSENT => 'Absent',
                    Attendance::STATUS_LATE => 'Late',
                    Attendance::STATUS_APOLOGY => 'Apology',
                    default => ucfirst((string) $record->status),
                };

                fputcsv($handle, [
                    $index++,
                    $student?->id ?? '—',
                    $student?->name ?? '—',
                    $student?->email ?? '—',
                    $statusLabel,
                    $record->notes ?: '—',
                    $record->updated_at ? $record->updated_at->format('Y-m-d H:i') : '—',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Instructor / Proctor Signature:', '____________________________________', 'Date:', '________________']);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Render an official printable PDF attendance sheet for a single session.
     */
    public function exportSessionPdf(CourseSession $session): DomPDF
    {
        $this->syncSessionRoster($session);

        $session->loadMissing(['course', 'intake', 'instructor']);
        $summary = $this->getSessionAttendanceSummary($session);

        $records = Attendance::query()
            ->with(['student'])
            ->where('course_session_id', $session->id)
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->whereNotIn('users.email', User::TEST_STUDENT_EMAILS)
            ->where(function ($q) {
                $q->where('users.name', 'not like', '%bwalya mutale%')
                    ->orWhere('users.email', 'not like', '%zcuniversity%');
            })
            ->orderBy('users.name', 'asc')
            ->select('attendances.*')
            ->get();

        $data = [
            'session' => $session,
            'summary' => $summary,
            'records' => $records,
            'generatedAt' => Carbon::now(),
        ];

        return Pdf::loadView('reports.attendance-session-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Stream an Excel-compatible CSV export for a course/intake cumulative attendance matrix.
     */
    public function exportCourseCumulativeExcel(Course $course, ?int $intakeId = null): StreamedResponse
    {
        $sessionsQuery = CourseSession::query()
            ->where('course_id', $course->id)
            ->orderBy('session_date', 'asc')
            ->orderBy('start_time', 'asc');

        if ($intakeId) {
            $sessionsQuery->where('course_intake_id', $intakeId);
        }

        $sessions = $sessionsQuery->get();

        // Get enrolled students
        $enrollmentQuery = Enrollment::query()
            ->where('course_id', $course->id)
            ->with('user');

        if ($intakeId) {
            $enrollmentQuery->where('course_intake_id', $intakeId);
        }

        $students = $enrollmentQuery->get()
            ->map(fn ($e) => $e->user)
            ->filter()
            ->reject(fn ($u) => $this->isExcludedStudent($u))
            ->sortBy('name')
            ->values();

        $sessionIds = $sessions->pluck('id')->all();
        $allAttendances = Attendance::query()
            ->whereIn('course_session_id', $sessionIds)
            ->get()
            ->groupBy('user_id');

        $cleanCourse = preg_replace('/[^A-Za-z0-9_\-]/', '_', $course->title);
        $filename = "Cumulative_Attendance_{$cleanCourse}_" . date('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($course, $intakeId, $sessions, $students, $allAttendances) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['THINKER HUB — CUMULATIVE COURSE ATTENDANCE MATRIX']);
            fputcsv($handle, ['Course', $course->title, 'Course Code', $course->code ?? '—']);
            if ($intakeId) {
                $intake = CourseIntake::find($intakeId);
                fputcsv($handle, ['Intake / Cohort', $intake?->name ?? 'Intake #' . $intakeId]);
            }
            fputcsv($handle, ['Total Sessions Scheduled', $sessions->count(), 'Total Enrolled Students', $students->count()]);
            fputcsv($handle, ['Generated At', Carbon::now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Dynamic columns: Student info + Each Session Date/Title + Present Count + Rate %
            $columnHeaders = ['#', 'Student ID', 'Student Name', 'Email'];
            foreach ($sessions as $s) {
                $columnHeaders[] = $s->getEffectiveDate()->format('M d') . ' (' . ($s->title ?: 'Session') . ')';
            }
            $columnHeaders[] = 'Present';
            $columnHeaders[] = 'Absent';
            $columnHeaders[] = 'Late';
            $columnHeaders[] = 'Apology';
            $columnHeaders[] = 'Total Attended';
            $columnHeaders[] = 'Attendance Rate (%)';

            fputcsv($handle, $columnHeaders);

            $index = 1;
            foreach ($students as $student) {
                $studentAttendances = $allAttendances->get($student->id, collect());
                $attBySession = $studentAttendances->keyBy('course_session_id');

                $presentCount = 0;
                $absentCount = 0;
                $lateCount = 0;
                $apologyCount = 0;

                $row = [
                    $index++,
                    $student->id,
                    $student->name,
                    $student->email,
                ];

                foreach ($sessions as $s) {
                    $att = $attBySession->get($s->id);
                    $st = $att ? strtolower((string) $att->status) : 'unmarked';

                    if ($st === Attendance::STATUS_PRESENT) {
                        $presentCount++;
                        $row[] = 'P';
                    } elseif ($st === Attendance::STATUS_LATE) {
                        $lateCount++;
                        $row[] = 'L';
                    } elseif ($st === Attendance::STATUS_APOLOGY) {
                        $apologyCount++;
                        $row[] = 'E'; // Excused/Apology
                    } elseif ($st === Attendance::STATUS_ABSENT) {
                        $absentCount++;
                        $row[] = 'A';
                    } else {
                        $row[] = '—';
                    }
                }

                $attendedTotal = $presentCount + $lateCount;
                $totalSessions = $sessions->count();
                $rate = $totalSessions > 0 ? (int) round(($attendedTotal / $totalSessions) * 100) : 0;

                $row[] = $presentCount;
                $row[] = $absentCount;
                $row[] = $lateCount;
                $row[] = $apologyCount;
                $row[] = $attendedTotal;
                $row[] = $rate . '%';

                fputcsv($handle, $row);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Legend: P = Present, L = Late, A = Absent, E = Apology/Excused, — = Unmarked']);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Render a cumulative course attendance PDF report.
     */
    public function exportCourseCumulativePdf(Course $course, ?int $intakeId = null): DomPDF
    {
        $sessionsQuery = CourseSession::query()
            ->where('course_id', $course->id)
            ->orderBy('session_date', 'asc')
            ->orderBy('start_time', 'asc');

        if ($intakeId) {
            $sessionsQuery->where('course_intake_id', $intakeId);
        }

        $sessions = $sessionsQuery->get();

        $enrollmentQuery = Enrollment::query()
            ->where('course_id', $course->id)
            ->with('user');

        if ($intakeId) {
            $enrollmentQuery->where('course_intake_id', $intakeId);
        }

        $students = $enrollmentQuery->get()
            ->map(fn ($e) => $e->user)
            ->filter()
            ->reject(fn ($u) => $this->isExcludedStudent($u))
            ->sortBy('name')
            ->values();

        $sessionIds = $sessions->pluck('id')->all();
        $allAttendances = Attendance::query()
            ->whereIn('course_session_id', $sessionIds)
            ->get()
            ->groupBy('user_id');

        $intake = $intakeId ? CourseIntake::find($intakeId) : null;

        $matrix = [];
        $totalAttendedAll = 0;
        $possibleAttendanceAll = $students->count() * max(1, $sessions->count());

        foreach ($students as $student) {
            $studentAttendances = $allAttendances->get($student->id, collect());
            $attBySession = $studentAttendances->keyBy('course_session_id');

            $present = 0;
            $late = 0;
            $absent = 0;
            $apology = 0;
            $statuses = [];

            foreach ($sessions as $s) {
                $att = $attBySession->get($s->id);
                $st = $att ? strtolower((string) $att->status) : 'unmarked';
                $statuses[$s->id] = $st;

                if ($st === Attendance::STATUS_PRESENT) {
                    $present++;
                } elseif ($st === Attendance::STATUS_LATE) {
                    $late++;
                } elseif ($st === Attendance::STATUS_ABSENT) {
                    $absent++;
                } elseif ($st === Attendance::STATUS_APOLOGY) {
                    $apology++;
                }
            }

            $attended = $present + $late;
            $totalAttendedAll += $attended;
            $rate = $sessions->count() > 0 ? (int) round(($attended / $sessions->count()) * 100) : 0;

            $matrix[] = [
                'student' => $student,
                'statuses' => $statuses,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'apology' => $apology,
                'attended' => $attended,
                'rate' => $rate,
            ];
        }

        $overallRate = $possibleAttendanceAll > 0 ? (int) round(($totalAttendedAll / $possibleAttendanceAll) * 100) : 0;

        $data = [
            'course' => $course,
            'intake' => $intake,
            'sessions' => $sessions,
            'matrix' => $matrix,
            'overallRate' => $overallRate,
            'totalStudents' => $students->count(),
            'totalSessions' => $sessions->count(),
            'generatedAt' => Carbon::now(),
        ];

        return Pdf::loadView('reports.attendance-cumulative-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Render an official printable PDF attendance schedule register matching the school register format:
     * Month header -> Weeks (Week 1, Week 2, Week 3, Week 4...) -> Days (M, T, W, TH, F) with
     * student rows, checkmarks (✓) for Present, crosses (✗) for Absent, and signature lines.
     */
    public function exportScheduleRegisterPdf(Course $course, ?int $intakeId = null, ?Carbon $upToDate = null, ?string $targetMonth = null): DomPDF
    {
        $upToDate = $upToDate ?: Carbon::today();

        $sessionsQuery = CourseSession::query()
            ->where('course_id', $course->id)
            ->where('session_date', '<=', $upToDate->toDateString())
            ->orderBy('session_date', 'asc')
            ->orderBy('start_time', 'asc');

        if ($intakeId) {
            $sessionsQuery->where('course_intake_id', $intakeId);
        }

        if ($targetMonth) {
            $sessionsQuery->where('session_date', 'like', $targetMonth . '%');
        }

        $sessions = $sessionsQuery->get();

        // If no past sessions found or if course has upcoming sessions, fall back to all sessions of course
        if ($sessions->isEmpty()) {
            $sessionsQuery = CourseSession::query()
                ->where('course_id', $course->id)
                ->orderBy('session_date', 'asc')
                ->orderBy('start_time', 'asc');

            if ($intakeId) {
                $sessionsQuery->where('course_intake_id', $intakeId);
            }
            $sessions = $sessionsQuery->get();
        }

        // Pre-sync attendance for all these sessions so all enrolled students have rows
        foreach ($sessions as $session) {
            $this->syncSessionRoster($session);
        }

        // Get enrolled students
        $enrollmentQuery = Enrollment::query()
            ->where('course_id', $course->id)
            ->with('user');

        if ($intakeId) {
            $enrollmentQuery->where('course_intake_id', $intakeId);
        }

        $students = $enrollmentQuery->get()
            ->map(fn ($e) => $e->user)
            ->filter()
            ->reject(fn ($u) => $this->isExcludedStudent($u))
            ->sortBy('name')
            ->values();

        $intake = $intakeId ? CourseIntake::find($intakeId) : null;

        // Group sessions by month (Y-m)
        $months = $sessions->groupBy(fn ($s) => $s->getEffectiveDate()->format('Y-m'));

        $monthDataList = [];

        foreach ($months as $monthKey => $monthSessions) {
            $monthDate = Carbon::parse($monthKey . '-01');
            $monthName = $monthDate->format('F Y');
            $sessionsByDate = $monthSessions->groupBy(fn ($s) => $s->getEffectiveDate()->format('Y-m-d'));

            $firstMonday = $monthDate->copy()->startOfWeek(Carbon::MONDAY);
            $lastFriday = $monthDate->copy()->endOfMonth()->endOfWeek(Carbon::FRIDAY);

            $current = $firstMonday->copy();
            $weeks = [];
            $weekIdx = 1;

            while ($current->lte($lastFriday)) {
                $weekDays = [];
                $hasSessionsInWeek = false;

                for ($d = 0; $d < 5; $d++) {
                    $dayDate = $current->copy()->addDays($d);
                    $dateStr = $dayDate->format('Y-m-d');
                    $isInMonth = $dayDate->month === $monthDate->month;

                    $daySessions = $sessionsByDate->get($dateStr, collect());
                    if ($daySessions->isNotEmpty()) {
                        $hasSessionsInWeek = true;
                    }

                    $dayCode = match ($d) {
                        0 => 'M',
                        1 => 'T',
                        2 => 'W',
                        3 => 'TH',
                        4 => 'F',
                    };

                    $weekDays[] = [
                        'code' => $dayCode,
                        'name' => $dayDate->format('l'),
                        'date_str' => $dateStr,
                        'day_num' => $dayDate->format('d'),
                        'in_month' => $isInMonth,
                        'sessions' => $daySessions,
                    ];
                }

                if ($hasSessionsInWeek) {
                    $weeks['Week ' . $weekIdx] = $weekDays;
                    $weekIdx++;
                }

                $current->addWeek();
            }

            // If no sessions grouped into weeks, fallback to calendar weeks of month
            if (empty($weeks)) {
                $current = $firstMonday->copy();
                $weekIdx = 1;
                while ($current->lte($lastFriday)) {
                    $weekDays = [];
                    $hasDaysInMonth = false;
                    for ($d = 0; $d < 5; $d++) {
                        $dayDate = $current->copy()->addDays($d);
                        $isInMonth = $dayDate->month === $monthDate->month;
                        if ($isInMonth) {
                            $hasDaysInMonth = true;
                        }
                        $dayCode = match ($d) { 0 => 'M', 1 => 'T', 2 => 'W', 3 => 'TH', 4 => 'F' };
                        $weekDays[] = [
                            'code' => $dayCode,
                            'name' => $dayDate->format('l'),
                            'date_str' => $dayDate->format('Y-m-d'),
                            'day_num' => $dayDate->format('d'),
                            'in_month' => $isInMonth,
                            'sessions' => $sessionsByDate->get($dayDate->format('Y-m-d'), collect()),
                        ];
                    }
                    if ($hasDaysInMonth) {
                        $weeks['Week ' . $weekIdx] = $weekDays;
                        $weekIdx++;
                    }
                    $current->addWeek();
                }
            }

            $allDays = [];
            foreach ($weeks as $wName => $wDays) {
                foreach ($wDays as $d) {
                    $allDays[] = $d;
                }
            }

            $studentIds = $students->pluck('id')->all();
            $attendances = Attendance::whereIn('course_session_id', $monthSessions->pluck('id'))
                ->whereIn('user_id', $studentIds)
                ->get()
                ->groupBy('user_id');

            $studentRows = [];
            $dailyTotals = [];
            foreach ($allDays as $d) {
                $dailyTotals[$d['date_str']] = ['present' => 0, 'absent' => 0, 'has_session' => $d['sessions']->isNotEmpty()];
            }

            foreach ($students as $st) {
                $stAtt = $attendances->get($st->id, collect())->keyBy('course_session_id');
                $marks = [];
                $presentCount = 0;
                $absentCount = 0;
                $totalScheduled = 0;

                foreach ($allDays as $d) {
                    $dateStr = $d['date_str'];
                    $daySess = $d['sessions'];

                    if ($daySess->isEmpty()) {
                        $marks[$dateStr] = ['type' => 'none', 'symbol' => ''];
                    } else {
                        $totalScheduled += $daySess->count();
                        $primarySess = $daySess->first();
                        $att = $stAtt->get($primarySess->id);
                        $status = $att ? strtolower((string) $att->status) : 'unmarked';

                        if ($status === 'present') {
                            $presentCount++;
                            $dailyTotals[$dateStr]['present']++;
                            $marks[$dateStr] = ['type' => 'present', 'symbol' => '&#10003;'];
                        } elseif ($status === 'late') {
                            $presentCount++;
                            $dailyTotals[$dateStr]['present']++;
                            $marks[$dateStr] = ['type' => 'late', 'symbol' => 'L'];
                        } elseif ($status === 'apology') {
                            $marks[$dateStr] = ['type' => 'apology', 'symbol' => 'E'];
                        } elseif ($status === 'absent') {
                            $absentCount++;
                            $dailyTotals[$dateStr]['absent']++;
                            $marks[$dateStr] = ['type' => 'absent', 'symbol' => '&#10007;'];
                        } else {
                            $marks[$dateStr] = ['type' => 'unmarked', 'symbol' => '—'];
                        }
                    }
                }

                $rate = $totalScheduled > 0 ? (int) round(($presentCount / $totalScheduled) * 100) : 0;

                $studentRows[] = [
                    'student' => $st,
                    'marks' => $marks,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'total' => $totalScheduled,
                    'rate' => $rate,
                ];
            }

            $monthDataList[] = [
                'monthKey' => $monthKey,
                'monthName' => $monthName,
                'weeks' => $weeks,
                'allDays' => $allDays,
                'studentRows' => $studentRows,
                'dailyTotals' => $dailyTotals,
                'totalSessions' => $monthSessions->count(),
            ];
        }

        $firstSession = $sessions->first();
        $instructorName = $firstSession?->instructor?->name ?? $course->course_by;

        $data = [
            'course' => $course,
            'intake' => $intake,
            'monthDataList' => $monthDataList,
            'totalStudents' => $students->count(),
            'instructorName' => $instructorName,
            'generatedAt' => Carbon::now(),
        ];

        return Pdf::loadView('reports.attendance-schedule-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }
}
