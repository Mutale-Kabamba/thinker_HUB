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
use Illuminate\Support\Collection;

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
        return User::query()
            ->where('role', 'student')
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    protected function searchCourses(string $term): array
    {
        return Course::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'code'])
            ->toArray();
    }

    protected function searchAssignments(string $term): array
    {
        return Assignment::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'name', 'scope'])
            ->toArray();
    }

    protected function searchAssessments(string $term): array
    {
        return Assessment::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")->orWhereRaw('CAST(score as CHAR) like ?', ["%{$term}%"]))
            ->limit(8)
            ->get(['id', 'name', 'score'])
            ->toArray();
    }

    protected function searchMaterials(string $term): array
    {
        return LearningMaterial::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('file_name', 'like', "%{$term}%")->orWhere('material_type', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'material_type'])
            ->toArray();
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

        return Course::query()
            ->whereIn('id', $this->enrolledCourseIds($user))
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'code'])
            ->toArray();
    }

    protected function searchVisibleAssignments(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return Assignment::query()
            ->visibleTo($user)
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['name', 'due_date'])
            ->map(fn (Assignment $a): array => [
                'name' => $a->name,
                'due' => $a->due_date?->format('Y-m-d') ?? 'No due date',
            ])
            ->toArray();
    }

    protected function searchVisibleMaterials(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return LearningMaterial::query()
            ->visibleTo($user)
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('file_name', 'like', "%{$term}%")
                ->orWhere('material_type', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'material_type'])
            ->toArray();
    }

    protected function searchVisibleAssessments(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return Assessment::query()
            ->visibleTo($user)
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereRaw('CAST(score as CHAR) like ?', ["%{$term}%"]))
            ->limit(8)
            ->get(['name', 'score', 'due_date'])
            ->map(fn (Assessment $assessment): array => [
                'name' => $assessment->name ?: 'Assessment',
                'score' => $assessment->score,
                'due_date' => $assessment->due_date?->format('Y-m-d') ?? '-',
            ])
            ->toArray();
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

        return Course::query()
            ->whereIn('id', $this->instructorCourseIds($user))
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'title', 'code', 'is_active'])
            ->toArray();
    }

    protected function searchInstructorStudents(string $term): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return User::query()
            ->where('role', 'student')
            ->whereHas('courses', fn ($q) => $q->whereIn('courses.id', $this->instructorCourseIds($user)))
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['id', 'name', 'email'])
            ->toArray();
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
}
