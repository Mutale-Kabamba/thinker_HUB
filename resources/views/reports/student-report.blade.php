@extends('reports.layout')

@section('title', 'Student Academic Report - ' . $student->name)
@section('report_type', 'Student Academic Dossier & Transcript')

@section('content')
    {{-- ========================================================================= --}}
    {{-- PAGE 1: STUDENT PROFILE DOSSIER & EXECUTIVE TRANSCRIPT OVERVIEW          --}}
    {{-- ========================================================================= --}}
    <div class="section-box">
        <div class="section-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-weight: 800; font-size: 8.5pt; color: #0f172a;">
                        STUDENT PROFILE & ACADEMIC TRANSCRIPT
                    </td>
                    <td class="text-right">
                        <span class="badge badge-info">{{ strtoupper($student->track ?? 'LEARNER') }} TRACK</span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section-body">
            <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                <tr>
                    <td style="width: 18%; padding: 2px 0; color: #64748b;"><strong>Student Name:</strong></td>
                    <td style="width: 32%; padding: 2px 0; font-weight: 700; color: #0f172a;">{{ $student->name }}</td>
                    <td style="width: 18%; padding: 2px 0; color: #64748b;"><strong>Enrollment Date:</strong></td>
                    <td style="width: 32%; padding: 2px 0;">{{ $student->created_at ? $student->created_at->format('M d, Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; color: #64748b;"><strong>Email Address:</strong></td>
                    <td style="padding: 2px 0;">{{ $student->email }}</td>
                    <td style="padding: 2px 0; color: #64748b;"><strong>XP & Coins:</strong></td>
                    <td style="padding: 2px 0;">{{ number_format($student->lifetime_xp ?? 0) }} XP • {{ number_format($student->spendable_coins ?? 0) }} Coins</td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; color: #64748b;"><strong>Focus / Track:</strong></td>
                    <td style="padding: 2px 0;">{{ $student->specialty ?: 'Software & Systems Engineering' }}</td>
                    <td style="padding: 2px 0; color: #64748b;"><strong>Active Streak:</strong></td>
                    <td style="padding: 2px 0;">🔥 {{ $student->current_streak ?? 0 }} Days</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Aggregate Summary Stats Grid --}}
    <table class="stats-grid-table">
        <tr>
            <td style="width: 20%;">
                <div class="stat-value">{{ $overall_stats['enrolled_courses_count'] }}</div>
                <div class="stat-caption">Enrolled Courses</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value" style="color: #16a34a;">{{ $overall_stats['completed_courses_count'] }}</div>
                <div class="stat-caption">Completed</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value">{{ $overall_stats['overall_attendance_rate'] }}%</div>
                <div class="stat-caption">Attendance Rate</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value">{{ $overall_stats['average_assignment_grade'] ? $overall_stats['average_assignment_grade'] . '%' : 'N/A' }}</div>
                <div class="stat-caption">Avg Assignment</div>
            </td>
            <td style="width: 20%;">
                <div class="stat-value">{{ $overall_stats['total_quizzes_passed'] }}</div>
                <div class="stat-caption">Quizzes Passed</div>
            </td>
        </tr>
    </table>

    {{-- Enrolled Curriculum Overview Table --}}
    <div class="section-box">
        <div class="section-header">
            Enrolled Courses & Academic Standing Summary
        </div>
        <div class="section-body" style="padding: 0;">
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Course Curriculum</th>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 15%; text-align: center;">Attendance</th>
                        <th style="width: 15%; text-align: center;">Assignment Avg</th>
                        <th style="width: 15%; text-align: center;">Progress</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses_data as $cOverview)
                        <tr>
                            <td>
                                <strong>{{ $cOverview['course']->title }}</strong>
                            </td>
                            <td>{{ $cOverview['course']->code ?? 'N/A' }}</td>
                            <td class="text-center">
                                <strong>{{ $cOverview['attendance_rate'] }}%</strong>
                                <span class="text-muted" style="font-size: 6pt;">({{ $cOverview['sessions_attended'] }}/{{ $cOverview['sessions_total'] }})</span>
                            </td>
                            <td class="text-center">
                                @if ($cOverview['avg_assignment_grade'] !== null)
                                    <strong>{{ $cOverview['avg_assignment_grade'] }}%</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $cOverview['academic_progress_rate'] }}%</strong>
                            </td>
                            <td class="text-center">
                                @if ($cOverview['is_completed'])
                                    <span class="badge badge-success">Completed</span>
                                @else
                                    <span class="badge badge-warning">Active</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- FOR EACH COURSE: DEDICATED PAGES FOR ATTENDANCE, ASSIGNMENTS, ETC.        --}}
    {{-- ========================================================================= --}}
    @foreach ($courses_data as $cData)

        {{-- --------------------------------------------------------------------- --}}
        {{-- DEDICATED PAGE: ATTENDANCE LOG                                        --}}
        {{-- --------------------------------------------------------------------- --}}
        @if ($options['include_attendance_log'] ?? true)
            <div class="page-break"></div>

            <div class="section-box">
                <div class="section-header" style="background: #f1f5f9; border-left: 3px solid #0f172a;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td>
                                <strong style="font-size: 8.5pt; color: #0f172a;">ATTENDANCE LOG: {{ $cData['course']->title }}</strong>
                                <span class="text-muted" style="font-size: 7pt; margin-left: 4px;">({{ $cData['course']->code ?? 'COURSE' }})</span>
                            </td>
                            <td class="text-right">
                                <span class="badge badge-info">Student: {{ $student->name }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="section-body">
                    {{-- Summary stats strip --}}
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7pt;">
                        <tr>
                            <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                <div style="font-size: 10pt; font-weight: 800; color: #0f172a;">{{ $cData['attendance_rate'] }}%</div>
                                <div class="stat-caption">Attendance Rate ({{ $cData['sessions_attended'] }}/{{ $cData['sessions_total'] }})</div>
                            </td>
                            <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                <div style="font-size: 10pt; font-weight: 800; color: #16a34a;">{{ $cData['present_count'] }}</div>
                                <div class="stat-caption">Sessions Present</div>
                            </td>
                            <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                <div style="font-size: 10pt; font-weight: 800; color: #d97706;">{{ $cData['late_count'] }} • {{ $cData['apology_count'] }}</div>
                                <div class="stat-caption">Late • Excused</div>
                            </td>
                            <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                <div style="font-size: 10pt; font-weight: 800; color: #dc2626;">{{ $cData['absent_count'] }}</div>
                                <div class="stat-caption">Unexcused Absences</div>
                            </td>
                        </tr>
                    </table>

                    @if (empty($cData['session_log']))
                        <p style="font-size: 7.5pt; color: #94a3b8; font-style: italic; margin: 6px 0;">No scheduled sessions recorded for this course.</p>
                    @else
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Session Topic / Lesson</th>
                                    <th style="width: 25%;">Scheduled Date & Time</th>
                                    <th style="width: 15%; text-align: center;">Attendance Status</th>
                                    <th style="width: 20%;">Instructor Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cData['session_log'] as $session)
                                    <tr>
                                        <td><strong>{{ $session['title'] }}</strong></td>
                                        <td>{{ $session['session_date'] }} {{ $session['time'] ? '• ' . $session['time'] : '' }}</td>
                                        <td class="text-center">
                                            @if ($session['status'] === 'Present')
                                                <span class="badge badge-success">Present</span>
                                            @elseif ($session['status'] === 'Late')
                                                <span class="badge badge-warning">Late</span>
                                            @elseif ($session['status'] === 'Apology')
                                                <span class="badge badge-info">Excused</span>
                                            @else
                                                <span class="badge badge-danger">Absent</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $session['remarks'] ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endif

        {{-- --------------------------------------------------------------------- --}}
        {{-- DEDICATED PAGE: ASSIGNMENT GRADES & EVALUATIONS                       --}}
        {{-- --------------------------------------------------------------------- --}}
        <div class="page-break"></div>

        <div class="section-box">
            <div class="section-header" style="background: #f1f5f9; border-left: 3px solid #0f172a;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td>
                            <strong style="font-size: 8.5pt; color: #0f172a;">ASSIGNMENT GRADES & EVALUATIONS: {{ $cData['course']->title }}</strong>
                            <span class="text-muted" style="font-size: 7pt; margin-left: 4px;">({{ $cData['course']->code ?? 'COURSE' }})</span>
                        </td>
                        <td class="text-right">
                            <span class="badge badge-info">Student: {{ $student->name }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="section-body">
                {{-- Summary stats strip --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7pt;">
                    <tr>
                        <td style="width: 33.3%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 10pt; font-weight: 800; color: #0f172a;">
                                {{ $cData['avg_assignment_grade'] !== null ? $cData['avg_assignment_grade'] . '%' : 'N/A' }}
                            </div>
                            <div class="stat-caption">Average Assignment Score</div>
                        </td>
                        <td style="width: 33.3%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 10pt; font-weight: 800; color: #16a34a;">
                                {{ count($cData['assignments_log']) }}
                            </div>
                            <div class="stat-caption">Total Assignments</div>
                        </td>
                        <td style="width: 33.3%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 10pt; font-weight: 800; color: #0284c7;">
                                {{ collect($cData['assignments_log'])->where('status', 'Graded')->count() }} / {{ count($cData['assignments_log']) }}
                            </div>
                            <div class="stat-caption">Graded Evaluations</div>
                        </td>
                    </tr>
                </table>

                @if (empty($cData['assignments_log']))
                    <p style="font-size: 7.5pt; color: #94a3b8; font-style: italic; margin: 6px 0;">No assignments assigned for this course curriculum.</p>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Assignment Name</th>
                                <th style="width: 15%;">Due Date</th>
                                <th style="width: 18%;">Submitted At</th>
                                <th style="width: 12%; text-align: center;">Score</th>
                                <th style="width: 12%; text-align: center;">Timing</th>
                                <th style="width: 13%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cData['assignments_log'] as $assign)
                                <tr>
                                    <td>
                                        <strong>{{ $assign['name'] }}</strong>
                                        @if ($assign['feedback'])
                                            <div class="text-muted" style="font-size: 6.5pt; margin-top: 1px;">
                                                Feedback: <em>{{ $assign['feedback'] }}</em>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $assign['due_date'] }}</td>
                                    <td>{{ $assign['submitted_at'] ?: 'Not Submitted' }}</td>
                                    <td class="text-center">
                                        @if ($assign['grade'] !== null)
                                            <strong style="font-size: 8pt;">{{ $assign['grade'] }}%</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($assign['on_time_status'] === 'On Time')
                                            <span class="badge badge-success">On Time</span>
                                        @elseif ($assign['on_time_status'] === 'Late')
                                            <span class="badge badge-danger">Late</span>
                                        @elseif ($assign['on_time_status'] === 'Overdue')
                                            <span class="badge badge-danger">Overdue</span>
                                        @else
                                            <span class="badge badge-gray">{{ $assign['on_time_status'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($assign['status'] === 'Graded')
                                            <span class="badge badge-success">Graded</span>
                                        @elseif ($assign['status'] === 'Submitted')
                                            <span class="badge badge-info">Submitted</span>
                                        @else
                                            <span class="badge badge-gray">{{ $assign['status'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- --------------------------------------------------------------------- --}}
        {{-- DEDICATED PAGE: FORMAL ASSESSMENTS (if assessments exist)             --}}
        {{-- --------------------------------------------------------------------- --}}
        @if (!empty($cData['assessments_log']))
            <div class="page-break"></div>

            <div class="section-box">
                <div class="section-header" style="background: #f1f5f9; border-left: 3px solid #0f172a;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td>
                                <strong style="font-size: 8.5pt; color: #0f172a;">FORMAL ASSESSMENTS & BENCHMARKS: {{ $cData['course']->title }}</strong>
                                <span class="text-muted" style="font-size: 7pt; margin-left: 4px;">({{ $cData['course']->code ?? 'COURSE' }})</span>
                            </td>
                            <td class="text-right">
                                <span class="badge badge-info">Student: {{ $student->name }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="section-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Assessment Benchmark</th>
                                <th style="width: 25%;">Due Date / Submission</th>
                                <th style="width: 15%; text-align: center;">Passing Score</th>
                                <th style="width: 10%; text-align: center;">Score</th>
                                <th style="width: 10%; text-align: center;">Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cData['assessments_log'] as $assmt)
                                <tr>
                                    <td>
                                        <strong>{{ $assmt['name'] }}</strong>
                                        @if ($assmt['feedback'])
                                            <div class="text-muted" style="font-size: 6.5pt; margin-top: 1px;">
                                                Feedback: <em>{{ $assmt['feedback'] }}</em>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $assmt['due_date'] }}</td>
                                    <td class="text-center">{{ $assmt['passing_score'] }}%</td>
                                    <td class="text-center">
                                        @if ($assmt['score'] !== null)
                                            <strong style="font-size: 8pt;">{{ $assmt['score'] }}%</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($assmt['passed'])
                                            <span class="badge badge-success">Passed</span>
                                        @elseif ($assmt['score'] !== null)
                                            <span class="badge badge-danger">Retake Required</span>
                                        @else
                                            <span class="badge badge-gray">{{ $assmt['status'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- --------------------------------------------------------------------- --}}
        {{-- DEDICATED PAGES: QUIZ EVALUATIONS & ANSWER SHEETS (PER QUIZ PER PAGE) --}}
        {{-- --------------------------------------------------------------------- --}}
        @if (($options['include_answer_sheets'] ?? true) && !empty($cData['quizzes_log']))
            @foreach ($cData['quizzes_log'] as $qIndex => $qLog)
                <div class="page-break"></div>

                <div class="section-box">
                    <div class="section-header" style="background: #f1f5f9; border-left: 3px solid #0f172a;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td>
                                    <strong style="font-size: 8.5pt; color: #0f172a;">
                                        QUIZ ANSWER SHEET: {{ $qLog['title'] }}
                                    </strong>
                                    <span class="text-muted" style="font-size: 7pt; margin-left: 4px;">
                                        ({{ $cData['course']->title }})
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="badge badge-info">Student: {{ $student->name }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="section-body">
                        {{-- Quiz Performance KPI Bar --}}
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7pt;">
                            <tr>
                                <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                    <div style="font-size: 10pt; font-weight: 800; color: #0f172a;">
                                        {{ $qLog['best_score'] ?? $qLog['percentage'] }} / {{ $qLog['total_points'] ?? 100 }}
                                    </div>
                                    <div class="stat-caption">Points Earned</div>
                                </td>
                                <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                    <div style="font-size: 10pt; font-weight: 800; color: #0f172a;">
                                        {{ $qLog['percentage'] }}%
                                    </div>
                                    <div class="stat-caption">Overall Percentage</div>
                                </td>
                                <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                    <div style="font-size: 10pt; font-weight: 800; color: #64748b;">
                                        {{ $qLog['passing_score'] }}%
                                    </div>
                                    <div class="stat-caption">Passing Benchmark</div>
                                </td>
                                <td style="width: 25%; padding: 4px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                                    <div style="margin-top: 2px;">
                                        @if ($qLog['passed'])
                                            <span class="badge badge-success" style="font-size: 7.5pt; padding: 2px 8px;">✓ PASSED</span>
                                        @else
                                            <span class="badge badge-danger" style="font-size: 7.5pt; padding: 2px 8px;">✗ FAILED</span>
                                        @endif
                                    </div>
                                    <div class="stat-caption" style="margin-top: 4px;">{{ $qLog['completed_at'] ?: 'Attempt Logged' }}</div>
                                </td>
                            </tr>
                        </table>

                        {{-- Full Questions & Official Answer Sheet --}}
                        <div style="margin-top: 6px;">
                            @foreach ($qLog['answer_sheet'] as $idx => $sheet)
                                <div class="question-item">
                                    <div class="question-title-row">
                                        Q{{ $sheet['question_number'] }}. {{ $sheet['question_text'] }}
                                        <span class="text-muted" style="font-weight: normal; font-size: 6.5pt;">
                                            [{{ $sheet['points_earned'] }}/{{ $sheet['points_available'] }} pts]
                                        </span>
                                    </div>

                                    @if (!empty($sheet['options']))
                                        <table class="option-list">
                                            @foreach ($sheet['options'] as $opt)
                                                @php
                                                    $isStudentChoice = $opt['is_student_choice'];
                                                    $isCorrect = $opt['is_correct'];
                                                @endphp
                                                <tr>
                                                    <td style="width: 18px; font-weight: bold; text-align: center;">
                                                        @if ($isCorrect)
                                                            <span style="color: #15803d;">[✓]</span>
                                                        @elseif ($isStudentChoice && !$isCorrect)
                                                            <span style="color: #b91c1c;">[✗]</span>
                                                        @else
                                                            <span style="color: #94a3b8;">[ ]</span>
                                                        @endif
                                                    </td>
                                                    <td class="{{ $isCorrect ? 'opt-correct' : ($isStudentChoice ? 'opt-wrong' : 'opt-neutral') }}">
                                                        {{ $opt['text'] }}
                                                        @if ($isCorrect && $isStudentChoice)
                                                            <span style="font-size: 6pt; color: #15803d; font-weight: bold;">(Correct Student Selection)</span>
                                                        @elseif ($isStudentChoice && !$isCorrect)
                                                            <span style="font-size: 6pt; color: #b91c1c; font-weight: bold;">(Student Choice - Incorrect)</span>
                                                        @elseif ($isCorrect && !$isStudentChoice)
                                                            <span style="font-size: 6pt; color: #15803d; font-weight: bold;">(Correct Answer)</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    @elseif ($sheet['student_selected_text'])
                                        <div style="font-size: 7pt; margin-top: 2px; padding: 2px 4px; background: #f8fafc; border-radius: 3px;">
                                            <strong>Student Written Response:</strong> {{ $sheet['student_selected_text'] }}
                                        </div>
                                    @endif

                                    @if ($sheet['explanation'])
                                        <div class="explanation-text">
                                            <strong>Key Concept / Explanation:</strong> {{ $sheet['explanation'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

    @endforeach
@endsection
