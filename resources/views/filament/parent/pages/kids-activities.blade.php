<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- ============================================================ --}}
        {{-- HEADER WITH CHILD SELECTOR CHIPS                             --}}
        {{-- ============================================================ --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                        <x-heroicon-o-chart-bar-square class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
                        <span>Kids Activities & Performance</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Track quiz attempts, project submissions, teacher evaluations, and milestone achievements across all enrolled courses.
                    </p>
                </div>

                {{-- Search Bar --}}
                <div class="relative w-full md:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Search activity or course..." 
                        class="w-full text-xs pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                    @if ($search !== '')
                        <button 
                            wire:click="$set('search', '')" 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>

            {{-- Children Filter Chips --}}
            @if (!empty($kids))
                <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 border-t border-slate-100 dark:border-slate-700/60">
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider shrink-0 mr-1">
                        Filter Child:
                    </span>
                    <button 
                        wire:click="filterKid(null)" 
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ is_null($selectedKidId) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300' }}"
                    >
                        <x-heroicon-o-user-group class="w-3.5 h-3.5" />
                        <span>All Children ({{ count($kids) }})</span>
                    </button>

                    @foreach ($kids as $kid)
                        <button 
                            wire:click="filterKid({{ $kid['id'] }})" 
                            type="button"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $selectedKidId === $kid['id'] ? 'bg-indigo-600 text-white shadow-xs ring-2 ring-indigo-300 dark:ring-indigo-900' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300' }}"
                        >
                            <div class="w-4 h-4 rounded-full overflow-hidden bg-indigo-200 dark:bg-indigo-900 flex items-center justify-center text-[10px] shrink-0 font-extrabold">
                                @if ($kid['avatar'])
                                    <img src="{{ $kid['avatar'] }}" alt="{{ $kid['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ strtoupper(substr($kid['name'], 0, 1)) }}</span>
                                @endif
                            </div>
                            <span>{{ $kid['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- METRICS SUMMARY CARDS                                        --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                    <x-heroicon-o-academic-cap class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Average Score</div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                        {{ $metrics['average_score'] ?? 0 }}%
                    </div>
                    <div class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">
                        Across {{ $metrics['total_completed'] ?? 0 }} evaluations
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                    <x-heroicon-o-check-badge class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Pass Rate</div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                        {{ $metrics['pass_rate'] ?? 0 }}%
                    </div>
                    <div class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                        {{ $metrics['quizzes_passed'] ?? 0 }} Quizzes passed
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 flex items-center justify-center text-sky-600 dark:text-sky-400 shrink-0">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Quizzes & Projects</div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                        {{ ($metrics['quizzes_count'] ?? 0) + ($metrics['assignments_count'] ?? 0) }}
                    </div>
                    <div class="text-[10px] font-bold text-sky-600 dark:text-sky-400 mt-0.5">
                        {{ $metrics['quizzes_count'] ?? 0 }} Quizzes · {{ $metrics['assignments_count'] ?? 0 }} Projects
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 flex items-center justify-center text-purple-600 dark:text-purple-400 shrink-0">
                    <x-heroicon-o-trophy class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Assessments</div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                        {{ $metrics['assessments_count'] ?? 0 }}
                    </div>
                    <div class="text-[10px] font-bold text-purple-600 dark:text-purple-400 mt-0.5">
                        Official evaluations
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- FILTER TABS & COURSE SELECTOR                                --}}
        {{-- ============================================================ --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
            {{-- Type Pill Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                @php
                    $types = [
                        'all' => ['label' => 'All Activities', 'icon' => 'heroicon-o-squares-2x2'],
                        'quiz' => ['label' => 'Quizzes', 'icon' => 'heroicon-o-question-mark-circle'],
                        'assignment' => ['label' => 'Assignments', 'icon' => 'heroicon-o-document-text'],
                        'assessment' => ['label' => 'Assessments', 'icon' => 'heroicon-o-clipboard-document-check'],
                        'badge' => ['label' => 'Milestones', 'icon' => 'heroicon-o-sparkles'],
                    ];
                @endphp

                @foreach ($types as $key => $meta)
                    <button 
                        wire:click="filterType('{{ $key }}')" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $selectedType === $key ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}"
                    >
                        @if ($meta['icon'] === 'heroicon-o-squares-2x2')
                            <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5" />
                        @elseif ($meta['icon'] === 'heroicon-o-question-mark-circle')
                            <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5" />
                        @elseif ($meta['icon'] === 'heroicon-o-document-text')
                            <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                        @elseif ($meta['icon'] === 'heroicon-o-clipboard-document-check')
                            <x-heroicon-o-clipboard-document-check class="w-3.5 h-3.5" />
                        @else
                            <x-heroicon-o-sparkles class="w-3.5 h-3.5" />
                        @endif
                        <span>{{ $meta['label'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Course Filter Dropdown --}}
            @if (!empty($courses))
                <div class="sm:w-64 shrink-0">
                    <select 
                        wire:model.live="selectedCourseId" 
                        class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-semibold"
                    >
                        <option value="">All Enrolled Courses</option>
                        @foreach ($courses as $c)
                            <option value="{{ $c['id'] }}">
                                {{ $c['title'] }} {{ $c['is_for_kids'] ? '[Kids]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- ACTIVITIES LIST / TIMELINE FEED                              --}}
        {{-- ============================================================ --}}
        @if (empty($activities))
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-12 border border-slate-200 dark:border-slate-700 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400">
                    <x-heroicon-o-folder-open class="w-8 h-8" />
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">No Activity Records Found</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @if ($search !== '' || $selectedCourseId || $selectedType !== 'all')
                            No activities match the current filters. Try resetting your search or filter options.
                        @else
                            Your children have not completed any quizzes or assignments yet. As they study, their progress will appear here in real time.
                        @endif
                    </p>
                </div>
                @if ($search !== '' || $selectedCourseId || $selectedType !== 'all')
                    <div class="pt-2">
                        <button 
                            wire:click="$set('search', ''); $set('selectedCourseId', null); $set('selectedType', 'all')" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-all"
                        >
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                            <span>Reset Filters</span>
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="space-y-3">
                @foreach ($activities as $act)
                    <div 
                        wire:key="act-{{ $act['id'] }}" 
                        class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs hover:border-slate-300 dark:hover:border-slate-600 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <div class="flex items-start gap-4">
                            {{-- Type Icon Badge --}}
                            <div class="w-12 h-12 rounded-xl shrink-0 flex items-center justify-center shadow-xs 
                                @if ($act['type'] === 'quiz') bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800
                                @elseif ($act['type'] === 'assignment') bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800
                                @elseif ($act['type'] === 'assessment') bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800
                                @else bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800
                                @endif"
                            >
                                @if ($act['type'] === 'quiz')
                                    <x-heroicon-o-question-mark-circle class="w-6 h-6" />
                                @elseif ($act['type'] === 'assignment')
                                    <x-heroicon-o-document-text class="w-6 h-6" />
                                @elseif ($act['type'] === 'assessment')
                                    <x-heroicon-o-clipboard-document-check class="w-6 h-6" />
                                @else
                                    <x-heroicon-o-sparkles class="w-6 h-6" />
                                @endif
                            </div>

                            {{-- Details --}}
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                        @if ($act['type'] === 'quiz') bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300
                                        @elseif ($act['type'] === 'assignment') bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300
                                        @elseif ($act['type'] === 'assessment') bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300
                                        @else bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300
                                        @endif"
                                    >
                                        {{ $act['type_badge'] }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                        <x-heroicon-o-user class="w-3 h-3 text-slate-400" />
                                        <span>{{ $act['kid_name'] }}</span>
                                    </span>

                                    <span class="text-xs text-slate-400 dark:text-slate-500">·</span>
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $act['course'] }}</span>
                                </div>

                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                    {{ $act['title'] }}
                                </h3>

                                <div class="flex items-center gap-3 text-xs text-slate-400 dark:text-slate-500 pt-0.5">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                        <span>{{ $act['formatted_date'] }} ({{ $act['time_ago'] }})</span>
                                    </span>
                                </div>

                                {{-- Teacher Feedback Callout if present --}}
                                @if (!empty($act['feedback']))
                                    <div class="mt-2 text-xs bg-slate-50 dark:bg-slate-750 p-3 rounded-xl border border-slate-100 dark:border-slate-700 text-slate-600 dark:text-slate-300 space-y-1">
                                        <div class="font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-3.5 h-3.5" />
                                            <span>Teacher Feedback:</span>
                                        </div>
                                        <p class="italic text-slate-700 dark:text-slate-300">"{{ $act['feedback'] }}"</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Score / Status Badge --}}
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 shrink-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-700/60">
                            <div class="text-right">
                                <div class="text-lg sm:text-xl font-black 
                                    @if ($act['type'] === 'badge') text-amber-500
                                    @elseif ($act['passed']) text-emerald-600 dark:text-emerald-400
                                    @else text-rose-500 dark:text-rose-400
                                    @endif"
                                >
                                    {{ $act['score_display'] }}
                                </div>
                            </div>

                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold
                                @if ($act['status_color'] === 'emerald') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                @elseif ($act['status_color'] === 'amber') bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                @else bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800
                                @endif"
                            >
                                @if ($act['passed'])
                                    <x-heroicon-o-check class="w-3.5 h-3.5" />
                                @else
                                    <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5" />
                                @endif
                                <span>{{ $act['status_label'] }}</span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
