<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReportGenerationService
{
    /**
     * Generate and retrieve full data array for Student Performance Dossier / Academic Transcript.
     */
    public function getStudentAcademicData(User $student, ?Course $scopedCourse = null, array $options = []): array
    {
        $includeAnswerSheets = $options['include_answer_sheets'] ?? true;
        $includeAttendanceLog = $options['include_attendance_log'] ?? true;

        $enrollmentsQuery = Enrollment::query()
            ->with(['course.instructors', 'course.intakes'])
            ->where('user_id', $student->id);

        if ($scopedCourse) {
            $enrollmentsQuery->where('course_id', $scopedCourse->id);
        }

        $enrollments = $enrollmentsQuery->get();

        $coursesData = [];
        $overallStats = [
            'enrolled_courses_count' => $enrollments->count(),
            'completed_courses_count' => 0,
            'total_sessions_attended' => 0,
            'total_sessions_scheduled' => 0,
            'total_assignments_submitted' => 0,
            'total_assignments_graded' => 0,
            'average_assignment_grade' => 0,
            'total_assessments_passed' => 0,
            'total_quizzes_passed' => 0,
            'total_quiz_points_earned' => 0,
            'total_quiz_points_possible' => 0,
        ];

        $assignmentGradesSum = 0;
        $assignmentGradesCount = 0;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (! $course) {
                continue;
            }

            if ($enrollment->completed_at !== null) {
                $overallStats['completed_courses_count']++;
            }

            // 1. Attendance & Sessions
            $sessions = CourseSession::query()
                ->where('course_id', $course->id)
                ->orderBy('session_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            $attendances = Attendance::query()
                ->where('user_id', $student->id)
                ->whereIn('course_session_id', $sessions->pluck('id'))
                ->get()
                ->keyBy('course_session_id');

            $sessionLog = [];
            $presentCount = 0;
            $lateCount = 0;
            $apologyCount = 0;
            $absentCount = 0;

            foreach ($sessions as $session) {
                $att = $attendances->get($session->id);
                $status = $att?->status ?? ($session->session_date?->isPast() ? 'Absent' : 'Upcoming');

                if ($status === 'Present') {
                    $presentCount++;
                } elseif ($status === 'Late') {
                    $lateCount++;
                } elseif ($status === 'Apology') {
                    $apologyCount++;
                } elseif ($status === 'Absent') {
                    $absentCount++;
                }

                $sessionLog[] = [
                    'session_id' => $session->id,
                    'title' => $session->title ?: "Session #{$session->id}",
                    'session_date' => $session->session_date ? Carbon::parse($session->session_date)->format('M d, Y') : 'TBD',
                    'time' => $session->start_time ? Carbon::parse($session->start_time)->format('H:i') : null,
                    'status' => $status,
                    'remarks' => $att?->remarks,
                    'is_past' => $session->session_date ? Carbon::parse($session->session_date)->isPast() : false,
                ];
            }

            $totalScheduled = $sessions->count();
            $attendedTotal = $presentCount + $lateCount;
            $attendanceRate = $totalScheduled > 0 ? round(($attendedTotal / $totalScheduled) * 100) : 100;

            $overallStats['total_sessions_attended'] += $attendedTotal;
            $overallStats['total_sessions_scheduled'] += $totalScheduled;

            // 2. Assignments & Submissions
            $assignments = Assignment::query()
                ->where('course_id', $course->id)
                ->orderBy('due_date', 'asc')
                ->get();

            $assignmentSubmissions = AssignmentSubmission::query()
                ->where('user_id', $student->id)
                ->whereIn('assignment_id', $assignments->pluck('id'))
                ->get()
                ->keyBy('assignment_id');

            $assignmentsLog = [];
            $courseAssignmentGradesSum = 0;
            $courseAssignmentGradesCount = 0;

            foreach ($assignments as $assignment) {
                $sub = $assignmentSubmissions->get($assignment->id);
                $grade = $sub?->grade !== null ? (float) $sub->grade : null;
                $isSubmitted = $sub !== null && in_array($sub->status, ['Submitted', 'Graded', 'Checked', 'Returned']);
                
                $onTimeStatus = 'Not Submitted';
                if ($isSubmitted) {
                    $overallStats['total_assignments_submitted']++;
                    $submittedAt = $sub->submitted_at ?? $sub->created_at;
                    if ($assignment->due_date && $submittedAt) {
                        $onTimeStatus = $submittedAt->lte($assignment->due_date->endOfDay()) ? 'On Time' : 'Late';
                    } else {
                        $onTimeStatus = 'Submitted';
                    }
                } elseif ($assignment->due_date && $assignment->due_date->isPast()) {
                    $onTimeStatus = 'Overdue';
                } else {
                    $onTimeStatus = 'Pending';
                }

                if ($grade !== null) {
                    $overallStats['total_assignments_graded']++;
                    $courseAssignmentGradesSum += $grade;
                    $courseAssignmentGradesCount++;
                    $assignmentGradesSum += $grade;
                    $assignmentGradesCount++;
                }

                $assignmentsLog[] = [
                    'assignment_id' => $assignment->id,
                    'name' => $assignment->name,
                    'due_date' => $assignment->due_date ? Carbon::parse($assignment->due_date)->format('M d, Y') : 'No Due Date',
                    'status' => $sub?->status ?? ($assignment->due_date?->isPast() ? 'Missing' : 'Pending'),
                    'on_time_status' => $onTimeStatus,
                    'submitted_at' => $sub?->submitted_at ? Carbon::parse($sub->submitted_at)->format('M d, Y H:i') : ($sub?->created_at ? Carbon::parse($sub->created_at)->format('M d, Y H:i') : '-'),
                    'grade' => $grade,
                    'feedback' => $sub?->feedback,
                    'has_files' => ! empty($sub?->all_file_paths),
                    'file_count' => count($sub?->all_file_paths ?? []),
                    'text_content' => $sub?->content,
                    'link' => $sub?->link,
                ];
            }

            $courseAvgAssignmentGrade = $courseAssignmentGradesCount > 0 ? round($courseAssignmentGradesSum / $courseAssignmentGradesCount, 1) : null;

            // 3. Assessments & Submissions
            $assessments = Assessment::query()
                ->where('course_id', $course->id)
                ->orderBy('due_date', 'asc')
                ->get();

            $assessmentSubmissions = AssessmentSubmission::query()
                ->where('user_id', $student->id)
                ->whereIn('assessment_id', $assessments->pluck('id'))
                ->get()
                ->keyBy('assessment_id');

            $assessmentsLog = [];
            foreach ($assessments as $assessment) {
                $sub = $assessmentSubmissions->get($assessment->id);
                $score = $sub?->score !== null ? (float) $sub->score : null;
                $passed = $score !== null && $score >= ($assessment->passing_score ?? 50);

                if ($passed) {
                    $overallStats['total_assessments_passed']++;
                }

                $assessmentsLog[] = [
                    'assessment_id' => $assessment->id,
                    'name' => $assessment->name ?: "Assessment #{$assessment->id}",
                    'due_date' => $assessment->due_date ? Carbon::parse($assessment->due_date)->format('M d, Y') : 'No Due Date',
                    'status' => $sub?->status ?? 'Not submitted',
                    'score' => $score,
                    'passed' => $passed,
                    'passing_score' => $assessment->passing_score ?? 50,
                    'is_retake' => (bool) ($sub?->is_retake ?? false),
                    'feedback' => $sub?->feedback,
                ];
            }

            // 4. Quizzes with FULL ACTUAL ANSWER SHEETS
            $quizzes = Quiz::query()
                ->where('course_id', $course->id)
                ->with(['questions.options'])
                ->get();

            $quizzesLog = [];
            foreach ($quizzes as $quiz) {
                $attempts = QuizAttempt::query()
                    ->where('user_id', $student->id)
                    ->where('quiz_id', $quiz->id)
                    ->orderBy('percentage', 'desc')
                    ->orderBy('completed_at', 'desc')
                    ->get();

                $bestAttempt = $attempts->first();
                $attemptCount = $attempts->count();
                $passed = (bool) ($bestAttempt?->passed ?? false);

                if ($passed) {
                    $overallStats['total_quizzes_passed']++;
                }

                if ($bestAttempt) {
                    $overallStats['total_quiz_points_earned'] += (int) ($bestAttempt->score ?? 0);
                    $overallStats['total_quiz_points_possible'] += (int) ($bestAttempt->total_points ?? 0);
                }

                // Build Answer Sheet for Best Attempt
                $answerSheet = [];
                if ($bestAttempt && $includeAnswerSheets) {
                    $answers = QuizAnswer::query()
                        ->where('quiz_attempt_id', $bestAttempt->id)
                        ->get()
                        ->keyBy('quiz_question_id');

                    foreach ($quiz->questions->sortBy('sort_order') as $index => $question) {
                        $answer = $answers->get($question->id);
                        $selectedOption = $question->options->firstWhere('id', $answer?->quiz_option_id);
                        $correctOption = $question->options->firstWhere('is_correct', true);

                        $isCorrect = (bool) ($answer?->is_correct ?? false);
                        if (! $answer && $selectedOption && $correctOption) {
                            $isCorrect = $selectedOption->id === $correctOption->id;
                        }

                        $optionsList = [];
                        foreach ($question->options as $opt) {
                            $optionsList[] = [
                                'id' => $opt->id,
                                'text' => $opt->option_text,
                                'is_correct' => (bool) $opt->is_correct,
                                'is_student_choice' => $selectedOption && $selectedOption->id === $opt->id,
                            ];
                        }

                        $answerSheet[] = [
                            'question_number' => $index + 1,
                            'question_text' => $question->question,
                            'type' => $question->type ?: 'multiple_choice',
                            'points_available' => $question->points ?? 1,
                            'points_earned' => $answer?->points_earned ?? ($isCorrect ? ($question->points ?? 1) : 0),
                            'is_correct' => $isCorrect,
                            'student_selected_text' => $selectedOption?->option_text ?: ($answer?->text_answer ?: 'No answer given'),
                            'correct_answer_text' => $correctOption?->option_text ?: 'N/A',
                            'options' => $optionsList,
                            'explanation' => $question->explanation,
                            'feedback' => $answer?->feedback,
                        ];
                    }
                }

                $quizzesLog[] = [
                    'quiz_id' => $quiz->id,
                    'title' => $quiz->title,
                    'passing_score' => $quiz->passing_score ?? 70,
                    'attempt_count' => $attemptCount,
                    'best_score' => $bestAttempt?->score,
                    'total_points' => $bestAttempt?->total_points,
                    'percentage' => $bestAttempt?->percentage,
                    'passed' => $passed,
                    'completed_at' => $bestAttempt?->completed_at ? Carbon::parse($bestAttempt->completed_at)->format('M d, Y H:i') : null,
                    'answer_sheet' => $answerSheet,
                ];
            }

            // Summary progress percentages for this course
            $totalCourseItems = $assignments->count() + $assessments->count() + $quizzes->count();
            $completedCourseItems = $assignmentsLog ? collect($assignmentsLog)->where('status', '!=', 'Pending')->where('status', '!=', 'Missing')->count() : 0;
            $completedCourseItems += collect($assessmentsLog)->where('score', '!==', null)->count();
            $completedCourseItems += collect($quizzesLog)->where('passed', true)->count();
            $academicProgressRate = $totalCourseItems > 0 ? round(($completedCourseItems / $totalCourseItems) * 100) : 0;

            $coursesData[] = [
                'course' => $course,
                'enrollment' => $enrollment,
                'instructors' => $course->instructors,
                'is_completed' => $enrollment->completed_at !== null,
                'completed_at' => $enrollment->completed_at ? Carbon::parse($enrollment->completed_at)->format('M d, Y') : null,
                'attendance_rate' => $attendanceRate,
                'sessions_attended' => $attendedTotal,
                'sessions_total' => $totalScheduled,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'apology_count' => $apologyCount,
                'absent_count' => $absentCount,
                'session_log' => $sessionLog,
                'assignments_log' => $assignmentsLog,
                'avg_assignment_grade' => $courseAvgAssignmentGrade,
                'assessments_log' => $assessmentsLog,
                'quizzes_log' => $quizzesLog,
                'academic_progress_rate' => $academicProgressRate,
            ];
        }

        if ($assignmentGradesCount > 0) {
            $overallStats['average_assignment_grade'] = round($assignmentGradesSum / $assignmentGradesCount, 1);
        }

        $overallStats['overall_attendance_rate'] = $overallStats['total_sessions_scheduled'] > 0
            ? round(($overallStats['total_sessions_attended'] / $overallStats['total_sessions_scheduled']) * 100)
            : 100;

        return [
            'student' => $student,
            'generated_at' => Carbon::now()->format('F d, Y • H:i T'),
            'scoped_course' => $scopedCourse,
            'options' => $options,
            'overall_stats' => $overallStats,
            'courses_data' => $coursesData,
        ];
    }

    /**
     * Generate and retrieve full data array for Course & Cohort Executive Analytics Report.
     */
    public function getCourseAnalyticsData(Course $course, ?int $intakeId = null, array $options = []): array
    {
        $enrollmentsQuery = Enrollment::query()
            ->with('user')
            ->where('course_id', $course->id);

        if ($intakeId) {
            $enrollmentsQuery->where('course_intake_id', $intakeId);
        }

        $enrollments = $enrollmentsQuery->get();
        $studentIds = $enrollments->pluck('user_id')->unique()->filter();
        $students = User::query()->whereIn('id', $studentIds)->get()->keyBy('id');

        $intake = $intakeId ? CourseIntake::find($intakeId) : null;

        // 1. Sessions & Aggregate Attendance
        $sessions = CourseSession::query()
            ->where('course_id', $course->id)
            ->when($intakeId, fn ($q) => $q->where('course_intake_id', $intakeId))
            ->orderBy('session_date', 'asc')
            ->get();

        $allAttendances = Attendance::query()
            ->whereIn('course_session_id', $sessions->pluck('id'))
            ->get();

        $presentTotal = $allAttendances->where('status', 'Present')->count();
        $lateTotal = $allAttendances->where('status', 'Late')->count();
        $apologyTotal = $allAttendances->where('status', 'Apology')->count();
        $absentTotal = $allAttendances->where('status', 'Absent')->count();
        $totalMarked = $allAttendances->count();

        $aggregateAttendanceRate = $totalMarked > 0
            ? round((($presentTotal + $lateTotal) / $totalMarked) * 100, 1)
            : 100;

        // 2. Assignments & Grade Distribution
        $assignments = Assignment::query()
            ->where('course_id', $course->id)
            ->when($intakeId, fn ($q) => $q->where('course_intake_id', $intakeId))
            ->get();

        $submissions = AssignmentSubmission::query()
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->whereIn('user_id', $studentIds)
            ->get();

        $gradeCounts = [
            'A (80-100%)' => 0,
            'B (65-79%)' => 0,
            'C (50-64%)' => 0,
            'F (Below 50%)' => 0,
        ];

        $gradedSubmissions = $submissions->whereNotNull('grade');
        foreach ($gradedSubmissions as $sub) {
            $g = (float) $sub->grade;
            if ($g >= 80) {
                $gradeCounts['A (80-100%)']++;
            } elseif ($g >= 65) {
                $gradeCounts['B (65-79%)']++;
            } elseif ($g >= 50) {
                $gradeCounts['C (50-64%)']++;
            } else {
                $gradeCounts['F (Below 50%)']++;
            }
        }

        $avgAssignmentScore = $gradedSubmissions->count() > 0
            ? round($gradedSubmissions->avg('grade'), 1)
            : null;

        // 3. Assessments Metrics
        $assessments = Assessment::query()
            ->where('course_id', $course->id)
            ->when($intakeId, fn ($q) => $q->where('course_intake_id', $intakeId))
            ->get();

        $assessmentSubs = AssessmentSubmission::query()
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->whereIn('user_id', $studentIds)
            ->get();

        $gradedAssessments = $assessmentSubs->whereNotNull('score');
        $avgAssessmentScore = $gradedAssessments->count() > 0 ? round($gradedAssessments->avg('score'), 1) : null;
        $passedAssessmentCount = $gradedAssessments->filter(fn ($s) => $s->score >= 50)->count();
        $assessmentPassRate = $gradedAssessments->count() > 0 ? round(($passedAssessmentCount / $gradedAssessments->count()) * 100, 1) : null;

        // 4. Quizzes Aggregate Stats
        $quizzes = Quiz::query()
            ->where('course_id', $course->id)
            ->with(['questions.answers'])
            ->get();

        $quizAttempts = QuizAttempt::query()
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->whereIn('user_id', $studentIds)
            ->get();

        $avgQuizPercentage = $quizAttempts->count() > 0 ? round($quizAttempts->avg('percentage'), 1) : null;
        $quizPassRate = $quizAttempts->count() > 0 ? round(($quizAttempts->where('passed', true)->count() / $quizAttempts->count()) * 100, 1) : null;

        // 5. Per-Student Roster Matrix
        $studentRoster = [];
        foreach ($enrollments as $enrollment) {
            $student = $students->get($enrollment->user_id);
            if (! $student) {
                continue;
            }

            $stAttendances = $allAttendances->where('user_id', $student->id);
            $stAttended = $stAttendances->whereIn('status', ['Present', 'Late'])->count();
            $stTotalSessions = $sessions->count();
            $stAttRate = $stTotalSessions > 0 ? round(($stAttended / $stTotalSessions) * 100) : 100;

            $stSubs = $submissions->where('user_id', $student->id);
            $stAvgGrade = $stSubs->whereNotNull('grade')->count() > 0 ? round($stSubs->whereNotNull('grade')->avg('grade'), 1) : null;

            $stQuizzesPassed = $quizAttempts->where('user_id', $student->id)->where('passed', true)->pluck('quiz_id')->unique()->count();

            $studentRoster[] = [
                'student_id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'track' => $student->track ?? 'Beginner',
                'enrolled_at' => $enrollment->created_at ? Carbon::parse($enrollment->created_at)->format('M d, Y') : '-',
                'completed' => $enrollment->completed_at !== null,
                'completed_at' => $enrollment->completed_at ? Carbon::parse($enrollment->completed_at)->format('M d, Y') : null,
                'attendance_rate' => $stAttRate,
                'attended_sessions' => $stAttended,
                'total_sessions' => $stTotalSessions,
                'assignments_submitted' => $stSubs->count(),
                'avg_assignment_grade' => $stAvgGrade,
                'quizzes_passed' => $stQuizzesPassed,
                'total_quizzes' => $quizzes->count(),
            ];
        }

        $completedStudentsCount = $enrollments->whereNotNull('completed_at')->count();
        $completionRate = $enrollments->count() > 0 ? round(($completedStudentsCount / $enrollments->count()) * 100, 1) : 0;

        return [
            'course' => $course,
            'intake' => $intake,
            'generated_at' => Carbon::now()->format('F d, Y • H:i T'),
            'total_students' => $enrollments->count(),
            'completed_students_count' => $completedStudentsCount,
            'completion_rate' => $completionRate,
            'attendance' => [
                'rate' => $aggregateAttendanceRate,
                'present' => $presentTotal,
                'late' => $lateTotal,
                'apology' => $apologyTotal,
                'absent' => $absentTotal,
                'total_marked' => $totalMarked,
                'total_sessions' => $sessions->count(),
            ],
            'assignments' => [
                'total' => $assignments->count(),
                'total_submissions' => $submissions->count(),
                'average_score' => $avgAssignmentScore,
                'grade_distribution' => $gradeCounts,
            ],
            'assessments' => [
                'total' => $assessments->count(),
                'average_score' => $avgAssessmentScore,
                'pass_rate' => $assessmentPassRate,
            ],
            'quizzes' => [
                'total' => $quizzes->count(),
                'average_score' => $avgQuizPercentage,
                'pass_rate' => $quizPassRate,
                'total_attempts' => $quizAttempts->count(),
            ],
            'roster' => $studentRoster,
        ];
    }

    /**
     * Render Student Dossier & Transcript PDF.
     */
    public function renderStudentReportPdf(User $student, ?Course $scopedCourse = null, array $options = []): DomPDF
    {
        $data = $this->getStudentAcademicData($student, $scopedCourse, $options);

        return Pdf::loadView('reports.student-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Render Course & Cohort Executive Analytics PDF.
     */
    public function renderCourseReportPdf(Course $course, ?int $intakeId = null, array $options = []): DomPDF
    {
        $data = $this->getCourseAnalyticsData($course, $intakeId, $options);

        return Pdf::loadView('reports.course-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Render Intake / Cohort Report PDF.
     */
    public function renderIntakeReportPdf(CourseIntake $intake, array $options = []): DomPDF
    {
        $course = $intake->course;
        $data = $this->getCourseAnalyticsData($course, $intake->id, $options);
        $data['intake'] = $intake;

        return Pdf::loadView('reports.intake-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }
}
