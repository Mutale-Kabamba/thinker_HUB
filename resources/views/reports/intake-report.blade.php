@extends('reports.layout')

@section('title', 'Cohort Performance Report - ' . ($intake->name ?? 'Cohort'))

@section('content')
    {{-- Intake Header Executive Dossier --}}
    <div class="card" style="margin-bottom: 12px;">
        <div class="card-header" style="background: #0f172a; color: #ffffff;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-size: 11pt; font-weight: 800; color: #ffffff;">
                        COHORT / INTAKE ACADEMIC EVALUATION REPORT
                    </td>
                    <td style="text-align: right;">
                        <span class="badge" style="background: #7c3aed; color: #ffffff; border: none; font-size: 7.5pt; padding: 3px 10px;">
                            {{ $intake->name ?? 'COHORT' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-body">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr>
                    <td style="width: 25%; padding: 4px 0;"><strong>Course:</strong></td>
                    <td style="width: 35%; padding: 4px 0; font-weight: 700; color: #0d9488;">{{ $course->title }} ({{ $course->code }})</td>
                    <td style="width: 20%; padding: 4px 0;"><strong>Start Date:</strong></td>
                    <td style="width: 20%; padding: 4px 0;">{{ $intake->start_date ? \Carbon\Carbon::parse($intake->start_date)->format('M d, Y') : 'TBD' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Cohort Title:</strong></td>
                    <td style="padding: 4px 0;">{{ $intake->name }}</td>
                    <td style="padding: 4px 0;"><strong>End / Graduation:</strong></td>
                    <td style="padding: 4px 0;">{{ $intake->end_date ? \Carbon\Carbon::parse($intake->end_date)->format('M d, Y') : 'Ongoing' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Assigned Instructors:</strong></td>
                    <td style="padding: 4px 0;">
                        {{ $course->instructors->pluck('name')->implode(', ') ?: 'Thinker HUB Faculty' }}
                    </td>
                    <td style="padding: 4px 0;"><strong>Intake Status:</strong></td>
                    <td style="padding: 4px 0;">
                        <span class="badge {{ $intake->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $intake->is_active ? 'ACTIVE' : 'COMPLETED / ARCHIVED' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Aggregate Summary Stats Tiles --}}
    <table class="stats-table">
        <tr>
            <td style="width: 20%; padding: 0 4px 0 0;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #0d9488;">{{ $total_students }}</div>
                    <div class="stat-label">Cohort Students</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #16a34a;">{{ $completion_rate }}%</div>
                    <div class="stat-label">Graduated ({{ $completed_students_count }})</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #7c3aed;">{{ $attendance['rate'] }}%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #ea580c;">{{ $assignments['average_score'] ? $assignments['average_score'] . '%' : 'N/A' }}</div>
                    <div class="stat-label">Avg Assignment Grade</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 0 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #2563eb;">{{ $quizzes['pass_rate'] !== null ? $quizzes['pass_rate'] . '%' : 'N/A' }}</div>
                    <div class="stat-label">Quiz Mastery</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Comprehensive Student Roster Matrix --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-size: 9pt; font-weight: 700; color: #0f172a;">
                        COHORT MEMBER PERFORMANCE MATRIX
                    </td>
                    <td style="text-align: right; font-size: 7pt; color: #64748b;">
                        {{ count($roster) }} Students
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-body" style="padding: 0;">
            @if (empty($roster))
                <p style="padding: 12px; font-size: 8pt; color: #94a3b8; font-style: italic; margin: 0;">No students enrolled in this cohort.</p>
            @else
                <table class="data-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Student Name</th>
                            <th style="width: 12%;">Track</th>
                            <th style="width: 14%; text-align: center;">Attendance</th>
                            <th style="width: 14%; text-align: center;">Assignments</th>
                            <th style="width: 13%; text-align: center;">Avg Grade</th>
                            <th style="width: 12%; text-align: center;">Quizzes</th>
                            <th style="width: 10%; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roster as $st)
                            <tr>
                                <td>
                                    <strong>{{ $st['name'] }}</strong>
                                    <div style="font-size: 6.5pt; color: #64748b;">{{ $st['email'] }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $st['track'] }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <strong style="color: {{ $st['attendance_rate'] >= 75 ? '#15803d' : '#b91c1c' }};">
                                        {{ $st['attendance_rate'] }}%
                                    </strong>
                                    <div style="font-size: 6.5pt; color: #64748b;">({{ $st['attended_sessions'] }}/{{ $st['total_sessions'] }})</div>
                                </td>
                                <td style="text-align: center;">
                                    <strong>{{ $st['assignments_submitted'] }}</strong>
                                </td>
                                <td style="text-align: center; font-weight: 700; color: {{ ($st['avg_assignment_grade'] ?? 0) >= 50 ? '#15803d' : '#b91c1c' }};">
                                    {{ $st['avg_assignment_grade'] !== null ? $st['avg_assignment_grade'] . '%' : '-' }}
                                </td>
                                <td style="text-align: center;">
                                    <strong>{{ $st['quizzes_passed'] }} / {{ $st['total_quizzes'] }}</strong>
                                </td>
                                <td style="text-align: center;">
                                    @if ($st['completed'])
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-warning">In Progress</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Official Verification Block --}}
    <div class="no-break" style="margin-top: 20px; border-top: 2px solid #0d9488; padding-top: 10px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
            <tr>
                <td style="width: 35%; vertical-align: top;">
                    <p style="margin: 0 0 4px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Cohort Coordinator</p>
                    <div style="height: 35px; border-bottom: 1px dashed #cbd5e1; width: 85%;"></div>
                    <p style="margin: 3px 0 0; color: #64748b;">Thinker HUB Program Operations</p>
                </td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    <div style="display: inline-block; border: 2px solid #0d9488; border-radius: 9999px; width: 60px; height: 60px; line-height: 56px; font-weight: 800; color: #0d9488; font-size: 7pt;">
                        VERIFIED
                    </div>
                </td>
                <td style="width: 35%; text-align: right; vertical-align: top;">
                    <p style="margin: 0 0 4px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Official Registry Seal</p>
                    <div style="height: 35px; border-bottom: 1px dashed #cbd5e1; width: 85%; margin-left: auto;"></div>
                    <p style="margin: 3px 0 0; color: #64748b;">Generated on: {{ now()->format('F d, Y') }}</p>
                </td>
            </tr>
        </table>
    </div>
@endsection
