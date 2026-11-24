<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Bar --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">From Date</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">To Date</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Program / Tenant</label>
                    <select wire:model.live="tenantId" @disabled($isProgramAdmin) class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @if(! $isProgramAdmin)
                            <option value="">All Programs / Global</option>
                        @endif
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->name }}{{ $tenant->country_name ? ' (' . $tenant->country_name . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Course</label>
                    <select wire:model.live="courseId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">UTM Source</label>
                    <select wire:model.live="utmSource" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">All Channels</option>
                        @foreach($utmSources as $src)
                            <option value="{{ $src }}">{{ $src }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Real-Time KPI Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Registrations</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($kpis['total_registrations']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Active Learners</p>
                <p class="mt-2 text-2xl font-extrabold text-teal-600 dark:text-teal-400">{{ number_format($kpis['active_learners']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Course Completion Rate</p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $kpis['completion_rate'] }}%</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $kpis['completed_enrollments'] }} / {{ $kpis['total_enrollments'] }} enrollments</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Avg Assessment Score</p>
                <p class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $kpis['avg_quiz_score'] }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">SCORM / xAPI Completion</p>
                <p class="mt-2 text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $kpis['scorm_completion_rate'] }}%</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $kpis['scorm_attempts'] }} attempts</p>
            </div>
        </div>

        {{-- One-Click CSV & XLSX Exports --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Exportable Reports (Applies Active Filters)</h3>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['key' => 'learner_progress', 'label' => 'Learner Progress'],
                    ['key' => 'assessment_results', 'label' => 'Assessment Results'],
                    ['key' => 'scorm_logs', 'label' => 'SCORM / xAPI Logs'],
                    ['key' => 'utm_attribution', 'label' => 'UTM Attribution Funnel'],
                ] as $report)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 dark:border-slate-800 dark:bg-slate-950/60">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $report['label'] }}</span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="exportReport('{{ $report['key'] }}', 'csv')" class="rounded-md bg-slate-200 px-2 py-1 text-[11px] font-bold text-slate-800 hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-100">CSV</button>
                            <button type="button" wire:click="exportReport('{{ $report['key'] }}', 'xlsx')" class="rounded-md bg-teal-600 px-2 py-1 text-[11px] font-bold text-white hover:bg-teal-500">XLSX</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Breakdown Tables --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- UTM Campaign Funnel --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">Acquisition & Completion by UTM Source</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 text-slate-500 dark:border-slate-800">
                            <tr>
                                <th class="py-2 pr-3 font-semibold">Channel / Source</th>
                                <th class="py-2 px-2 font-semibold">Registrations</th>
                                <th class="py-2 px-2 font-semibold">Enrollments</th>
                                <th class="py-2 px-2 font-semibold">Completed</th>
                                <th class="py-2 pl-2 font-semibold">Completion %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($utmBreakdown as $row)
                                <tr>
                                    <td class="py-2.5 pr-3 font-semibold text-slate-900 dark:text-white">{{ $row['source'] }}</td>
                                    <td class="py-2.5 px-2">{{ $row['registrations'] }}</td>
                                    <td class="py-2.5 px-2">{{ $row['enrollments'] }}</td>
                                    <td class="py-2.5 px-2">{{ $row['completed'] }}</td>
                                    <td class="py-2.5 pl-2 font-bold text-teal-600 dark:text-teal-400">{{ $row['conversion_rate'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">No UTM attribution data recorded for the selected filters yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Regional / Global Cohort Breakdown --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">Breakdown by Program / Tenant</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 text-slate-500 dark:border-slate-800">
                            <tr>
                                <th class="py-2 pr-3 font-semibold">Program / Tenant</th>
                                <th class="py-2 px-2 font-semibold">Country</th>
                                <th class="py-2 px-2 font-semibold">Learners</th>
                                <th class="py-2 px-2 font-semibold">Enrollments</th>
                                <th class="py-2 pl-2 font-semibold">Completion %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($tenantBreakdown as $row)
                                <tr>
                                    <td class="py-2.5 pr-3 font-semibold text-slate-900 dark:text-white">{{ $row['name'] }}</td>
                                    <td class="py-2.5 px-2">{{ $row['country'] }}</td>
                                    <td class="py-2.5 px-2">{{ $row['learners'] }}</td>
                                    <td class="py-2.5 px-2">{{ $row['enrollments'] }}</td>
                                    <td class="py-2.5 pl-2 font-bold text-emerald-600 dark:text-emerald-400">{{ $row['completion_rate'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">No programs/tenants configured yet. Create one under Programs & Tenants.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
