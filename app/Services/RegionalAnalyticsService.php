<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\ScormAttempt;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtmAttribution;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class RegionalAnalyticsService
{
    /**
     * Compute real-time KPIs and breakdowns filtered by date range, course, tenant, and UTM source.
     *
     * @param  array{date_from?: ?string, date_to?: ?string, course_id?: ?int, tenant_id?: ?int, utm_source?: ?string}  $filters
     * @return array{
     *     kpis: array<string, mixed>,
     *     utm_breakdown: list<array<string, mixed>>,
     *     tenant_breakdown: list<array<string, mixed>>
     * }
     */
    public function summary(array $filters = [], ?User $actingUser = null): array
    {
        $tenantId = $this->resolveEffectiveTenantId($filters, $actingUser);
        $dateFrom = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $dateTo = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : null;
        $courseId = ! empty($filters['course_id']) ? (int) $filters['course_id'] : null;
        $utmSource = ! empty($filters['utm_source']) ? trim((string) $filters['utm_source']) : null;

        // 1. Registrations & Active Learners
        $userQuery = User::query()->where('role', 'student');
        if ($tenantId !== null && Schema::hasColumn('users', 'tenant_id')) {
            $userQuery->where('tenant_id', $tenantId);
        }
        if ($dateFrom) {
            $userQuery->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $userQuery->where('created_at', '<=', $dateTo);
        }
        if ($utmSource !== null && Schema::hasTable('utm_attributions')) {
            $userQuery->whereIn('id', UtmAttribution::query()->where('utm_source', $utmSource)->select('user_id'));
        }

        $totalRegistrations = (clone $userQuery)->count();
        $activeLearners = (clone $userQuery)->where('is_active', true)->count();

        // 2. Enrollments & Completion Rate
        $enrollmentQuery = Enrollment::query();
        if ($tenantId !== null && Schema::hasColumn('enrollments', 'tenant_id')) {
            $enrollmentQuery->where('tenant_id', $tenantId);
        }
        if ($courseId !== null) {
            $enrollmentQuery->where('course_id', $courseId);
        }
        if ($dateFrom) {
            $enrollmentQuery->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $enrollmentQuery->where('created_at', '<=', $dateTo);
        }
        if ($utmSource !== null && Schema::hasTable('utm_attributions')) {
            $enrollmentQuery->whereIn('id', UtmAttribution::query()->where('utm_source', $utmSource)->select('enrollment_id'));
        }

        $totalEnrollments = (clone $enrollmentQuery)->count();
        $completedEnrollments = (clone $enrollmentQuery)
            ->where(function ($q): void {
                $q->whereNotNull('completed_at')->orWhere('progress_percentage', '>=', 100);
            })
            ->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 1) : 0.0;

        // 3. Average Assessment / Quiz Score
        $avgQuizScore = 0.0;
        if (Schema::hasTable('quiz_attempts')) {
            $quizQuery = QuizAttempt::query()->whereNotNull('score')->where('total_points', '>', 0);
            if ($tenantId !== null && Schema::hasColumn('quiz_attempts', 'tenant_id')) {
                $quizQuery->where('tenant_id', $tenantId);
            }
            if ($dateFrom) {
                $quizQuery->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $quizQuery->where('created_at', '<=', $dateTo);
            }
            $attempts = $quizQuery->get(['score', 'total_points']);
            if ($attempts->isNotEmpty()) {
                $avgQuizScore = round($attempts->avg(fn ($a) => ((float) $a->score / max(1, (float) $a->total_points)) * 100), 1);
            }
        }

        // 4. SCORM / xAPI Completion Rate
        $scormTotal = 0;
        $scormCompleted = 0;
        if (Schema::hasTable('scorm_attempts')) {
            $scormQuery = ScormAttempt::query();
            if ($tenantId !== null) {
                $scormQuery->where('tenant_id', $tenantId);
            }
            if ($dateFrom) {
                $scormQuery->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $scormQuery->where('created_at', '<=', $dateTo);
            }
            $scormTotal = (clone $scormQuery)->count();
            $scormCompleted = (clone $scormQuery)->whereIn('lesson_status', ['completed', 'passed'])->count();
        }
        $scormCompletionRate = $scormTotal > 0 ? round(($scormCompleted / $scormTotal) * 100, 1) : 0.0;

        // 5. UTM Source Funnel Breakdown
        $utmBreakdown = [];
        if (Schema::hasTable('utm_attributions')) {
            $attrQuery = UtmAttribution::query()->with(['enrollment']);
            if ($tenantId !== null) {
                $attrQuery->where('tenant_id', $tenantId);
            }
            if ($dateFrom) {
                $attrQuery->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $attrQuery->where('created_at', '<=', $dateTo);
            }
            if ($utmSource !== null) {
                $attrQuery->where('utm_source', $utmSource);
            }

            $grouped = $attrQuery->get()->groupBy(fn (UtmAttribution $a) => $a->utm_source ?: 'Direct / Unknown');
            foreach ($grouped as $source => $items) {
                $regs = $items->pluck('user_id')->filter()->unique()->count();
                $enrolls = $items->pluck('enrollment')->filter()->unique('id');
                $enrollCount = $enrolls->count();
                $completedCount = $enrolls->filter(fn (Enrollment $e) => $e->completed_at !== null || (int) $e->progress_percentage >= 100)->count();
                $utmBreakdown[] = [
                    'source' => (string) $source,
                    'registrations' => $regs,
                    'enrollments' => $enrollCount,
                    'completed' => $completedCount,
                    'conversion_rate' => $enrollCount > 0 ? round(($completedCount / $enrollCount) * 100, 1) : 0.0,
                ];
            }
            usort($utmBreakdown, fn ($a, $b) => $b['registrations'] <=> $a['registrations']);
        }

        // 6. Tenant / Cohort Breakdown (Global)
        $tenantBreakdown = [];
        if (Schema::hasTable('tenants')) {
            $tenantsQuery = Tenant::query()->orderBy('name');
            if ($tenantId !== null) {
                $tenantsQuery->whereKey($tenantId);
            }
            foreach ($tenantsQuery->get() as $tenant) {
                $tUsers = User::query()->where('role', 'student')->where('tenant_id', $tenant->id)->count();
                $tEnrolls = Enrollment::query()->where('tenant_id', $tenant->id)->count();
                $tCompleted = Enrollment::query()
                    ->where('tenant_id', $tenant->id)
                    ->where(fn ($q) => $q->whereNotNull('completed_at')->orWhere('progress_percentage', '>=', 100))
                    ->count();

                $tenantBreakdown[] = [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'country' => $tenant->country_name ?: ($tenant->country_code ?: 'Global'),
                    'learners' => $tUsers,
                    'enrollments' => $tEnrolls,
                    'completed' => $tCompleted,
                    'completion_rate' => $tEnrolls > 0 ? round(($tCompleted / $tEnrolls) * 100, 1) : 0.0,
                ];
            }
        }

        return [
            'kpis' => [
                'total_registrations' => $totalRegistrations,
                'active_learners' => $activeLearners,
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => $completionRate,
                'avg_quiz_score' => $avgQuizScore,
                'scorm_attempts' => $scormTotal,
                'scorm_completion_rate' => $scormCompletionRate,
            ],
            'utm_breakdown' => $utmBreakdown,
            'tenant_breakdown' => $tenantBreakdown,
        ];
    }

    /**
     * Build export rows for the requested dataset.
     *
     * @param  array{date_from?: ?string, date_to?: ?string, course_id?: ?int, tenant_id?: ?int, utm_source?: ?string}  $filters
     * @return array{headers: list<string>, rows: list<list<scalar|null>>}
     */
    public function datasetForExport(string $dataset, array $filters = [], ?User $actingUser = null): array
    {
        $tenantId = $this->resolveEffectiveTenantId($filters, $actingUser);
        $dateFrom = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $dateTo = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : null;
        $courseId = ! empty($filters['course_id']) ? (int) $filters['course_id'] : null;
        $utmSource = ! empty($filters['utm_source']) ? trim((string) $filters['utm_source']) : null;

        return match ($dataset) {
            'assessment_results' => $this->exportAssessmentResults($tenantId, $courseId, $dateFrom, $dateTo),
            'scorm_logs' => $this->exportScormLogs($tenantId, $courseId, $dateFrom, $dateTo),
            'utm_attribution' => $this->exportUtmAttribution($tenantId, $utmSource, $dateFrom, $dateTo),
            default => $this->exportLearnerProgress($tenantId, $courseId, $utmSource, $dateFrom, $dateTo),
        };
    }

    private function resolveEffectiveTenantId(array $filters, ?User $actingUser): ?int
    {
        if ($actingUser && $actingUser->isProgramAdmin()) {
            return $actingUser->managedTenantId();
        }

        return ! empty($filters['tenant_id']) ? (int) $filters['tenant_id'] : null;
    }

    private function exportLearnerProgress(?int $tenantId, ?int $courseId, ?string $utmSource, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $query = Enrollment::query()->with(['user', 'course', 'tenant'])->latest();
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }
        if ($courseId !== null) {
            $query->where('course_id', $courseId);
        }
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }
        if ($utmSource !== null && Schema::hasTable('utm_attributions')) {
            $query->whereIn('id', UtmAttribution::query()->where('utm_source', $utmSource)->select('enrollment_id'));
        }

        $rows = [];
        foreach ($query->limit(5000)->get() as $enrollment) {
            $rows[] = [
                $enrollment->user?->name ?? '—',
                $enrollment->user?->email ?? '—',
                $enrollment->tenant?->name ?? 'Global',
                $enrollment->course?->title ?? '—',
                (int) ($enrollment->progress_percentage ?? 0) . '%',
                $enrollment->completed_at ? 'Completed' : 'In Progress',
                $enrollment->created_at?->format('Y-m-d H:i') ?? '—',
                $enrollment->completed_at?->format('Y-m-d H:i') ?? '—',
            ];
        }

        return [
            'headers' => ['Learner Name', 'Email', 'Program / Tenant', 'Course', 'Progress', 'Status', 'Enrolled At', 'Completed At'],
            'rows' => $rows,
        ];
    }

    private function exportAssessmentResults(?int $tenantId, ?int $courseId, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $query = QuizAttempt::query()->with(['user', 'quiz.course'])->latest();
        if ($tenantId !== null && Schema::hasColumn('quiz_attempts', 'tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }
        if ($courseId !== null) {
            $query->whereHas('quiz', fn ($q) => $q->where('course_id', $courseId));
        }
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->limit(5000)->get() as $attempt) {
            $pct = $attempt->total_points > 0 ? round(((float) $attempt->score / (float) $attempt->total_points) * 100, 1) : 0;
            $rows[] = [
                $attempt->user?->name ?? '—',
                $attempt->user?->email ?? '—',
                $attempt->quiz?->course?->title ?? '—',
                $attempt->quiz?->title ?? '—',
                (string) ($attempt->score ?? 0) . ' / ' . (string) ($attempt->total_points ?? 0),
                $pct . '%',
                $attempt->passed ? 'Passed' : 'Failed',
                $attempt->created_at?->format('Y-m-d H:i') ?? '—',
            ];
        }

        return [
            'headers' => ['Learner Name', 'Email', 'Course', 'Quiz / Assessment', 'Score', 'Percentage', 'Result', 'Submitted At'],
            'rows' => $rows,
        ];
    }

    private function exportScormLogs(?int $tenantId, ?int $courseId, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $query = ScormAttempt::query()->with(['user', 'package.course', 'tenant'])->latest();
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }
        if ($courseId !== null) {
            $query->whereHas('package', fn ($q) => $q->where('course_id', $courseId));
        }
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->limit(5000)->get() as $attempt) {
            $rows[] = [
                $attempt->user?->name ?? '—',
                $attempt->user?->email ?? '—',
                $attempt->tenant?->name ?? 'Global',
                $attempt->package?->title ?? '—',
                strtoupper((string) ($attempt->package?->standard ?? 'scorm_12')),
                $attempt->lesson_status,
                $attempt->score_raw !== null ? (string) $attempt->score_raw : '—',
                $attempt->session_time ?? '—',
                $attempt->lesson_location ?? '—',
                $attempt->completed_at?->format('Y-m-d H:i') ?? '—',
            ];
        }

        return [
            'headers' => ['Learner Name', 'Email', 'Program / Tenant', 'SCORM / xAPI Module', 'Standard', 'Status', 'Raw Score', 'Session Time', 'Bookmark', 'Completed At'],
            'rows' => $rows,
        ];
    }

    private function exportUtmAttribution(?int $tenantId, ?string $utmSource, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $query = UtmAttribution::query()->with(['user', 'enrollment.course', 'tenant'])->latest();
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }
        if ($utmSource !== null) {
            $query->where('utm_source', $utmSource);
        }
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->limit(5000)->get() as $attr) {
            $rows[] = [
                $attr->user?->name ?? '—',
                $attr->user?->email ?? '—',
                $attr->tenant?->name ?? 'Global',
                $attr->utm_source ?? 'Direct',
                $attr->utm_medium ?? '—',
                $attr->utm_campaign ?? '—',
                $attr->utm_content ?? '—',
                $attr->utm_term ?? '—',
                $attr->enrollment?->course?->title ?? '—',
                $attr->enrollment ? ((int) $attr->enrollment->progress_percentage . '%') : '—',
                $attr->enrollment?->completed_at ? 'Completed' : ($attr->enrollment ? 'In Progress' : 'Registered'),
                $attr->captured_at?->format('Y-m-d H:i') ?? $attr->created_at?->format('Y-m-d H:i') ?? '—',
            ];
        }

        return [
            'headers' => ['Learner Name', 'Email', 'Program / Tenant', 'UTM Source', 'UTM Medium', 'UTM Campaign', 'UTM Content', 'UTM Term', 'Course', 'Progress', 'Funnel Stage', 'Captured At'],
            'rows' => $rows,
        ];
    }
}
