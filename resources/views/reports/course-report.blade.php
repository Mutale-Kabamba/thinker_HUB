@extends('reports.layout')

@section('title', 'Course Analytics & Performance Report - ' . $course->title)

@section('content')
    {{-- Course Header Executive Dossier --}}
    <div class="card" style="margin-bottom: 12px;">
        <div class="card-header" style="background: #0f172a; color: #ffffff;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-size: 11pt; font-weight: 800; color: #ffffff;">
                        COURSE EXECUTIVE ANALYTICS & COHORT PERFORMANCE REPORT
                    </td>
                    <td style="text-align: right;">
                        <span class="badge" style="background: #0d9488; color: #ffffff; border: none; font-size: 7.5pt; padding: 3px 10px;">
                            {{ $course->code ?? 'COURSE' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-body">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr>
                    <td style="width: 25%; padding: 4px 0;"><strong>Course Title:</strong></td>
                    <td style="width: 35%; padding: 4px 0; font-weight: 700; color: #0d9488;">{{ $course->title }}</td>
                    <td style="width: 20%; padding: 4px 0;"><strong>Offering Mode:</strong></td>
                    <td style="width: 20%; padding: 4px 0;">{{ ucfirst($course->offering_mode ?? 'Cohort-Based') }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Assigned Instructors:</strong></td>
                    <td style="padding: 4px 0;">
                        {{ $course->instructors->pluck('name')->implode(', ') ?: 'Department Academic Faculty' }}
                    </td>
                    <td style="padding: 4px 0;"><strong>Cohort / Intake Scope:</strong></td>
                    <td style="padding: 4px 0;">{{ $intake?->name ?: 'All Cohorts / Global' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Curriculum Timeline:</strong></td>
                    <td style="padding: 4px 0;">{{ $course->timeline ?: 'Self-Paced / 6 Weeks' }}</td>
                    <td style="padding: 4px 0;"><strong>Course Status:</strong></td>
                    <td style="padding: 4px 0;">
                        <span class="badge {{ $course->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $course->is_active ? 'ACTIVE ENROLLMENT' : 'ARCHIVED' }}
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
                    <div class="stat-label">Enrolled Students</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #16a34a;">{{ $completion_rate }}%</div>
                    <div class="stat-label">Completion ({{ $completed_students_count }})</div>
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
                    <div class="stat-label">Quiz Pass Rate</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Visual Analytics & Performance Distribution Cards --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px;">
        <tr>
            {{-- Attendance Distribution Box --}}
            <td style="width: 50%; vertical-align: top; padding-right: 6px;">
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="font-size: 8.5pt;">
                        SESSION ATTENDANCE COMPOSITION
                    </div>
                    <div class="card-body" style="font-size: 8pt;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 6px;">
                            <tr>
                                <td><span class="badge badge-success">Present</span> {{ $attendance['present'] }} sessions</td>
                                <td><span class="badge badge-warning">Late</span> {{ $attendance['late'] }} sessions</td>
                            </tr>
                            <tr>
                                <td style="padding-top: 4px;"><span class="badge badge-info">Excused</span> {{ $attendance['apology'] }} sessions</td>
                                <td style="padding-top: 4px;"><span class="badge badge-danger">Absent</span> {{ $attendance['absent'] }} sessions</td>
                            </tr>
                        </table>

                        <div style="margin-top: 8px;">
                            <div style="font-size: 7pt; font-weight: 700; color: #64748b; margin-bottom: 3px;">
                                OVERALL ATTENDANCE BENCHMARK ({{ $attendance['rate'] }}%)
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: {{ $attendance['rate'] }}%; background: #0d9488;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>

            {{-- Grade Distribution Histogram --}}
            <td style="width: 50%; vertical-align: top; padding-left: 6px;">
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="font-size: 8.5pt;">
                        ASSIGNMENT GRADE DISTRIBUTION
                    </div>
                    <div class="card-body" style="font-size: 7.5pt;">
                        @php
                            $totalGraded = array_sum($assignments['grade_distribution']);
                        @endphp
                        @foreach ($assignments['grade_distribution'] as $band => $count)
                            @php
                                $percent = $totalGraded > 0 ? round(($count / $totalGraded) * 100) : 0;
                                $barColor = match(substr($band, 0, 1)) {
                                    'A' => '#16a34a',
                                    'B' => '#2563eb',
                                    'C' => '#f59e0b',
                                    default => '#ef4444',
                                };
                            @endphp
                            <div style="margin-bottom: 5px;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 7pt; margin-bottom: 2px;">
                                    <tr>
                                        <td><strong>{{ $band }}</strong></td>
                                        <td style="text-align: right; color: #64748b;">{{ $count }} submissions ({{ $percent }}%)</td>
                                    </tr>
                                </table>
                                <div class="progress-bar-container" style="height: 5px;">
                                    <div class="progress-bar-fill" style="width: {{ $percent }}%; background: {{ $barColor }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
                        STUDENT COHORT PERFORMANCE MATRIX ({{ count($roster) }} Enrolled)
                    </td>
                    <td style="text-align: right; font-size: 7pt; color: #64748b;">
                        Sorted by Student Record
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-body" style="padding: 0;">
            @if (empty($roster))
                <p style="padding: 12px; font-size: 8pt; color: #94a3b8; font-style: italic; margin: 0;">No students enrolled in this course scope.</p>
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

    {{-- Official Institutional Verification Block --}}
    <div class="no-break" style="margin-top: 20px; border-top: 2px solid #0d9488; padding-top: 10px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
            <tr>
                <td style="width: 35%; vertical-align: top;">
                    <p style="margin: 0 0 4px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Dean of Academic Affairs</p>
                    <div style="height: 35px; border-bottom: 1px dashed #cbd5e1; width: 85%;"></div>
                    <p style="margin: 3px 0 0; color: #64748b;">Thinker HUB Institutional Board</p>
                </td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    <div style="display: inline-block; border: 2px solid #0d9488; border-radius: 9999px; width: 60px; height: 60px; line-height: 56px; font-weight: 800; color: #0d9488; font-size: 7pt;">
                        VERIFIED
                    </div>
                </td>
                <td style="width: 35%; text-align: right; vertical-align: top;">
                    <p style="margin: 0 0 4px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Registry seal & Date</p>
                    <div style="height: 35px; border-bottom: 1px dashed #cbd5e1; width: 85%; margin-left: auto;"></div>
                    <p style="margin: 3px 0 0; color: #64748b;">Generated on: {{ now()->format('F d, Y') }}</p>
                </td>
            </tr>
        </table>
    </div>
@endsection
