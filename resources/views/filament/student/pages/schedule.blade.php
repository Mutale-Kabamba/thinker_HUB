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
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Manage and view your live cohort sessions and agenda</p>
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
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">Click class or date for agenda</span>
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
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Showing dates within your bounds.</span>
                        </div>
                    @endif

                    {{-- ==================== MONTH VIEW ==================== --}}
                    @if ($rangeMode === 'month')
                        <div class="space-y-2">
                            {{-- Day Names Header --}}
                            <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider py-1 border-b border-slate-100 dark:border-[#233842]">
                                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                            </div>

                            {{-- Month Grid Body --}}
                            <div class="space-y-1.5">
                                @foreach ($calendarWeeks as $week)
                                    <div class="grid grid-cols-7 gap-1.5">
                                        @foreach ($week as $day)
                                            @php
                                                $sessionCount = count($day['sessions']);
                                            @endphp
                                            <div 
                                                wire:click="selectDay('{{ $day['date_full'] }}')"
                                                class="rounded-xl border p-2 min-h-[72px] flex flex-col justify-between transition-all duration-200 cursor-pointer group
                                                    {{ $day['is_today'] ? 'border-purple-300 dark:border-purple-800 bg-purple-50/40 dark:bg-purple-950/20 shadow-2xs' : 'border-slate-100 dark:border-[#1c2c34] bg-white dark:bg-[#102028]/60 hover:border-purple-300 dark:hover:border-purple-800 hover:bg-purple-50/20' }}
                                                    {{ ! $day['in_month'] ? 'opacity-35 bg-slate-50/50 dark:bg-slate-900/30' : '' }}"
                                                title="{{ $sessionCount > 0 ? $sessionCount.' class session(s) on '.$day['date_full'].' - Click to view agenda' : $day['date_full'] }}"
                                            >
                                                {{-- Day Cell Top: Date Number & Count Badge --}}
                                                <div class="flex items-center justify-between gap-1">
                                                    <span 
                                                        class="w-6 h-6 rounded-full text-xs flex items-center justify-center transition-colors
                                                            {{ $day['is_today'] ? 'bg-[#7C3AED] text-white font-black shadow-2xs' : 'font-bold text-slate-700 dark:text-slate-300 group-hover:bg-slate-100 dark:group-hover:bg-slate-800' }}"
                                                    >
                                                        {{ $day['date'] }}
                                                    </span>
                                                    @if ($sessionCount > 1)
                                                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500">
                                                            {{ $sessionCount }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Dots Container for Scheduled Items --}}
                                                <div class="flex items-center justify-center gap-1.5 py-1.5 flex-wrap min-h-[14px]">
                                                    @foreach (array_slice($day['sessions'], 0, 4) as $s)
                                                        <span 
                                                            wire:click.stop="openSessionDetails({{ $s['id'] }})"
                                                            class="w-2.5 h-2.5 rounded-full transition-transform hover:scale-150 cursor-pointer
                                                                {{ $s['status'] === 'completed' ? 'bg-emerald-500 shadow-2xs' : ($s['status'] === 'rescheduled' ? 'bg-amber-500 shadow-2xs' : ($s['status'] === 'cancelled' ? 'bg-rose-500 shadow-2xs' : 'bg-[#7C3AED] shadow-2xs')) }}"
                                                            title="{{ $s['course_code'] ? $s['course_code'].' · ' : '' }}{{ $s['title'] }} ({{ $s['start_time'] }}) - Click to view details"
                                                        ></span>
                                                    @endforeach
                                                    @if ($sessionCount > 4)
                                                        <span class="text-[9px] font-black text-[#7C3AED] dark:text-purple-400">+{{ $sessionCount - 4 }}</span>
                                                    @endif
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-3">
                            @foreach ($weekDays as $day)
                                <div class="rounded-xl border p-3 flex flex-col justify-between space-y-3
                                    {{ $day['is_today'] ? 'border-purple-300 dark:border-purple-800 bg-purple-50/30 dark:bg-purple-950/20' : 'border-slate-100 dark:border-[#233842] bg-white dark:bg-[#102028]' }}"
                                >
                                    {{-- Column Header --}}
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#233842]">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500">{{ $day['day_name'] }}</span>
                                            <span class="ml-1 text-xs font-black {{ $day['is_today'] ? 'text-[#7C3AED] dark:text-purple-400' : 'text-slate-800 dark:text-slate-200' }}">{{ $day['date_num'] }}</span>
                                        </div>
                                        @if ($day['is_today'])
                                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-[#7C3AED] text-white">TODAY</span>
                                        @endif
                                    </div>

                                    {{-- Column Sessions --}}
                                    <div class="space-y-2 flex-1">
                                        @forelse ($day['sessions'] as $s)
                                            <div 
                                                wire:click="openSessionDetails({{ $s['id'] }})"
                                                class="p-2.5 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/80 dark:bg-slate-800/50 hover:border-purple-300 dark:hover:border-purple-800 cursor-pointer space-y-1 transition-all"
                                            >
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="font-extrabold text-[#7C3AED] dark:text-purple-400">{{ $s['start_time'] }}</span>
                                                    <span class="px-1.5 py-0.2 rounded-full font-bold {{ $s['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' }}">
                                                        {{ ucfirst($s['status']) }}
                                                    </span>
                                                </div>
                                                <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">
                                                    @if ($s['course_code'])
                                                        <span class="text-[#7C3AED] dark:text-purple-400">{{ $s['course_code'] }}</span> ·
                                                    @endif
                                                    {{ $s['title'] }}
                                                </h4>
                                                @if (!empty($s['instructor_name']))
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ $s['instructor_name'] }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="py-4 text-center text-slate-400 text-xs font-medium">—</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- ==================== DAY VIEW ==================== --}}
                    @if ($rangeMode === 'day')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3.5 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/50">
                                <div>
                                    <h3 class="text-sm font-extrabold text-purple-900 dark:text-purple-200">{{ $dayViewData['day_name'] ?? '' }}, {{ $dayViewData['formatted_date'] ?? '' }}</h3>
                                    <p class="text-xs text-purple-700/80 dark:text-purple-300/80 font-medium">{{ count($dayViewData['sessions'] ?? []) }} class session(s) scheduled</p>
                                </div>
                                @if (!empty($dayViewData['is_today']))
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-[#7C3AED] text-white shadow-2xs">Today</span>
                                @endif
                            </div>

                            <div class="space-y-3">
                                @forelse ($dayViewData['sessions'] ?? [] as $s)
                                    <div 
                                        wire:click="openSessionDetails({{ $s['id'] }})"
                                        class="p-4 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 hover:border-purple-300 dark:hover:border-purple-800 cursor-pointer flex items-center justify-between gap-4 transition-all"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="w-16 text-center py-2 px-1 rounded-xl bg-white dark:bg-[#102028] border border-slate-100 dark:border-[#233842] flex-shrink-0">
                                                <span class="block text-xs font-black text-[#7C3AED] dark:text-purple-400">{{ $s['start_time'] ?? '—' }}</span>
                                                <span class="block text-[10px] text-slate-400">{{ $s['end_time'] ?? '—' }}</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                                    @if (!empty($s['course_code']))
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">{{ $s['course_code'] }}</span>
                                                    @endif
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $s['type_label'] ?? 'Group' }}</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ ucfirst($s['status'] ?? 'scheduled') }}</span>
                                                </div>
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $s['title'] ?? 'Session' }}</h4>
                                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $s['course_title'] ?? '' }}</p>
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
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                {{ $showAllSessions || filled($searchSession) || filled($filterStatus) ? 'Sessions' : 'Current Week' }}
                            </h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 border border-purple-100 dark:border-purple-900">
                            {{ count($displayedSessions) }} {{ $showAllSessions ? 'total' : 'this week' }}
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
                    </div>

                    {{-- Quick Search Input (Centered Icon) --}}
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
                        @forelse ($displayedSessions as $s)
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
                                    @if (!empty($s['instructor_name']))
                                        <div class="flex items-center gap-1.5">
                                            <x-heroicon-o-user class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                                            <span>{{ $s['instructor_name'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                No sessions found for this period.
                            </div>
                        @endforelse

                        {{-- View More / Show Current Week Only Button --}}
                        @if (!$showAllSessions && count($filteredSessions) > count($displayedSessions))
                            <button 
                                type="button" 
                                wire:click="toggleShowAllSessions"
                                class="w-full py-2.5 rounded-xl border border-dashed border-purple-300 dark:border-purple-800 text-xs font-extrabold text-[#7C3AED] dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors flex items-center justify-center gap-1.5 shadow-2xs mt-2"
                            >
                                <span>View More (All {{ count($filteredSessions) }} Sessions)</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        @elseif ($showAllSessions && count($filteredSessions) > count(array_filter($filteredSessions, fn ($s) => $s['is_current_week'])))
                            <button 
                                type="button" 
                                wire:click="toggleShowAllSessions"
                                class="w-full py-2 rounded-xl border border-dashed border-slate-200 dark:border-[#233842] text-xs font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 mt-2"
                            >
                                <span>Show Current Week Only</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- 3. BOTTOM SECTION: RESCHEDULE REQUESTS & COURSE PROGRESS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Reschedule Requests Card --}}
            <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-arrow-path class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Reschedule Requests</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
                            <input type="checkbox" wire:model.live="showRequestHistory" class="rounded text-[#7C3AED]">
                            <span>History</span>
                        </label>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            {{ count($rescheduleRequests) }}
                        </span>
                    </div>
                </div>

                @if (count($rescheduleRequests) > 0)
                    <div class="space-y-2">
                        @foreach ($rescheduleRequests as $request)
                            <div class="p-3 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100">Session #{{ $request['session_id'] }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $request['message'] }}</p>
                                    @if ($request['preferred_date'])
                                        <p class="text-[10px] text-[#7C3AED] dark:text-purple-400 font-semibold mt-0.5">Prefers {{ $request['preferred_date'] }} @if ($request['preferred_time']) at {{ $request['preferred_time'] }} @endif</p>
                                    @endif
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200">
                                    {{ ucfirst($request['decision_status']) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 py-3 text-center">No pending reschedule requests.</p>
                @endif
            </div>

            {{-- Course Progress & Attendance Card --}}
            @if (count($courseProgress) > 0 || count($attendanceSummary) > 0)
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar-square class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Course Progress &amp; Attendance</h3>
                    </div>

                    <div class="space-y-3">
                        @foreach ($courseProgress as $progress)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-slate-900 dark:text-slate-100">{{ $progress['course_title'] }} <span class="text-slate-400 font-normal">({{ $progress['course_code'] }})</span></span>
                                    <span class="text-[#7C3AED] dark:text-purple-400">{{ $progress['completed'] }}/{{ $progress['total'] }} ({{ $progress['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#7C3AED] rounded-full transition-all duration-300" style="width: {{ $progress['percentage'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach

                        @foreach ($attendanceSummary as $summary)
                            <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-[#233842]">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-slate-900 dark:text-slate-100">{{ $summary['course_title'] }} Attendance</span>
                                    <span class="{{ $summary['percentage'] >= 75 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $summary['attended'] }}/{{ $summary['total'] }} ({{ $summary['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                    <div class="h-full {{ $summary['percentage'] >= 75 ? 'bg-emerald-500' : 'bg-amber-500' }} rounded-full" style="width: {{ $summary['percentage'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

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

                    {{-- Instructor Info --}}
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-academic-cap class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $selectedSessionDetails['instructor_name'] }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Course Instructor</p>
                            </div>
                        </div>
                        @if ($selectedSessionDetails['instructor_whatsapp'])
                            <a 
                                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $selectedSessionDetails['instructor_whatsapp']) }}" 
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
                                <span>Join Class</span>
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

                {{-- Footer --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-[#233842]">
                    <div>
                        @if ($selectedSessionDetails['status'] === 'scheduled')
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

                    <div class="flex items-center gap-2">
                        @if ($selectedSessionDetails['google_calendar_url'])
                            <a 
                                href="{{ $selectedSessionDetails['google_calendar_url'] }}" 
                                target="_blank" 
                                rel="noopener"
                                class="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-[#7C3AED] transition-colors inline-flex items-center gap-1 no-underline"
                            >
                                <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                <span>Sync Google</span>
                            </a>
                        @endif
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
        </div>
    @endif

    {{-- 5. RESCHEDULE REQUEST FORM MODAL --}}
    @if ($rescheduleRequestSessionId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-arrow-path class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Request Session Reschedule</h3>
                    </div>
                    <button type="button" wire:click="cancelRescheduleRequest" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Your instructor and administration will be notified with your requested date and rationale.
                </p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                            Reason for Rescheduling <span class="text-rose-500">*</span>
                        </label>
                        <textarea 
                            wire:model="rescheduleRequestReason"
                            rows="3"
                            placeholder="Please explain why you need to reschedule..."
                            class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-[#7C3AED] focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">Preferred Date</label>
                            <input 
                                type="date"
                                wire:model="reschedulePreferredDate"
                                class="w-full p-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">Preferred Time</label>
                            <input 
                                type="time"
                                wire:model="reschedulePreferredTime"
                                class="w-full p-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-[#233842]">
                    <button 
                        type="button"
                        wire:click="cancelRescheduleRequest"
                        class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="submitRescheduleRequest"
                        class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all"
                    >
                        Send Request
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
