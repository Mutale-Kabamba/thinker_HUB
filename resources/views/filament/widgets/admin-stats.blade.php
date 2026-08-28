<x-filament-widgets::widget>
    <div class="space-y-6">
        {{-- ============================================================ --}}
        {{-- 1. TOP HERO GREETING BANNER CARD                             --}}
        {{-- ============================================================ --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-700 via-emerald-800 to-teal-900 text-white p-6 sm:p-7 shadow-md border border-teal-600/30">
            {{-- Decorative ambient background blur circles --}}
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -top-10 w-48 h-48 bg-teal-400/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                {{-- Left Text & Actions --}}
                <div class="space-y-3 max-w-2xl text-center md:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 backdrop-blur-md border border-white/20 text-teal-100">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Thinker HUB • Admin Portal</span>
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/20 text-emerald-200 border border-emerald-400/20">
                            Active Cohorts: {{ $activeCohorts }} · Active Courses: {{ $activeCourses }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white leading-tight">
                        Welcome back, {{ auth()->user()->name }}!
                    </h1>

                    <p class="text-xs sm:text-sm text-teal-50/90 leading-relaxed font-normal">
                        Thinker HUB platform overview, curriculum management, and real-time learner performance oversight.
                    </p>

                    <div class="pt-1.5 flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <a 
                            href="{{ $coursesUrl }}" 
                            class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 rounded-xl text-xs font-bold text-slate-900 bg-white hover:bg-slate-50 shadow-sm hover:shadow-md transition-all duration-150 transform hover:-translate-y-0.5"
                        >
                            <span>View Classrooms</span>
                            <svg class="w-3.5 h-3.5 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <a 
                            href="{{ $studentsUrl }}" 
                            class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 rounded-xl text-xs font-bold text-white bg-white/15 hover:bg-white/25 border border-white/20 backdrop-blur-xs shadow-sm hover:shadow-md transition-all duration-150 transform hover:-translate-y-0.5"
                        >
                            <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Active Learners ({{ $activeLearners }})</span>
                        </a>
                    </div>
                </div>

                {{-- Right Avatar Profile Box --}}
                <div class="relative flex-shrink-0 flex items-center justify-center">
                    <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-full p-1 bg-white/20 backdrop-blur-md border-2 border-white/40 shadow-xl flex items-center justify-center">
                        @if (auth()->user()->profile_photo_path)
                            <img 
                                src="{{ auth()->user()->getFilamentAvatarUrl() }}" 
                                alt="{{ auth()->user()->name }}" 
                                class="w-full h-full object-cover rounded-full shadow-inner border border-white/50"
                            />
                        @else
                            <div class="w-full h-full rounded-full bg-teal-900 text-white font-black text-2xl sm:text-3xl flex items-center justify-center shadow-inner border border-white/50">
                                {{ \Illuminate\Support\Str::upper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- 2. STATUS KPI CARDS (MODERN ALIGNED CARD ARCHITECTURE)       --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Card 1: Registered Students (Teal / Emerald Theme) --}}
            <a 
                href="{{ $studentsUrl }}" 
                class="bg-white dark:bg-[#111b21] p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
            >
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>

                    <div class="relative w-11 h-11 flex items-center justify-center">
                        <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-teal-500/20 dark:text-teal-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-teal-500 dark:text-teal-400" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[10px] font-extrabold text-teal-600 dark:text-teal-300">
                            100%
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ sprintf('%02d', $registeredStudents) }}
                    </div>
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-1 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors flex items-center gap-1">
                        <span>Registered Students</span>
                        <span>&rarr;</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                        Active learner accounts
                    </div>
                </div>
            </a>

            {{-- Card 2: Assigned Assessments (Sky / Blue Theme) --}}
            <a 
                href="{{ $assessmentsUrl }}" 
                class="bg-white dark:bg-[#111b21] p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
            >
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div class="relative w-11 h-11 flex items-center justify-center">
                        <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-sky-500/20 dark:text-sky-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-sky-500 dark:text-sky-400" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[10px] font-extrabold text-sky-600 dark:text-sky-300">
                            {{ $assignedAssessments }}
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ sprintf('%02d', $assignedAssessments) }}
                    </div>
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-1 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors flex items-center gap-1">
                        <span>Assigned Assessments</span>
                        <span>&rarr;</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                        Evaluation & quiz records
                    </div>
                </div>
            </a>

            {{-- Card 3: Published Assignments (Amber / Orange Theme) --}}
            <a 
                href="{{ $assignmentsUrl }}" 
                class="bg-white dark:bg-[#111b21] p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
            >
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>

                    <div class="relative w-11 h-11 flex items-center justify-center">
                        <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-amber-500/20 dark:text-amber-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-amber-500 dark:text-amber-400" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[10px] font-extrabold text-amber-600 dark:text-amber-300">
                            {{ $publishedAssignments }}
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ sprintf('%02d', $publishedAssignments) }}
                    </div>
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-1 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors flex items-center gap-1">
                        <span>Published Assignments</span>
                        <span>&rarr;</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                        Active coursework deliverables
                    </div>
                </div>
            </a>

            {{-- Card 4: Learning Materials (Purple / Indigo Theme) --}}
            <a 
                href="{{ $materialsUrl }}" 
                class="bg-white dark:bg-[#111b21] p-5 rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
            >
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>

                    <div class="relative w-11 h-11 flex items-center justify-center">
                        <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-purple-500/20 dark:text-purple-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-purple-500 dark:text-purple-400" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-[10px] font-extrabold text-purple-600 dark:text-purple-300">
                            {{ $materials }}
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ sprintf('%02d', $materials) }}
                    </div>
                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors flex items-center gap-1">
                        <span>Learning Materials</span>
                        <span>&rarr;</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                        Guides, slides & notes
                    </div>
                </div>
            </a>

        </div>
    </div>
</x-filament-widgets::widget>
