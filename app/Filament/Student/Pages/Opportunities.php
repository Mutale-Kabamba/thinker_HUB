<?php

namespace App\Filament\Student\Pages;

use App\Models\Opportunity;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class Opportunities extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'GROWTH & SOCIAL';

    protected static ?string $navigationLabel = 'Opportunities';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.student.pages.opportunities';

    /** @var array<int, array<string, mixed>> */
    public array $opportunities = [];

    /** @var array<int, string> */
    public array $types = [];

    #[Url(as: 'type')]
    public string $filterType = '';

    public ?int $openComments = null;

    public function mount(): void
    {
        $this->types = Opportunity::TYPES;
        $this->loadOpportunities();
    }

    public function toggleComments(int $id): void
    {
        $this->openComments = $this->openComments === $id ? null : $id;
    }

    public function updatedFilterType(): void
    {
        $this->loadOpportunities();
    }

    private function loadOpportunities(): void
    {
        $query = Opportunity::query()->active();

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        $this->opportunities = $query
            ->orderByRaw('expires_at IS NULL')
            ->orderBy('expires_at')
            ->latest()
            ->get()
            ->map(fn (Opportunity $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'description' => $item->description,
                'link_url' => $item->link_url,
                'promo_code' => $item->promo_code,
                'provider' => $item->provider,
                'expires_at' => $item->expires_at?->format('M d, Y'),
            ])
            ->values()
            ->all();
    }
}
