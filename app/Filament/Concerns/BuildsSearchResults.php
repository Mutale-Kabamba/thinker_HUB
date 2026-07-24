<?php

namespace App\Filament\Concerns;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

trait BuildsSearchResults
{
    public string $query = '';

    public array $results = [];

    protected ?Collection $enrolledCourseIds = null;

    protected ?Collection $instructorCourseIds = null;

    /**
     * Map of result array keys to the search method that fills each section.
     *
     * @return array<string, string>
     */
    abstract protected function searchSections(): array;

    public function mount(): void
    {
        $this->query = (string) request()->query('q', '');
        $this->runSearch();
    }

    public function updatedQuery(): void
    {
        $this->runSearch();
    }

    protected function runSearch(): void
    {
        $this->results = array_fill_keys(array_keys($this->searchSections()), []);

        $term = trim($this->query);

        if ($term === '') {
            return;
        }

        foreach ($this->searchSections() as $key => $method) {
            $this->results[$key] = $this->{$method}($term);
        }
    }

    /*
    |----------------------------------------------------------------------
    | Admin panel sections
    |----------------------------------------------------------------------
    */

    protected function searchStudents(string $term): array
    {
        return $this->applyContentSearch(
            User::query()->select(['id', 'name', 'email'])->where('role', 'student'),
            $term,
            'users',
            'users_fts',
            ['name', 'email'],
        )->toArray();
    }

    protected function searchCourses(string $term): array
    {
        return $this->applyContentSearch(
            Course::query()->select(['id', 'title', 'code']),
            $term,
            'courses',
            'courses_fts',
            ['title', 'code', 'description'],
        )->toArray();
    }

    protected function searchAssignments(string $term): array
    {
        return $this->applyContentSearch(
            Assignment::query()->select(['id', 'name', 'scope']),
            $term,
            'assignments',
            'assignments_fts',
            ['name', 'description'],
        )->toArray();
    }

    protected function searchAssessments(string $term): array
    {
        return $this->applyContentSearch(
            Assessment::query()->select(['id', 'name', 'score']),
            $term,
            'assessments',
            'assessments_fts',
            ['name', 'description', ['CAST(score as CHAR) like ?']],
        )->toArray();
    }

    protected function searchMaterials(string $term): array
    {
        return $this->applyContentSearch(
            LearningMaterial::query()->select(['id', 'title', 'material_type']),
            $term,
            'learning_materials',
            'materials_fts',
            ['title', 'file_name', 'material_type'],
        )->toArray();
    }

