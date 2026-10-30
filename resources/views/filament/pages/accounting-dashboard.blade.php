<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Top Financial KPI Cards --}}
        @php
            $stats = $stats ?? $this->kpi_stats;
            $ledgerItems = $ledgerItems ?? $this->ledger_items;
            $courseOptions = $courseOptions ?? $this->course_options;
            $staffOptions = $staffOptions ?? $this->staff_options;
            $viewingEnrollment = $viewingEnrollment ?? $this->viewing_enrollment;
            $editingEnrollment = $editingEnrollment ?? $this->editing_enrollment;
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Gross Revenue --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gross Revenue</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ZMW {{ number_format($stats['gross_revenue'], 2) }}
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Total collected tuition across all channels
                    </p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Direct Online:</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">ZMW {{ number_format($stats['direct_revenue'], 2) }}</span>
                </div>
            </div>

            {{-- Manual Registration Revenue --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Admin & In-Person</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-building-library class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ZMW {{ number_format($stats['manual_revenue'], 2) }}
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Bank transfers, cash desk & POS payments
                    </p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Reconciled manually:</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $stats['paid_count'] > 0 ? round(($stats['manual_revenue'] / max(1, $stats['gross_revenue'])) * 100) : 0 }}% of total</span>
                </div>
            </div>

            {{-- Total Registrations & Ratio --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Enrolled</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-user-group class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ $stats['total_count'] }} <span class="text-sm font-medium text-slate-500">Students</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ $stats['paid_count'] }} Paid • {{ $stats['free_count'] }} Free / Sponsored
                    </p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Paid Ratio:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['paid_percentage'] }}% Paid</span>
                </div>
            </div>

            {{-- Scholarships & Fee Waivers --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Scholarship Value</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400">
                        <x-heroicon-o-sparkles class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        ZMW {{ number_format($stats['waiver_value'], 2) }}
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Value of 100% tuition waivers & sponsorships
                    </p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Free recipients:</span>
                    <span class="font-bold text-purple-600 dark:text-purple-400">{{ $stats['free_count'] }} Learners</span>
                </div>
            </div>

        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                
                {{-- Search Box --}}
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Search Ledger</label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Student, reference, course, or notes..."
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                        />
                    </div>
                </div>

                {{-- Course Filter --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Course Scope</label>
                    <select
                        wire:model.live="courseId"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                    >
                        <option value="">All Courses</option>
                        @foreach ($courseOptions as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Registration Channel --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Channel</label>
                    <select
                        wire:model.live="channel"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                    >
                        <option value="all">All Channels</option>
                        <option value="direct_checkout">Direct Online Checkout</option>
                        <option value="admin_manual">Admin Manual Added</option>
                        <option value="instructor_manual">Instructor Added</option>
                    </select>
                </div>

                {{-- Fee Type --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Fee Type</label>
                    <select
                        wire:model.live="feeType"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                    >
                        <option value="all">All Fee Types</option>
                        <option value="paid">Paid Only</option>
                        <option value="free">Free / Sponsored Only</option>
                    </select>
                </div>

                {{-- Date Range --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Time Period</label>
                    <select
                        wire:model.live="dateRange"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                    >
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="this_year">This Year</option>
                    </select>
                </div>

            </div>

            {{-- Action buttons row --}}
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <div class="text-xs text-slate-500">
                    Showing <span class="font-bold text-slate-700 dark:text-slate-300">{{ $ledgerItems->count() }}</span> filtered transaction records
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
                    >
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                        <span>Reset Filters</span>
                    </button>
                    <button
                        type="button"
                        wire:click="exportCsv"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 active:scale-98 transition"
                    >
                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Financial Ledger Table --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Transaction &amp; Registration Ledger</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Complete audit trail of all direct checkouts and administrative course registrations</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase font-semibold border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Receipt / Ref</th>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Course &amp; Cohort</th>
                            <th class="px-4 py-3">Channel</th>
                            <th class="px-4 py-3">Fee Status &amp; Amount</th>
                            <th class="px-4 py-3">Method / Provider</th>
                            <th class="px-4 py-3">Recorded Date</th>
                            <th class="px-4 py-3">Recorded By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-700 dark:text-slate-300">
                        @forelse ($ledgerItems as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                
                                {{-- Reference --}}
                                <td class="px-4 py-3 font-mono font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                    {{ $item->transaction_reference ?? 'REF-' . $item->id }}
                                </td>

                                {{-- Student --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-7 w-7 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-200 shrink-0">
                                            {{ strtoupper(substr($item->user?->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900 dark:text-white truncate">
                                                {{ $item->user?->name ?? 'Unknown Student' }}
                                                @if ($item->user?->is_kid)
                                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.2 text-[9px] rounded-full bg-purple-100 text-purple-700 font-bold">Junior</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 truncate">{{ $item->user?->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Course & Intake --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        <span class="font-mono text-slate-400 mr-1">{{ $item->course?->code }}</span>
                                        {{ $item->course?->title }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        Cohort: <span class="font-medium text-slate-600 dark:text-slate-400">{{ $item->intake?->name ?? 'General (No cohort)' }}</span>
                                    </div>
                                </td>

                                {{-- Channel --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($item->registration_type === 'direct_checkout')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            <x-heroicon-o-globe-alt class="w-3 h-3" />
                                            <span>Direct Checkout</span>
                                        </span>
                                    @elseif ($item->registration_type === 'admin_manual')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                            <x-heroicon-o-user-plus class="w-3 h-3" />
                                            <span>Admin Added</span>
                                        </span>
                                    @elseif ($item->registration_type === 'instructor_manual')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                            <x-heroicon-o-academic-cap class="w-3 h-3" />
                                            <span>Instructor Added</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700" title="Legacy registration. Click Edit to reconcile.">
                                            <x-heroicon-o-question-mark-circle class="w-3 h-3 text-amber-500" />
                                            <span>Needs Review</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Fee Status & Amount --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($item->isFree())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                            <x-heroicon-o-sparkles class="w-3 h-3" />
                                            <span>Free / Sponsored</span>
                                        </span>
                                    @else
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white">ZMW {{ number_format($item->fee_amount ?? 0, 2) }}</span>
                                            <span class="ml-1 inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">PAID</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Method & Provider --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ $item->paymentMethodLabel() }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $item->payment?->provider ?? ($item->isFree() ? 'Tuition Waiver' : 'Direct') }}
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                    {{ $item->paid_at ? $item->paid_at->format('M j, Y H:i') : $item->created_at->format('M j, Y H:i') }}
                                </td>

                                {{-- Recorded By --}}
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $item->registeredBy?->name ?? ($item->registration_type === 'direct_checkout' ? 'Online Gateway' : 'Unspecified') }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            wire:click="editTransaction({{ $item->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-[11px] font-semibold text-white shadow-2xs transition active:scale-95 cursor-pointer"
                                            title="Edit & Reconcile Transaction"
                                        >
                                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                            <span>Edit</span>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="viewDetails({{ $item->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer"
                                            title="View Full Receipt"
                                        >
                                            <x-heroicon-o-eye class="w-3.5 h-3.5 text-slate-400" />
                                            <span>Details</span>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                                    <x-heroicon-o-document-magnifying-glass class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" />
                                    <div class="font-medium text-slate-600 dark:text-slate-400">No accounting records match the selected filters.</div>
                                    <p class="text-xs text-slate-400 mt-1">Try resetting your search query or filters above.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Details Slide-over / Modal --}}
        @if ($viewingEnrollment)
            @php
                $detail = $viewingEnrollment;
            @endphp
            <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
                <div class="relative w-full max-w-xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-2xl space-y-5">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400">
                                <x-heroicon-o-receipt-percent class="h-6 w-6" />
                            </span>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white">Transaction &amp; Registration Receipt</h4>
                                <p class="text-xs text-slate-400 font-mono">{{ $detail->transaction_reference ?? 'REF-' . $detail->id }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeDetails"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 transition"
                        >
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-2.5">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Student Name:</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $detail->user?->name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Student Email:</span>
                                <span class="text-slate-700 dark:text-slate-300">{{ $detail->user?->email }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Course:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $detail->course?->title }} ({{ $detail->course?->code }})</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Assigned Cohort:</span>
                                <span class="text-slate-700 dark:text-slate-300">{{ $detail->intake?->name ?? 'General' }}</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-2.5">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Fee Classification:</span>
                                <span class="font-bold {{ $detail->isFree() ? 'text-purple-600' : 'text-emerald-600' }}">
                                    {{ $detail->isFree() ? 'Free / Scholarship' : 'PAID (ZMW ' . number_format($detail->fee_amount ?? 0, 2) . ')' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Enrollment Channel:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $detail->registrationChannelLabel() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Payment Method:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $detail->paymentMethodLabel() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Bank / Provider:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $detail->payment?->provider ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Recorded / Approved By:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $detail->registeredBy?->name ?? ($detail->registration_type === 'direct_checkout' ? 'Automated Online Gateway' : 'System') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Date Timestamp:</span>
                                <span class="text-slate-700 dark:text-slate-300">{{ $detail->paid_at ? $detail->paid_at->format('M j, Y H:i:s') : $detail->created_at->format('M j, Y H:i:s') }}</span>
                            </div>
                        </div>

                        @if ($detail->payment_notes)
                            <div class="p-3.5 rounded-xl border border-amber-200/60 dark:border-amber-900/60 bg-amber-50/50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200">
                                <div class="font-bold text-[11px] uppercase tracking-wider mb-1">Audit Remarks / Justification:</div>
                                <p class="text-xs">{{ $detail->payment_notes }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="editTransaction({{ $detail->id }})"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-xs font-semibold text-white shadow-2xs transition active:scale-98 cursor-pointer"
                        >
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            <span>Edit / Reconcile Transaction</span>
                        </button>
                        <button
                            type="button"
                            wire:click="closeDetails"
                            class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white text-xs font-semibold text-white dark:text-slate-900 hover:opacity-90 transition cursor-pointer"
                        >
                            Close Receipt
                        </button>
                    </div>

                </div>
            </div>
        @endif

        {{-- Edit & Reconcile Transaction Modal --}}
        @if ($editingEnrollment)
            @php
                $editRecord = $editingEnrollment;
            @endphp
            <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
                <div class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-2xl space-y-5 my-8">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400">
                                <x-heroicon-o-pencil-square class="h-6 w-6" />
                            </span>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white">Edit &amp; Reconcile Student Registration</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Updating payment details &amp; channel for <strong class="text-slate-800 dark:text-slate-200">{{ $editRecord->user?->name }}</strong> ({{ $editRecord->user?->email }})
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeEdit"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 transition"
                        >
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Context Banner --}}
                    <div class="flex items-center justify-between flex-wrap gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 text-xs">
                        <div>
                            <span class="text-slate-500">Course:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $editRecord->course?->title }}</span>
                            <span class="font-mono text-slate-400">({{ $editRecord->course?->code }})</span>
                            <span class="text-slate-400 ml-1">• Cohort: {{ $editRecord->intake?->name ?? 'General' }}</span>
                        </div>
                        @if ($editRecord->course?->fees)
                            <button
                                type="button"
                                wire:click="setFeeToCourseTuition"
                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-700 hover:text-teal-800 dark:text-teal-400 underline cursor-pointer"
                                title="Pre-fill standard course tuition"
                            >
                                <x-heroicon-o-sparkles class="w-3.5 h-3.5" />
                                <span>Set to Standard Tuition (ZMW {{ number_format($editRecord->course->fees, 2) }})</span>
                            </button>
                        @endif
                    </div>

                    {{-- Edit Form --}}
                    <form wire:submit.prevent="saveTransaction" class="space-y-4 text-xs">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            {{-- Registration Channel --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Registration Channel <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    wire:model="editRegistrationType"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    required
                                >
                                    <option value="admin_manual">Admin Manual Added (Campus / Office)</option>
                                    <option value="instructor_manual">Instructor Added</option>
                                    <option value="direct_checkout">Direct Online Checkout</option>
                                </select>
                                <p class="mt-0.5 text-[10px] text-slate-400">Select Admin Added for students registered manually.</p>
                            </div>

                            {{-- Fee Classification --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Fee Classification <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    wire:model.live="editPaymentStatus"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    required
                                >
                                    <option value="paid">Paid Student (Tuition Collected)</option>
                                    <option value="free">Free / Sponsored (Scholarship / Full Waiver)</option>
                                </select>
                                <p class="mt-0.5 text-[10px] text-slate-400">Mark as Paid if the student paid tuition.</p>
                            </div>

                            {{-- Fee Amount (ZMW) --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Fee Amount (ZMW) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs font-mono">ZMW</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model="editFeeAmount"
                                        class="w-full pl-13 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                        required
                                    />
                                </div>
                                <p class="mt-0.5 text-[10px] text-slate-400">Enter actual amount collected (or 0 for free).</p>
                            </div>

                            {{-- Payment Method --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Payment Method <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    wire:model="editPaymentMethod"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    required
                                >
                                    <option value="bank_transfer">Bank Transfer / Direct Deposit</option>
                                    <option value="cash">Cash (Campus / Finance Desk)</option>
                                    <option value="mobile_money">Mobile Money (Airtel / MTN / Zamtel)</option>
                                    <option value="card">Debit / Credit Card (POS / Terminal)</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="scholarship">Scholarship / Merit Grant / Waiver</option>
                                    <option value="other">Other / Direct Settlement</option>
                                </select>
                            </div>

                            {{-- Provider / Bank --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Bank / Provider / Terminal
                                </label>
                                <input
                                    type="text"
                                    wire:model="editProvider"
                                    placeholder="e.g. Stanbic Bank Zambia, Absa, Airtel Money, Finance Reception Desk"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                />
                            </div>

                            {{-- Reference Number --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Receipt / Reference Number <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="editTransactionReference"
                                    placeholder="e.g. REC-2026-4412, TX-STB-8812"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs font-mono text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                    required
                                />
                                <p class="mt-0.5 text-[10px] text-slate-400">Used for financial audit and reconciliation.</p>
                            </div>

                            {{-- Date Recorded / Paid --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Payment / Enrollment Date
                                </label>
                                <input
                                    type="datetime-local"
                                    wire:model="editPaidAt"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                />
                            </div>

                            {{-- Recorded By --}}
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Recorded / Verified By
                                </label>
                                <select
                                    wire:model="editRegisteredByUserId"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                                >
                                    <option value="">Automated Gateway / System</option>
                                    @foreach ($staffOptions as $staffId => $staffName)
                                        <option value="{{ $staffId }}">{{ $staffName }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        {{-- Accounting Remarks --}}
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Accounting Remarks &amp; Audit Notes
                            </label>
                            <textarea
                                wire:model="editPaymentNotes"
                                rows="3"
                                placeholder="e.g. Paid in cash at campus front reception. Receipt #REC-4412 issued. Or: Reconciled with verified Stanbic Bank deposit slip."
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500"
                            ></textarea>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button
                                type="button"
                                wire:click="closeEdit"
                                class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 active:scale-98 text-xs font-bold text-white shadow-xs transition cursor-pointer"
                            >
                                <x-heroicon-o-check class="w-4 h-4" />
                                <span>Save &amp; Reconcile Changes</span>
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
