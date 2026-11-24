<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Tenant;
use App\Models\UtmAttribution;
use App\Services\AnalyticsExportService;
use App\Services\RegionalAnalyticsService;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RegionalAnalytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMUNITY & SYSTEM';

    protected static ?string $navigationLabel = 'Analytics & Reports';

    protected static ?string $slug = 'regional-analytics';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.regional-analytics';

    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?string $courseId = '';
    public ?string $tenantId = '';
    public ?string $utmSource = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->isAdmin() || $user->isProgramAdmin()));
    }

    public function getTitle(): string|Htmlable
    {
        return 'Platform Analytics & Exportable Reports';
    }

    public function mount(): void
    {
        $user = auth()->user();
        if ($user && $user->isProgramAdmin() && $user->managedTenantId()) {
            $this->tenantId = (string) $user->managedTenantId();
        }
    }

    public function exportReport(string $dataset, string $format): Response
    {
        return app(AnalyticsExportService::class)->download(
            dataset: $dataset,
            format: $format,
            filters: $this->activeFilters(),
            actingUser: auth()->user(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function activeFilters(): array
    {
        return [
            'date_from' => $this->dateFrom ?: null,
            'date_to' => $this->dateTo ?: null,
            'course_id' => $this->courseId !== '' ? (int) $this->courseId : null,
            'tenant_id' => $this->tenantId !== '' ? (int) $this->tenantId : null,
            'utm_source' => $this->utmSource !== '' ? $this->utmSource : null,
        ];
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $summary = app(RegionalAnalyticsService::class)->summary($this->activeFilters(), $user);

        $tenants = Schema::hasTable('tenants')
            ? Tenant::query()
                ->when($user?->isProgramAdmin() && $user->managedTenantId(), fn ($q) => $q->whereKey($user->managedTenantId()))
                ->orderBy('name')
                ->get(['id', 'name', 'country_name'])
            : collect();

        $courses = Course::query()
            ->visibleToAdministrator($user, includeGlobal: true)
            ->orderBy('title')
            ->get(['id', 'title']);

        $utmSources = Schema::hasTable('utm_attributions')
            ? UtmAttribution::query()
                ->whereNotNull('utm_source')
                ->where('utm_source', '!=', '')
                ->distinct()
                ->orderBy('utm_source')
                ->pluck('utm_source')
            : collect();

        return [
            'kpis' => $summary['kpis'],
            'utmBreakdown' => $summary['utm_breakdown'],
            'tenantBreakdown' => $summary['tenant_breakdown'],
            'tenants' => $tenants,
            'courses' => $courses,
            'utmSources' => $utmSources,
            'isProgramAdmin' => (bool) $user?->isProgramAdmin(),
        ];
    }
}
