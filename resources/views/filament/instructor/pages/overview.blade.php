<x-filament-panels::page>
    <div class="space-y-6">
        {{-- 1. Top Contextual Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Welcome, {{ auth()->user()?->first_name ?: 'Instructor' }}! 👨‍🏫
                    </h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                        Instructor Workspace
                    </span>
                </div>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    You have <strong class="text-slate-900 dark:text-white">{{ count($courses) }} active {{ count($courses) === 1 ? 'class' : 'classes' }}</strong> and <strong class="text-rose-600 dark:text-rose-400">{{ $pendingSubmissionsCount }} submission{{ $pendingSubmissionsCount === 1 ? '' : 's' }}</strong> waiting for review.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a 
                    href="{{ \App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl() }}" 
                    class="inline-flex items-center justify-center px-4 py-2 rounded-full text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:text-teal-300 transition-colors"
                >
                    Review Submissions &rarr;
                </a>
                <a 
                    href="{{ route('filament.instructor.pages.schedule') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 rounded-full text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 shadow-xs transition-colors"
                >
                    Schedule Class &rarr;
                </a>
            </div>
        </div>

        {{-- 2. Four KPI Metric StatCards with Sparklines --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-edtech.stat-card
                title="Total Classes"
                :value="count($courses)"
                delta="+100%"
                deltaType="positive"
                subtitle="Assigned courses"
                color="teal"
                :sparkline="[10, 20, 30, 45, 50, 65, 80]"
            />

            <x-edtech.stat-card
                title="Total Students"
                :value="$totalStudents"
                delta="+25%"
                deltaType="positive"
                subtitle="Across active cohorts"
                color="sky"
                :sparkline="[15, 28, 40, 52, 68, 85, 95]"
            />

            <x-edtech.stat-card
                title="Pending Reviews"
                :value="$pendingSubmissionsCount"
                :delta="$pendingSubmissionsCount > 0 ? 'Needs Attention' : 'All Checked'"
                :deltaType="$pendingSubmissionsCount > 0 ? 'negative' : 'positive'"
                subtitle="Submissions queued"
                color="rose"
                :href="\App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl()"
                :sparkline="[35, 25, 40, 20, 30, 15, 10]"
            />

            <x-edtech.stat-card
                title="Upcoming Sessions"
                :value="$upcomingSessionCount"
                delta="This Week"
                deltaType="neutral"
                subtitle="Scheduled live classes"
                color="indigo"
                :href="route('filament.instructor.pages.schedule')"
                :sparkline="[5, 15, 25, 40, 60, 75, 90]"
            />
        </div>

        {{-- 3. Core 2-Column Dashboard Grid Area --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- LEFT COLUMN: 8 Columns (Classrooms & Cohort Management Table) --}}
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                                My Classrooms & Cohorts
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Manage students, active intakes, and deliverables for your assigned classes.
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50/75 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th class="py-3 px-4">Course / Classroom</th>
                                    <th class="py-3 px-4">Current Intake</th>
                                    <th class="py-3 px-4">Enrolled Students</th>
                                    <th class="py-3 px-4">Category</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                                @forelse ($courses as $course)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm">
                                                {{ $course['title'] }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                {{ $course['code'] }}
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300">
                                                {{ $course['intake'] }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2">
                                                <x-edtech.avatar-group :users="$course['student_list']" size="sm" :limit="3" />
                                                <span class="text-xs text-slate-500 font-bold">({{ $course['students'] }})</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $course['category'] }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            @if ($course['is_active'])
                                                <x-edtech.badge-pill variant="mint" size="sm" :dot="true">Active Class</x-edtech.badge-pill>
                                            @else
                                                <x-edtech.badge-pill variant="slate" size="sm">Archived</x-edtech.badge-pill>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a 
                                                    href="{{ \App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl() }}" 
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:text-teal-300 transition-colors"
                                                >
                                                    Grade &rarr;
                                                </a>
                                                <a 
                                                    href="{{ route('filament.instructor.pages.schedule') }}" 
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 transition-colors"
                                                >
                                                    Schedule
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400">
                                            No active courses assigned yet. Contact administrator to assign courses.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Submissions Quick Review Queue Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Submission Queue
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Student assignments and assessments ready for scoring and feedback.
                            </p>
                        </div>
                        <a href="{{ \App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl() }}" class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline">
                            Open Review Desk &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <a href="{{ \App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl() }}" class="flex items-center justify-between p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 hover:border-teal-400 transition-all group">
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-teal-600 transition-colors">
                                    Assignment Submissions
                                </span>
                                <p class="text-xs text-slate-500 mt-0.5">Filter by course or submission status</p>
                            </div>
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold text-xs">
                                &rarr;
                            </span>
                        </a>

                        <a href="{{ \App\Filament\Instructor\Resources\AssessmentSubmissionResource\AssessmentSubmissionResource::getUrl() }}" class="flex items-center justify-between p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 hover:border-teal-400 transition-all group">
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-teal-600 transition-colors">
                                    Assessment Evaluations
                                </span>
                                <p class="text-xs text-slate-500 mt-0.5">Grade test papers and project deliverables</p>
                            </div>
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-800 font-bold text-xs">
                                &rarr;
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: 4 Columns (Session Calendar & Live Schedule Rail) --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Calendar Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ \Carbon\Carbon::createFromDate($calendarYear, $calendarMonth, 1)->format('F Y') }}
                        </h3>
                        <div class="flex items-center gap-1">
                            <button type="button" wire:click="previousMonth" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" wire:click="nextMonth" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Day headers --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        @foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $dow)
                            <div>{{ $dow }}</div>
                        @endforeach
                    </div>

                    {{-- Month Days Grid --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold">
                        @foreach ($calendarWeeks as $week)
                            @foreach ($week as $dayCell)
                                <div
                                    class="h-8 w-8 mx-auto rounded-full flex flex-col items-center justify-center relative transition-all {{ $dayCell['is_today'] ? 'bg-teal-600 text-white font-extrabold shadow-xs' : ($dayCell['in_month'] ? 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' : 'text-slate-300 dark:text-slate-600 opacity-40') }}"
                                    title="{{ count($dayCell['sessions']) > 0 ? count($dayCell['sessions']) . ' session(s)' : '' }}"
                                >
                                    <span>{{ $dayCell['date'] }}</span>
                                    @if (count($dayCell['sessions']) > 0 && !$dayCell['is_today'])
                                        <span class="w-1 h-1 rounded-full bg-teal-500 absolute bottom-1"></span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                {{-- Live Sessions Timetable --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Upcoming Live Classes
                        </h3>
                        <a href="{{ route('filament.instructor.pages.schedule') }}" class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline">
                            Timetable &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($upcomingSessions as $session)
                            <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $session['is_today'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300' : 'bg-slate-200/70 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                                        {{ $session['is_today'] ? 'Today • ' . $session['time'] : $session['date'] . ' • ' . $session['time'] }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-teal-600 capitalize">
                                        {{ $session['type'] === 'one_on_one' ? '1-on-1' : 'Group Class' }}
                                    </span>
                                </div>

                                <div class="text-xs font-bold text-slate-900 dark:text-white line-clamp-1">
                                    {{ $session['title'] }}
                                </div>
                                <div class="text-[11px] text-slate-500 flex items-center justify-between">
                                    <span>{{ $session['course'] }}</span>
                                    @if ($session['student_name'])
                                        <span class="font-medium text-slate-600 dark:text-slate-400">Student: {{ $session['student_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-slate-400">
                                No upcoming classes scheduled.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
