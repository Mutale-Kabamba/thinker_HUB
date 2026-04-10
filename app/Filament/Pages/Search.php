<?php

namespace App\Filament\Pages;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\User;
use Filament\Pages\Page;

class Search extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 90;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.search';

    public string $query = '';

    public array $results = [
        'students' => [],
        'courses' => [],
        'assignments' => [],
        'assessments' => [],
        'materials' => [],
    ];

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
        $term = trim($this->query);

        if ($term === '') {
            $this->results = [
                'students' => [],
                'courses' => [],
                'assignments' => [],
                'assessments' => [],
                'materials' => [],
            ];

            return;
        }

        $this->results['students'] = User::query()
            ->where('role', 'student')
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['name', 'email'])
            ->toArray();

        $this->results['courses'] = Course::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['title', 'code'])
            ->toArray();

        $this->results['assignments'] = Assignment::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['name', 'scope'])
            ->toArray();

        $this->results['assessments'] = Assessment::query()
            ->where(fn ($q) => $q->where('status', 'like', "%{$term}%")->orWhereRaw('CAST(score as CHAR) like ?', ["%{$term}%"]))
            ->limit(8)
            ->get(['status', 'score'])
            ->toArray();

        $this->results['materials'] = LearningMaterial::query()
            ->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('file_name', 'like', "%{$term}%")->orWhere('material_type', 'like', "%{$term}%"))
            ->limit(8)
            ->get(['title', 'material_type'])
            ->toArray();
    }
}
