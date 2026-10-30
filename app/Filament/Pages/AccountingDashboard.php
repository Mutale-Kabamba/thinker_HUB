<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'FINANCE & ACCOUNTING';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Accounting Dashboard';

    protected static ?string $title = 'Accounting & Registrations Dashboard';

    protected string $view = 'filament.pages.accounting-dashboard';

    public string $search = '';

    public string $dateRange = 'all'; // 'all', 'today', 'this_week', 'this_month', 'this_year'

    public string $channel = 'all'; // 'all', 'direct_checkout', 'admin_manual', 'instructor_manual'

    public string $feeType = 'all'; // 'all', 'paid', 'free'

    public string $paymentMethod = 'all';

    public ?int $courseId = null;

    public ?int $viewingEnrollmentId = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        // Default mount
    }

    protected function getViewData(): array
    {
        return [
            'stats' => $this->kpi_stats,
            'ledgerItems' => $this->ledger_items,
            'courseOptions' => $this->course_options,
            'viewingEnrollment' => $this->viewing_enrollment,
        ];
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->dateRange = 'all';
        $this->channel = 'all';
        $this->feeType = 'all';
        $this->paymentMethod = 'all';
        $this->courseId = null;
    }

    public function viewDetails(?int $id): void
    {
        $this->viewingEnrollmentId = $id;
    }

    public function closeDetails(): void
    {
        $this->viewingEnrollmentId = null;
    }

    /**
     * Base query for enrollments filtered according to user controls.
     */
    protected function getFilteredQuery(): Builder
    {
        $query = Enrollment::query()
            ->with(['user', 'course', 'intake', 'registeredBy', 'payment'])
            ->latest('created_at');

        // Course filter
        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        }

        // Channel filter
        if ($this->channel !== 'all') {
            $query->where('registration_type', $this->channel);
        }

        // Fee Type filter
        if ($this->feeType === 'paid') {
            $query->where(function ($q): void {
                $q->where('payment_status', Enrollment::STATUS_PAID)
                    ->orWhere('is_free', false);
            });
        } elseif ($this->feeType === 'free') {
            $query->where(function ($q): void {
                $q->where('payment_status', Enrollment::STATUS_FREE)
                    ->orWhere('is_free', true);
            });
        }

        // Payment Method filter
        if ($this->paymentMethod !== 'all') {
            $query->where('payment_method', $this->paymentMethod);
        }

        // Date Range filter
        $now = Carbon::now();
        if ($this->dateRange === 'today') {
            $query->whereDate('created_at', $now->toDateString());
        } elseif ($this->dateRange === 'this_week') {
            $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        } elseif ($this->dateRange === 'this_month') {
            $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        } elseif ($this->dateRange === 'this_year') {
            $query->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
        }

        // Search text
        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $sub) use ($term): void {
                $sub->where('transaction_reference', 'like', $term)
                    ->orWhere('payment_notes', 'like', $term)
                    ->orWhereHas('user', function (Builder $u) use ($term): void {
                        $u->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    })
                    ->orWhereHas('course', function (Builder $c) use ($term): void {
                        $c->where('title', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    });
            });
        }

        return $query;
    }

    /**
     * Compute summary financial metrics across the filtered dataset.
     *
     * @return array<string, mixed>
     */
    public function getKpiStatsProperty(): array
    {
        $base = (clone $this->getFilteredQuery());
        $all = $base->get();

        $grossRevenue = $all->where('is_free', false)->sum('fee_amount');
        $directRevenue = $all->where('is_free', false)->where('registration_type', Enrollment::CHANNEL_DIRECT_CHECKOUT)->sum('fee_amount');
        $manualRevenue = $all->where('is_free', false)->whereIn('registration_type', [Enrollment::CHANNEL_ADMIN_MANUAL, Enrollment::CHANNEL_INSTRUCTOR_MANUAL])->sum('fee_amount');

        $totalCount = $all->count();
        $paidCount = $all->where('is_free', false)->count();
        $freeCount = $all->where('is_free', true)->count();
        $paidPercentage = $totalCount > 0 ? round(($paidCount / $totalCount) * 100, 1) : 0;

        // Calculate estimated scholarship/waiver volume based on course fees
        $waiverValue = $all->where('is_free', true)->sum(function (Enrollment $e): float {
            return (float) ($e->course?->fees ?? 0);
        });

        return [
            'gross_revenue' => (float) $grossRevenue,
            'direct_revenue' => (float) $directRevenue,
            'manual_revenue' => (float) $manualRevenue,
            'total_count' => $totalCount,
            'paid_count' => $paidCount,
            'free_count' => $freeCount,
            'paid_percentage' => $paidPercentage,
            'waiver_value' => (float) $waiverValue,
        ];
    }

    /**
     * Get paginated ledger items.
     */
    public function getLedgerItemsProperty(): Collection
    {
        return $this->getFilteredQuery()->limit(150)->get();
    }

    /**
     * Get the active record for details modal.
     */
    public function getViewingEnrollmentProperty(): ?Enrollment
    {
        if (! $this->viewingEnrollmentId) {
            return null;
        }

        return Enrollment::query()
            ->with(['user', 'course', 'intake', 'registeredBy', 'payment'])
            ->find($this->viewingEnrollmentId);
    }

    /**
     * List of courses for dropdown filter.
     *
     * @return array<int, string>
     */
    public function getCourseOptionsProperty(): array
    {
        return Course::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    /**
     * Export the filtered ledger as CSV for financial audit.
     */
    public function exportCsv(): StreamedResponse
    {
        $records = $this->getFilteredQuery()->get();
        $filename = 'thinker-hub-accounting-ledger-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Receipt / Reference #',
                'Student Name',
                'Student Email',
                'Course Code',
                'Course Title',
                'Intake Cohort',
                'Registration Channel',
                'Fee Classification',
                'Amount Paid (ZMW)',
                'Payment Method',
                'Bank / Provider',
                'Recorded By',
                'Date Recorded',
                'Accounting Notes',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->transaction_reference ?? 'N/A',
                    $r->user?->name ?? 'Unknown Student',
                    $r->user?->email ?? '',
                    $r->course?->code ?? '',
                    $r->course?->title ?? '',
                    $r->intake?->name ?? 'General',
                    $r->registrationChannelLabel(),
                    $r->isFree() ? 'Free / Scholarship' : 'Paid',
                    $r->isFree() ? '0.00' : number_format((float) ($r->fee_amount ?? 0), 2, '.', ''),
                    $r->paymentMethodLabel(),
                    $r->payment?->provider ?? ($r->isFree() ? 'Waiver' : 'N/A'),
                    $r->registeredBy?->name ?? ($r->registration_type === 'direct_checkout' ? 'Direct Online Checkout' : 'System'),
                    $r->paid_at ? $r->paid_at->format('Y-m-d H:i') : $r->created_at->format('Y-m-d H:i'),
                    $r->payment_notes ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
