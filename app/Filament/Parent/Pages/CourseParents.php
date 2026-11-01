<?php

namespace App\Filament\Parent\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class CourseParents extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Course Parents Circle';

    protected static ?string $title = 'Course Parents Circle';

    protected static ?int $navigationSort = 5;

    protected static string|\UnitEnum|null $navigationGroup = 'PARENT COMMUNITY';

    protected string $view = 'filament.parent.pages.course-parents';

    #[Url(as: 'course')]
    public ?int $selectedCourseId = null;

    public string $search = '';

    public array $courses = [];

    public array $peerParents = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedSelectedCourseId(): void
    {
        $this->loadData();
    }

    public function updatedSearch(): void
    {
        $this->loadData();
    }

    public function selectCourse(?int $courseId): void
    {
        $this->selectedCourseId = $courseId;
        $this->loadData();
    }

    public function loadData(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $kidIds = $parent->kids()->pluck('users.id')->all();

        // 1. Fetch courses enrolled by parent's kids
        $kidCourses = Course::query()
            ->whereHas('enrollments', fn ($q) => $q->whereIn('user_id', $kidIds))
            ->orderBy('title')
            ->get();

        $this->courses = $kidCourses->map(fn (Course $c): array => [
            'id' => $c->id,
            'title' => $c->title,
            'code' => $c->code,
            'is_for_kids' => (bool) $c->is_for_kids,
        ])->all();

        // 2. Fetch fellow parents strictly in the same course(s)
        $rawParents = $parent->getCoursePeerParents($this->selectedCourseId);

        $selectedCourse = $this->selectedCourseId ? Course::find($this->selectedCourseId) : null;

        $searchTerm = mb_strtolower(trim($this->search));

        $this->peerParents = $rawParents->map(function (User $p) use ($selectedCourse): array {
            // Find child name(s) and shared course details
            $kidsInfo = $p->kids->map(function (User $child) use ($selectedCourse): array {
                $enrolledCourses = $child->enrollments
                    ->map(fn ($e) => $e->course?->title)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $childFirstName = explode(' ', trim($child->name))[0] ?? 'Child';

                return [
                    'first_name' => $childFirstName,
                    'track' => $child->track ?? 'Beginner',
                    'courses' => $enrolledCourses,
                ];
            })->all();

            $allCourses = collect($kidsInfo)->pluck('courses')->flatten()->unique()->values()->all();
            $childrenNames = collect($kidsInfo)->pluck('first_name')->unique()->join(', ');

            return [
                'id' => $p->id,
                'name' => $p->name,
                'avatar' => $p->getFilamentAvatarUrl(),
                'email' => $p->email,
                'whatsapp' => $p->whatsapp,
                'children_summary' => $childrenNames ? "Parent of {$childrenNames}" : 'Parent',
                'shared_courses' => $allCourses,
                'joined_date' => $p->created_at?->format('M Y'),
            ];
        })
        ->filter(function (array $p) use ($searchTerm): bool {
            if ($searchTerm === '') {
                return true;
            }

            return str_contains(mb_strtolower($p['name']), $searchTerm)
                || str_contains(mb_strtolower($p['children_summary']), $searchTerm)
                || str_contains(mb_strtolower(implode(' ', $p['shared_courses'])), $searchTerm);
        })
        ->values()
        ->all();
    }
}
