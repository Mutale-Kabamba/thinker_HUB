<?php

namespace App\Filament\Student\Pages;

use App\Models\LearningMaterial;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Support\PublicDiskPath;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

class Materials extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'LEARNING';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        try {
            $lastViewed = session('last_viewed_materials_at_' . $user->id)
                ?? \Illuminate\Support\Facades\Cache::get('user_' . $user->id . '_last_viewed_materials_at');

            $query = LearningMaterial::query()->visibleTo($user);

            if ($lastViewed) {
                $query->where('created_at', '>', $lastViewed);
            } else {
                $query->where('created_at', '>=', now()->subDays(3));
            }

            $newCount = $query->count();

            return $newCount > 0 ? (string) $newCount : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New learning materials';
    }

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
        $user = auth()->user();
        if ($user) {
            session(['last_viewed_materials_at_' . $user->id => now()]);
            \Illuminate\Support\Facades\Cache::put('user_' . $user->id . '_last_viewed_materials_at', now(), now()->addDays(60));
        }

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

        $path = PublicDiskPath::normalize($material->file_path);
        $disk = Storage::disk('public');

        if (! $path || ! $disk->exists($path)) {
            Notification::make()->title('File not found.')->danger()->send();

            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $downloadName = Str::slug($material->title).'.'.$extension;

        try {
            app(\App\Services\GamificationService::class)->awardMaterialView($user, $material);
        } catch (\Throwable $e) {
            report($e);
        }

        return $disk->download($path, $downloadName);
    }

    public function recordView(int $materialId): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $material = LearningMaterial::query()->visibleTo($user)->find($materialId);
        if (! $material) {
            return;
        }

        try {
            app(\App\Services\GamificationService::class)->awardMaterialView($user, $material);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function toggleBookmark(int $materialId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $material = LearningMaterial::query()->visibleTo($user)->find($materialId);

        if (! $material) {
            return;
        }

        $user->toggleBookmark($material);

        $this->loadMaterials();
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

        $bookmarkedMaterialIds = $user->bookmarks()
            ->where('bookmarkable_type', (new LearningMaterial)->getMorphClass())
            ->pluck('bookmarkable_id')
            ->all();

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
                'bookmarked' => in_array($item->id, $bookmarkedMaterialIds, true),
            ])
            ->values()
            ->all();
    }
}
