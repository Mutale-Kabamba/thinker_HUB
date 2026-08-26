@extends('reports.layout')

@section('title', 'Student Academic Report - ' . $student->name)

@section('content')
    {{-- Student Header Dossier --}}
    <div class="card" style="margin-bottom: 12px;">
        <div class="card-header" style="background: #0f172a; color: #ffffff;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-size: 11pt; font-weight: 800; color: #ffffff;">
                        STUDENT ACADEMIC DOSSIER & PERFORMANCE TRANSCRIPT
                    </td>
                    <td style="text-align: right;">
                        <span class="badge" style="background: #0d9488; color: #ffffff; border: none; font-size: 7.5pt; padding: 3px 10px;">
                            {{ strtoupper($student->track ?? 'LEARNER') }} TRACK
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-body">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                <tr>
                    <td style="width: 25%; padding: 4px 0;"><strong>Student Name:</strong></td>
                    <td style="width: 35%; padding: 4px 0; font-weight: 700; color: #0d9488;">{{ $student->name }}</td>
                    <td style="width: 20%; padding: 4px 0;"><strong>Enrollment Date:</strong></td>
                    <td style="width: 20%; padding: 4px 0;">{{ $student->created_at ? $student->created_at->format('M d, Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Email Address:</strong></td>
                    <td style="padding: 4px 0;">{{ $student->email }}</td>
                    <td style="padding: 4px 0;"><strong>Lifetime XP / Coins:</strong></td>
                    <td style="padding: 4px 0;">{{ number_format($student->lifetime_xp ?? 0) }} XP • {{ number_format($student->spendable_coins ?? 0) }} Coins</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;"><strong>Specialty / Focus:</strong></td>
                    <td style="padding: 4px 0;">{{ $student->specialty ?: 'Web & Software Engineering' }}</td>
                    <td style="padding: 4px 0;"><strong>Active Streak:</strong></td>
                    <td style="padding: 4px 0;">🔥 {{ $student->current_streak ?? 0 }} Days</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Aggregate Summary Stats Tiles --}}
    <table class="stats-table">
        <tr>
            <td style="width: 20%; padding: 0 4px 0 0;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #0d9488;">{{ $overall_stats['enrolled_courses_count'] }}</div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #16a34a;">{{ $overall_stats['completed_courses_count'] }}</div>
                    <div class="stat-label">Completed</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #7c3aed;">{{ $overall_stats['overall_attendance_rate'] }}%</div>
                    <div class="stat-label">Avg Attendance</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #ea580c;">{{ $overall_stats['average_assignment_grade'] ? $overall_stats['average_assignment_grade'] . '%' : 'N/A' }}</div>
                    <div class="stat-label">Avg Assignment</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 0 0 4px;">
                <div class="stat-tile">
                    <div class="stat-val" style="color: #2563eb;">{{ $overall_stats['total_quizzes_passed'] }}</div>
                    <div class="stat-label">Quizzes Passed</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Course Detailed Breakdown --}}
    @foreach ($courses_data as $courseIndex => $cData)
        @if ($courseIndex > 0)
            <div class="page-break"></div>
        @endif

        <div class="card" style="margin-top: 10px;">
            <div class="card-header" style="background: #f1f5f9; border-left: 4px solid #0d9488;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td>
                            <strong style="font-size: 10pt; color: #0f172a;">{{ $cData['course']->title }}</strong>
                            <span style="font-size: 8pt; color: #64748b; margin-left: 8px;">({{ $cData['course']->code ?? 'COURSE' }})</span>
                        </td>
                        <td style="text-align: right;">
                            @if ($cData['is_completed'])
                                <span class="badge badge-success">✓ COMPLETED ({{ $cData['completed_at'] }})</span>
                            @else
                                <span class="badge badge-warning">IN PROGRESS</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-body">
                {{-- Course Summary Metrics Bar --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt;">
                    <tr>
                        <td style="width: 25%; padding: 4px; background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 11pt; font-weight: 800; color: #0f172a;">{{ $cData['attendance_rate'] }}%</div>
                            <div style="font-size: 6.5pt; text-transform: uppercase; color: #64748b; font-weight: 700;">Attendance ({{ $cData['sessions_attended'] }}/{{ $cData['sessions_total'] }})</div>
                        </td>
                        <td style="width: 25%; padding: 4px; background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 11pt; font-weight: 800; color: #0d9488;">{{ $cData['avg_assignment_grade'] ? $cData['avg_assignment_grade'] . '%' : 'N/A' }}</div>
                            <div style="font-size: 6.5pt; text-transform: uppercase; color: #64748b; font-weight: 700;">Assignment Grade</div>
                        </td>
                        <td style="width: 25%; padding: 4px; background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 11pt; font-weight: 800; color: #7c3aed;">{{ count($cData['quizzes_log']) }} Quizzes</div>
                            <div style="font-size: 6.5pt; text-transform: uppercase; color: #64748b; font-weight: 700;">Assessments Evaluated</div>
                        </td>
                        <td style="width: 25%; padding: 4px; background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 11pt; font-weight: 800; color: #2563eb;">{{ $cData['academic_progress_rate'] }}%</div>
                            <div style="font-size: 6.5pt; text-transform: uppercase; color: #64748b; font-weight: 700;">Overall Progress</div>
                        </td>
                    </tr>
                </table>

                {{-- SECTION 1: ATTENDANCE LOG --}}
                <div style="margin-top: 10px; margin-bottom: 6px;">
                    <strong style="font-size: 8.5pt; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                        1. Session Attendance Timeline
                    </strong>
                    <span style="font-size: 7.5pt; color: #64748b; margin-left: 6px;">
                        (Present: {{ $cData['present_count'] }} • Late: {{ $cData['late_count'] }} • Apology: {{ $cData['apology_count'] }} • Absent: {{ $cData['absent_count'] }})
                    </span>
                </div>
                @if (empty($cData['session_log']))
                    <p style="font-size: 7.5pt; color: #94a3b8; font-style: italic;">No sessions scheduled for this course.</p>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Session Topic</th>
                                <th style="width: 20%;">Date & Time</th>
                                <th style="width: 15%; text-align: center;">Status</th>
                                <th style="width: 20%;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cData['session_log'] as $session)
                                <tr>
                                    <td><strong>{{ $session['title'] }}</strong></td>
                                    <td>{{ $session['session_date'] }} {{ $session['time'] ? '• ' . $session['time'] : '' }}</td>
                                    <td style="text-align: center;">
                                        @if ($session['status'] === 'Present')
                                            <span class="badge badge-success">Present</span>
                                        @elseif ($session['status'] === 'Late')
                                            <span class="badge badge-warning">Late</span>
                                        @elseif ($session['status'] === 'Apology')
                                            <span class="badge badge-info">Excused</span>
                                        @elseif ($session['status'] === 'Absent')
                                            <span class="badge badge-danger">Absent</span>
                                        @else
                                            <span class="badge badge-gray">{{ $session['status'] }}</span>
                                        @endif
                                    </td>
                                    <td style="color: #64748b; font-size: 7pt;">{{ $session['remarks'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- SECTION 2: ASSIGNMENTS & PRACTICAL SUBMISSIONS --}}
                <div style="margin-top: 14px; margin-bottom: 6px;">
                    <strong style="font-size: 8.5pt; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                        2. Assignment Submissions & Evaluations
                    </strong>
                </div>
                @if (empty($cData['assignments_log']))
                    <p style="font-size: 7.5pt; color: #94a3b8; font-style: italic;">No assignments created for this course.</p>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Assignment Name</th>
                                <th style="width: 18%;">Due Date</th>
                                <th style="width: 18%;">Submitted</th>
                                <th style="width: 12%; text-align: center;">Timing</th>
                                <th style="width: 10%; text-align: center;">Grade</th>
                                <th style="width: 12%;">Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cData['assignments_log'] as $asg)
                                <tr>
                                    <td><strong>{{ $asg['name'] }}</strong></td>
                                    <td>{{ $asg['due_date'] }}</td>
                                    <td>{{ $asg['submitted_at'] }}</td>
                                    <td style="text-align: center;">
                                        @if ($asg['on_time_status'] === 'On Time')
                                            <span class="badge badge-success">On Time</span>
                                        @elseif ($asg['on_time_status'] === 'Late')
                                            <span class="badge badge-danger">Late</span>
                                        @elseif ($asg['on_time_status'] === 'Overdue')
                                            <span class="badge badge-danger">Overdue</span>
                                        @else
                                            <span class="badge badge-gray">{{ $asg['on_time_status'] }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: {{ $asg['grade'] >= 50 ? '#15803d' : '#b91c1c' }};">
                                        {{ $asg['grade'] !== null ? $asg['grade'] . '%' : '-' }}
                                    </td>
                                    <td style="font-size: 7pt; color: #475569;">
                                        {{ $asg['feedback'] ? Str::limit($asg['feedback'], 60) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- SECTION 3: ASSESSMENTS --}}
                @if (! empty($cData['assessments_log']))
                    <div style="margin-top: 14px; margin-bottom: 6px;">
                        <strong style="font-size: 8.5pt; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                            3. Formal Assessments & Benchmarks
                        </strong>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Assessment Title</th>
                                <th style="width: 20%;">Due Date</th>
                                <th style="width: 15%; text-align: center;">Score</th>
                                <th style="width: 15%; text-align: center;">Result</th>
                                <th style="width: 10%;">Retake</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cData['assessments_log'] as $ass)
                                <tr>
                                    <td><strong>{{ $ass['name'] }}</strong></td>
                                    <td>{{ $ass['due_date'] }}</td>
                                    <td style="text-align: center; font-weight: 700;">
                                        {{ $ass['score'] !== null ? $ass['score'] . '%' : '-' }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if ($ass['score'] === null)
                                            <span class="badge badge-gray">Not Taken</span>
                                        @elseif ($ass['passed'])
                                            <span class="badge badge-success">PASSED</span>
                                        @else
                                            <span class="badge badge-danger">FAILED</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 7pt; color: #64748b;">
                                        {{ $ass['is_retake'] ? '2nd Try' : '1st Try' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- SECTION 4: QUIZZES & ACTUAL ANSWER SHEETS --}}
                <div style="margin-top: 14px; margin-bottom: 6px;">
                    <strong style="font-size: 8.5pt; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                        4. Quizzes & Actual Answer Sheets
                    </strong>
                </div>
                @if (empty($cData['quizzes_log']))
                    <p style="font-size: 7.5pt; color: #94a3b8; font-style: italic;">No quizzes evaluated for this course.</p>
                @else
                    @foreach ($cData['quizzes_log'] as $qIndex => $quiz)
                        <div class="no-break" style="margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
                            <div style="background: #f8fafc; padding: 6px 10px; border-bottom: 1px solid #cbd5e1;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td>
                                            <strong style="font-size: 8.5pt; color: #0f172a;">Quiz: {{ $quiz['title'] }}</strong>
                                            <span style="font-size: 7pt; color: #64748b; margin-left: 8px;">
                                                (Pass Mark: {{ $quiz['passing_score'] }}% • Attempts: {{ $quiz['attempt_count'] }})
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            @if ($quiz['best_score'] !== null)
                                                <strong style="font-size: 9pt; color: {{ $quiz['passed'] ? '#15803d' : '#b91c1c' }};">
                                                    Score: {{ $quiz['best_score'] }}/{{ $quiz['total_points'] }} ({{ $quiz['percentage'] }}%)
                                                </strong>
                                                <span class="badge {{ $quiz['passed'] ? 'badge-success' : 'badge-danger' }}" style="margin-left: 6px;">
                                                    {{ $quiz['passed'] ? 'PASSED' : 'FAILED' }}
                                                </span>
                                            @else
                                                <span class="badge badge-gray">Not Attempted</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            @if (! empty($quiz['answer_sheet']))
                                <div style="padding: 8px 10px;">
                                    @foreach ($quiz['answer_sheet'] as $qItem)
                                        <div class="question-card">
                                            <div class="question-header">
                                                <table style="width: 100%; border-collapse: collapse;">
                                                    <tr>
                                                        <td>
                                                            <span style="color: #0d9488;">Q{{ $qItem['question_number'] }}.</span> {{ $qItem['question_text'] }}
                                                        </td>
                                                        <td style="text-align: right; width: 22%;">
                                                            @if ($qItem['is_correct'])
                                                                <span class="badge badge-success">✓ +{{ $qItem['points_earned'] }}/{{ $qItem['points_available'] }} PTS</span>
                                                            @else
                                                                <span class="badge badge-danger">✗ 0/{{ $qItem['points_available'] }} PTS</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>

                                            {{-- Options and Student Choice --}}
                                            @if ($qItem['type'] === 'multiple_choice' && ! empty($qItem['options']))
                                                <div style="margin-top: 4px;">
                                                    @foreach ($qItem['options'] as $opt)
                                                        @php
                                                            $optClass = 'option-neutral';
                                                            if ($opt['is_correct']) {
                                                                $optClass = 'option-correct';
                                                            } elseif ($opt['is_student_choice'] && ! $opt['is_correct']) {
                                                                $optClass = 'option-wrong-choice';
                                                            }
                                                        @endphp
                                                        <div class="option-row {{ $optClass }}">
                                                            @if ($opt['is_student_choice'])
                                                                <strong>[Student Selected]</strong>
                                                            @endif
                                                            @if ($opt['is_correct'])
                                                                <strong>[Correct Answer]</strong>
                                                            @endif
                                                            {{ $opt['text'] }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div style="font-size: 7.5pt; margin-top: 4px; padding: 4px 8px; background: #f8fafc; border-radius: 4px;">
                                                    <div><strong>Student Written Answer:</strong> {{ $qItem['student_selected_text'] }}</div>
                                                    @if ($qItem['feedback'])
                                                        <div style="color: #0d9488; margin-top: 2px;"><strong>Feedback:</strong> {{ $qItem['feedback'] }}</div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if ($qItem['explanation'])
                                                <div class="explanation-box">
                                                    <strong>Explanation:</strong> {{ $qItem['explanation'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach

    {{-- Official Institutional Verification Block --}}
    <div class="no-break" style="margin-top: 20px; border-top: 2px solid #0d9488; padding-top: 10px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
            <tr>
                <td style="width: 35%; vertical-align: top;">
                    <p style="margin: 0 0 4px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Lead Academic Director</p>
                    <div style="height: 35px; border-bottom: 1px dashed #cbd5e1; width: 85%;"></div>
                    <p style="margin: 3px 0 0; color: #64748b;">Thinker HUB Academic Evaluation Board</p>
                </td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    <div style="display: inline-block; border: 2px solid #0d9488; border-radius: 9999px; width: 60px; height: 60px; line-height: 56px; font-weight: 800; color: #0d9488; font-size: 7pt;">
                        VERIFIED
                    </div>
                </td>
                <td style="width: 35%; text-align: right; vertical-align: top;">
                    <p style="margin: 0 0 4px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Official Registry Seal</p>
                    <div style="height: 35px; border-bottom: 1px dashed #cbd5e1; width: 85%; margin-left: auto;"></div>
                    <p style="margin: 3px 0 0; color: #64748b;">Issued on: {{ now()->format('F d, Y') }}</p>
                </td>
            </tr>
        </table>
    </div>
@endsection
