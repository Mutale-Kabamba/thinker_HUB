<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InstructorReminderService
{
    /**
     * Get all course IDs that the instructor is responsible for.
     *
     * @return list<int>
     */
    public function getInstructorCourseIds(User $instructor): array
    {
        if ($instructor->isAdmin()) {
            return Course::query()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $courseIds = Course::query()
            ->where(function ($query) use ($instructor) {
                $query->where('course_by', (string) $instructor->id)
                    ->orWhere('course_by', (string) $instructor->name)
                    ->orWhereHas('instructors', fn ($q) => $q->where('users.id', $instructor->id));
            })
            ->pluck('id')
            ->merge($instructor->instructorCourses()->pluck('courses.id'));

        // Also include courses from course_class_instructor pivot if available
        if (Schema::hasTable('course_class_instructor') && Schema::hasTable('course_classes')) {
            $classCourseIds = \Illuminate\Support\Facades\DB::table('course_class_instructor')
                ->join('course_classes', 'course_classes.id', '=', 'course_class_instructor.course_class_id')
                ->where('course_class_instructor.user_id', $instructor->id)
                ->pluck('course_classes.course_id');
            $courseIds = $courseIds->merge($classCourseIds);
        }

        // Also include courses from CourseSession assigned to this instructor
        if (Schema::hasTable('course_sessions')) {
            $sessionCourseIds = CourseSession::query()
                ->where('instructor_id', $instructor->id)
                ->pluck('course_id');
            $courseIds = $courseIds->merge($sessionCourseIds);
        }

        return $courseIds
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Get sessions whose attendance register needs to be marked.
     * (Scheduled sessions that took place today or in the past, status not completed or no attendances).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getUnmarkedSessions(User $instructor, int $limit = 10): Collection
    {
        if (! Schema::hasTable('course_sessions')) {
            return collect();
        }

        $courseIds = $this->getInstructorCourseIds($instructor);
        $today = Carbon::today();
        // Look back up to 180 days for unmarked registers in current and recent cohorts
        $lookbackDate = Carbon::today()->subDays(180);

        $query = CourseSession::query()
            ->where(function ($q) use ($instructor, $courseIds) {
                $q->where('instructor_id', $instructor->id);
                if (! empty($courseIds)) {
                    $q->orWhereIn('course_id', $courseIds);
                }
            })
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($today, $lookbackDate) {
                $q->whereDate('session_date', '<=', $today)
                    ->whereDate('session_date', '>=', $lookbackDate);
            })
            ->where(function ($q) {
                // Either status is still scheduled (not yet completed)
                // OR it has no attendance records created at all
                $q->where('status', '!=', 'completed')
                    ->orWhereDoesntHave('attendances');
            })
            ->with(['course', 'intake', 'student'])
            ->withCount(['attendances' => function ($aq) {
                $aq->whereHas('student', fn ($sq) => $sq->where('is_active', true)->withoutTestStudents());
            }])
            ->orderBy('session_date', 'desc')
            ->orderBy('start_time', 'desc');

        return $query->limit($limit)->get()->map(function (CourseSession $session) use ($today) {
            $effectiveDate = $session->getEffectiveDate();
            $isToday = $effectiveDate->isToday();
            $isPast = $effectiveDate->isPast() && ! $isToday;
            $daysAgo = $effectiveDate->diffInDays($today);

            $dateDisplay = $isToday
                ? 'Today'
                : ($daysAgo === 1 ? 'Yesterday' : $effectiveDate->format('M j, Y'));

            $startTimeDisplay = 'TBD';
            if (filled($session->start_time)) {
                try {
                    $startTimeDisplay = Carbon::parse($session->start_time)->format('g:i A');
                } catch (\Throwable) {
                    $startTimeDisplay = (string) $session->start_time;
                }
            }

            return [
                'id' => $session->id,
                'title' => $session->title ?: ($session->course?->title ? "{$session->course->title} Session" : 'Class Session'),
                'course_id' => $session->course_id,
                'course_title' => $session->course?->title ?? 'Course',
                'course_code' => $session->course?->code ?? 'CS-101',
                'intake_name' => $session->intake?->name ?? 'Current Cohort',
                'session_date' => $effectiveDate->format('Y-m-d'),
                'date_display' => $dateDisplay,
                'start_time' => $session->start_time,
                'time_display' => $startTimeDisplay,
                'is_today' => $isToday,
                'is_overdue' => $isPast,
                'days_ago' => $daysAgo,
                'type' => $session->type ?? 'group',
                'student_name' => $session->student?->name,
                'status' => $session->status,
                'attendances_count' => (int) ($session->attendances_count ?? 0),
                'action_url' => url('/teach/attendance-register?session_id=' . $session->id),
            ];
        });
    }

    /**
     * Get pending assignment submissions waiting for review / grading.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getPendingAssignments(User $instructor, int $limit = 10): Collection
    {
        if (! Schema::hasTable('assignment_submissions')) {
            return collect();
        }

        $courseIds = $this->getInstructorCourseIds($instructor);
        if (empty($courseIds)) {
            return collect();
        }

        $query = AssignmentSubmission::query()
            ->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->where(function ($q) {
                $q->whereNull('grade')
                    ->orWhereNotIn('status', ['Graded', 'Checked']);
            })
            ->with(['assignment.course', 'user'])
            ->latest('submitted_at');

        return $query->limit($limit)->get()->map(function (AssignmentSubmission $sub) {
            $submittedAt = $sub->submitted_at;
            $studentName = $sub->user?->name ?? 'Student';
            $photo = null;
            if ($sub->user) {
                $photo = $sub->user->profile_photo_url ?? ($sub->user->profile_photo_path ? asset('storage/' . $sub->user->profile_photo_path) : null);
            }

            return [
                'id' => $sub->id,
                'assignment_id' => $sub->assignment_id,
                'assignment_title' => $sub->assignment?->name ?? 'Assignment',
                'course_title' => $sub->assignment?->course?->title ?? 'Course',
                'course_code' => $sub->assignment?->course?->code ?? '',
                'student_name' => $studentName,
                'student_initials' => Str::upper(substr($studentName, 0, 2)),
                'student_photo' => $photo,
                'submitted_at_diff' => $submittedAt ? $submittedAt->diffForHumans() : 'Recently',
                'submitted_at_formatted' => $submittedAt ? $submittedAt->format('M j, g:i A') : 'Recently',
                'status' => $sub->status ?: 'Submitted',
                'grade_url' => url('/teach/assignment-submissions/' . $sub->id . '/edit'),
            ];
        });
    }

    /**
     * Get pending assessment submissions waiting for review / grading.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getPendingAssessments(User $instructor, int $limit = 10): Collection
    {
        if (! Schema::hasTable('assessment_submissions')) {
            return collect();
        }

        $courseIds = $this->getInstructorCourseIds($instructor);
        if (empty($courseIds)) {
            return collect();
        }

        $query = AssessmentSubmission::query()
            ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->where(function ($q) {
                $q->whereNull('grade')
                    ->orWhereNotIn('status', ['Graded', 'Checked']);
            })
            ->with(['assessment.course', 'user'])
            ->latest('submitted_at');

        return $query->limit($limit)->get()->map(function (AssessmentSubmission $sub) {
            $submittedAt = $sub->submitted_at;
            $studentName = $sub->user?->name ?? 'Student';
            $photo = null;
            if ($sub->user) {
                $photo = $sub->user->profile_photo_url ?? ($sub->user->profile_photo_path ? asset('storage/' . $sub->user->profile_photo_path) : null);
            }

            return [
                'id' => $sub->id,
                'assessment_id' => $sub->assessment_id,
                'assessment_title' => $sub->assessment?->name ?? 'Assessment',
                'course_title' => $sub->assessment?->course?->title ?? 'Course',
                'course_code' => $sub->assessment?->course?->code ?? '',
                'student_name' => $studentName,
                'student_initials' => Str::upper(substr($studentName, 0, 2)),
                'student_photo' => $photo,
                'submitted_at_diff' => $submittedAt ? $submittedAt->diffForHumans() : 'Recently',
                'submitted_at_formatted' => $submittedAt ? $submittedAt->format('M j, g:i A') : 'Recently',
                'status' => $sub->status ?: 'Submitted',
                'grade_url' => url('/teach/assessment-submissions/' . $sub->id . '/edit'),
            ];
        });
    }

    /**
     * Get sessions scheduled for today.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTodaySessions(User $instructor): Collection
    {
        if (! Schema::hasTable('course_sessions')) {
            return collect();
        }

        $courseIds = $this->getInstructorCourseIds($instructor);
        $today = Carbon::today();
        $todayStr = $today->toDateString();

        return CourseSession::query()
            ->where(function ($q) use ($instructor, $courseIds) {
                $q->where('instructor_id', $instructor->id);
                if (! empty($courseIds)) {
                    $q->orWhereIn('course_id', $courseIds);
                }
            })
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($today, $todayStr) {
                $q->where(function ($sub) use ($today, $todayStr) {
                    $sub->where(function ($s) {
                        $s->where('status', '!=', 'rescheduled')
                            ->orWhereNull('rescheduled_date');
                    })->where(function ($d) use ($today, $todayStr) {
                        $d->whereDate('session_date', $today)
                            ->orWhere('session_date', 'like', $todayStr . '%');
                    });
                })->orWhere(function ($sub) use ($today, $todayStr) {
                    $sub->where('status', 'rescheduled')
                        ->where(function ($d) use ($today, $todayStr) {
                            $d->whereDate('rescheduled_date', $today)
                                ->orWhere('rescheduled_date', 'like', $todayStr . '%');
                        });
                });
            })
            ->with(['course', 'intake', 'student'])
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function (CourseSession $s) {
                $startTime = $s->getEffectiveStartTime();
                $timeDisplay = 'TBD';
                if (filled($startTime) && $startTime !== '00:00:00') {
                    try {
                        $timeDisplay = Carbon::parse($startTime)->format('g:i A');
                    } catch (\Throwable) {
                        $timeDisplay = (string) $startTime;
                    }
                }

                return [
                    'id' => $s->id,
                    'title' => $s->title ?: ($s->course?->title ?? 'Class Session'),
                    'course_title' => $s->course?->title ?? 'Course',
                    'course_code' => $s->course?->code ?? '',
                    'start_time' => $startTime,
                    'time_display' => $timeDisplay,
                    'type' => $s->type ?? 'group',
                    'student_name' => $s->student?->name,
                    'status' => $s->status,
                    'action_url' => url('/teach/attendance-register?session_id=' . $s->id),
                ];
            });
    }

    /**
     * Gather comprehensive reminder data for the instructor popup.
     *
     * @return array<string, mixed>
     */
    public function getRemindersData(User $instructor): array
    {
        $nameParts = array_values(array_filter(explode(' ', trim($instructor->name ?? 'Instructor'))));
        $firstWord = $nameParts[0] ?? 'Instructor';
        $titles = ['dr.', 'dr', 'prof.', 'prof', 'mr.', 'mr', 'mrs.', 'mrs', 'ms.', 'ms'];
        if (in_array(strtolower($firstWord), $titles, true) && isset($nameParts[1])) {
            $firstName = $nameParts[1];
        } else {
            $firstName = $firstWord;
        }

        $avatar = null;
        try {
            $avatar = $instructor->profile_photo_url ?? ($instructor->profile_photo_path ? asset('storage/' . $instructor->profile_photo_path) : null);
        } catch (\Throwable) {
            $avatar = null;
        }

        $unmarkedSessions = collect();
        try {
            $unmarkedSessions = $this->getUnmarkedSessions($instructor);
        } catch (\Throwable $e) {
            report($e);
        }

        $pendingAssignments = collect();
        try {
            $pendingAssignments = $this->getPendingAssignments($instructor);
        } catch (\Throwable $e) {
            report($e);
        }

        $pendingAssessments = collect();
        try {
            $pendingAssessments = $this->getPendingAssessments($instructor);
        } catch (\Throwable $e) {
            report($e);
        }

        $todaySessions = collect();
        try {
            $todaySessions = $this->getTodaySessions($instructor);
        } catch (\Throwable $e) {
            report($e);
        }

        $unmarkedCount = $unmarkedSessions->count();
        $assignmentsCount = $pendingAssignments->count();
        $assessmentsCount = $pendingAssessments->count();
        $todaySessionsCount = $todaySessions->count();

        $totalActionable = $unmarkedCount + $assignmentsCount + $assessmentsCount;
        $hasOverdue = $unmarkedSessions->contains('is_overdue', true);

        $urgencyLevel = 'clear';
        if ($totalActionable > 0) {
            $urgencyLevel = $hasOverdue ? 'critical' : 'warning';
        } elseif ($todaySessionsCount > 0) {
            $urgencyLevel = 'info';
        }

        return [
            'instructor_name' => $firstName,
            'full_name' => $instructor->name,
            'avatar' => $avatar,
            'has_pending' => ($totalActionable > 0 || $todaySessionsCount > 0),
            'total_actionable' => $totalActionable,
            'has_overdue' => $hasOverdue,
            'urgency_level' => $urgencyLevel,
            'unmarked_sessions' => $unmarkedSessions->values()->all(),
            'unmarked_count' => $unmarkedCount,
            'pending_assignments' => $pendingAssignments->values()->all(),
            'pending_assignments_count' => $assignmentsCount,
            'pending_assessments' => $pendingAssessments->values()->all(),
            'pending_assessments_count' => $assessmentsCount,
            'today_sessions' => $todaySessions->values()->all(),
            'today_sessions_count' => $todaySessionsCount,
            'today_date_formatted' => Carbon::now()->format('l, F j, Y'),
            'attendance_register_url' => url('/teach/attendance-register'),
            'assignments_url' => url('/teach/assignment-submissions'),
            'assessments_url' => url('/teach/assessment-submissions'),
            'schedule_url' => url('/teach/schedule'),
        ];
    }

    /**
     * Determine if the smart popup should trigger automatically on this request.
     *
     * Rules:
     * 1. Only show if there is an activity due (unmarked registers, pending assignments, or pending assessments).
     * 2. Only show once per login session (subsequent page navigations will not re-trigger it).
     * 3. Respect user dismissal for the day or session.
     */
    public function shouldShowPopup(User $instructor, array $data): bool
    {
        if (! $instructor->isInstructor() && ! $instructor->isAdmin()) {
            return false;
        }

        // Rule 1: Only popup when there is an activity due
        $totalActionable = (int) ($data['total_actionable'] ?? 0);
        if ($totalActionable <= 0) {
            return false;
        }

        // Rule 2: Only popup once per login session
        if (session('instructor_reminder_shown_for_session') || session('instructor_reminder_dismissed_session')) {
            return false;
        }

        // Check if explicitly dismissed for today
        $dismissedUntil = session('instructor_reminder_dismissed_until');
        if ($dismissedUntil && now()->timestamp < (int) $dismissedUntil) {
            return false;
        }

        return true;
    }

    /**
     * Mark the reminder popup as having been shown for the current login session.
     */
    public function markPopupShownForSession(): void
    {
        session(['instructor_reminder_shown_for_session' => true]);
    }
}