    protected function searchEnrollments(string $term): array
    {
        return Enrollment::query()
            ->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%"))
            ->orWhereHas('course', fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
            ->with(['user:id,name', 'course:id,title,code'])
            ->limit(8)
            ->get()
            ->map(fn (Enrollment $e): array => [
                'id' => $e->id,
                'student' => $e->user?->name ?? 'Unknown',
                'course' => $e->course?->code.' - '.$e->course?->title,
                'course_id' => $e->course_id,
            ])
            ->toArray();
    }

    protected function searchAssignmentSubmissions(string $term): array
    {
        return AssignmentSubmission::query()
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$term}%"))
                ->orWhereHas('assignment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with(['user:id,name', 'assignment:id,name'])
            ->limit(8)
            ->get()
            ->map(fn (AssignmentSubmission $s): array => [
                'id' => $s->id,
                'student' => $s->user?->name ?? 'Unknown',
                'assignment' => $s->assignment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'grade' => $s->grade,
            ])
            ->toArray();
    }

    protected function searchAssessmentSubmissions(string $term): array
    {
        return AssessmentSubmission::query()
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$term}%"))
                ->orWhereHas('assessment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with(['user:id,name', 'assessment:id,name'])
            ->limit(8)
            ->get()
            ->map(fn (AssessmentSubmission $s): array => [
                'id' => $s->id,
                'student' => $s->user?->name ?? 'Unknown',
                'assessment' => $s->assessment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'score' => $s->score,
            ])
            ->toArray();
    }

    /*
    |----------------------------------------------------------------------
    | Student panel sections
    |----------------------------------------------------------------------
    */

    protected function searchEnrolledCourses(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $this->applyContentSearch(
            Course::query()
                ->select(['id', 'title', 'code'])
                ->whereIn('id', $this->enrolledCourseIds($user)),
            $term,
            'courses',
            'courses_fts',
            ['title', 'code', 'description'],
        )->toArray();
    }

    protected function searchVisibleAssignments(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $this->applyContentSearch(
            Assignment::query()->select(['id', 'name', 'due_date'])->visibleTo($user),
            $term,
            'assignments',
            'assignments_fts',
            ['name', 'description'],
        )->map(fn (Assignment $a): array => [
            'name' => $a->name,
            'due' => $a->due_date?->format('Y-m-d') ?? 'No due date',
        ])->toArray();
    }

    protected function searchVisibleMaterials(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $this->applyContentSearch(
            LearningMaterial::query()->select(['id', 'title', 'material_type'])->visibleTo($user),
            $term,
            'learning_materials',
            'materials_fts',
            ['title', 'file_name', 'material_type'],
        )->toArray();
    }

    protected function searchVisibleAssessments(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $this->applyContentSearch(
            Assessment::query()->select(['id', 'name', 'score', 'due_date'])->visibleTo($user),
            $term,
            'assessments',
            'assessments_fts',
            ['name', 'description', ['CAST(score as CHAR) like ?']],
        )->map(fn (Assessment $assessment): array => [
            'name' => $assessment->name ?: 'Assessment',
            'score' => $assessment->score,
            'due_date' => $assessment->due_date?->format('Y-m-d') ?? '-',
        ])->toArray();
    }

    protected function searchMyAssignmentSubmissions(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('assignment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with('assignment:id,name')
            ->limit(8)
            ->get()
            ->map(fn (AssignmentSubmission $s): array => [
                'assignment' => $s->assignment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'grade' => $s->grade,
            ])
            ->toArray();
    }

    protected function searchMyAssessmentSubmissions(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return AssessmentSubmission::query()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhereHas('assessment', fn ($q2) => $q2->where('name', 'like', "%{$term}%")))
            ->with('assessment:id,name')
            ->limit(8)
            ->get()
            ->map(fn (AssessmentSubmission $s): array => [
                'assessment' => $s->assessment?->name ?? 'Unknown',
                'status' => $s->status ?? 'Pending',
                'score' => $s->score,
            ])
            ->toArray();
    }

    /*
    |----------------------------------------------------------------------
    | Instructor panel sections
    |----------------------------------------------------------------------
    */

    protected function searchInstructorCourses(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $this->applyContentSearch(
            Course::query()
                ->select(['id', 'title', 'code', 'is_active'])
                ->whereIn('id', $this->instructorCourseIds($user)),
            $term,
            'courses',
            'courses_fts',
            ['title', 'code', 'description'],
        )->toArray();
    }

    protected function searchInstructorStudents(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return $this->applyContentSearch(
            User::query()
                ->select(['id', 'name', 'email'])
                ->where('role', 'student')
                ->whereHas('courses', fn ($q) => $q->whereIn('courses.id', $this->instructorCourseIds($user))),
            $term,
            'users',
            'users_fts',
            ['name', 'email'],
        )->toArray();
    }

    protected function searchInstructorSessions(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return CourseSession::query()
            ->whereIn('course_id', $this->instructorCourseIds($user))
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('status', 'like', "%{$term}%")
                ->orWhereHas('course', fn ($q2) => $q2->where('title', 'like', "%{$term}%")))
            ->with(['course:id,title,code'])
            ->limit(8)
            ->get()
            ->map(fn (CourseSession $s): array => [
                'title' => $s->title ?: ($s->course->title ?? '—'),
                'course' => $s->course->code ?? '',
                'date' => $s->getEffectiveDate()->format('M d, Y'),
                'status' => ucfirst($s->status),
            ])
            ->toArray();
    }

    /*
    |----------------------------------------------------------------------
    | Scoping helpers (memoized per request)
    |----------------------------------------------------------------------
    */

    protected function enrolledCourseIds(User $user): Collection
    {
        return $this->enrolledCourseIds ??= $user->courses()->pluck('courses.id');
    }

    protected function instructorCourseIds(User $user): Collection
    {
        return $this->instructorCourseIds ??= $user->instructorCourses()->pluck('courses.id');
    }

    /*
    |----------------------------------------------------------------------
    | Full-text search helpers (SQLite FTS5, automatic LIKE fallback)
    |----------------------------------------------------------------------
    */

