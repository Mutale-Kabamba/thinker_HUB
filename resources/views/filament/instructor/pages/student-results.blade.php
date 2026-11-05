@php
    $tasksData = $this->getTasksData();
    $students = $this->getStudentsData();
    $kpi = $this->getKpiStats($students);
    $catStats = $tasksData['category_stats'];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- 1. TOP NAVIGATION & STATS RIBBON --}}
        <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-4 sm:p-5 border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 flex items-center justify-center shadow-2xs border border-teal-100 dark:border-teal-900/60">
                    <x-heroicon-o-academic-cap class="w-5 h-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">
                            Grading &amp; Evaluations
                        </span>
                        <span class="text-slate-300 dark:text-slate-600">•</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            {{ count($students) }} Students • {{ $tasksData['totals']['total'] }} Taken Tasks
                        </span>
                    </div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-slate-100">Student &amp; Task Evaluation Results</h2>
                </div>
            </div>

            {{-- Mode Switcher & Export CSV --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-[#233842] flex items-center gap-1">
                    <button 
                        type="button"
                        wire:click="setViewMode('tasks')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5 {{ $viewMode === 'tasks' ? 'bg-teal-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}"
                    >
                        <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5" />
                        <span>Evaluation Cards</span>
                    </button>
                    <button 
                        type="button"
                        wire:click="setViewMode('students')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5 {{ $viewMode === 'students' ? 'bg-teal-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}"
                    >
                        <x-heroicon-o-users class="w-3.5 h-3.5" />
                        <span>Per Student</span>
                    </button>
                </div>

                <button 
                    type="button"
                    wire:click="exportCsv"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-[#233842] bg-white dark:bg-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-700/80 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors inline-flex items-center gap-1.5"
                    title="Export Results to CSV"
                >
                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                    <span>Export CSV</span>
                </button>
            </div>
        </div>

        {{-- KPI STATS PILLS RIBBON --}}
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-[#102028] border border-slate-100 dark:border-[#233842] shadow-2xs">
                <span class="text-slate-400 dark:text-slate-500 font-medium">Class Average:</span>
                <strong class="{{ $kpi['avg_overall'] !== null ? ($kpi['avg_overall'] >= 75 ? 'text-emerald-600 dark:text-emerald-400' : ($kpi['avg_overall'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')) : 'text-slate-700 dark:text-slate-300' }}">
                    {{ $kpi['avg_overall'] !== null ? $kpi['avg_overall'].'%' : '—' }}
                </strong>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-[#102028] border border-slate-100 dark:border-[#233842] shadow-2xs">
                <span class="text-slate-400 dark:text-slate-500 font-medium">Task Ordering:</span>
                <strong class="text-teal-600 dark:text-teal-400 font-bold">FILO (Latest Taken First)</strong>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-[#102028] border border-slate-100 dark:border-[#233842] shadow-2xs">
                <span class="text-slate-400 dark:text-slate-500 font-medium">Student Ranking:</span>
                <strong class="text-emerald-600 dark:text-emerald-400 font-bold">DEC (Highest Score First)</strong>
            </div>
        </div>

        {{-- 2. SEARCH & FILTER CONTROLS BAR --}}
        <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-3.5 border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex-1 min-w-[200px] relative">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input 
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search student or task title..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Course Filter --}}
                <select 
                    wire:model.live="courseFilter"
                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500"
                >
                    <option value="">All Courses</option>
                    @foreach ($this->courseOptions as $cId => $cTitle)
                        <option value="{{ $cId }}">{{ $cTitle }}</option>
                    @endforeach
                </select>

                {{-- Track Filter --}}
                <select 
                    wire:model.live="trackFilter"
                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500"
                >
                    <option value="">All Levels</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>

                @if(!empty($search) || !empty($courseFilter) || !empty($trackFilter) || $activeCategory !== 'quizzes')
                    <button 
                        type="button"
                        wire:click="resetFilters"
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-colors"
                        title="Reset all filters"
                    >
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                @endif
            </div>
        </div>

        {{-- ==================== VIEW 1: CATEGORY CARDS & TASK MATRIX (FILO ORDER) ==================== --}}
        @if($viewMode === 'tasks')

            {{-- 3 Category Selector Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- 1. QUIZZES CATEGORY CARD --}}
                @php $isQuizActive = ($activeCategory === 'quizzes' || $activeCategory === 'all'); @endphp
                <div 
                    wire:click="selectCategory('quizzes')"
                    class="cursor-pointer rounded-2xl p-4 sm:p-5 transition-all {{ $isQuizActive ? 'border-2 border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 shadow-md ring-2 ring-emerald-500/20' : 'edtech-card bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] hover:border-emerald-300 dark:hover:border-emerald-800 shadow-sm' }}"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
                                <x-heroicon-o-academic-cap class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Quizzes</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100/80 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            {{ $catStats['quizzes']['count'] }} Taken
                        </span>
                    </div>

                    <div class="flex items-baseline justify-between my-2">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 block">Average Score</span>
                            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                                {{ $catStats['quizzes']['avg_score'] !== null ? $catStats['quizzes']['avg_score'].'%' : '—' }}
                            </span>
                        </div>
                        <div class="text-right text-xs">
                            <span class="text-slate-400 dark:text-slate-500">Attempts:</span>
                            <strong class="text-slate-800 dark:text-slate-200 ml-1 font-bold">{{ $catStats['quizzes']['attempts'] }}</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-3 mt-2 border-t border-slate-100 dark:border-[#233842]/80">
                        <span class="text-slate-500 dark:text-slate-400">
                            Passed: <strong class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $catStats['quizzes']['passed'] }}</strong>
                        </span>
                        <span class="text-[11px] font-extrabold {{ $isQuizActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                            {{ $isQuizActive ? '▲ Active' : '▼ Click to View' }}
                        </span>
                    </div>
                </div>

                {{-- 2. ASSIGNMENTS CATEGORY CARD --}}
                @php $isAssignActive = ($activeCategory === 'assignments' || $activeCategory === 'all'); @endphp
                <div 
                    wire:click="selectCategory('assignments')"
                    class="cursor-pointer rounded-2xl p-4 sm:p-5 transition-all {{ $isAssignActive ? 'border-2 border-teal-500 bg-teal-50/40 dark:bg-teal-950/20 shadow-md ring-2 ring-teal-500/20' : 'edtech-card bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] hover:border-teal-300 dark:hover:border-teal-800 shadow-sm' }}"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-teal-100 dark:bg-teal-900/50 text-teal-700 dark:text-teal-300 flex items-center justify-center">
                                <x-heroicon-o-document-text class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Assignments</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100/80 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                            {{ $catStats['assignments']['count'] }} Taken
                        </span>
                    </div>

                    <div class="flex items-baseline justify-between my-2">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 block">Average Score</span>
                            <span class="text-xl font-black text-teal-600 dark:text-teal-400">
                                {{ $catStats['assignments']['avg_score'] !== null ? $catStats['assignments']['avg_score'].'%' : '—' }}
                            </span>
                        </div>
                        <div class="text-right text-xs">
                            <span class="text-slate-400 dark:text-slate-500">Submissions:</span>
                            <strong class="text-slate-800 dark:text-slate-200 ml-1 font-bold">{{ $catStats['assignments']['submissions'] }}</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-3 mt-2 border-t border-slate-100 dark:border-[#233842]/80">
                        <span class="text-slate-500 dark:text-slate-400">
                            Graded: <strong class="text-teal-600 dark:text-teal-400 font-bold">{{ $catStats['assignments']['graded'] }}</strong>
                        </span>
                        <span class="text-[11px] font-extrabold {{ $isAssignActive ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}">
                            {{ $isAssignActive ? '▲ Active' : '▼ Click to View' }}
                        </span>
                    </div>
                </div>

                {{-- 3. ASSESSMENTS CATEGORY CARD --}}
                @php $isAssessActive = ($activeCategory === 'assessments' || $activeCategory === 'all'); @endphp
                <div 
                    wire:click="selectCategory('assessments')"
                    class="cursor-pointer rounded-2xl p-4 sm:p-5 transition-all {{ $isAssessActive ? 'border-2 border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/20 shadow-md ring-2 ring-indigo-500/20' : 'edtech-card bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] hover:border-indigo-300 dark:hover:border-indigo-800 shadow-sm' }}"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center">
                                <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Assessments</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100/80 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            {{ $catStats['assessments']['count'] }} Taken
                        </span>
                    </div>

                    <div class="flex items-baseline justify-between my-2">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 block">Average Score</span>
                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">
                                {{ $catStats['assessments']['avg_score'] !== null ? $catStats['assessments']['avg_score'].'%' : '—' }}
                            </span>
                        </div>
                        <div class="text-right text-xs">
                            <span class="text-slate-400 dark:text-slate-500">Submissions:</span>
                            <strong class="text-slate-800 dark:text-slate-200 ml-1 font-bold">{{ $catStats['assessments']['submissions'] }}</strong>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-3 mt-2 border-t border-slate-100 dark:border-[#233842]/80">
                        <span class="text-slate-500 dark:text-slate-400">
                            Graded: <strong class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $catStats['assessments']['graded'] }}</strong>
                        </span>
                        <span class="text-[11px] font-extrabold {{ $isAssessActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">
                            {{ $isAssessActive ? '▲ Active' : '▼ Click to View' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ==================== OPENED CATEGORY DETAILS (FILO ORDER) ==================== --}}
            <div class="space-y-6">

                {{-- 1. FULL QUIZZES SECTION --}}
                @if($isQuizActive)
                    <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl border border-emerald-500/30 dark:border-emerald-800/40 shadow-sm overflow-hidden">
                        {{-- Header --}}
                        <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/50 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-heroicon-o-academic-cap class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                    Quizzes Taken (FILO Order • Student Scores in DEC Order)
                                </h3>
                            </div>
                            <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                {{ count($tasksData['quizzes']) }} Taken • {{ $catStats['quizzes']['attempts'] }} Total Attempts
                            </span>
                        </div>

                        @if(count($tasksData['quizzes']) === 0)
                            <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                No quizzes have been taken yet. Once students take a quiz, it will appear here in FILO order.
                            </div>
                        @else
                            <div class="divide-y divide-slate-100 dark:divide-[#233842]">
                                @foreach ($tasksData['quizzes'] as $q)
                                    @php $isTaskOpen = $this->isTaskExpanded($q['key']); @endphp
                                    <div>
                                        {{-- Task Row Header --}}
                                        <div 
                                            wire:click="toggleTask('{{ $q['key'] }}')"
                                            class="p-4 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 flex flex-wrap items-center justify-between gap-3 cursor-pointer transition-colors"
                                        >
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="text-teal-600 dark:text-teal-400">
                                                    @if($isTaskOpen)
                                                        <x-heroicon-o-chevron-down class="w-4 h-4" />
                                                    @else
                                                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                                                    @endif
                                                </span>
                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-extrabold text-xs text-slate-900 dark:text-slate-100">{{ $q['title'] }}</span>
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">{{ $q['course_code'] }}</span>
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">Taken</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                        {{ $q['course'] }} • Pass: {{ $q['pass_percentage'] }}% • {{ $q['results_count'] }} Attempts
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2.5 text-xs">
                                                <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842]">
                                                    <span class="text-slate-400 text-[10px]">Avg:</span>
                                                    <strong class="{{ $q['avg_score'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($q['avg_score'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                                        {{ $q['avg_score'] !== null ? $q['avg_score'].'%' : '—' }}
                                                    </strong>
                                                </div>
                                                <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842]">
                                                    <span class="text-slate-400 text-[10px]">Passed:</span>
                                                    <strong class="text-emerald-600 dark:text-emerald-400">{{ $q['passed_count'] }}/{{ $q['results_count'] }}</strong>
                                                </div>
                                                <span class="text-teal-600 dark:text-teal-400 font-bold text-xs">
                                                    {{ $isTaskOpen ? '▲ Collapse' : '▼ Expand' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Student Attempts Matrix (DEC Order) --}}
                                        @if($isTaskOpen)
                                            <div class="divide-y divide-slate-100 dark:divide-[#233842] pl-6 sm:pl-10 pr-4 bg-white dark:bg-[#102028]">
                                                @foreach ($q['results'] as $idx => $res)
                                                    <div class="py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black shrink-0 {{ match($idx) {
                                                                0 => 'bg-amber-400 text-amber-950 ring-2 ring-amber-300 shadow-xs',
                                                                1 => 'bg-slate-300 text-slate-800 ring-2 ring-slate-200 shadow-xs',
                                                                2 => 'bg-amber-700 text-amber-100 ring-2 ring-amber-600 shadow-xs',
                                                                default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
                                                            } }}">
                                                                {{ $idx + 1 }}
                                                            </span>

                                                            <div class="min-w-0">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $res['student_name'] }}</span>
                                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $res['student_track'] }}</span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                                                    {{ $res['student_email'] }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-2.5 flex-wrap justify-end">
                                                            <span class="text-slate-400 text-[11px]">
                                                                {{ $res['score'] ?? 0 }}/{{ $res['total_points'] ?? '—' }} pts
                                                            </span>

                                                            <span class="text-sm font-extrabold min-w-[48px] text-right {{ $res['percentage'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($res['percentage'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                                                {{ $res['percentage'] !== null ? $res['percentage'].'%' : '—' }}
                                                            </span>

                                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $res['passed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                                                                {{ $res['passed'] ? 'Passed' : 'Failed' }}
                                                            </span>

                                                            @if(!empty($res['is_retake']))
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800" title="2nd Attempt (Capped at pass mark {{ $q['pass_percentage'] ?? 50 }}%)">
                                                                    2nd Try
                                                                </span>
                                                            @endif

                                                            <button 
                                                                type="button" 
                                                                wire:click="viewQuizAttempt({{ $res['attempt_id'] }})"
                                                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold inline-flex items-center gap-1 transition-colors"
                                                                title="View full questions and student submitted choices"
                                                            >
                                                                <x-heroicon-o-eye class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                                                                <span>View Answers</span>
                                                            </button>

                                                            @if(!empty($res['retake_allowed']))
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="revokeQuizRetake({{ $res['student_id'] }}, {{ $q['id'] }})"
                                                                    class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold transition-colors"
                                                                    title="Click to revoke 2nd try permission"
                                                                >
                                                                    ⭐ 2nd Try Granted (Revoke)
                                                                </button>
                                                            @else
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="grantQuizRetake({{ $res['student_id'] }}, {{ $q['id'] }})"
                                                                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-[#233842] text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/40 text-[11px] font-bold transition-colors"
                                                                    title="Allow student to take this quiz again (score capped at pass mark)"
                                                                >
                                                                    + Grant 2nd Try
                                                                </button>
                                                            @endif

                                                            <span class="text-[10px] text-slate-400 min-w-[70px] text-right">
                                                                {{ $res['completed_at'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- 2. FULL ASSIGNMENTS SECTION --}}
                @if($isAssignActive)
                    <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl border border-teal-500/30 dark:border-teal-800/40 shadow-sm overflow-hidden">
                        {{-- Header --}}
                        <div class="p-4 bg-teal-50/70 dark:bg-teal-950/40 border-b border-teal-100 dark:border-teal-900/50 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-heroicon-o-document-text class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                    Assignments Taken (FILO Order • Student Grades in DEC Order)
                                </h3>
                            </div>
                            <span class="text-xs font-semibold text-teal-800 dark:text-teal-300">
                                {{ count($tasksData['assignments']) }} Taken • {{ $catStats['assignments']['submissions'] }} Submissions
                            </span>
                        </div>

                        @if(count($tasksData['assignments']) === 0)
                            <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                No assignments have been submitted yet. Once students submit an assignment, it will appear here in FILO order.
                            </div>
                        @else
                            <div class="divide-y divide-slate-100 dark:divide-[#233842]">
                                @foreach ($tasksData['assignments'] as $a)
                                    @php $isTaskOpen = $this->isTaskExpanded($a['key']); @endphp
                                    <div>
                                        {{-- Task Row Header --}}
                                        <div 
                                            wire:click="toggleTask('{{ $a['key'] }}')"
                                            class="p-4 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 flex flex-wrap items-center justify-between gap-3 cursor-pointer transition-colors"
                                        >
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="text-teal-600 dark:text-teal-400">
                                                    @if($isTaskOpen)
                                                        <x-heroicon-o-chevron-down class="w-4 h-4" />
                                                    @else
                                                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                                                    @endif
                                                </span>
                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-extrabold text-xs text-slate-900 dark:text-slate-100">{{ $a['title'] }}</span>
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">{{ $a['course_code'] }}</span>
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-100 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300">Taken</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                        {{ $a['course'] }} • Due: {{ $a['due_date'] }} • {{ $a['results_count'] }} Submissions
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2.5 text-xs">
                                                <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842]">
                                                    <span class="text-slate-400 text-[10px]">Avg:</span>
                                                    <strong class="{{ $a['avg_score'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($a['avg_score'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                                        {{ $a['avg_score'] !== null ? $a['avg_score'].'%' : '—' }}
                                                    </strong>
                                                </div>
                                                <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842]">
                                                    <span class="text-slate-400 text-[10px]">Graded:</span>
                                                    <strong class="text-teal-600 dark:text-teal-400">{{ $a['graded_count'] }}/{{ $a['results_count'] }}</strong>
                                                </div>
                                                <span class="text-teal-600 dark:text-teal-400 font-bold text-xs">
                                                    {{ $isTaskOpen ? '▲ Collapse' : '▼ Expand' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Student Submissions Matrix (DEC Order) --}}
                                        @if($isTaskOpen)
                                            <div class="divide-y divide-slate-100 dark:divide-[#233842] pl-6 sm:pl-10 pr-4 bg-white dark:bg-[#102028]">
                                                @foreach ($a['results'] as $idx => $res)
                                                    <div class="py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black shrink-0 {{ match($idx) {
                                                                0 => 'bg-amber-400 text-amber-950 ring-2 ring-amber-300 shadow-xs',
                                                                1 => 'bg-slate-300 text-slate-800 ring-2 ring-slate-200 shadow-xs',
                                                                2 => 'bg-amber-700 text-amber-100 ring-2 ring-amber-600 shadow-xs',
                                                                default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
                                                            } }}">
                                                                {{ $idx + 1 }}
                                                            </span>

                                                            <div class="min-w-0">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $res['student_name'] }}</span>
                                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $res['student_track'] }}</span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                                                    {{ $res['student_email'] }}
                                                                </div>
                                                                @if(!empty($res['feedback']))
                                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 italic mt-0.5">
                                                                        "{{ \Illuminate\Support\Str::limit($res['feedback'], 50) }}"
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-2.5 flex-wrap justify-end">
                                                            <span class="text-sm font-extrabold min-w-[48px] text-right {{ $res['grade'] !== null ? ($res['grade'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($res['grade'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')) : 'text-slate-400' }}">
                                                                {{ $res['grade'] !== null ? $res['grade'].'%' : 'Ungraded' }}
                                                            </span>

                                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ ($res['status'] ?? '') === 'Graded' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                                                {{ $res['status'] ?? 'Submitted' }}
                                                            </span>

                                                            @if(!empty($res['is_retake']))
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800" title="2nd Attempt (Capped at 50%)">
                                                                    2nd Try
                                                                </span>
                                                            @endif

                                                            @if(!empty($res['retake_allowed']))
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="revokeAssignmentRetake({{ $res['submission_id'] }})"
                                                                    class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200 dark:border-teal-800 text-[11px] font-bold transition-colors"
                                                                    title="Click to revoke 2nd try permission"
                                                                >
                                                                    ⭐ 2nd Try Granted (Revoke)
                                                                </button>
                                                            @else
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="grantAssignmentRetake({{ $res['submission_id'] }})"
                                                                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-[#233842] text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/40 text-[11px] font-bold transition-colors"
                                                                    title="Allow student to submit a second attempt (grade capped at 50%)"
                                                                >
                                                                    + Grant 2nd Try
                                                                </button>
                                                            @endif

                                                            <span class="text-[10px] text-slate-400 min-w-[70px] text-right">
                                                                {{ $res['submitted_at'] }}
                                                            </span>

                                                            @if(!empty($res['submission_id']))
                                                                <a 
                                                                    href="{{ route('filament.instructor.resources.assignment-submission-resource.assignment-submissions.edit', ['record' => $res['submission_id']]) }}"
                                                                    class="px-3 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-bold shadow-2xs transition-all no-underline inline-flex items-center gap-1"
                                                                >
                                                                    <span>Grade / Review</span>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- 3. FULL ASSESSMENTS SECTION --}}
                @if($isAssessActive)
                    <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl border border-indigo-500/30 dark:border-indigo-800/40 shadow-sm overflow-hidden">
                        {{-- Header --}}
                        <div class="p-4 bg-indigo-50/70 dark:bg-indigo-950/40 border-b border-indigo-100 dark:border-indigo-900/50 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                    Assessments Taken (FILO Order • Student Scores in DEC Order)
                                </h3>
                            </div>
                            <span class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">
                                {{ count($tasksData['assessments']) }} Taken • {{ $catStats['assessments']['submissions'] }} Submissions
                            </span>
                        </div>

                        @if(count($tasksData['assessments']) === 0)
                            <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                No assessments have been submitted yet. Once students submit an assessment, it will appear here in FILO order.
                            </div>
                        @else
                            <div class="divide-y divide-slate-100 dark:divide-[#233842]">
                                @foreach ($tasksData['assessments'] as $as)
                                    @php $isTaskOpen = $this->isTaskExpanded($as['key']); @endphp
                                    <div>
                                        {{-- Task Row Header --}}
                                        <div 
                                            wire:click="toggleTask('{{ $as['key'] }}')"
                                            class="p-4 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 flex flex-wrap items-center justify-between gap-3 cursor-pointer transition-colors"
                                        >
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="text-teal-600 dark:text-teal-400">
                                                    @if($isTaskOpen)
                                                        <x-heroicon-o-chevron-down class="w-4 h-4" />
                                                    @else
                                                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                                                    @endif
                                                </span>
                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-extrabold text-xs text-slate-900 dark:text-slate-100">{{ $as['title'] }}</span>
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">{{ $as['course_code'] }}</span>
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">Taken</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                        {{ $as['course'] }} • Due: {{ $as['due_date'] }} • {{ $as['results_count'] }} Submissions
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2.5 text-xs">
                                                <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842]">
                                                    <span class="text-slate-400 text-[10px]">Avg:</span>
                                                    <strong class="{{ $as['avg_score'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($as['avg_score'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                                        {{ $as['avg_score'] !== null ? $as['avg_score'].'%' : '—' }}
                                                    </strong>
                                                </div>
                                                <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842]">
                                                    <span class="text-slate-400 text-[10px]">Graded:</span>
                                                    <strong class="text-indigo-600 dark:text-indigo-400">{{ $as['graded_count'] }}/{{ $as['results_count'] }}</strong>
                                                </div>
                                                <span class="text-teal-600 dark:text-teal-400 font-bold text-xs">
                                                    {{ $isTaskOpen ? '▲ Collapse' : '▼ Expand' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Student Submissions Matrix (DEC Order) --}}
                                        @if($isTaskOpen)
                                            <div class="divide-y divide-slate-100 dark:divide-[#233842] pl-6 sm:pl-10 pr-4 bg-white dark:bg-[#102028]">
                                                @foreach ($as['results'] as $idx => $res)
                                                    <div class="py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                                        <div class="flex items-center gap-3 min-w-0 flex-1">
                                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black shrink-0 {{ match($idx) {
                                                                0 => 'bg-amber-400 text-amber-950 ring-2 ring-amber-300 shadow-xs',
                                                                1 => 'bg-slate-300 text-slate-800 ring-2 ring-slate-200 shadow-xs',
                                                                2 => 'bg-amber-700 text-amber-100 ring-2 ring-amber-600 shadow-xs',
                                                                default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
                                                            } }}">
                                                                {{ $idx + 1 }}
                                                            </span>

                                                            <div class="min-w-0">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $res['student_name'] }}</span>
                                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $res['student_track'] }}</span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                                                    {{ $res['student_email'] }}
                                                                </div>
                                                                @if(!empty($res['feedback']))
                                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 italic mt-0.5">
                                                                        "{{ \Illuminate\Support\Str::limit($res['feedback'], 50) }}"
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-2.5 flex-wrap justify-end">
                                                            <span class="text-sm font-extrabold min-w-[48px] text-right {{ $res['score'] !== null ? ($res['score'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($res['score'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')) : 'text-slate-400' }}">
                                                                {{ $res['score'] !== null ? $res['score'].'%' : 'Ungraded' }}
                                                            </span>

                                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ ($res['status'] ?? '') === 'Graded' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                                                {{ $res['status'] ?? 'Submitted' }}
                                                            </span>

                                                            @if(!empty($res['is_retake']))
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-cyan-50 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800" title="2nd Attempt (Capped at 50%)">
                                                                    2nd Try
                                                                </span>
                                                            @endif

                                                            @if(!empty($res['retake_allowed']))
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="revokeAssessmentRetake({{ $res['submission_id'] }})"
                                                                    class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[11px] font-bold transition-colors"
                                                                    title="Click to revoke 2nd try permission"
                                                                >
                                                                    ⭐ 2nd Try Granted (Revoke)
                                                                </button>
                                                            @else
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="grantAssessmentRetake({{ $res['submission_id'] }})"
                                                                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-[#233842] text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-[11px] font-bold transition-colors"
                                                                    title="Allow student to submit a second attempt (score capped at 50%)"
                                                                >
                                                                    + Grant 2nd Try
                                                                </button>
                                                            @endif

                                                            <span class="text-[10px] text-slate-400 min-w-[70px] text-right">
                                                                {{ $res['submitted_at'] }}
                                                            </span>

                                                            @if(!empty($res['submission_id']))
                                                                <a 
                                                                    href="{{ route('filament.instructor.resources.assessment-submission-resource.assessment-submissions.edit', ['record' => $res['submission_id']]) }}"
                                                                    class="px-3 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold shadow-2xs transition-all no-underline inline-flex items-center gap-1"
                                                                >
                                                                    <span>Grade / Review</span>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        {{-- ==================== VIEW 2: PER STUDENT TABLE & EXPANDABLE DRAWER ==================== --}}
        @else
            <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl border border-slate-200 dark:border-[#233842] shadow-sm overflow-hidden">
                @if(count($students) === 0)
                    <div class="p-12 text-center">
                        <x-heroicon-o-users class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                        <p class="text-sm font-extrabold text-slate-800 dark:text-slate-200">No students found matching your criteria</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Try clearing search filters to see all enrolled learners.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-[#233842]">
                        @foreach ($students as $idx => $row)
                            @php $isExpanded = !empty($this->expandedStudents[$row['id']]); @endphp
                            <div class="{{ $isExpanded ? 'bg-teal-50/20 dark:bg-teal-950/10' : '' }} transition-colors">
                                {{-- Student Summary Row --}}
                                <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <button 
                                            type="button"
                                            wire:click="toggleExpand({{ $row['id'] }})"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            @if($isExpanded)
                                                <x-heroicon-o-chevron-down class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                            @else
                                                <x-heroicon-o-chevron-right class="w-4 h-4" />
                                            @endif
                                        </button>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-extrabold text-sm text-slate-900 dark:text-slate-100">{{ $row['name'] }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $row['track'] }}</span>

                                                @if(!empty($row['all_courses_completed']))
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 inline-flex items-center gap-1">
                                                        <x-heroicon-s-check-circle class="w-3 h-3" /> Completed
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                                {{ $row['email'] }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Mini Scores Breakdown & Course Completion Actions --}}
                                    <div class="flex items-center gap-3 flex-wrap text-xs">
                                        <div class="px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842] text-center">
                                            <span class="text-slate-400 text-[10px] block font-semibold">Quiz</span>
                                            <strong class="text-emerald-600 dark:text-emerald-400">{{ $row['avg_quiz_score'] !== null ? $row['avg_quiz_score'].'%' : '—' }}</strong>
                                        </div>
                                        <div class="px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842] text-center">
                                            <span class="text-slate-400 text-[10px] block font-semibold">Assign</span>
                                            <strong class="text-teal-600 dark:text-teal-400">{{ $row['avg_assignment_grade'] !== null ? $row['avg_assignment_grade'].'%' : '—' }}</strong>
                                        </div>
                                        <div class="px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842] text-center">
                                            <span class="text-slate-400 text-[10px] block font-semibold">Assess</span>
                                            <strong class="text-indigo-600 dark:text-indigo-400">{{ $row['avg_assessment_score'] !== null ? $row['avg_assessment_score'].'%' : '—' }}</strong>
                                        </div>

                                        {{-- Overall Score --}}
                                        <div class="text-right min-w-[55px] px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                                            <span class="text-slate-400 text-[10px] block font-semibold">Overall</span>
                                            <span class="text-sm font-black {{ match($row['tier_key']) {
                                                'distinction' => 'text-emerald-600 dark:text-emerald-400',
                                                'merit' => 'text-cyan-600 dark:text-cyan-400',
                                                'pass' => 'text-amber-600 dark:text-amber-400',
                                                default => 'text-rose-600 dark:text-rose-400'
                                            } }}">
                                                {{ $row['overall_score'] !== null ? $row['overall_score'].'%' : '—' }}
                                            </span>
                                        </div>

                                        {{-- Quick Mark Complete Buttons --}}
                                        @foreach ($row['courses'] as $c)
                                            @if($c['is_completed'])
                                                <span class="px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold inline-flex items-center gap-1.5">
                                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                                    <span>{{ $c['code'] ?: 'Course' }}: Ready</span>
                                                </span>
                                            @else
                                                <button 
                                                    type="button"
                                                    wire:click="markCourseComplete({{ $row['id'] }}, {{ $c['id'] }})"
                                                    class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-2xs transition-all inline-flex items-center gap-1.5"
                                                    title="Mark course as completed to release certificate for {{ $row['name'] }}"
                                                >
                                                    <x-heroicon-s-check class="w-3.5 h-3.5" />
                                                    <span>Complete {{ $c['code'] ?: 'Course' }}</span>
                                                </button>
                                            @endif
                                        @endforeach

                                        {{-- Award XP/Badge Button --}}
                                        <button 
                                            type="button"
                                            wire:click="openAwardModal({{ $row['id'] }})"
                                            class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold shadow-2xs transition-all inline-flex items-center gap-1.5"
                                            title="Award XP Points & Badges for presentations, hackathons, and off-platform excellence"
                                        >
                                            <x-heroicon-s-sparkles class="w-3.5 h-3.5" />
                                            <span>Award XP/Badge</span>
                                        </button>

                                        <button 
                                            type="button"
                                            wire:click="toggleExpand({{ $row['id'] }})"
                                            class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-[#233842] bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition-colors"
                                        >
                                            {{ $isExpanded ? 'Hide' : 'Details' }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Expanded Student Breakdown --}}
                                @if($isExpanded)
                                    <div class="px-6 sm:px-12 pb-5 pt-2 bg-slate-50/50 dark:bg-slate-900/40 border-t border-slate-100 dark:border-[#233842] space-y-4">
                                        
                                        {{-- Course Completion & Certificate Section --}}
                                        <div class="p-3.5 rounded-xl bg-white dark:bg-[#102028] border border-slate-200/80 dark:border-[#233842] space-y-2.5">
                                            <div class="flex items-center justify-between flex-wrap gap-2">
                                                <span class="text-[11px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">
                                                    Course Completion &amp; Certificate Readiness
                                                </span>
                                                <span class="text-[10px] text-slate-400 dark:text-slate-500">
                                                    Certificates become available once instructor marks completion
                                                </span>
                                            </div>

                                            <div class="space-y-2">
                                                @foreach ($row['courses'] as $c)
                                                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842] flex items-center justify-between flex-wrap gap-2 text-xs">
                                                        <div class="flex items-center gap-2">
                                                            <strong class="text-slate-900 dark:text-slate-100">{{ $c['title'] }}</strong>
                                                            @if(!empty($c['code']))
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">{{ $c['code'] }}</span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center gap-2">
                                                            @if($c['is_completed'])
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 inline-flex items-center gap-1">
                                                                    <x-heroicon-s-check-circle class="w-3 h-3" /> Completed on {{ $c['completed_at'] }}
                                                                </span>
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                                                    Certificate Ready
                                                                </span>
                                                                <button 
                                                                    type="button"
                                                                    wire:click="unmarkCourseComplete({{ $row['id'] }}, {{ $c['id'] }})"
                                                                    class="text-[11px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 underline ml-1"
                                                                    title="Reset completion status"
                                                                >
                                                                    Reset
                                                                </button>
                                                            @else
                                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                                    In Progress
                                                                </span>
                                                                <button 
                                                                    type="button"
                                                                    wire:click="markCourseComplete({{ $row['id'] }}, {{ $c['id'] }})"
                                                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-2xs inline-flex items-center gap-1"
                                                                >
                                                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                                                    <span>Mark Complete</span>
                                                                </button>
                                                            @endif

                                                            <button 
                                                                type="button"
                                                                wire:click="removeStudentFromCourse({{ $row['id'] }}, {{ $c['id'] }})"
                                                                wire:confirm="Are you sure you want to remove {{ $row['name'] }} from '{{ $c['title'] }}'? This will unenrol the student from the course."
                                                                class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 hover:text-rose-700 transition-colors ml-1"
                                                                title="Remove student from this course"
                                                            >
                                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Quizzes (DEC Order) --}}
                                        @if(count($row['quiz_details']) > 0)
                                            <div class="space-y-1.5">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                                    Quizzes (DEC Order)
                                                </span>
                                                <div class="space-y-1">
                                                    @foreach ($row['quiz_details'] as $qd)
                                                        <div class="p-2.5 rounded-xl bg-white dark:bg-[#102028] border border-slate-100 dark:border-[#233842] flex items-center justify-between flex-wrap gap-2 text-xs">
                                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $qd['title'] }} ({{ $qd['course'] }})</span>
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <strong class="{{ $qd['passed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                                    {{ $qd['percentage'] }}%
                                                                </strong>

                                                                @if(!empty($qd['is_retake']))
                                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">2nd Try</span>
                                                                @endif

                                                                <button 
                                                                    type="button" 
                                                                    wire:click="viewQuizAttempt({{ $qd['id'] }})"
                                                                    class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-[10px] font-bold inline-flex items-center gap-1 transition-colors"
                                                                >
                                                                    <x-heroicon-o-eye class="w-3 h-3 text-teal-600 dark:text-teal-400" />
                                                                    <span>Answers</span>
                                                                </button>

                                                                @if(!empty($qd['retake_allowed']))
                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="revokeQuizRetake({{ $row['id'] }}, {{ $qd['quiz_id'] }})"
                                                                        class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold"
                                                                    >
                                                                        ⭐ 2nd Try Granted (Revoke)
                                                                    </button>
                                                                @else
                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="grantQuizRetake({{ $row['id'] }}, {{ $qd['quiz_id'] }})"
                                                                        class="px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-[#233842] text-teal-600 dark:text-teal-400 text-[10px] font-bold"
                                                                    >
                                                                        + Grant 2nd Try
                                                                    </button>
                                                                @endif
                                                                <span class="text-slate-400 text-[10px]">{{ $qd['date'] }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Assignments (DEC Order) --}}
                                        @if(count($row['assignment_details']) > 0)
                                            <div class="space-y-1.5">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">
                                                    Assignments (DEC Order)
                                                </span>
                                                <div class="space-y-1">
                                                    @foreach ($row['assignment_details'] as $ad)
                                                        <div class="p-2.5 rounded-xl bg-white dark:bg-[#102028] border border-slate-100 dark:border-[#233842] flex items-center justify-between flex-wrap gap-2 text-xs">
                                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $ad['title'] }} ({{ $ad['course'] }})</span>
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <strong class="{{ $ad['grade'] !== null ? ($ad['grade'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') : 'text-slate-400' }}">
                                                                    {{ $ad['grade'] !== null ? $ad['grade'].'%' : 'Ungraded' }}
                                                                </strong>

                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $ad['status'] }}</span>

                                                                @if(!empty($ad['is_retake']))
                                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">2nd Try</span>
                                                                @endif

                                                                @if(!empty($ad['retake_allowed']))
                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="revokeAssignmentRetake({{ $ad['id'] }})"
                                                                        class="px-2 py-0.5 rounded-md bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200 dark:border-teal-800 text-[10px] font-bold"
                                                                    >
                                                                        ⭐ 2nd Try Granted (Revoke)
                                                                    </button>
                                                                @else
                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="grantAssignmentRetake({{ $ad['id'] }})"
                                                                        class="px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-[#233842] text-teal-600 dark:text-teal-400 text-[10px] font-bold"
                                                                    >
                                                                        + Grant 2nd Try
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Assessments (DEC Order) --}}
                                        @if(count($row['assessment_details']) > 0)
                                            <div class="space-y-1.5">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                                    Assessments (DEC Order)
                                                </span>
                                                <div class="space-y-1">
                                                    @foreach ($row['assessment_details'] as $asub)
                                                        <div class="p-2.5 rounded-xl bg-white dark:bg-[#102028] border border-slate-100 dark:border-[#233842] flex items-center justify-between flex-wrap gap-2 text-xs">
                                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $asub['title'] }} ({{ $asub['course'] }})</span>
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <strong class="{{ $asub['score'] !== null ? ($asub['score'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') : 'text-slate-400' }}">
                                                                    {{ $asub['score'] !== null ? $asub['score'].'%' : 'Ungraded' }}
                                                                </strong>

                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $asub['status'] }}</span>

                                                                @if(!empty($asub['is_retake']))
                                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300">2nd Try</span>
                                                                @endif

                                                                @if(!empty($asub['retake_allowed']))
                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="revokeAssessmentRetake({{ $asub['id'] }})"
                                                                        class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[10px] font-bold"
                                                                    >
                                                                        ⭐ 2nd Try Granted (Revoke)
                                                                    </button>
                                                                @else
                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="grantAssessmentRetake({{ $asub['id'] }})"
                                                                        class="px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-[#233842] text-indigo-600 dark:text-indigo-400 text-[10px] font-bold"
                                                                    >
                                                                        + Grant 2nd Try
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- ==================== MODAL 1: QUIZ ATTEMPT ANSWERS REVIEW ==================== --}}
        @if ($this->selectedQuizAttempt)
            <div 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                x-data
                @keydown.escape.window="$wire.closeQuizAttemptModal()"
            >
                <div class="relative w-full max-w-4xl max-h-[90vh] bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in duration-150">
                    {{-- Modal Header --}}
                    <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-[#233842] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 flex items-center justify-center border border-teal-100 dark:border-teal-900/60">
                                <x-heroicon-o-academic-cap class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                    Student Quiz Review &amp; Answers
                                </h3>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Full question breakdown, student selections, and answer accuracy
                                </p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            wire:click="closeQuizAttemptModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold leading-none"
                            title="Close modal"
                        >
                            &times;
                        </button>
                    </div>

                    {{-- Scrollable Content Body --}}
                    <div class="p-5 overflow-y-auto flex-1 bg-slate-50/40 dark:bg-slate-900/40">
                        @include('filament.instructor.modals.quiz-attempt-answers', ['attempt' => $this->selectedQuizAttempt])
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-100 dark:border-[#233842] flex items-center justify-end gap-2.5 flex-wrap">
                        @php $modalAttempt = $this->selectedQuizAttempt; @endphp
                        @if ($modalAttempt && !empty($modalAttempt->retake_allowed))
                            <button 
                                type="button" 
                                wire:click="revokeQuizRetake({{ $modalAttempt->user_id }}, {{ $modalAttempt->quiz_id }})"
                                class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold transition-colors"
                            >
                                ⭐ 2nd Try Granted (Revoke)
                            </button>
                        @elseif ($modalAttempt && $modalAttempt->completed_at)
                            <button 
                                type="button" 
                                wire:click="grantQuizRetake({{ $modalAttempt->user_id }}, {{ $modalAttempt->quiz_id }})"
                                class="px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-2xs transition-all"
                            >
                                + Grant 2nd Try
                            </button>
                        @endif
                        <button 
                            type="button" 
                            wire:click="closeQuizAttemptModal"
                            class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== MODAL 2: AWARD XP & BADGES ==================== --}}
        @if ($this->showAwardModal)
            <div 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                x-data
                @keydown.escape.window="$wire.closeAwardModal()"
            >
                <div class="relative w-full max-w-lg max-h-[90vh] bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in duration-150">
                    {{-- Modal Header --}}
                    <div class="px-5 py-4 bg-amber-50/70 dark:bg-amber-950/30 border-b border-amber-100 dark:border-amber-900/40 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-xs">
                                <x-heroicon-s-sparkles class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-amber-950 dark:text-amber-200">
                                    Award XP &amp; Badges to {{ $this->awardStudentName }}
                                </h3>
                                <p class="text-[11px] text-amber-800 dark:text-amber-400">
                                    Recognize presentations, hackathons, and off-platform excellence
                                </p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            wire:click="closeAwardModal"
                            class="text-amber-800 dark:text-amber-300 hover:text-amber-950 text-xl font-bold leading-none"
                            title="Close modal"
                        >
                            &times;
                        </button>
                    </div>

                    {{-- Form Body --}}
                    <div class="p-5 overflow-y-auto flex-1 space-y-4 text-xs">
                        {{-- Course Select --}}
                        <div>
                            <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Associated Course (Optional)
                            </label>
                            <select 
                                wire:model.live="awardCourseId"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            >
                                <option value="">None / General Off-Platform Activity</option>
                                @foreach ($this->instructorCourseOptions() as $cId => $cTitle)
                                    <option value="{{ $cId }}">{{ $cTitle }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Activity Type --}}
                        <div>
                            <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Activity / Recognition Reason <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                wire:model.live="awardActivityType"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            >
                                <option value="Outstanding Presentation">🎤 Outstanding Presentation</option>
                                <option value="Classroom Participation &amp; Debate">💬 Classroom Participation &amp; Debate</option>
                                <option value="Project Demo &amp; Showcase">💻 Project Demo &amp; Showcase</option>
                                <option value="Hackathon / Competition Winner">🚀 Hackathon / Competition Winner</option>
                                <option value="Peer Mentoring &amp; Collaboration">🤝 Peer Mentoring &amp; Collaboration</option>
                                <option value="Lab Practical Excellence">⚙️ Lab Practical Excellence</option>
                                <option value="Leadership &amp; Teamwork">👑 Leadership &amp; Teamwork</option>
                                <option value="Extracurricular Contribution">🌟 Extracurricular Contribution</option>
                                <option value="custom">➕ Other / Custom Activity</option>
                            </select>
                        </div>

                        @if ($this->awardActivityType === 'custom')
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1">
                                    Custom Activity Name <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="awardCustomActivity"
                                    placeholder="e.g. AI Prompt Challenge Winner"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                                />
                            </div>
                        @endif

                        {{-- XP and Coins Grid --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1">
                                    XP Points <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    wire:model.live.debounce.300ms="awardXp" 
                                    min="1" 
                                    max="2000"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                                />
                                <span class="text-[10px] text-slate-400 block mt-1">
                                    Contributes to lifetime rank
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1">
                                    Thinker Coins (TC)
                                </label>
                                <input 
                                    type="number" 
                                    wire:model="awardCoins" 
                                    min="0" 
                                    max="1000"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                                />
                                <span class="text-[10px] text-slate-400 block mt-1">
                                    Spendable reward coins (30%)
                                </span>
                            </div>
                        </div>

                        {{-- Badge Selection --}}
                        <div>
                            <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Award Badge (Optional)
                            </label>
                            <select 
                                wire:model.live="awardBadgeId"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            >
                                <option value="">No Badge (XP / Coins only)</option>
                                @foreach (\App\Models\Badge::all() as $b)
                                    <option value="{{ $b->id }}">{{ $b->icon }} {{ $b->name }} (+{{ $b->xp_reward }} XP)</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($this->awardBadgeId)
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30">
                                <input type="checkbox" id="awardBadgeBonusXp" wire:model="awardBadgeBonusXp" class="rounded text-amber-600 focus:ring-amber-500 cursor-pointer" />
                                <label for="awardBadgeBonusXp" class="text-xs text-slate-700 dark:text-slate-200 cursor-pointer font-medium">
                                    Also grant badge's inherent bonus XP reward to student
                                </label>
                            </div>
                        @endif

                        {{-- Commendation Note --}}
                        <div>
                            <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1">
                                Commendation Note / Reason (Optional)
                            </label>
                            <textarea 
                                wire:model="awardNote" 
                                rows="2"
                                placeholder="e.g. Delivered a captivating presentation with outstanding live demonstration."
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-slate-800/80 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                            ></textarea>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-100 dark:border-[#233842] flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            wire:click="closeAwardModal"
                            class="px-4 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="button" 
                            wire:click="submitAward"
                            class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold shadow-2xs transition-all inline-flex items-center gap-1.5"
                        >
                            <x-heroicon-s-sparkles class="w-3.5 h-3.5" />
                            <span>Award Recognition</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
