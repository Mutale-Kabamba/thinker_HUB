@extends('reports.layout')

@section('title', 'Cumulative Attendance — ' . $course->title)
@section('report_type', 'Course Attendance Matrix')

@section('content')
    <div class="section-box">
        <div class="section-header">Course & Cohort Summary</div>
        <div class="section-body">
            <table class="data-table" style="margin-bottom: 0; font-size: 8pt;">
                <tr>
                    <td style="width: 15%; font-weight: 700; color: #475569; background: #f8fafc;">Course:</td>
                    <td style="width: 35%;"><strong>{{ $course->title }}</strong> @if($course->code) <span class="text-muted">({{ $course->code }})</span> @endif</td>
                    <td style="width: 15%; font-weight: 700; color: #475569; background: #f8fafc;">Class / Intake:</td>
                    <td style="width: 35%;">{{ $intake?->name ?? 'All Enrolled Cohorts' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Total Sessions:</td>
                    <td><strong>{{ $totalSessions }}</strong> scheduled sessions</td>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Total Students:</td>
                    <td><strong>{{ $totalStudents }}</strong> enrolled students</td>
                </tr>
                <tr>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Overall Attendance:</td>
                    <td colspan="3">
                        <strong style="color: {{ $overallRate >= 75 ? '#059669' : ($overallRate >= 50 ? '#d97706' : '#e11d48') }}; font-size: 9.5pt;">
                            {{ $overallRate }}%
                        </strong>
                        <span class="text-muted" style="font-size: 7pt; margin-left: 8px;">(Aggregated across all scheduled sessions)</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section-box">
        <div class="section-header">
            Attendance Matrix (Students × Sessions)
        </div>
        <div class="section-body" style="padding: 0; overflow-x: auto;">
            <table class="data-table" style="margin-bottom: 0; font-size: 6.8pt;">
                <thead>
                    <tr>
                        <th style="width: 3%; text-align: center;">#</th>
                        <th style="width: 22%;">Student Name</th>
                        @foreach($sessions as $s)
                            <th style="text-align: center; white-space: nowrap; font-size: 6pt;">
                                {{ $s->getEffectiveDate()->format('m/d') }}<br>
                                <span style="font-size: 5.5pt; color: #64748b; font-weight: normal;">{{ \Illuminate\Support\Str::limit($s->title ?: 'Session', 10) }}</span>
                            </th>
                        @endforeach
                        <th style="text-align: center; width: 6%;">Attended</th>
                        <th style="text-align: center; width: 6%;">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matrix as $idx => $row)
                        <tr>
                            <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $row['student']->name }}</strong>
                                <div style="font-size: 5.8pt; color: #64748b;">
                                    {{ $row['student']->email }}
                                    @if(!empty($isPlayItForward) || $row['student']->gender || $row['student']->nrc_passport)
                                        • {{ $row['student']->gender ?: '—' }} • NRC: {{ $row['student']->nrc_passport ?: '—' }}
                                    @endif
                                </div>
                            </td>
                            @foreach($sessions as $s)
                                @php
                                    $st = $row['statuses'][$s->id] ?? 'unmarked';
                                    $cellBg = match ($st) {
                                        'present' => '#ecfdf5',
                                        'late' => '#fffbeb',
                                        'apology' => '#eff6ff',
                                        'absent' => '#fff1f2',
                                        default => '#ffffff',
                                    };
                                    $cellText = match ($st) {
                                        'present' => 'P',
                                        'late' => 'L',
                                        'apology' => 'E',
                                        'absent' => 'A',
                                        default => '—',
                                    };
                                    $cellColor = match ($st) {
                                        'present' => '#059669',
                                        'late' => '#d97706',
                                        'apology' => '#2563eb',
                                        'absent' => '#e11d48',
                                        default => '#94a3b8',
                                    };
                                @endphp
                                <td style="text-align: center; background: {{ $cellBg }}; color: {{ $cellColor }}; font-weight: 700;">
                                    {{ $cellText }}
                                </td>
                            @endforeach
                            <td style="text-align: center; font-weight: 700;">
                                {{ $row['attended'] }} / {{ $sessions->count() }}
                            </td>
                            <td style="text-align: center; font-weight: 800; color: {{ $row['rate'] >= 75 ? '#059669' : ($row['rate'] >= 50 ? '#d97706' : '#e11d48') }};">
                                {{ $row['rate'] }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $sessions->count() + 4 }}" style="text-align: center; padding: 12px; color: #94a3b8;">
                                No students enrolled or sessions recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 6px; font-size: 6.5pt; color: #64748b;">
        <strong>Legend:</strong>
        <span style="color: #059669; font-weight: 700; margin-left: 6px;">P = Present</span>
        <span style="color: #d97706; font-weight: 700; margin-left: 6px;">L = Late</span>
        <span style="color: #2563eb; font-weight: 700; margin-left: 6px;">E = Apology / Excused</span>
        <span style="color: #e11d48; font-weight: 700; margin-left: 6px;">A = Absent</span>
        <span style="color: #94a3b8; margin-left: 6px;">— = Unmarked / Not Enrolled</span>
    </div>
@endsection