    /**
     * Apply the best available search to a content-table query: ranked FTS5
     * MATCH when the index exists (SQLite with the migration applied), or
     * the original LIKE '%term%' behaviour everywhere else. Scoping clauses
     * already on the query (visibility, enrolment, ownership) apply in both
     * modes, and the result limit and selected columns are unchanged.
     *
     * @param  array<int, string|array{0: string}>  $likeColumns  Plain column names for LIKE, or [raw where fragment] entries.
     */
    protected function applyContentSearch(Builder $query, string $term, string $baseTable, string $ftsTable, array $likeColumns, int $limit = 8): Collection
    {
        $ids = $this->ftsIds($ftsTable, $baseTable, $term);

        if ($ids !== null) {
            if ($ids === []) {
                return new Collection;
            }

            $rank = array_flip($ids);

            return $query
                ->whereIn("{$baseTable}.id", $ids)
                ->get()
                ->sortBy(fn ($model): int => $rank[$model->id] ?? PHP_INT_MAX)
                ->take($limit)
                ->values();
        }

        $query->where(function (Builder $q) use ($likeColumns, $term): void {
            foreach ($likeColumns as $index => $column) {
                $or = $index > 0;

                if (is_array($column)) {
                    $q->{$or ? 'orWhereRaw' : 'whereRaw'}($column[0], ["%{$term}%"]);
                } else {
                    $q->{$or ? 'orWhere' : 'where'}($column, 'like', "%{$term}%");
                }
            }
        });

        return $query->limit($limit)->get();
    }

    /**
     * Base-table ids matching the term, ordered by bm25() relevance (best
     * first). More ids than the display limit are fetched so that scoping
     * filters applied afterwards do not starve the result list. Returns null
     * when FTS is unavailable or errors, signalling the LIKE fallback.
     *
     * @return array<int, int>|null
     */
    protected function ftsIds(string $ftsTable, string $baseTable, string $term, int $limit = 40): ?array
    {
        if (! $this->ftsAvailable($ftsTable)) {
            return null;
        }

        $match = $this->toFtsMatchQuery($term);

        if ($match === null) {
            return null;
        }

        try {
            return DB::table($baseTable)
                ->join($ftsTable, "{$ftsTable}.rowid", '=', "{$baseTable}.id")
                ->whereRaw("{$ftsTable} MATCH ?", [$match])
                ->orderByRaw("bm25({$ftsTable}{$this->ftsColumnWeights($ftsTable)})")
                ->limit($limit)
                ->pluck("{$baseTable}.id")
                ->map(fn ($id): int => (int) $id)
                ->all();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Whether a usable FTS5 index exists for the given FTS table. Memoized
     * per table so repeated sections cost one schema lookup per request.
     */
    protected function ftsAvailable(string $ftsTable): bool
    {
        static $available = [];

        return $available[$ftsTable] ??= DB::getDriverName() === 'sqlite' && Schema::hasTable($ftsTable);
    }

    /**
     * Column weights for bm25(): the first indexed column (title/name) counts
     * most, the second (code/email/file) next, the rest equally. Matches the
     * column order created by the FTS migration; empty string when the table
     * is unknown, which makes bm25() use equal weights.
     */
    protected function ftsColumnWeights(string $ftsTable): string
    {
        $columnCount = [
            'users_fts' => 2,
            'courses_fts' => 3,
            'assignments_fts' => 2,
            'assessments_fts' => 3,
            'materials_fts' => 3,
        ][$ftsTable] ?? null;

        if ($columnCount === null) {
            return '';
        }

        $weights = array_slice(array_merge([10.0, 5.0], array_fill(0, $columnCount, 1.0)), 0, $columnCount);

        return ', '.implode(', ', $weights);
    }

    /**
     * Convert a raw search term into a safe FTS5 MATCH query: each
     * whitespace-separated token becomes a quoted prefix term, and tokens
     * are ANDed together. Returns null when nothing searchable remains.
     */
    protected function toFtsMatchQuery(string $term): ?string
    {
        $tokens = preg_split('/\s+/u', trim($term)) ?: [];
        $tokens = array_values(array_filter($tokens, fn (string $token): bool => $token !== ''));

        if ($tokens === []) {
            return null;
        }

        return implode(' ', array_map(
            fn (string $token): string => '"'.str_replace('"', '""', $token).'"*',
            $tokens
        ));
    }
}
