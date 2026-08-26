<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- Header Card --}}
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800">
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
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-100 transition"
                >
                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-4 h-4" />
                    <span>Gradebook & Roster</span>
                </a>
            </div>
        </div>

        {{-- Headline Stat Cards with Sparklines --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-edtech.stat-card
                title="Avg Quiz Score"
                :value="$overallAvgScore !== null ? $overallAvgScore.'%' : '—'"
                delta="All Attempts"
                deltaType="positive"
                subtitle="Class average across quizzes"
                color="teal"
                :sparkline="[65, 70, 75, 72, 80, 85, (int)($overallAvgScore ?: 80)]"
            />

            <x-edtech.stat-card
                title="Assessment Turnaround"
                :value="$turnaround['assessments']['label'] ?? '—'"
                delta="{{ $turnaround['assessments']['count'] ?? 0 }} Graded"
                deltaType="neutral"
                subtitle="Avg grading duration"
                color="sky"
                :sparkline="[12, 10, 8, 9, 7, 6, 5]"
            />

            <x-edtech.stat-card
                title="Assignment Turnaround"
                :value="$turnaround['assignments']['label'] ?? '—'"
                delta="{{ $turnaround['assignments']['count'] ?? 0 }} Graded"
                deltaType="neutral"
                subtitle="Avg feedback duration"
                color="indigo"
                :sparkline="[24, 20, 18, 15, 12, 10, 8]"
            />

            <x-edtech.stat-card
                title="At-Risk Learners"
                :value="count($atRiskStudents)"
                :delta="count($atRiskStudents) > 0 ? 'Needs Attention' : 'All Engaged'"
                :deltaType="count($atRiskStudents) > 0 ? 'negative' : 'positive'"
                subtitle="Inactive 14+ days"
                color="{{ count($atRiskStudents) > 0 ? 'rose' : 'emerald' }}"
                :sparkline="[0, 1, 2, 1, 0, count($atRiskStudents)]"
            />
        </div>

        {{-- 2-Column Core Analytics Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column: Course Completion & Quiz Performance (8 Cols) --}}
            <div class="lg:col-span-8 space-y-6">
                
                {{-- Course Completion Rates --}}
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Course Completion Rates
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
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
                                <div class="p-3.5 rounded-xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-2">
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
                                            <span class="text-xs font-black text-teal-600 dark:text-teal-400">
                                                {{ $row['completed'] }}/{{ $row['enrolled'] }} Learners
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300">
                                                {{ $row['percentage'] }}%
                                            </span>
                                        </div>
                                    </div>

                                    <div class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-gradient-to-r from-teal-500 to-emerald-500 rounded-full transition-all duration-500" 
                                            style="width: {{ $row['percentage'] }}%;"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Quiz Scores Breakdown Table --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Quiz Performance Matrix
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
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
                                <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4">Quiz Title</th>
                                        <th class="py-3 px-4">Course</th>
                                        <th class="py-3 px-4 text-center">Attempts</th>
                                        <th class="py-3 px-4 text-center">Avg Score</th>
                                        <th class="py-3 px-4 text-right">Pass Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
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
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ count($atRiskStudents) > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                            <h4 class="font-black text-xs uppercase tracking-wider text-slate-900 dark:text-white">
                                At-Risk Learners
                            </h4>
                        </div>
                        <span class="text-xs font-extrabold px-2 py-0.5 rounded-full {{ count($atRiskStudents) > 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' }}">
                            {{ count($atRiskStudents) }} Inactive
                        </span>
                    </div>

                    @if (count($atRiskStudents) === 0)
                        <div class="py-6 text-center text-slate-500 dark:text-slate-400 space-y-2">
                            <span class="text-3xl block">🌟</span>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">All students are actively engaged!</p>
                            <p class="text-[11px]">No inactive students detected in the last 14 days.</p>
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($atRiskStudents as $student)
                                <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 space-y-1.5">
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

                {{-- Turnaround Metric Breakdown Card --}}
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h4 class="font-black text-xs uppercase tracking-wider text-slate-900 dark:text-white">
                            Feedback Velocity
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Average duration from learner turn-in to instructor feedback.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/60 text-center space-y-1">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Assessments</span>
                            <span class="text-base font-black text-teal-600 dark:text-teal-400 block">
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
