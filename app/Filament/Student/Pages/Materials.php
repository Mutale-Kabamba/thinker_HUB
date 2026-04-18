<?php

namespace App\Filament\Student\Pages;

use App\Models\LearningMaterial;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;

class Materials extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.student.pages.materials';

    public array $materials = [];

    #[Url(as: 'category')]
    public string $filterCategory = '';

    #[Url(as: 'type')]
    public string $filterType = '';

    #[Url(as: 'course')]
    public string $filterCourse = '';

    public array $availableCourses = [];

    public function mount(): void
    {
        $this->loadMaterials();
    }

    public function updatedFilterCategory(): void
    {
        $this->loadMaterials();
    }

    public function updatedFilterType(): void
    {
        $this->loadMaterials();
    }

    public function updatedFilterCourse(): void
    {
        $this->loadMaterials();
    }

    public function downloadFile(int $materialId): mixed
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $material = LearningMaterial::query()
            ->visibleTo($user)
            ->findOrFail($materialId);

        if (! $material->file_path) {
            Notification::make()->title('File not available.')->danger()->send();

            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($material->file_path)) {
            Notification::make()->title('File not found.')->danger()->send();

            return null;
        }

        $extension = pathinfo($material->file_path, PATHINFO_EXTENSION);
        $downloadName = \Illuminate\Support\Str::slug($material->title) . '.' . $extension;

        return $disk->download($material->file_path, $downloadName);
    }

    private function loadMaterials(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $scopeLabels = [
            'all' => 'General',
            'level' => 'Level',
            'personal' => 'Personal',
        ];

        $query = LearningMaterial::query()
            ->with('course')
            ->visibleTo($user);

        if ($this->filterCategory !== '') {
            $query->where('category', $this->filterCategory);
        }
        if ($this->filterType !== '') {
            $query->where('material_type', $this->filterType);
        }
        if ($this->filterCourse !== '') {
            $query->where('course_id', $this->filterCourse);
        }

        $allMaterials = $query->latest()->get();

        // Build available courses from all visible materials (unfiltered)
        $this->availableCourses = LearningMaterial::query()
            ->with('course')
            ->visibleTo($user)
            ->get()
            ->pluck('course')
            ->filter()
            ->unique('id')
            ->map(fn ($c) => ['id' => (string) $c->id, 'title' => $c->title])
            ->values()
            ->all();

        $this->materials = $allMaterials
            ->map(fn (LearningMaterial $item) => [
                'id' => $item->id,
                'course' => $item->course?->title ?? 'Unassigned course',
                'title' => $item->title,
                'category' => $item->category ?? 'General Notices',
                'description' => $item->description,
                'scope' => $scopeLabels[$item->scope] ?? ucfirst($item->scope),
                'type' => $item->material_type,
                'file_path' => $item->file_path,
                'link_url' => $item->link_url,
                'video_url' => $item->video_url,
                'created_at' => $item->created_at?->format('M d, Y'),
            ])
            ->values()
            ->all();
    }
}
