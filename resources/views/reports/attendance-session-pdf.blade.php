@extends('reports.layout')

@section('title', 'Attendance Register — ' . ($session->title ?: 'Session'))
@section('report_type', 'Official Attendance Register')

@section('content')
    {{-- Session Metadata Panel --}}
    <div class="section-box">
        <div class="section-header">Session & Academic Context</div>
        <div class="section-body">
            <table class="data-table" style="margin-bottom: 0; font-size: 8pt;">
                <tr>
                    <td style="width: 20%; font-weight: 700; color: #475569; background: #f8fafc;">Course:</td>
                    <td style="width: 30%;"><strong>{{ $session->course?->title ?? '—' }}</strong> @if($session->course?->code) <span class="text-muted">({{ $session->course->code }})</span> @endif</td>
                    <td style="width: 20%; font-weight: 700; color: #475569; background: #f8fafc;">Session Date:</td>
                    <td style="width: 30%;"><strong>{{ $session->getEffectiveDate()->format('l, d F Y') }}</strong></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Session Title:</td>
                    <td>{{ $session->title ?: 'Class Session' }}</td>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Scheduled Time:</td>
                    <td>{{ $session->getEffectiveStartTime() }} – {{ $session->getEffectiveEndTime() }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Class / Intake:</td>
                    <td>{{ $session->intake?->name ?? 'All Enrolled Students' }}</td>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Instructor:</td>
                    <td>{{ $session->instructor?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Session Type:</td>
                    <td>
                        <span class="badge" style="background: #f1f5f9; color: #334155;">
                            {{ $session->isOneOnOne() ? '1-on-1 Mentorship' : 'Group Cohort' }}
                        </span>
                    </td>
                    <td style="font-weight: 700; color: #475569; background: #f8fafc;">Status:</td>
                    <td>
                        <span class="badge" style="background: {{ $session->status === 'completed' ? '#ecfdf5' : '#eff6ff' }}; color: {{ $session->status === 'completed' ? '#059669' : '#2563eb' }};">
                            {{ ucfirst($session->status ?? 'Scheduled') }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- KPI Metric Tiles --}}
    <table class="stats-grid-table">
        <tr>
            <td style="width: 17%;">
                <div class="stat-value" style="color: #0f172a;">{{ $summary['total'] }}</div>
                <div class="stat-caption">Registered</div>
            </td>
            <td style="width: 17%;">
                <div class="stat-value" style="color: #059669;">{{ $summary['present'] }}</div>
                <div class="stat-caption">Present</div>
            </td>
            <td style="width: 16%;">
                <div class="stat-value" style="color: #e11d48;">{{ $summary['absent'] }}</div>
                <div class="stat-caption">Absent</div>
            </td>
            <td style="width: 16%;">
                <div class="stat-value" style="color: #d97706;">{{ $summary['late'] }}</div>
                <div class="stat-caption">Late</div>
            </td>
            <td style="width: 17%;">
                <div class="stat-value" style="color: #2563eb;">{{ $summary['apology'] }}</div>
                <div class="stat-caption">Apology</div>
            </td>
            <td style="width: 17%;">
                <div class="stat-value" style="color: {{ $summary['attendance_rate'] >= 75 ? '#059669' : ($summary['attendance_rate'] >= 50 ? '#d97706' : '#e11d48') }};">
                    {{ $summary['attendance_rate'] }}%
                </div>
                <div class="stat-caption">Attendance Rate</div>
            </td>
        </tr>
    </table>

    {{-- Student Register Table --}}
    <div class="section-box">
        <div class="section-header">
            Student Attendance Roster ({{ $records->count() }} Records)
        </div>
        <div class="section-body" style="padding: 0;">
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 28%;">Student Name</th>
                        <th style="width: 25%;">Email</th>
                        <th style="width: 14%; text-align: center;">Status</th>
                        <th style="width: 28%;">Remarks / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $record)
                        @php
                            $status = $record->status;
                            $badgeBg = match ($status) {
                                'present' => '#ecfdf5',
                                'absent' => '#fff1f2',
                                'late' => '#fffbeb',
                                'apology' => '#eff6ff',
                                default => '#f1f5f9',
                            };
                            $badgeColor = match ($status) {
                                'present' => '#059669',
                                'absent' => '#e11d48',
                                'late' => '#d97706',
                                'apology' => '#2563eb',
                                default => '#64748b',
                            };
                            $statusLabel = match ($status) {
                                'present' => 'Present',
                                'absent' => 'Absent',
                                'late' => 'Late',
                                'apology' => 'Apology / Excused',
                                default => ucfirst((string) $status),
                            };
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-size: 7pt;">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $record->student?->name ?? '—' }}</strong>
                                @if(!empty($isPlayItForward) || $record->student?->gender || $record->student?->nrc_passport)
                                    <div style="font-size: 6.5pt; color: #64748b; margin-top: 1px;">
                                        <span>Gender: <strong>{{ $record->student?->gender ?: '—' }}</strong></span>
                                        <span style="color: #cbd5e1; margin: 0 2px;">•</span>
                                        <span>NRC/ID: <strong>{{ $record->student?->nrc_passport ?: '—' }}</strong></span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size: 7pt;">
                                {{ $record->student?->email ?? '—' }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: {{ $badgeBg }}; color: {{ $badgeColor }}; font-weight: 700; padding: 2px 6px;">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td style="font-size: 7pt; color: #475569;">
                                {{ $record->notes ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 15px; color: #94a3b8;">
                                No attendance records found for this session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Official Sign-off Box --}}
    <div style="margin-top: 18px; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 10px 14px; background: #fafbfc; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 2px;">Verification & Attestation</div>
                    <div style="color: #64748b; font-size: 6.8pt; line-height: 1.3;">
                        I hereby confirm that this attendance register accurately reflects student attendance and participation for the specified academic session in accordance with institutional policy.
                    </div>
                </td>
                <td style="width: 40%; vertical-align: bottom; text-align: right;">
                    <div style="display: inline-block; text-align: left; width: 170px;">
                        <div style="border-bottom: 1px solid #94a3b8; height: 26px; margin-bottom: 3px;"></div>
                        <div style="font-size: 6.5pt; color: #64748b; text-transform: uppercase;">
                            Instructor / Proctor Signature • {{ date('d M Y') }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endsection
