<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Attendance Register Schedule — {{ $course->title }}</title>
    <style>
        @page {
            margin: 6mm 8mm 8mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 6.8pt;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .logo-text {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .logo-text span {
            color: #0d9488;
        }

        .tagline {
            font-size: 5.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
        }

        .meta-text {
            text-align: right;
            font-size: 6pt;
            color: #475569;
            line-height: 1.25;
        }

        .context-bar {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 3px 6px;
            margin-bottom: 6px;
            font-size: 6.8pt;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 6px;
        }

        .grid-table th, .grid-table td {
            border: 1px solid #94a3b8;
            padding: 1px 1px;
            text-align: center;
            vertical-align: middle;
        }

        .grid-table .th-name {
            background: #f8fafc;
            color: #0f172a;
            font-weight: 800;
            text-align: left;
            padding-left: 6px;
            padding-right: 4px;
            font-size: 7pt;
            border: 1.5px solid #334155;
        }

        .grid-table .th-month {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 7.2pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1.5px solid #334155;
            padding: 2px;
        }

        .grid-table .th-week {
            background: #e2e8f0;
            color: #1e293b;
            font-weight: 700;
            font-size: 6.2pt;
            border: 1px solid #64748b;
            padding: 1.2px;
        }

        .grid-table .th-day {
            background: #ffffff;
            color: #334155;
            font-weight: 700;
            font-size: 5.8pt;
            border: 1px solid #94a3b8;
            line-height: 1;
            padding: 1.5px 0px;
        }

        .grid-table .td-name {
            text-align: left;
            padding: 2.5px 6px;
            font-weight: 700;
            font-size: 6.8pt;
            line-height: 1.15;
            color: #0f172a;
            border: 1px solid #94a3b8;
            word-wrap: break-word;
            white-space: normal;
        }

        .grid-table .td-cell {
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 1;
            padding: 0px 0px;
        }

        .mark-present {
            color: #059669;
            font-size: 9.5pt;
            font-weight: bold;
        }

        .mark-absent {
            color: #e11d48;
            font-size: 9.5pt;
            font-weight: bold;
        }

        .mark-late {
            color: #d97706;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .mark-apology {
            color: #2563eb;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .mark-unmarked {
            color: #94a3b8;
            font-size: 7.5pt;
        }

        .mark-nosession {
            color: #cbd5e1;
            font-size: 5pt;
        }

        .summary-th {
            font-size: 6.8pt;
            font-weight: 800;
            border: 1.5px solid #334155 !important;
            padding: 2px 0px;
        }

        .notes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
            margin-top: 4px;
        }

        .rule-line {
            border-bottom: 1px solid #cbd5e1;
            height: 14px;
        }

        .signature-line {
            border-bottom: 1px solid #94a3b8;
            height: 16px;
            width: 90%;
        }

        .legend-bar {
            margin-top: 4px;
            padding: 2px 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            font-size: 5.5pt;
            color: #475569;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 2px;
            font-size: 5.5pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- Document Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                <div class="logo-text">think<span>.er</span> HUB</div>
                <div class="tagline">Official Attendance Register • Marked Schedule</div>
            </td>
            <td class="meta-text" style="width: 50%; vertical-align: middle;">
                <div style="font-weight: 800; color: #0f172a; font-size: 7.5pt; text-transform: uppercase;">
                    Schedule Attendance Sheet (Up to Current Date)
                </div>
                <div><strong>Generated:</strong> {{ $generatedAt->format('d M Y, H:i') }} • <strong>Doc:</strong> REG-{{ $course->code ?? 'CRS' }}-{{ date('Ymd') }}</div>
            </td>
        </tr>
    </table>

    {{-- Course & Academic Context --}}
    <div class="context-bar">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 35%;">
                    <strong>Course:</strong> {{ $course->title }} @if($course->code) <span style="color: #64748b;">({{ $course->code }})</span> @endif
                </td>
                <td style="width: 25%;">
                    <strong>Cohort / Intake:</strong> {{ $intake?->name ?? 'All Active Students' }}
                </td>
                <td style="width: 20%;">
                    <strong>Instructor:</strong> {{ $instructorName ?? $course->course_by ?? 'Faculty Assigned' }}
                </td>
                <td style="width: 20%; text-align: right;">
                    <strong>Enrolled Students:</strong> {{ $totalStudents }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Month Registers Loop --}}
    @foreach($monthDataList as $monthIndex => $monthData)
        @if($monthIndex > 0)
            <div style="page-break-before: always;"></div>
        @endif

        @php
            $dayCount = count($monthData['allDays']);
            $summaryWidth = 8.0; // P (2.4%) + A (2.4%) + % (3.2%)
            $nameColWidth = 26.0; // 26% for student name guarantees full display without cutoff
            $availableDayWidth = 100.0 - $nameColWidth - $summaryWidth;
            $dayPct = $dayCount > 0 ? round($availableDayWidth / $dayCount, 3) : 3.3;
        @endphp

        {{-- The Outline Register Table --}}
        <table class="grid-table">
            <colgroup>
                <col style="width: {{ $nameColWidth }}%;">
                @foreach($monthData['allDays'] as $day)
                    <col style="width: {{ $dayPct }}%;">
                @endforeach
                <col style="width: 2.4%;">
                <col style="width: 2.4%;">
                <col style="width: 3.2%;">
            </colgroup>
            <thead>
                {{-- Row 1: Name Header & Month Banner & Summary Headers --}}
                <tr>
                    <th rowspan="3" class="th-name" style="width: {{ $nameColWidth }}%;">
                        Student Name
                    </th>
                    <th colspan="{{ $dayCount }}" class="th-month" style="width: {{ $availableDayWidth }}%;">
                        Month: {{ $monthData['monthName'] }}
                    </th>
                    <th rowspan="3" class="summary-th" style="width: 2.4%; background: #ecfdf5; color: #065f46;" title="Present Count">
                        P
                    </th>
                    <th rowspan="3" class="summary-th" style="width: 2.4%; background: #fff1f2; color: #9f1239;" title="Absent Count">
                        A
                    </th>
                    <th rowspan="3" class="summary-th" style="width: 3.2%; background: #f8fafc; color: #0f172a;" title="Attendance Rate">
                        %
                    </th>
                </tr>

                {{-- Row 2: Weeks Header (Week 1, Week 2, Week 3, Week 4...) --}}
                <tr>
                    @foreach($monthData['weeks'] as $weekName => $weekDays)
                        <th colspan="{{ count($weekDays) }}" class="th-week">
                            {{ $weekName }}
                        </th>
                    @endforeach
                </tr>

                {{-- Row 3: Day Columns (M, T, W, TH, F) --}}
                <tr>
                    @foreach($monthData['allDays'] as $day)
                        <th class="th-day" style="background: {{ $day['sessions']->isNotEmpty() ? '#ffffff' : '#f8fafc' }};">
                            {{ $day['code'] }}
                            <div style="font-size: 5pt; font-weight: normal; color: {{ $day['sessions']->isNotEmpty() ? '#0f172a' : '#94a3b8' }};">
                                {{ $day['day_num'] }}
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- Student Rows --}}
                @forelse($monthData['studentRows'] as $rowIdx => $row)
                    <tr>
                        {{-- Student Name --}}
                        <td class="td-name">
                            {{ $rowIdx + 1 }}. {{ $row['student']->name }}
                        </td>

                        {{-- Day Attendance Marks --}}
                        @foreach($monthData['allDays'] as $day)
                            @php
                                $mark = $row['marks'][$day['date_str']] ?? ['type' => 'none', 'symbol' => ''];
                                $hasSession = $day['sessions']->isNotEmpty();
                                $cellBg = match($mark['type']) {
                                    'present' => '#ecfdf5',
                                    'absent' => '#fff1f2',
                                    'late' => '#fffbeb',
                                    'apology' => '#eff6ff',
                                    default => ($hasSession ? '#ffffff' : '#f8fafc'),
                                };
                            @endphp
                            <td class="td-cell" style="background: {{ $cellBg }};">
                                @if($mark['type'] === 'present')
                                    <span class="mark-present">&#10003;</span>
                                @elseif($mark['type'] === 'absent')
                                    <span class="mark-absent">&#10007;</span>
                                @elseif($mark['type'] === 'late')
                                    <span class="mark-late">L</span>
                                @elseif($mark['type'] === 'apology')
                                    <span class="mark-apology">E</span>
                                @elseif($mark['type'] === 'unmarked')
                                    <span class="mark-unmarked">—</span>
                                @else
                                    <span class="mark-nosession">·</span>
                                @endif
                            </td>
                        @endforeach

                        {{-- Summary P, A, % --}}
                        <td style="font-weight: bold; background: #ecfdf5; color: #047857; font-size: 7.5pt;">
                            {{ $row['present'] }}
                        </td>
                        <td style="font-weight: bold; background: #fff1f2; color: #be123c; font-size: 7.5pt;">
                            {{ $row['absent'] }}
                        </td>
                        <td style="font-weight: 800; font-size: 7.5pt; color: {{ $row['rate'] >= 75 ? '#059669' : ($row['rate'] >= 50 ? '#d97706' : '#e11d48') }};">
                            {{ $row['rate'] }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $dayCount + 4 }}" style="text-align: center; padding: 12px; color: #94a3b8;">
                            No students enrolled for this register period.
                        </td>
                    </tr>
                @endforelse

                {{-- Daily Totals Row (Total Present) --}}
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td class="td-name" style="font-size: 6.5pt; text-transform: uppercase; color: #475569; background: #f1f5f9;">
                        Daily Present (&#10003;)
                    </td>
                    @foreach($monthData['allDays'] as $day)
                        @php
                            $dt = $monthData['dailyTotals'][$day['date_str']] ?? null;
                        @endphp
                        <td style="font-size: 6.8pt; font-weight: bold; color: #059669; background: #fcfdfe;">
                            @if($dt && $dt['has_session'])
                                {{ $dt['present'] }}
                            @else
                                <span style="color: #cbd5e1;">·</span>
                            @endif
                        </td>
                    @endforeach
                    <td colspan="3" style="background: #f1f5f9; font-size: 6.5pt; color: #64748b;">
                        {{ $monthData['totalSessions'] }} Sessions
                    </td>
                </tr>

                {{-- Daily Totals Row (Total Absent) --}}
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td class="td-name" style="font-size: 6.5pt; text-transform: uppercase; color: #475569; background: #f1f5f9;">
                        Daily Absent (&#10007;)
                    </td>
                    @foreach($monthData['allDays'] as $day)
                        @php
                            $dt = $monthData['dailyTotals'][$day['date_str']] ?? null;
                        @endphp
                        <td style="font-size: 6.8pt; font-weight: bold; color: #e11d48; background: #fcfdfe;">
                            @if($dt && $dt['has_session'])
                                {{ $dt['absent'] }}
                            @else
                                <span style="color: #cbd5e1;">·</span>
                            @endif
                        </td>
                    @endforeach
                    <td colspan="3" style="background: #f1f5f9; font-size: 6.5pt; color: #64748b;">
                        Aggregated
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Handwritten Style Ruled Notes & Verification Lines --}}
        <table class="notes-table" style="page-break-inside: avoid;">
            <tr>
                <td style="width: 58%; vertical-align: top; padding-right: 15px;">
                    <div style="font-weight: bold; color: #334155; font-size: 7.2pt; text-transform: uppercase; margin-bottom: 2px;">
                        Instructor Notes & Attendance Remarks:
                    </div>
                    <div class="rule-line"></div>
                    <div class="rule-line"></div>
                    <div class="rule-line"></div>
                </td>
                <td style="width: 42%; vertical-align: top;">
                    <div style="font-weight: bold; color: #334155; font-size: 7.2pt; text-transform: uppercase; margin-bottom: 2px;">
                        Signatures & Official Attestation:
                    </div>
                    <div style="margin-top: 6px;">
                        <div class="signature-line"></div>
                        <div style="font-size: 5.8pt; color: #64748b; text-transform: uppercase; margin-top: 2px;">
                            Class Instructor Signature • Date
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <div class="signature-line"></div>
                        <div style="font-size: 5.8pt; color: #64748b; text-transform: uppercase; margin-top: 2px;">
                            Academic Dean / Quality Verifier • Date
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Legend --}}
        <div class="legend-bar">
            <strong>Register Legend:</strong>
            <span style="color: #059669; font-weight: bold; margin-left: 8px;">&#10003; = Present</span>
            <span style="color: #e11d48; font-weight: bold; margin-left: 8px;">&#10007; = Absent</span>
            <span style="color: #d97706; font-weight: bold; margin-left: 8px;">L = Late Arrival</span>
            <span style="color: #2563eb; font-weight: bold; margin-left: 8px;">E = Apology / Excused</span>
            <span style="color: #94a3b8; margin-left: 8px;">— = Unmarked</span>
            <span style="color: #cbd5e1; margin-left: 8px;">· = No Session Scheduled</span>
        </div>
    @endforeach

    {{-- Footer --}}
    <div class="page-footer">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 60%;">Thinker HUB LMS • Schedule Attendance Register • Page 1</td>
                <td style="width: 40%; text-align: right;">Confidential Academic Record</td>
            </tr>
        </table>
    </div>

</body>
</html>
