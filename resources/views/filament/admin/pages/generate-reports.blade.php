<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- 1. TOP CONTROL & HERO BAR (Student Portal EdTech Style) --}}
        <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 flex items-center justify-center shadow-2xs border border-purple-100 dark:border-purple-900/60">
                    <x-heroicon-o-document-chart-bar class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider bg-purple-50 text-[#7C3AED] dark:bg-purple-950/40 dark:text-purple-300 border border-purple-100 dark:border-purple-900/50 rounded-lg">
                            Academic & Analytics Engine
                        </span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-0.5">
                        Performance & Academic Reports Center
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Generate official, print-ready PDF reports with embedded metrics, attendance logs, and complete Quiz Answer Sheets.
                    </p>
                </div>
            </div>

            {{-- Segmented Range Tabs (EdTech Tab Style) --}}
            <div class="p-1.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-[#233842] flex flex-wrap items-center gap-1">
                <button
                    type="button"
                    wire:click="$set('activeTab', 'student')"
                    class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all flex items-center gap-2 {{ $activeTab === 'student' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}"
                >
                    <x-heroicon-o-academic-cap class="w-4 h-4" />
                    Student Dossier
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'course')"
                    class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all flex items-center gap-2 {{ $activeTab === 'course' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}"
                >
                    <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                    Course Analytics
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'student_directory')"
                    class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all flex items-center gap-2 {{ $activeTab === 'student_directory' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}"
                >
                    <x-heroicon-o-users class="w-4 h-4" />
                    Student Roster
                </button>
                <button
                    type="button"
                    wire:click="$set('activeTab', 'course_directory')"
                    class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all flex items-center gap-2 {{ $activeTab === 'course_directory' ? 'bg-[#7C3AED] text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/60' }}"
                >
                    <x-heroicon-o-book-open class="w-4 h-4" />
                    Course Catalog
                </button>
            </div>
        </div>

        {{-- TAB 1: STUDENT REPORT GENERATOR --}}
        @if ($activeTab === 'student')
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {{-- Generator Controls (4 cols) --}}
                <div class="lg:col-span-4 edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-[#233842] shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-[#233842]">
                        <div class="w-7 h-7 rounded-lg bg-purple-50 text-[#7C3AED] dark:bg-purple-950/40 dark:text-purple-300 flex items-center justify-center">
                            <x-heroicon-o-adjustments-horizontal class="w-4 h-4" />
                        </div>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Report Parameters
                        </h3>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Select Student <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="selectedStudentId" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-[#233842] bg-white dark:bg-[#0b151a] text-slate-800 dark:text-slate-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#7C3AED]">
                            <option value="">-- Choose a Student --</option>
                            @foreach ($this->students as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Filter by Course (Optional)
                        </label>
                        <select wire:model.live="selectedCourseId" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-[#233842] bg-white dark:bg-[#0b151a] text-slate-800 dark:text-slate-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#7C3AED]">
                            <option value="">-- All Enrolled Courses (Full Transcript) --</option>
                            @foreach ($this->courses as $c)
                                <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-[#233842] space-y-2.5">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="includeAnswerSheets" class="rounded-lg border-slate-300 dark:border-slate-700 text-[#7C3AED] focus:ring-[#7C3AED]">
                            <span>Include Full <strong>Quiz Answer Sheets</strong></span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="includeAttendanceLog" class="rounded-lg border-slate-300 dark:border-slate-700 text-[#7C3AED] focus:ring-[#7C3AED]">
                            <span>Include Complete <strong>Attendance Log</strong></span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-[#233842] flex flex-col gap-2.5">
                        <button
                            type="button"
                            wire:click="downloadStudentPdf"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-extrabold text-xs shadow-2xs transition-all bg-[#7C3AED] hover:bg-[#6D28D9] text-white"
                        >
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            Download Official PDF Report
                        </button>
                        @if ($selectedStudentId)
                            <a
                                href="{{ route('reports.student', ['student' => $selectedStudentId, 'course_id' => $selectedCourseId, 'include_answer_sheets' => $includeAnswerSheets ? 1 : 0, 'include_attendance_log' => $includeAttendanceLog ? 1 : 0, 'stream' => 1]) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-extrabold text-xs transition-all border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-[#0b151a] text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-[#162933]"
                            >
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                Preview PDF in Browser
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Live Dossier Preview (8 cols) --}}
                <div class="lg:col-span-8 edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-[#233842] shadow-sm">
                    @php
                        $targetStudent = $selectedStudentId ? \App\Models\User::find($selectedStudentId) : null;
                    @endphp

                    @if ($targetStudent)
                        <div class="space-y-6">
                            {{-- Header Student Profile --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 dark:border-[#233842] gap-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/50 text-[#7C3AED] dark:text-purple-300 font-black text-base flex items-center justify-center">
                                        {{ strtoupper(substr($targetStudent->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                                            {{ $targetStudent->name }}
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                            {{ $targetStudent->email }} • Joined {{ $targetStudent->created_at ? $targetStudent->created_at->format('M Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 text-xs font-black rounded-lg bg-purple-50 text-[#7C3AED] dark:bg-purple-950/40 dark:text-purple-300 border border-purple-100 dark:border-purple-900/50">
                                        {{ strtoupper($targetStudent->track ?? 'LEARNER') }} TRACK
                                    </span>
                                </div>
                            </div>

                            {{-- Aggregate Stat Tiles --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">XP Points</div>
                                    <div class="text-base font-black text-slate-800 dark:text-slate-100 mt-0.5">
                                        {{ number_format($targetStudent->lifetime_xp ?? 0) }}
                                    </div>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">Spendable Coins</div>
                                    <div class="text-base font-black text-[#7C3AED] dark:text-purple-300 mt-0.5">
                                        🪙 {{ number_format($targetStudent->spendable_coins ?? 0) }}
                                    </div>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">Streak</div>
                                    <div class="text-base font-black text-orange-600 dark:text-orange-400 mt-0.5">
                                        🔥 {{ $targetStudent->current_streak ?? 0 }} Days
                                    </div>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">Enrolled Courses</div>
                                    <div class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
                                        {{ $targetStudent->enrollments()->count() }}
                                    </div>
                                </div>
                            </div>

                            {{-- Enrolled Courses List --}}
                            <div>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3">
                                    Enrolled Curriculum & Progress
                                </h4>
                                <div class="space-y-2.5">
                                    @forelse ($targetStudent->enrollments as $enr)
                                        <div class="p-3.5 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/50 dark:bg-[#0b151a] flex items-center justify-between">
                                            <div>
                                                <div class="text-xs font-extrabold text-slate-800 dark:text-slate-200">
                                                    {{ $enr->course->title ?? 'Course' }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                                    Code: {{ $enr->course->code ?? 'N/A' }} • Enrolled: {{ $enr->created_at ? $enr->created_at->format('M d, Y') : '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                @if ($enr->completed_at)
                                                    <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/50">
                                                        ✓ Completed ({{ $enr->completed_at->format('M Y') }})
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-100 dark:border-amber-900/50">
                                                        In Progress
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 italic">No courses currently enrolled.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-academic-cap class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200">No Student Selected</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                Choose a student from the parameters menu on the left to preview their academic dossier and generate reports.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- TAB 2: COURSE ANALYTICS GENERATOR --}}
        @if ($activeTab === 'course')
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-4 edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-[#233842] shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-[#233842]">
                        <div class="w-7 h-7 rounded-lg bg-purple-50 text-[#7C3AED] dark:bg-purple-950/40 dark:text-purple-300 flex items-center justify-center">
                            <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                        </div>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Course Report Parameters
                        </h3>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Select Course <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="selectedCourseId" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-[#233842] bg-white dark:bg-[#0b151a] text-slate-800 dark:text-slate-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#7C3AED]">
                            <option value="">-- Choose a Course --</option>
                            @foreach ($this->courses as $c)
                                <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Filter by Cohort / Intake (Optional)
                        </label>
                        <select wire:model.live="selectedIntakeId" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-[#233842] bg-white dark:bg-[#0b151a] text-slate-800 dark:text-slate-100 px-3.5 py-2.5 focus:ring-2 focus:ring-[#7C3AED]">
                            <option value="">-- All Enrolled Cohorts --</option>
                            @foreach ($this->intakes as $intk)
                                <option value="{{ $intk->id }}">{{ $intk->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-[#233842] flex flex-col gap-2.5">
                        <button
                            type="button"
                            wire:click="downloadCoursePdf"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-extrabold text-xs shadow-2xs transition-all bg-[#7C3AED] hover:bg-[#6D28D9] text-white"
                        >
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            Download Executive Analytics PDF
                        </button>
                        @if ($selectedCourseId)
                            <a
                                href="{{ route('reports.course', ['course' => $selectedCourseId, 'intake_id' => $selectedIntakeId, 'stream' => 1]) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-extrabold text-xs transition-all border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-[#0b151a] text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-[#162933]"
                            >
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                Preview PDF in Browser
                            </a>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-8 edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-[#233842] shadow-sm">
                    @php
                        $targetCourse = $selectedCourseId ? \App\Models\Course::with(['enrollments', 'intakes'])->find($selectedCourseId) : null;
                    @endphp

                    @if ($targetCourse)
                        <div class="space-y-6">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-[#233842]">
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                                        {{ $targetCourse->title }}
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                        Code: {{ $targetCourse->code }} • {{ $targetCourse->enrollments()->count() }} Active Learners Enrolled
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-black rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/50">
                                    {{ $targetCourse->is_active ? 'ACTIVE' : 'ARCHIVED' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">Enrolled Students</div>
                                    <div class="text-base font-black text-slate-800 dark:text-slate-100 mt-0.5">
                                        {{ $targetCourse->enrollments()->count() }}
                                    </div>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">Completed</div>
                                    <div class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
                                        {{ $targetCourse->enrollments()->whereNotNull('completed_at')->count() }}
                                    </div>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">Cohorts</div>
                                    <div class="text-base font-black text-[#7C3AED] dark:text-purple-300 mt-0.5">
                                        {{ $targetCourse->intakes()->count() }}
                                    </div>
                                </div>
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b151a] border border-slate-100 dark:border-[#233842] text-center">
                                    <div class="text-xs font-black uppercase text-slate-400 dark:text-slate-500">Sessions</div>
                                    <div class="text-base font-black text-blue-600 dark:text-blue-400 mt-0.5">
                                        {{ $targetCourse->sessions()->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-chart-bar-square class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200">No Course Selected</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                Select a course from the parameters menu to generate executive cohort metrics and analytics.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- TAB 3: STUDENT ROSTER DIRECTORY --}}
        @if ($activeTab === 'student_directory')
            <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            Student Roster & 1-Click PDF Generation
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                            Generate and download official academic dossiers for any learner.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by name or email..."
                            class="text-xs font-semibold rounded-xl border border-slate-200 dark:border-[#233842] bg-white dark:bg-[#0b151a] text-slate-800 dark:text-slate-100 px-3.5 py-2 focus:ring-2 focus:ring-[#7C3AED] w-64"
                        >
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-[#233842] text-slate-400 font-extrabold uppercase text-[10px]">
                                <th class="py-2.5 px-3">Student</th>
                                <th class="py-2.5 px-3">Track</th>
                                <th class="py-2.5 px-3">Enrolled Courses</th>
                                <th class="py-2.5 px-3">XP / Coins</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#233842]">
                            @forelse ($this->students as $st)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-[#162933]/50 transition-colors">
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $st->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-medium">{{ $st->email }}</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-purple-50 text-[#7C3AED] dark:bg-purple-950/40 dark:text-purple-300">
                                            {{ strtoupper($st->track ?? 'LEARNER') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-slate-700 dark:text-slate-300">
                                        {{ $st->enrollments->count() }} Courses
                                    </td>
                                    <td class="py-3 px-3 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ number_format($st->lifetime_xp ?? 0) }} XP • 🪙 {{ number_format($st->spendable_coins ?? 0) }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                type="button"
                                                wire:click="downloadStudentPdf({{ $st->id }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-[#7C3AED] hover:bg-[#6D28D9] text-white font-extrabold text-[11px] inline-flex items-center gap-1 shadow-2xs"
                                                title="Download PDF"
                                            >
                                                <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                                PDF
                                            </button>
                                            <a
                                                href="{{ route('reports.student', ['student' => $st->id, 'stream' => 1]) }}"
                                                target="_blank"
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-[#0b151a] text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-[#162933] font-bold text-[11px] inline-flex items-center gap-1"
                                                title="Preview"
                                            >
                                                <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-400 font-medium italic">
                                        No students matching your search criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- TAB 4: COURSE DIRECTORY --}}
        @if ($activeTab === 'course_directory')
            <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            Course Catalog & Analytics Reports
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                            Generate comprehensive analytics and grade distributions for each course.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($this->courses as $c)
                        <div class="p-4 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/50 dark:bg-[#0b151a] flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-black uppercase text-slate-400">
                                        {{ $c->code ?? 'COURSE' }}
                                    </span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        {{ $c->enrollments->count() }} Enrolled
                                    </span>
                                </div>
                                <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 mt-1">
                                    {{ $c->title }}
                                </h4>
                            </div>

                            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-[#233842]">
                                <button
                                    type="button"
                                    wire:click="downloadCoursePdf({{ $c->id }})"
                                    class="flex-1 px-3 py-1.5 rounded-lg bg-[#7C3AED] hover:bg-[#6D28D9] text-white font-extrabold text-[11px] inline-flex items-center justify-center gap-1.5 shadow-2xs"
                                >
                                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                    Analytics PDF
                                </button>
                                <a
                                    href="{{ route('reports.course', ['course' => $c->id, 'stream' => 1]) }}"
                                    target="_blank"
                                    class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-[#233842] bg-slate-50 dark:bg-[#0b151a] text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-[#162933] font-bold text-[11px] inline-flex items-center justify-center gap-1"
                                >
                                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                    Preview
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
