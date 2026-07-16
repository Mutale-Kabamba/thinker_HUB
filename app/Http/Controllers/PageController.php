<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    private const TRACKS = ['Beginner', 'Intermediate', 'Advanced'];
    private const SCOPES = ['all', 'level', 'personal'];

    public function studentOverview(): View
    {
        $user = Auth::user();
        $this->authorize('viewAny', LearningMaterial::class);

        $visibleMaterials = LearningMaterial::query()
            ->visibleTo($user)
            ->latest()
            ->get();

        $stats = [
            'track' => $user->track,
            'uploads' => Assessment::query()->where('user_id', $user->id)->count(),
            'materials' => $visibleMaterials->count(),
        ];

        $materials = $visibleMaterials
            ->take(8)
            ->map(function (LearningMaterial $item): array {
                return [
                    'name' => $item->file_name ?: $item->title,
                    'type' => $item->material_type,
                ];
            })
            ->values()
            ->all();

        return view('student.overview', compact('stats', 'materials'));
    }

    public function studentAssignments(): View
    {
        $this->authorize('viewAny', Assignment::class);

        $scopeLabels = [
            'all' => 'General (all students)',
            'level' => 'Intended level only',
            'personal' => 'Personal',
        ];

        $assignments = Assignment::query()
            ->with('course')
            ->visibleTo(Auth::user())
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->get()
            ->map(function (Assignment $item) use ($scopeLabels): array {
                return [
                    'name' => $item->name,
                    'course' => $item->course?->title ?? 'Unassigned course',
                    'scope' => $scopeLabels[$item->scope] ?? ucfirst($item->scope),
                    'due' => optional($item->due_date)?->format('Y-m-d') ?? 'No due date',
                ];
            })
            ->values()
            ->all();

        return view('student.assignments', compact('assignments'));
    }

    public function studentMaterials(): View
    {
        $this->authorize('viewAny', LearningMaterial::class);

        $scopeLabels = [
            'all' => 'General',
            'level' => 'Intended level only',
            'personal' => 'Personal',
        ];

        $materials = LearningMaterial::query()
            ->with('course')
            ->visibleTo(Auth::user())
            ->latest()
            ->get()
            ->map(function (LearningMaterial $item) use ($scopeLabels): array {
                return [
                    'title' => $item->title,
                    'course' => $item->course?->title ?? 'Unassigned course',
                    'scope' => $scopeLabels[$item->scope] ?? ucfirst($item->scope),
                    'type' => $item->material_type,
                ];
            })
            ->values()
            ->all();

        return view('student.materials', compact('materials'));
    }

    public function studentCourses(): View
    {
        $this->authorize('viewAny', Course::class);

        $user = Auth::user();
        $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();
        $courses = Course::query()
            ->orderBy('title')
            ->get()
            ->map(function (Course $course) use ($enrolledCourseIds): array {
                return [
                    'title' => $course->title,
                    'code' => $course->code,
                    'description' => $course->description,
                    'is_active' => $course->is_active,
                    'enrolled' => in_array($course->id, $enrolledCourseIds, true),
                ];
            })
            ->all();

        return view('student.courses', compact('courses'));
    }

    public function adminOverview(): View
    {
        $metrics = [
            'students' => User::query()->where('role', 'student')->count(),
            'assessments' => Assessment::query()->count(),
            'submissions' => Assignment::query()->count(),
        ];

        return view('admin.overview', compact('metrics'));
    }

    public function adminStudents(): View
    {
        $this->authorize('viewAny', User::class);

        $students = User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->with('courses:id,title')
            ->get(['id', 'name', 'email', 'track'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'track' => $user->track,
                'courses' => $user->courses->pluck('title')->implode(', '),
            ])
            ->values()
            ->all();

        $courses = Course::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.students', compact('students', 'courses'));
    }

    public function adminAssignments(): View
    {
        $this->authorize('create', Assignment::class);

        $assignments = Assignment::query()
            ->with(['targetUser', 'course'])
            ->latest()
            ->get();
        $students = User::query()->where('role', 'student')->orderBy('name')->get(['id', 'name']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title', 'code']);

        return view('admin.assignments', [
            'assignments' => $assignments,
            'students' => $students,
            'courses' => $courses,
            'tracks' => self::TRACKS,
        ]);
    }

    public function adminAssessments(): View
    {
        $this->authorize('create', Assessment::class);

        $assessments = Assessment::query()
            ->with(['user', 'course'])
            ->latest()
            ->get();
        $students = User::query()->where('role', 'student')->orderBy('name')->get(['id', 'name']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title', 'code']);

        return view('admin.assessments', [
            'assessments' => $assessments,
            'students' => $students,
            'courses' => $courses,
        ]);
    }

    public function adminMaterials(): View
    {
        $this->authorize('create', LearningMaterial::class);

        $materials = LearningMaterial::query()
            ->with(['targetUser', 'course'])
            ->latest()
            ->get();
        $students = User::query()->where('role', 'student')->orderBy('name')->get(['id', 'name']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title', 'code']);

        return view('admin.materials', [
            'materials' => $materials,
            'students' => $students,
            'courses' => $courses,
            'tracks' => self::TRACKS,
        ]);
    }

    public function adminCourses(): View
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::query()
            ->withCount('enrollments')
            ->latest()
            ->get();

        return view('admin.courses', compact('courses'));
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $this->authorize('create', Assignment::class);

        Assignment::query()->create($this->validateAssignmentPayload($request));

        return redirect()->route('admin.assignments')->with('status', 'Assignment created.');
    }

    public function updateAssignment(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $assignment->update($this->validateAssignmentPayload($request));

        return redirect()->route('admin.assignments')->with('status', 'Assignment updated.');
    }

    public function destroyAssignment(Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();

        return redirect()->route('admin.assignments')->with('status', 'Assignment deleted.');
    }

    public function storeMaterial(Request $request): RedirectResponse
    {
        $this->authorize('create', LearningMaterial::class);

        LearningMaterial::query()->create($this->validateMaterialPayload($request));

        return redirect()->route('admin.materials')->with('status', 'Learning material created.');
    }

    public function updateMaterial(Request $request, LearningMaterial $learningMaterial): RedirectResponse
    {
        $this->authorize('update', $learningMaterial);

        $learningMaterial->update($this->validateMaterialPayload($request));

        return redirect()->route('admin.materials')->with('status', 'Learning material updated.');
    }

    public function destroyMaterial(LearningMaterial $learningMaterial): RedirectResponse
    {
        $this->authorize('delete', $learningMaterial);

        $learningMaterial->delete();

        return redirect()->route('admin.materials')->with('status', 'Learning material deleted.');
    }

    public function storeAssessment(Request $request): RedirectResponse
    {
        $this->authorize('create', Assessment::class);

        Assessment::query()->create($this->validateAssessmentPayload($request));

        return redirect()->route('admin.assessments')->with('status', 'Assessment created.');
    }

    public function updateAssessment(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        $assessment->update($this->validateAssessmentPayload($request));

        return redirect()->route('admin.assessments')->with('status', 'Assessment updated.');
    }

    public function destroyAssessment(Assessment $assessment): RedirectResponse
    {
        $this->authorize('delete', $assessment);

        $assessment->delete();

        return redirect()->route('admin.assessments')->with('status', 'Assessment deleted.');
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        Course::query()->create($this->validateCoursePayload($request));

        return redirect()->route('admin.courses')->with('status', 'Course created.');
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update($this->validateCoursePayload($request, $course));

        return redirect()->route('admin.courses')->with('status', 'Course updated.');
    }

    public function destroyCourse(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()->route('admin.courses')->with('status', 'Course deleted.');
    }

    private function validateAssignmentPayload(Request $request): array
    {
        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scope' => ['required', Rule::in(self::SCOPES)],
            'target_track' => ['nullable', Rule::in(self::TRACKS)],
            'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($data['scope'] !== 'level') {
            $data['target_track'] = null;
        }

        if ($data['scope'] !== 'personal') {
            $data['target_user_id'] = null;
        }

        if (($data['scope'] ?? null) === 'personal' && isset($data['target_user_id'])) {
            $this->assertStudentEnrolledInCourse((int) $data['target_user_id'], (int) $data['course_id']);
        }

        return $data;
    }

    private function validateMaterialPayload(Request $request): array
    {
        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'material_type' => ['required', 'string', 'max:100'],
            'scope' => ['required', Rule::in(self::SCOPES)],
            'target_track' => ['nullable', Rule::in(self::TRACKS)],
            'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'file_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['scope'] !== 'level') {
            $data['target_track'] = null;
        }

        if ($data['scope'] !== 'personal') {
            $data['target_user_id'] = null;
        }

        if (($data['scope'] ?? null) === 'personal' && isset($data['target_user_id'])) {
            $this->assertStudentEnrolledInCourse((int) $data['target_user_id'], (int) $data['course_id']);
        }

        return $data;
    }

    private function validateAssessmentPayload(Request $request): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $this->assertStudentEnrolledInCourse((int) $data['user_id'], (int) $data['course_id']);

        return $data;
    }

    private function validateCoursePayload(Request $request, ?Course $course = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($course?->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => (bool) $request->boolean('is_active'),
        ];
    }

    private function assertStudentEnrolledInCourse(int $userId, int $courseId): void
    {
        $isEnrolled = User::query()
            ->whereKey($userId)
            ->whereHas('courses', fn ($query) => $query->where('courses.id', $courseId))
            ->exists();

        if (! $isEnrolled) {
            abort(422, 'The selected student is not enrolled in the selected course.');
        }
    }
}
