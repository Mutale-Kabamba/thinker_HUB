<?php

namespace App\Ai\Tools\Admin;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SystemFinanceAnalyticsTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Query platform-wide revenue, breakdown by payment method (Mobile Money: MTN, Airtel, Zamtel; Cards; Bank transfers), revenue per course, and inspect recent or failed transactions.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'mode' => $schema->string()->description('Mode: "summary", "by_method", "by_course", "failed_transactions", or "recent_payments". Default is "summary".')->nullable(),
            'days' => $schema->integer()->description('Number of days to analyze (e.g. 7, 30, 90). Default is 30.')->nullable(),
            'limit' => $schema->integer()->description('Max records to return for list views. Default is 15.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. Financial analytics require administrative privileges.';
        }

        $mode = strtolower(trim((string) ($request['mode'] ?? 'summary')));
        $days = ! empty($request['days']) ? max(1, min(365, (int) $request['days'])) : 30;
        $limit = ! empty($request['limit']) ? max(1, min(50, (int) $request['limit'])) : 15;
        $startDate = now()->subDays($days);

        if ($mode === 'summary') {
            $totalAllTime = (float) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount');
            $periodQuery = Payment::where('status', Payment::STATUS_COMPLETED)->where('paid_at', '>=', $startDate);
            $totalPeriod = (float) $periodQuery->sum('amount');
            $countPeriod = $periodQuery->count();

            $discountsGiven = (float) Payment::where('paid_at', '>=', $startDate)->sum('discount_amount');
            $pendingCount = Payment::where('status', Payment::STATUS_PENDING)->count();
            $failedCount = Payment::where('status', Payment::STATUS_FAILED)->where('created_at', '>=', $startDate)->count();

            return json_encode([
                'currency' => 'ZMW',
                'period_days' => $days,
                'revenue_in_period' => round($totalPeriod, 2),
                'all_time_revenue' => round($totalAllTime, 2),
                'successful_transactions_in_period' => $countPeriod,
                'discounts_granted_in_period' => round($discountsGiven, 2),
                'currently_pending_transactions' => $pendingCount,
                'failed_transactions_in_period' => $failedCount,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($mode === 'by_method') {
            $byMethod = Payment::where('status', Payment::STATUS_COMPLETED)
                ->where('paid_at', '>=', $startDate)
                ->selectRaw('COALESCE(payment_method, "Unknown") as method, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('method')
                ->get();

            $byProvider = Payment::where('status', Payment::STATUS_COMPLETED)
                ->where('paid_at', '>=', $startDate)
                ->selectRaw('COALESCE(provider, "Direct/Other") as provider, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('provider')
                ->get();

            return json_encode([
                'currency' => 'ZMW',
                'period_days' => $days,
                'by_payment_method' => $byMethod->map(fn ($r) => [
                    'method' => $r->method,
                    'transactions' => $r->count,
                    'total_amount' => round((float) $r->total, 2),
                ])->all(),
                'by_provider' => $byProvider->map(fn ($r) => [
                    'provider' => $r->provider,
                    'transactions' => $r->count,
                    'total_amount' => round((float) $r->total, 2),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($mode === 'by_course') {
            $byCourse = Payment::where('status', Payment::STATUS_COMPLETED)
                ->where('paid_at', '>=', $startDate)
                ->whereNotNull('course_id')
                ->selectRaw('course_id, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('course_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->get();

            $courses = Course::whereIn('id', $byCourse->pluck('course_id'))->pluck('title', 'id');

            return json_encode([
                'currency' => 'ZMW',
                'period_days' => $days,
                'top_courses_by_revenue' => $byCourse->map(fn ($r) => [
                    'course_id' => $r->course_id,
                    'course_title' => $courses[$r->course_id] ?? "Course #{$r->course_id}",
                    'paid_enrollments' => $r->count,
                    'total_revenue' => round((float) $r->total, 2),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($mode === 'recent_payments' || $mode === 'failed_transactions') {
            $query = Payment::query()->with(['user:id,name,email', 'course:id,title']);

            if ($mode === 'failed_transactions') {
                $query->where('status', Payment::STATUS_FAILED);
            } else {
                $query->where('status', Payment::STATUS_COMPLETED);
            }

            $payments = $query->orderByDesc('created_at')->limit($limit)->get();

            return json_encode([
                'view' => $mode,
                'total_listed' => $payments->count(),
                'transactions' => $payments->map(fn ($p) => [
                    'id' => $p->id,
                    'user' => $p->user?->name ?? "User #{$p->user_id}",
                    'email' => $p->user?->email,
                    'course' => $p->course?->title,
                    'amount' => (float) $p->amount,
                    'currency' => $p->currency ?? 'ZMW',
                    'method' => $p->payment_method ?? $p->provider,
                    'reference' => $p->reference,
                    'status' => $p->status,
                    'date' => $p->paid_at?->format('Y-m-d H:i') ?? $p->created_at->format('Y-m-d H:i'),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "Error: Unknown mode '{$mode}'. Supported: summary, by_method, by_course, failed_transactions, recent_payments.";
    }
}

