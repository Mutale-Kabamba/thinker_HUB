<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- Header Card --}}
        <div class="edtech-card bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-[#7C3AED] dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800">
                        Performance Insights
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Course & Learner Analytics
                </h1>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Completion rates, quiz performance, grading turnaround times, and at-risk student monitoring.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a 
                    href="{{ route('filament.instructor.pages.student-results') }}" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold text-[#7C3AED] dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 transition shadow-2xs"
                >
                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-4 h-4" />
                    <span>Gradebook & Roster</span>
                </a>
            </div>
        </div>

        {{-- Status KPI Cards with Circular Rings matching Student Portal --}}
        <div class="space-y-3">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Performance Metrics</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Card 1: Avg Quiz Score (Amber / Peach Pastel) --}}
                <div class="bg-[#FFF9EC] dark:bg-[#1c1917] border border-[#FEEFD0] dark:border-[#292524] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group">
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#FDE68A] text-[#B45309] dark:bg-amber-900/60 dark:text-amber-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <x-heroicon-o-academic-cap class="w-4 h-4" />
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#FDE68A]/50 dark:text-amber-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#F59E0B]" stroke-dasharray="{{ (int)($overallAvgScore ?: 80) }}, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#B45309] dark:text-amber-300">
                                {{ $overallAvgScore !== null ? $overallAvgScore.'%' : '—' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ $overallAvgScore !== null ? $overallAvgScore.'%' : '—' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#B45309] dark:group-hover:text-amber-300 transition-colors">
                            Avg Quiz Score &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            Across all graded attempts
                        </div>
                    </div>
                </div>

                {{-- Card 2: Assessment Turnaround (Sky Pastel) --}}
                <div class="bg-[#F0F9FF] dark:bg-[#0c1f2d] border border-[#E0F2FE] dark:border-[#0f3b56] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group">
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#BAE6FD] text-[#0284C7] dark:bg-sky-900/60 dark:text-sky-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <x-heroicon-o-clock class="w-4 h-4" />
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#BAE6FD]/50 dark:text-sky-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#0284C7]" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#0284C7] dark:text-sky-300">
                                {{ $turnaround['assessments']['count'] ?? 0 }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ $turnaround['assessments']['label'] ?? '—' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#0284C7] dark:group-hover:text-sky-300 transition-colors">
                            Assessment Turnaround &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            {{ $turnaround['assessments']['count'] ?? 0 }} graded assessments
                        </div>
                    </div>
                </div>

                {{-- Card 3: Assignment Turnaround (Purple Pastel) --}}
                <div class="bg-[#F5F3FF] dark:bg-[#181126] border border-[#EDE9FE] dark:border-[#311f4a] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group">
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#DDD6FE] text-[#7C3AED] dark:bg-purple-900/60 dark:text-purple-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <x-heroicon-o-document-text class="w-4 h-4" />
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#DDD6FE]/50 dark:text-purple-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#7C3AED]" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#7C3AED] dark:text-purple-300">
                                {{ $turnaround['assignments']['count'] ?? 0 }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ $turnaround['assignments']['label'] ?? '—' }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#7C3AED] dark:group-hover:text-purple-300 transition-colors">
                            Assignment Turnaround &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            {{ $turnaround['assignments']['count'] ?? 0 }} graded assignments
                        </div>
                    </div>
                </div>

                {{-- Card 4: At-Risk Learners (Rose / Pink Pastel) --}}
                <div class="bg-[#FFF0F3] dark:bg-[#201316] border border-[#FDDDE3] dark:border-[#351920] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group">
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#FECDD3] text-[#E11D48] dark:bg-rose-900/60 dark:text-rose-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#FECDD3]/50 dark:text-rose-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#E11D48]" stroke-dasharray="{{ count($atRiskStudents) > 0 ? '75' : '0' }}, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#E11D48] dark:text-rose-300">
                                {{ count($atRiskStudents) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ sprintf('%02d', count($atRiskStudents)) }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#E11D48] dark:group-hover:text-rose-300 transition-colors">
                            At-Risk Learners &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            Inactive 14+ days
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- 2-Column Core Analytics Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column: Course Completion & Quiz Performance (8 Cols) --}}
            <div class="lg:col-span-8 space-y-6">
                
                {{-- Course Completion Rates Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] p-5 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#233842] pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                Course Completion Rates
                            </h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                Proportion of students who passed all required curriculum checkpoints.
                            </p>
                        </div>
                    </div>

                    @if (count($completionRows) === 0)
                        <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs font-semibold">
                            No courses assigned yet.
                        </div>
                    @else
                        <div class="space-y-4 pt-1">
                            @foreach ($completionRows as $row)
                                <div class="p-3.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-[#233842] space-y-2">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <div>
                                            <span class="font-extrabold text-sm text-slate-900 dark:text-white">
                                                {{ $row['course'] }}
                                            </span>
                                            <span class="ml-2 text-[11px] font-bold text-slate-400">
                                                {{ $row['code'] }} &bull; {{ $row['active_quizzes'] }} active quiz{{ $row['active_quizzes'] === 1 ? '' : 'zes' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-[#7C3AED] dark:text-purple-400">
                                                {{ $row['completed'] }}/{{ $row['enrolled'] }} Learners
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300">
                                                {{ $row['percentage'] }}%
                                            </span>
                                        </div>
                                    </div>

                                    <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-gradient-to-r from-[#7C3AED] to-indigo-600 rounded-full transition-all duration-500" 
                                            style="width: {{ $row['percentage'] }}%;"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Quiz Scores Breakdown Table Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-[#233842]">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                            Quiz Performance Matrix
                        </h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                            Detailed averages, attempt counts, and pass rates for each active quiz.
                        </p>
                    </div>

                    @if (count($quizScoreRows) === 0)
                        <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs font-semibold">
                            No completed quiz attempts yet.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold border-b border-slate-100 dark:border-[#233842]">
                                    <tr>
                                        <th class="py-3 px-4">Quiz Title</th>
                                        <th class="py-3 px-4">Course</th>
                                        <th class="py-3 px-4 text-center">Attempts</th>
                                        <th class="py-3 px-4 text-center">Avg Score</th>
                                        <th class="py-3 px-4 text-right">Pass Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-[#233842] font-medium">
                                    @foreach ($quizScoreRows as $row)
                                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                                {{ $row['quiz'] }}
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                                {{ $row['course'] }}
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-semibold">
                                                {{ $row['attempts'] }}
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-black text-slate-900 dark:text-white">
                                                {{ $row['avg_percentage'] }}%
                                            </td>
                                            <td class="py-3.5 px-4 text-right">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-black {{ $row['pass_rate'] >= 70 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : ($row['pass_rate'] >= 40 ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300') }}">
                                                    {{ $row['pass_rate'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: At-Risk Students & Turnaround (4 Cols) --}}
            <div class="lg:col-span-4 space-y-6">
                
                {{-- At-Risk Learners Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] p-5 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#233842] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ count($atRiskStudents) > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">
                                At-Risk Learners
                            </h2>
                        </div>
                        <span class="text-xs font-extrabold px-2 py-0.5 rounded-full {{ count($atRiskStudents) > 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' }}">
                            {{ count($atRiskStudents) }} Inactive
                        </span>
                    </div>

                    @if (count($atRiskStudents) === 0)
                        <div class="py-6 text-center text-slate-500 dark:text-slate-400 space-y-2">
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center">
                                <x-heroicon-o-check-badge class="w-6 h-6" />
                            </div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">All students are actively engaged!</p>
                            <p class="text-[11px]">No inactive students detected in the last 14 days.</p>
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($atRiskStudents as $student)
                                <div class="p-3 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 space-y-1.5">
                                    <div class="flex items-center justify-between gap-1">
                                        <h5 class="font-extrabold text-xs text-slate-900 dark:text-white truncate">
                                            {{ $student['name'] }}
                                        </h5>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $student['days_inactive'] === null || $student['days_inactive'] >= 30 ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300' }}">
                                            {{ $student['days_inactive'] === null ? 'Never active' : $student['days_inactive'].'d inactive' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 truncate">
                                        {{ $student['courses'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Turnaround Velocity Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] p-5 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="border-b border-slate-100 dark:border-[#233842] pb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">
                            Feedback Velocity
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Average duration from learner turn-in to instructor feedback.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/60 text-center space-y-1">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Assessments</span>
                            <span class="text-base font-black text-[#7C3AED] dark:text-purple-400 block">
                                {{ $turnaround['assessments']['label'] ?? '—' }}
                            </span>
                            <span class="text-[10px] text-slate-400 block">
                                {{ $turnaround['assessments']['count'] ?? 0 }} graded
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/60 text-center space-y-1">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Assignments</span>
                            <span class="text-base font-black text-indigo-600 dark:text-indigo-400 block">
                                {{ $turnaround['assignments']['label'] ?? '—' }}
                            </span>
                            <span class="text-[10px] text-slate-400 block">
                                {{ $turnaround['assignments']['count'] ?? 0 }} graded
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
