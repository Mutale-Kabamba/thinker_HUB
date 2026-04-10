<?php

namespace App\Filament\Student\Pages;

use App\Models\LearningMaterial;
use Filament\Pages\Page;

class Materials extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.student.pages.materials';

    public array $materials = [];

    public function mount(): void
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

        $this->materials = LearningMaterial::query()
            ->with('course')
            ->visibleTo($user)
            ->latest()
            ->get()
            ->map(fn (LearningMaterial $item) => [
                'course' => $item->course?->title ?? 'Unassigned course',
                'title' => $item->title,
                'scope' => $scopeLabels[$item->scope] ?? ucfirst($item->scope),
                'type' => $item->material_type,
            ])
            ->values()
            ->all();
    }
}
