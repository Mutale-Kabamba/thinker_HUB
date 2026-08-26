@extends('reports.layout')

@section('title', 'Cohort Performance Report - ' . $intake->name)
@section('report_type', 'Cohort & Intake Performance Dossier')

@section('content')
    {{-- Intake Metadata Box --}}
    <div class="section-box">
        <div class="section-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-weight: 800; font-size: 8pt; color: #0f172a;">
                        COHORT: {{ $intake->name }} • {{ $course->title }} ({{ $course->code ?? 'COURSE' }})
                    </td>
                    <td class="text-right">
                        <span class="badge badge-info">{{ $intake->is_active ? 'ACTIVE COHORT' : 'CONCLUDED' }}</span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section-body">
            <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                <tr>
                    <td style="width: 18%; padding: 2px 0; color: #64748b;"><strong>Start Date:</strong></td>
                    <td style="width: 32%; padding: 2px 0;">{{ $intake->start_date ? $intake->start_date->format('M d, Y') : '-' }}</td>
                    <td style="width: 18%; padding: 2px 0; color: #64748b;"><strong>End Date:</strong></td>
                    <td style="width: 32%; padding: 2px 0;">{{ $intake->end_date ? $intake->end_date->format('M d, Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; color: #64748b;"><strong>Instructor(s):</strong></td>
                    <td style="padding: 2px 0;">{{ $course->instructors->pluck('name')->join(', ') ?: ($course->course_by ?: 'Faculty Team') }}</td>
                    <td style="padding: 2px 0; color: #64748b;"><strong>Capacity:</strong></td>
                    <td style="padding: 2px 0;">{{ $total_students }} / {{ $intake->capacity ?? 'Unlimited' }} Enrolled</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Executive Stats Grid --}}
    <table class="stats-grid-table">
        <tr>
            <td style="width: 25%;">
                <div class="stat-value">{{ $total_students }}</div>
                <div class="stat-caption">Cohort Size</div>
            </td>
            <td style="width: 25%;">
                <div class="stat-value" style="color: #16a34a;">{{ $completion_rate }}%</div>
                <div class="stat-caption">Graduation Rate</div>
            </td>
            <td style="width: 25%;">
                <div class="stat-value">{{ $attendance['rate'] }}%</div>
                <div class="stat-caption">Attendance Rate</div>
            </td>
            <td style="width: 25%;">
                <div class="stat-value">{{ $assignments['average_score'] ? $assignments['average_score'] . '%' : 'N/A' }}</div>
                <div class="stat-caption">Avg Assignment Grade</div>
            </td>
        </tr>
    </table>

    {{-- Cohort Roster Table --}}
    <div class="section-box">
        <div class="section-header">
            Cohort Student Performance Matrix ({{ count($roster) }} Students)
        </div>
        <div class="section-body" style="padding: 0;">
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Student Name</th>
                        <th style="width: 15%;">Track</th>
                        <th style="width: 15%; text-align: center;">Attendance</th>
                        <th style="width: 15%; text-align: center;">Assignment Avg</th>
                        <th style="width: 15%; text-align: center;">Quizzes</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roster as $s)
                        <tr>
                            <td>
                                <strong>{{ $s['student']->name }}</strong>
                                <div class="text-muted" style="font-size: 6.5pt;">{{ $s['student']->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gray">{{ strtoupper($s['student']->track ?? 'LEARNER') }}</span>
                            </td>
                            <td class="text-center">
                                <strong>{{ $s['attendance_rate'] }}%</strong>
                                <div class="text-muted" style="font-size: 6pt;">{{ $s['sessions_attended'] }}/{{ $s['sessions_total'] }}</div>
                            </td>
                            <td class="text-center">
                                @if ($s['avg_assignment_grade'] !== null)
                                    <strong>{{ $s['avg_assignment_grade'] }}%</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $s['quizzes_passed'] }}</strong> / {{ $s['total_quizzes'] }}
                            </td>
                            <td class="text-center">
                                @if ($s['is_completed'])
                                    <span class="badge badge-success">Completed</span>
                                @else
                                    <span class="badge badge-warning">Active</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 8px;">
                                No students enrolled in this cohort.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
