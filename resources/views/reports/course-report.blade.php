@extends('reports.layout')

@section('title', 'Course Executive Analytics - ' . $course->title)
@section('report_type', 'Course Executive Analytics & Performance Report')

@section('content')
    {{-- Course Metadata Box --}}
    <div class="section-box">
        <div class="section-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-weight: 800; font-size: 8pt; color: #0f172a;">
                        {{ $course->title }} ({{ $course->code ?? 'COURSE' }})
                    </td>
                    <td class="text-right">
                        @if ($intake)
                            <span class="badge badge-info">Cohort: {{ $intake->name }}</span>
                        @else
                            <span class="badge badge-gray">All Enrolled Cohorts</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="section-body">
            <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                <tr>
                    <td style="width: 20%; padding: 2px 0; color: #64748b;"><strong>Instructor(s):</strong></td>
                    <td style="width: 30%; padding: 2px 0; font-weight: 700;">
                        {{ $course->instructors->pluck('name')->join(', ') ?: ($course->course_by ?: 'Faculty Team') }}
                    </td>
                    <td style="width: 20%; padding: 2px 0; color: #64748b;"><strong>Scheduled Sessions:</strong></td>
                    <td style="width: 30%; padding: 2px 0;">{{ $attendance['total_sessions'] ?? 0 }} Sessions</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; color: #64748b;"><strong>Status:</strong></td>
                    <td style="padding: 2px 0;">{{ $course->is_active ? 'Active Curriculum' : 'Archived' }}</td>
                    <td style="padding: 2px 0; color: #64748b;"><strong>Curriculum Tasks:</strong></td>
                    <td style="padding: 2px 0;">{{ $assignments['total'] ?? 0 }} Assignments • {{ $quizzes['total'] ?? 0 }} Quizzes</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Executive Stats Grid --}}
    <table class="stats-grid-table">
        <tr>
            <td style="width: 20%;">
                <div class="stat-value">{{ $total_students }}</div>
                <div class="stat-caption">Total Enrolled</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value" style="color: #16a34a;">{{ $completed_students_count }}</div>
                <div class="stat-caption">Graduated ({{ $completion_rate }}%)</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value">{{ $attendance['rate'] }}%</div>
                <div class="stat-caption">Cohort Attendance</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value">{{ $assignments['average_score'] ? $assignments['average_score'] . '%' : 'N/A' }}</div>
                <div class="stat-caption">Avg Assignment Grade</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value">{{ $quizzes['pass_rate'] ?? 100 }}%</div>
                <div class="stat-caption">Quiz Pass Rate</div>
            </td>
        </tr>
    </table>

    {{-- Attendance & Grade Distribution Matrix --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            {{-- Column 1: Attendance Breakdown --}}
            <td style="width: 49%; vertical-align: top; padding-right: 6px;">
                <div class="section-box" style="margin-bottom: 0;">
                    <div class="section-header">Attendance Composition</div>
                    <div class="section-body">
                        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                            <tr>
                                <td style="padding: 3px 0; width: 45%;"><strong>Present:</strong></td>
                                <td style="padding: 3px 0; width: 25%; text-align: right; font-weight: bold; color: #16a34a;">{{ $attendance['present'] }}</td>
                                <td style="padding: 3px 0; width: 30%; text-align: right;" class="text-muted">
                                    {{ $attendance['total_marked'] > 0 ? round(($attendance['present'] / $attendance['total_marked']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 0;"><strong>Late:</strong></td>
                                <td style="padding: 3px 0; text-align: right; font-weight: bold; color: #d97706;">{{ $attendance['late'] }}</td>
                                <td style="padding: 3px 0; text-align: right;" class="text-muted">
                                    {{ $attendance['total_marked'] > 0 ? round(($attendance['late'] / $attendance['total_marked']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 0;"><strong>Excused:</strong></td>
                                <td style="padding: 3px 0; text-align: right; font-weight: bold; color: #0284c7;">{{ $attendance['apology'] }}</td>
                                <td style="padding: 3px 0; text-align: right;" class="text-muted">
                                    {{ $attendance['total_marked'] > 0 ? round(($attendance['apology'] / $attendance['total_marked']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 0;"><strong>Absent:</strong></td>
                                <td style="padding: 3px 0; text-align: right; font-weight: bold; color: #dc2626;">{{ $attendance['absent'] }}</td>
                                <td style="padding: 3px 0; text-align: right;" class="text-muted">
                                    {{ $attendance['total_marked'] > 0 ? round(($attendance['absent'] / $attendance['total_marked']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>

            {{-- Column 2: Assignment Grade Brackets --}}
            <td style="width: 51%; vertical-align: top; padding-left: 6px;">
                <div class="section-box" style="margin-bottom: 0;">
                    <div class="section-header">Assignment Grade Distribution</div>
                    <div class="section-body">
                        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                            @foreach ($assignments['grade_distribution'] as $bracket => $count)
                                <tr>
                                    <td style="padding: 3px 0; width: 60%;"><strong>{{ $bracket }}:</strong></td>
                                    <td style="padding: 3px 0; width: 40%; text-align: right; font-weight: bold;">{{ $count }} submissions</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Student Performance Matrix Roster --}}
    <div class="section-box">
        <div class="section-header">
            Student Cohort Performance Matrix ({{ count($roster) }} Enrolled)
        </div>
        <div class="section-body" style="padding: 0;">
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Student Name</th>
                        <th style="width: 15%;">Track</th>
                        <th style="width: 15%; text-align: center;">Attendance</th>
                        <th style="width: 15%; text-align: center;">Avg Assignment</th>
                        <th style="width: 15%; text-align: center;">Quizzes</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roster as $s)
                        <tr>
                            <td>
                                <strong>{{ is_array($s) ? ($s['name'] ?? $s['student']?->name ?? 'Student') : ($s->name ?? 'Student') }}</strong>
                                <div class="text-muted" style="font-size: 6.5pt;">{{ is_array($s) ? ($s['email'] ?? $s['student']?->email ?? '') : ($s->email ?? '') }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gray">{{ strtoupper(is_array($s) ? ($s['track'] ?? $s['student']?->track ?? 'LEARNER') : ($s->track ?? 'LEARNER')) }}</span>
                            </td>
                            <td class="text-center">
                                <strong>{{ $s['attendance_rate'] ?? 0 }}%</strong>
                                <div class="text-muted" style="font-size: 6pt;">{{ $s['sessions_attended'] ?? $s['attended_sessions'] ?? 0 }}/{{ $s['sessions_total'] ?? $s['total_sessions'] ?? 0 }}</div>
                            </td>
                            <td class="text-center">
                                @if (isset($s['avg_assignment_grade']) && $s['avg_assignment_grade'] !== null)
                                    <strong>{{ $s['avg_assignment_grade'] }}%</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $s['quizzes_passed'] ?? 0 }}</strong> / {{ $s['total_quizzes'] ?? 0 }}
                            </td>
                            <td class="text-center">
                                @if (!empty($s['is_completed']) || !empty($s['completed']))
                                    <span class="badge badge-success">Completed</span>
                                @else
                                    <span class="badge badge-warning">Active</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 8px;">
                                No students enrolled in this course or cohort.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
