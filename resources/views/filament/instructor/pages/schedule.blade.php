<x-filament-panels::page>
    <div class="space-y-6">

        {{-- 1. TOP INTERACTIVE TIMETABLE CONTROL BAR --}}
        <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-4 sm:p-5 border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 flex items-center justify-center shadow-2xs border border-purple-100 dark:border-purple-900/60">
                    <x-heroicon-o-calendar-days class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Interactive Timetable</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Manage and schedule live cohort sessions, attendance, and student requests</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Segmented Range Tabs --}}
                <div class="p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-[#233842] flex items-center gap-1">
                    @foreach (['month' => 'Month', 'week' => 'Week', 'day' => 'Day', 'custom' => 'Custom'] as $modeKey => $modeLabel)
                        <button 
                            type="button"
                            wire:click="setRangeMode('{{ $modeKey }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $rangeMode === $modeKey ? 'bg-[#7C3AED] text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}"
                        >
                            {{ $modeLabel }}
                        </button>
                    @endforeach
                </div>

                {{-- Navigation Cluster --}}
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/80 dark:border-[#233842]">
                        <button 
                            type="button"
                            wire:click="previousPeriod"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-[#102028] hover:text-slate-900 dark:hover:text-white transition-colors"
                            title="Previous Period"
                        >
                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToToday"
                            class="px-2.5 py-1 rounded-lg text-xs font-extrabold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-[#102028] transition-colors"
                        >
                            Today
                        </button>
                        <button 
                            type="button"
                            wire:click="nextPeriod"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-[#102028] hover:text-slate-900 dark:hover:text-white transition-colors"
                            title="Next Period"
                        >
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="px-3 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/50 text-xs font-black text-[#7C3AED] dark:text-purple-300">
                        {{ $periodTitle }}
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. MAIN 2-COLUMN SCHEDULE GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- LEFT COLUMN: CALENDAR / TIMETABLE PANE (8 cols on LG) --}}
            <div class="lg:col-span-8 space-y-5">
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if ($rangeMode === 'month')
                                <x-heroicon-o-calendar class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Monthly Calendar</h3>
                            @elseif ($rangeMode === 'week')
                                <x-heroicon-o-calendar-days class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Weekly Timetable</h3>
                            @elseif ($rangeMode === 'day')
                                <x-heroicon-o-clock class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Daily Agenda</h3>
                            @else
                                <x-heroicon-o-calendar-days class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Custom Timetable</h3>
                            @endif
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">Click class for details &amp; actions</span>
                    </div>

                    {{-- Custom Date Bounds Toolbar (When rangeMode === 'custom') --}}
                    @if ($rangeMode === 'custom')
                        <div class="flex flex-wrap items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                                <span>From:</span>
                                <input type="date" wire:model.live="customStartDate" class="px-2 py-1 rounded-lg border border-slate-200 dark:border-[#233842] bg-white dark:bg-[#102028] text-xs">
                            </label>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                                <span>To:</span>
                                <input type="date" wire:model.live="customEndDate" class="px-2 py-1 rounded-lg border border-slate-200 dark:border-[#233842] bg-white dark:bg-[#102028] text-xs">
                            </label>
                            <span class="text-[11px] text-slate-400">Showing dates within your bounds.</span>
                        </div>
                    @endif

                    {{-- ==================== MONTH VIEW ==================== --}}
                    @if ($rangeMode === 'month')
                        <div class="space-y-1">
                            {{-- Day of Week Headers --}}
                            <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider py-1">
                                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                            </div>

                            {{-- Weeks Grid --}}
                            <div class="space-y-1">
                                @foreach ($calendarWeeks as $week)
                                    <div class="grid grid-cols-7 gap-1">
                                        @foreach ($week as $day)
                                            <div 
                                                class="min-h-[88px] p-1.5 rounded-xl border transition-all relative flex flex-col justify-between
                                                    {{ $day['is_today'] ? 'border-purple-300 dark:border-purple-800 bg-purple-50/40 dark:bg-purple-950/20 shadow-2xs' : ($day['in_month'] ? 'border-slate-100 dark:border-[#233842] bg-white dark:bg-[#102028]/60 hover:border-slate-200' : 'border-transparent bg-slate-50/40 dark:bg-slate-900/30 opacity-40') }}"
                                                @if (count($day['sessions']) > 0)
                                                    wire:click="openSessionDetails({{ $day['sessions'][0]['id'] }})"
                                                    style="cursor: pointer;"
                                                @endif
                                            >
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-extrabold {{ $day['is_today'] ? 'w-6 h-6 rounded-full bg-[#7C3AED] text-white flex items-center justify-center shadow-2xs' : ($day['in_month'] ? 'text-slate-800 dark:text-slate-200 pl-1' : 'text-slate-400') }}">
                                                        {{ $day['date'] }}
                                                    </span>
                                                    @if (count($day['sessions']) > 1)
                                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                                                            +{{ count($day['sessions']) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="space-y-1 mt-1">
                                                    @foreach ($day['sessions'] as $s)
                                                        <button 
                                                            type="button" 
                                                            wire:click.stop="openSessionDetails({{ $s['id'] }})"
                                                            class="w-full text-left p-1 rounded-md text-[10px] font-bold truncate block transition-colors
                                                                {{ $s['status'] === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 hover:bg-purple-100' }}"
                                                            title="{{ $s['course_code'] ? $s['course_code'].' · ' : '' }}{{ $s['title'] }} ({{ $s['start_time'] }})"
                                                        >
                                                            <span class="font-extrabold">{{ $s['start_time'] }}</span>
                                                            <span class="font-normal">{{ $s['course_code'] ? $s['course_code'] : $s['title'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ==================== WEEK VIEW ==================== --}}
                    @if ($rangeMode === 'week')
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-2">
                            @foreach ($weekDays as $day)
                                <div class="rounded-xl border p-2.5 flex flex-col justify-between space-y-2
                                    {{ $day['is_today'] ? 'border-purple-300 dark:border-purple-800 bg-purple-50/40 dark:bg-purple-950/20 ring-2 ring-[#7C3AED]/20' : 'border-slate-100 dark:border-[#233842] bg-white dark:bg-[#102028]' }}"
                                >
                                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-[#233842]">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $day['day_name'] }}</span>
                                        <span class="text-xs font-black {{ $day['is_today'] ? 'text-[#7C3AED]' : 'text-slate-800 dark:text-slate-200' }}">{{ $day['date_num'] }}</span>
                                    </div>

                                    <div class="space-y-1.5 min-h-[140px]">
                                        @forelse ($day['sessions'] as $s)
                                            <div 
                                                wire:click="openSessionDetails({{ $s['id'] }})"
                                                class="p-2 rounded-lg border border-slate-100 dark:border-slate-700/60 bg-slate-50/80 dark:bg-slate-800/60 hover:border-purple-300 dark:hover:border-purple-700 cursor-pointer space-y-1 transition-all"
                                            >
                                                <div class="flex items-center justify-between text-[9px] font-bold">
                                                    <span class="text-[#7C3AED] dark:text-purple-400">{{ $s['start_time'] }}</span>
                                                    <span class="px-1 rounded text-[8px] {{ $s['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' }}">{{ ucfirst($s['status']) }}</span>
                                                </div>
                                                <h5 class="text-[11px] font-extrabold text-slate-900 dark:text-slate-100 line-clamp-2 leading-snug">{{ $s['title'] }}</h5>
                                                @if (!empty($s['student_name']))
                                                    <p class="text-[10px] text-slate-400 truncate">{{ $s['student_name'] }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="h-full flex items-center justify-center text-slate-300 dark:text-slate-600 text-xs">—</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- ==================== DAY VIEW ==================== --}}
                    @if ($rangeMode === 'day')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#233842]">
                                <div>
                                    <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">{{ $dayViewData['day_name'] ?? '' }}, {{ $dayViewData['formatted_date'] ?? '' }}</h4>
                                    <p class="text-xs text-slate-400">{{ count($dayViewData['sessions'] ?? []) }} session(s) scheduled for today</p>
                                </div>
                                @if (!empty($dayViewData['is_today']))
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#7C3AED] text-white">Today</span>
                                @endif
                            </div>

                            <div class="space-y-2.5">
                                @forelse ($dayViewData['sessions'] ?? [] as $s)
                                    <div 
                                        wire:click="openSessionDetails({{ $s['id'] }})"
                                        class="p-4 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 hover:border-purple-300 dark:hover:border-purple-800 cursor-pointer flex items-center justify-between gap-4 transition-all"
                                    >
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-14 text-center py-1.5 px-1 rounded-lg bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-900/50">
                                                <span class="block text-xs font-black text-[#7C3AED] dark:text-purple-300">{{ $s['start_time'] }}</span>
                                                <span class="block text-[10px] text-slate-400 font-medium">{{ $s['end_time'] }}</span>
                                            </div>
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    @if (!empty($s['course_code']))
                                                        <span class="px-1.5 py-0.2 rounded font-extrabold text-[10px] bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">{{ $s['course_code'] }}</span>
                                                    @endif
                                                    <span class="text-[10px] font-bold text-slate-400">{{ $s['type_label'] ?? 'Group' }}</span>
                                                </div>
                                                <h5 class="text-xs font-extrabold text-slate-900 dark:text-slate-100">{{ $s['title'] }}</h5>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $s['course_title'] }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 text-xs font-extrabold text-[#7C3AED] dark:text-purple-400">
                                            <span>Details</span>
                                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-8 text-center text-slate-400 text-xs">
                                        No classes scheduled on this day.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- ==================== CUSTOM VIEW ==================== --}}
                    @if ($rangeMode === 'custom')
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach ($customDays as $day)
                                <div class="rounded-xl border p-3 flex flex-col justify-between space-y-2
                                    {{ $day['is_today'] ? 'border-purple-300 dark:border-purple-800 bg-purple-50/30 dark:bg-purple-950/20' : 'border-slate-100 dark:border-[#233842] bg-white dark:bg-[#102028]' }}"
                                >
                                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-[#233842]">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $day['day_name'] }}</span>
                                        <span class="text-xs font-black {{ $day['is_today'] ? 'text-[#7C3AED]' : 'text-slate-800 dark:text-slate-200' }}">{{ $day['date_num'] }}</span>
                                    </div>
                                    <div class="space-y-1.5">
                                        @forelse ($day['sessions'] as $s)
                                            <button 
                                                type="button" 
                                                wire:click="openSessionDetails({{ $s['id'] }})"
                                                class="w-full text-left p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 text-[10px] font-semibold truncate block hover:bg-purple-50 dark:hover:bg-purple-950/40"
                                            >
                                                <span class="text-[#7C3AED] font-bold">{{ $s['start_time'] }}</span>
                                                <span>{{ $s['title'] }}</span>
                                            </button>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN: FILTER BY STATUS & SESSION LIST PANE (4 cols on LG) --}}
            <div class="lg:col-span-4 space-y-5">
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-funnel class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Filter by Status</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 border border-purple-100 dark:border-purple-900">
                            {{ count($filteredSessions) }} in view
                        </span>
                    </div>

                    {{-- Status Filter Pills --}}
                    <div class="flex flex-wrap gap-1.5">
                        <button 
                            type="button"
                            wire:click="setFilterStatus('')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === '' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
                        >
                            All ({{ $statusCounts['all'] }})
                        </button>
                        <button 
                            type="button"
                            wire:click="setFilterStatus('scheduled')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'scheduled' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
                        >
                            Scheduled ({{ $statusCounts['scheduled'] }})
                        </button>
                        <button 
                            type="button"
                            wire:click="setFilterStatus('completed')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'completed' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
                        >
                            Completed ({{ $statusCounts['completed'] }})
                        </button>
                        <button 
                            type="button"
                            wire:click="setFilterStatus('rescheduled')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'rescheduled' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
                        >
                            Rescheduled ({{ $statusCounts['rescheduled'] }})
                        </button>
                        <button 
                            type="button"
                            wire:click="setFilterStatus('cancelled')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'cancelled' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
                        >
                            Cancelled ({{ $statusCounts['cancelled'] }})
                        </button>
                    </div>

                    {{-- Quick Search Input --}}
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input 
                            type="text"
                            wire:model.live.debounce.300ms="searchSession"
                            placeholder="Search topic, course or code..."
                            class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                        >
                    </div>

                    {{-- Scrollable Session Card List --}}
                    <div class="max-h-[520px] overflow-y-auto space-y-2.5 pr-1">
                        @forelse ($filteredSessions as $s)
                            <div 
                                wire:click="openSessionDetails({{ $s['id'] }})"
                                class="p-3.5 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 hover:border-purple-300 dark:hover:border-purple-800 cursor-pointer space-y-2 transition-all"
                            >
                                <div class="flex items-center justify-between text-[10px]">
                                    <div class="flex items-center gap-1.5">
                                        @if ($s['course_code'])
                                            <span class="px-1.5 py-0.5 rounded font-extrabold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">{{ $s['course_code'] }}</span>
                                        @endif
                                        <span class="font-bold text-slate-400">{{ $s['type_label'] ?? 'Group' }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full font-bold {{ $s['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' }}">
                                        {{ ucfirst($s['status']) }}
                                    </span>
                                </div>

                                <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $s['title'] }}</h4>

                                <div class="space-y-1 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <x-heroicon-o-calendar class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                                        <span>{{ $s['session_date'] }} · {{ $s['start_time'] }} - {{ $s['end_time'] }}</span>
                                    </div>
                                    @if (!empty($s['student_name']))
                                        <div class="flex items-center gap-1.5">
                                            <x-heroicon-o-user class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                                            <span>{{ $s['student_name'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                No sessions match your filter.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- 3. BOTTOM SECTION: STUDENT RESCHEDULE REQUESTS --}}
        @if (count($pendingRescheduleRequests) > 0)
            <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-purple-200/80 dark:border-purple-900/60 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-bell-alert class="w-5 h-5 text-[#7C3AED] dark:text-purple-400" />
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Student Reschedule Requests</h3>
                            <p class="text-xs text-slate-400">Action required for the following student scheduling inquiries</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-500 text-white shadow-2xs">
                        {{ count($pendingRescheduleRequests) }} pending
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($pendingRescheduleRequests as $req)
                        <div class="p-3.5 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <span class="font-extrabold text-xs text-slate-900 dark:text-white truncate block">
                                    {{ $req['student_name'] }} <span class="text-slate-400 font-normal">(Session #{{ $req['session_id'] }})</span>
                                </span>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                    Reason: {{ $req['reason'] ?: 'None provided' }}
                                </p>
                                @if ($req['preferred_date'])
                                    <p class="text-[10px] text-[#7C3AED] dark:text-purple-300 font-bold">
                                        Requested: {{ $req['preferred_date'] }} @if ($req['preferred_time']) at {{ $req['preferred_time'] }} @endif
                                    </p>
                                @endif
                            </div>
                            <button 
                                type="button"
                                wire:click="openDecisionWizard('{{ $req['id'] }}')"
                                class="px-3 py-1.5 rounded-full text-xs font-extrabold text-white bg-[#7C3AED] hover:bg-[#6D28D9] dark:bg-purple-600 dark:hover:bg-purple-500 shadow-2xs transition flex-shrink-0"
                            >
                                Review Request
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- 4. CLASS DETAILS POP-UP MODAL --}}
    @if ($showSessionDetailsModal && $selectedSessionDetails)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in duration-150">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                            @if ($selectedSessionDetails['course_code'])
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 text-[10px] font-extrabold border border-purple-200 dark:border-purple-900">
                                    {{ $selectedSessionDetails['course_code'] }}
                                </span>
                            @endif
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold">
                                {{ $selectedSessionDetails['type_label'] }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 text-[10px] font-bold border border-emerald-200 dark:border-emerald-800">
                                {{ ucfirst($selectedSessionDetails['status']) }}
                            </span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">{{ $selectedSessionDetails['title'] }}</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ $selectedSessionDetails['course_title'] }}</p>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeSessionDetails"
                        class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                        title="Close"
                    >
                        &times;
                    </button>
                </div>

                {{-- Body details --}}
                <div class="space-y-3">
                    {{-- Date & Time --}}
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-o-calendar-days class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $selectedSessionDetails['session_date'] }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $selectedSessionDetails['start_time'] }} – {{ $selectedSessionDetails['end_time'] }}</p>
                        </div>
                    </div>

                    {{-- Student / Cohort Info --}}
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-user-group class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $selectedSessionDetails['student_name'] }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Learner / Audience</p>
                            </div>
                        </div>
                        @if ($selectedSessionDetails['student_whatsapp'])
                            <a 
                                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $selectedSessionDetails['student_whatsapp']) }}" 
                                target="_blank" 
                                rel="noopener"
                                class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold inline-flex items-center gap-1.5 no-underline transition-colors"
                            >
                                <x-heroicon-o-chat-bubble-left-right class="w-3.5 h-3.5" />
                                <span>WhatsApp</span>
                            </a>
                        @endif
                    </div>

                    {{-- Virtual Meeting Link --}}
                    @if ($selectedSessionDetails['meeting_link'])
                        <div class="p-3.5 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900 flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-purple-900 dark:text-purple-200">Virtual Classroom</p>
                                <p class="text-[11px] text-purple-700/80 dark:text-purple-300/80 truncate">{{ $selectedSessionDetails['meeting_link'] }}</p>
                            </div>
                            <a 
                                href="{{ $selectedSessionDetails['meeting_link'] }}" 
                                target="_blank" 
                                rel="noopener"
                                class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all no-underline inline-flex items-center gap-1.5"
                            >
                                <x-heroicon-o-video-camera class="w-3.5 h-3.5" />
                                <span>Start Class</span>
                            </a>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if ($selectedSessionDetails['notes'])
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                            <p class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 mb-1">Agenda &amp; Notes</p>
                            <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $selectedSessionDetails['notes'] }}</p>
                        </div>
                    @endif
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-[#233842]">
                    <div class="flex items-center gap-2">
                        @if ($selectedSessionDetails['status'] !== 'completed')
                            <button 
                                type="button"
                                wire:click="markCompleted({{ $selectedSessionDetails['id'] }})"
                                class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-xs font-bold inline-flex items-center gap-1.5 transition-colors border border-emerald-200 dark:border-emerald-800"
                            >
                                <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                <span>Mark Completed</span>
                            </button>

                            <button 
                                type="button"
                                wire:click="openRescheduleFromDetails"
                                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold inline-flex items-center gap-1.5 transition-colors"
                            >
                                <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                <span>Reschedule</span>
                            </button>
                        @endif
                    </div>

                    <button 
                        type="button" 
                        wire:click="closeSessionDetails"
                        class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- 5. POP-UP MODAL 2: INSTRUCTOR RESCHEDULE MODAL --}}
    @if ($rescheduleSessionId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-arrow-path class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Reschedule Session</h3>
                    </div>
                    <button type="button" wire:click="cancelReschedule" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                            New Date <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="date"
                            wire:model="rescheduleDate"
                            class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Start Time <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="time"
                                wire:model="rescheduleStartTime"
                                class="w-full p-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">End Time</label>
                            <input 
                                type="time"
                                wire:model="rescheduleEndTime"
                                class="w-full p-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-[#233842]">
                    <button 
                        type="button"
                        wire:click="cancelReschedule"
                        class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="submitReschedule"
                        class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all"
                    >
                        Save &amp; Notify Students
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- 6. POP-UP MODAL 3: DECISION WIZARD MODAL --}}
    @if ($decisionNotificationId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-bell-alert class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Student Reschedule Request</h3>
                    </div>
                    <button type="button" wire:click="closeDecisionWizard" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                @if ($decisionStep === 'review')
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842] space-y-1">
                            <p class="text-xs font-extrabold text-slate-900 dark:text-slate-100">{{ $decisionStudentName }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Reason: {{ $decisionReason ?: 'None provided' }}</p>
                            @if ($decisionPreferredDate)
                                <p class="text-[11px] text-[#7C3AED] dark:text-purple-300 font-bold mt-1">
                                    Requested: {{ $decisionPreferredDate }} @if ($decisionPreferredTime) at {{ $decisionPreferredTime }} @endif
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-[#233842]">
                            <button 
                                type="button"
                                wire:click="setDecisionStep('decline')"
                                class="px-4 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 text-xs font-bold transition-colors border border-rose-200 dark:border-rose-900"
                            >
                                Decline Request
                            </button>
                            <button 
                                type="button"
                                wire:click="setDecisionStep('accept')"
                                class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all"
                            >
                                Accept &amp; Set Time
                            </button>
                        </div>
                    </div>
                @elseif ($decisionStep === 'accept')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Confirmed Date <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="date"
                                wire:model="decisionDate"
                                class="w-full p-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                            >
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                                    Start Time <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                type="time"
                                wire:model="decisionStartTime"
                                class="w-full p-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">End Time</label>
                                <input 
                                type="time"
                                wire:model="decisionEndTime"
                                class="w-full p-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                                >
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-[#233842]">
                            <button 
                                type="button"
                                wire:click="setDecisionStep('review')"
                                class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors"
                            >
                                Back
                            </button>
                            <button 
                                type="button"
                                wire:click="acceptRescheduleRequest"
                                class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all"
                            >
                                Confirm Acceptance
                            </button>
                        </div>
                    </div>
                @elseif ($decisionStep === 'decline')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Decline Note (Optional for Student)
                            </label>
                            <textarea 
                                wire:model="declineReason"
                                rows="3"
                                placeholder="Explain why this time slot cannot be accommodated..."
                                class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-[#7C3AED] focus:outline-none"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-[#233842]">
                            <button 
                                type="button"
                                wire:click="setDecisionStep('review')"
                                class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors"
                            >
                                Back
                            </button>
                            <button 
                                type="button"
                                wire:click="declineRescheduleRequest"
                                class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition-all"
                            >
                                Confirm Decline
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
