<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Top Hero Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 p-6 text-white shadow-lg">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30 rounded-full">
                            Official Reporting Engine
                        </span>
                    </div>
                    <h2 class="text-xl md:text-2xl font-black tracking-tight text-white">
                        Academic & Performance Report Center
                    </h2>
                    <p class="text-xs md:text-sm text-slate-300 mt-1 max-w-2xl">
                        Generate structured, print-ready PDF reports with embedded metrics, attendance logs, assignment grades, and full <strong>Quiz Answer Sheets</strong> showing every question, choice, student response, and explanation.
                    </p>
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        {{-- Navigation Tabs --}}
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
            <button
                type="button"
                wire:click="$set('activeTab', 'student')"
                class="px-4 py-2 text-xs font-bold rounded-full transition-all flex items-center gap-2 {{ $activeTab === 'student' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
            >
                <x-heroicon-o-academic-cap class="w-4 h-4" />
                Student Dossier & Answer Sheets
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'course')"
                class="px-4 py-2 text-xs font-bold rounded-full transition-all flex items-center gap-2 {{ $activeTab === 'course' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
            >
                <x-heroicon-o-chart-bar-square class="w-4 h-4" />
                Course & Cohort Analytics
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'student_directory')"
                class="px-4 py-2 text-xs font-bold rounded-full transition-all flex items-center gap-2 {{ $activeTab === 'student_directory' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
            >
                <x-heroicon-o-users class="w-4 h-4" />
                Student Roster Directory
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'course_directory')"
                class="px-4 py-2 text-xs font-bold rounded-full transition-all flex items-center gap-2 {{ $activeTab === 'course_directory' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}"
            >
                <x-heroicon-o-book-open class="w-4 h-4" />
                Course Catalog Directory
            </button>
        </div>

        {{-- TAB 1: STUDENT REPORT GENERATOR --}}
        @if ($activeTab === 'student')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Generator Controls --}}
                <div class="lg:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <x-heroicon-o-adjustments-horizontal class="w-4 h-4 text-teal-600" />
                        Report Parameters
                    </h3>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Select Student <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="selectedStudentId" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">-- Choose a Student --</option>
                            @foreach ($this->students as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Filter by Course (Optional)
                        </label>
                        <select wire:model.live="selectedCourseId" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">-- All Enrolled Courses (Full Transcript) --</option>
                            @foreach ($this->courses as $c)
                                <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="includeAnswerSheets" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <span>Include Full <strong>Quiz Answer Sheets</strong></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="includeAttendanceLog" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <span>Include Complete <strong>Attendance Log</strong></span>
                        </label>
                    </div>

                    <div class="pt-4 flex flex-col gap-2">
                        @if ($selectedStudentId)
                            <a
                                href="{{ route('reports.student', ['student' => $selectedStudentId, 'course_id' => $selectedCourseId, 'include_answer_sheets' => $includeAnswerSheets ? 1 : 0, 'include_attendance_log' => $includeAttendanceLog ? 1 : 0, 'stream' => 0]) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-xs py-2.5 px-4 rounded-full shadow-md transition"
                            >
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                Download Student PDF Report
                            </a>
                            <a
                                href="{{ route('reports.student', ['student' => $selectedStudentId, 'course_id' => $selectedCourseId, 'include_answer_sheets' => $includeAnswerSheets ? 1 : 0, 'include_attendance_log' => $includeAttendanceLog ? 1 : 0, 'stream' => 1]) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-full border border-slate-300 dark:border-slate-700 transition"
                            >
                                <x-heroicon-o-eye class="w-4 h-4" />
                                Open Print / PDF Preview
                            </a>
                        @else
                            <button disabled class="w-full bg-slate-200 text-slate-400 text-xs font-bold py-2.5 px-4 rounded-full cursor-not-allowed">
                                Select a Student to Generate
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Selected Student Summary Preview --}}
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    @php
                        $targetStudent = $selectedStudentId ? $this->students->firstWhere('id', $selectedStudentId) : null;
                    @endphp

                    @if ($targetStudent)
                        <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                            <div>
                                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                    {{ $targetStudent->track ?? 'Beginner' }} Track
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $targetStudent->name }}</h3>
                                <p class="text-xs text-slate-500">{{ $targetStudent->email }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-400">Joined</span>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $targetStudent->created_at ? $targetStudent->created_at->format('M d, Y') : '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-teal-600">{{ $targetStudent->enrollments->count() }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Enrolled Courses</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-emerald-600">{{ $targetStudent->enrollments->whereNotNull('completed_at')->count() }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Completed</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-purple-600">{{ number_format($targetStudent->lifetime_xp ?? 0) }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Lifetime XP</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-amber-600">🔥 {{ $targetStudent->current_streak ?? 0 }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Streak (Days)</div>
                            </div>
                        </div>

                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                            Enrolled Courses Included in Report:
                        </h4>
                        <div class="space-y-2">
                            @forelse ($targetStudent->enrollments as $enr)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                                    <div>
                                        <strong class="text-xs text-slate-800 dark:text-slate-200">{{ $enr->course?->title ?? 'Course' }}</strong>
                                        <span class="text-[11px] text-slate-500 ml-1">({{ $enr->course?->code ?? '-' }})</span>
                                    </div>
                                    <div>
                                        @if ($enr->completed_at)
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                Completed
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">
                                                In Progress
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic">No courses currently enrolled.</p>
                            @endforelse
                        </div>
                    @else
                        <div class="text-center py-12 text-slate-400">
                            <x-heroicon-o-academic-cap class="w-12 h-12 mx-auto mb-2 opacity-40" />
                            <p class="text-xs">Select a student from the left panel to preview details and download report.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- TAB 2: COURSE & COHORT REPORT GENERATOR --}}
        @if ($activeTab === 'course')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Parameters --}}
                <div class="lg:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-4 h-4 text-teal-600" />
                        Course Analytics Parameters
                    </h3>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Select Course <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="selectedCourseId" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">-- Choose a Course --</option>
                            @foreach ($this->courses as $c)
                                <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Select Intake / Cohort (Optional)
                        </label>
                        <select wire:model.live="selectedIntakeId" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">-- All Cohorts (Global Overview) --</option>
                            @foreach ($this->intakes as $itk)
                                <option value="{{ $itk->id }}">{{ $itk->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 flex flex-col gap-2">
                        @if ($selectedCourseId)
                            <a
                                href="{{ route('reports.course', ['course' => $selectedCourseId, 'intake_id' => $selectedIntakeId, 'stream' => 0]) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold text-xs py-2.5 px-4 rounded-full shadow-md transition"
                            >
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                Download Course Analytics PDF
                            </a>
                            <a
                                href="{{ route('reports.course', ['course' => $selectedCourseId, 'intake_id' => $selectedIntakeId, 'stream' => 1]) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-full border border-slate-300 dark:border-slate-700 transition"
                            >
                                <x-heroicon-o-eye class="w-4 h-4" />
                                Open Print / PDF Preview
                            </a>
                        @else
                            <button disabled class="w-full bg-slate-200 text-slate-400 text-xs font-bold py-2.5 px-4 rounded-full cursor-not-allowed">
                                Select a Course to Generate
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Course Summary Preview --}}
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    @php
                        $targetCourse = $selectedCourseId ? $this->courses->firstWhere('id', $selectedCourseId) : null;
                    @endphp

                    @if ($targetCourse)
                        <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                            <div>
                                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ $targetCourse->code }}
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $targetCourse->title }}</h3>
                                <p class="text-xs text-slate-500">{{ $targetCourse->timeline ?: 'Self-paced' }} • {{ $targetCourse->instructors->pluck('name')->implode(', ') ?: 'Thinker HUB Faculty' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $targetCourse->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $targetCourse->is_active ? 'Active' : 'Archived' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-teal-600">{{ $targetCourse->enrollments->count() }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Enrolled Students</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-emerald-600">{{ $targetCourse->enrollments->whereNotNull('completed_at')->count() }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Graduated</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-purple-600">{{ $targetCourse->intakes->count() }}</div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Intake Cohorts</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 text-center">
                                <div class="text-base font-extrabold text-blue-600">
                                    {{ $targetCourse->enrollments->count() > 0 ? round(($targetCourse->enrollments->whereNotNull('completed_at')->count() / $targetCourse->enrollments->count()) * 100) : 0 }}%
                                </div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase">Completion Rate</div>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500">
                            The generated report includes aggregate session attendance rates, assignment score distributions (A, B, C, F breakdown), formal assessment pass marks, quiz accuracy metrics, and the full student cohort ranking table.
                        </p>
                    @else
                        <div class="text-center py-12 text-slate-400">
                            <x-heroicon-o-chart-pie class="w-12 h-12 mx-auto mb-2 opacity-40" />
                            <p class="text-xs">Select a course from the left panel to preview analytics and download report.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- TAB 3: STUDENT DIRECTORY (QUICK 1-CLICK ACTIONS) --}}
        @if ($activeTab === 'student_directory')
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Student Academic Performance Directory</h3>
                        <p class="text-xs text-slate-500">Quickly generate or preview individual student transcripts and quiz answer sheets.</p>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by student name or email..."
                            class="text-xs rounded-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 w-full sm:w-64"
                        >
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 uppercase bg-slate-50 dark:bg-slate-800/50">
                                <th class="py-2.5 px-3">Student</th>
                                <th class="py-2.5 px-3">Track</th>
                                <th class="py-2.5 px-3 text-center">Courses</th>
                                <th class="py-2.5 px-3 text-center">Completed</th>
                                <th class="py-2.5 px-3 text-right">PDF Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($this->students as $st)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $st->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $st->email }}</div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ $st->track ?? 'Beginner' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                        {{ $st->enrollments->count() }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                            {{ $st->enrollments->whereNotNull('completed_at')->count() }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right space-x-1">
                                        <a
                                            href="{{ route('reports.student', ['student' => $st->id, 'include_answer_sheets' => 1, 'include_attendance_log' => 1, 'stream' => 0]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] rounded-full shadow-sm"
                                        >
                                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                            PDF
                                        </a>
                                        <a
                                            href="{{ route('reports.student', ['student' => $st->id, 'include_answer_sheets' => 1, 'include_attendance_log' => 1, 'stream' => 1]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-bold text-[11px] rounded-full border border-slate-300 dark:border-slate-700"
                                        >
                                            <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                            Preview
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 italic">No students matching criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- TAB 4: COURSE DIRECTORY --}}
        @if ($activeTab === 'course_directory')
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Course Analytics Catalog</h3>
                        <p class="text-xs text-slate-500">Download executive reports for each curriculum and track cohort progress.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 uppercase bg-slate-50 dark:bg-slate-800/50">
                                <th class="py-2.5 px-3">Course</th>
                                <th class="py-2.5 px-3">Instructors</th>
                                <th class="py-2.5 px-3 text-center">Enrolled</th>
                                <th class="py-2.5 px-3 text-center">Completion Rate</th>
                                <th class="py-2.5 px-3 text-right">PDF Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($this->courses as $c)
                                @php
                                    $enrCount = $c->enrollments->count();
                                    $compCount = $c->enrollments->whereNotNull('completed_at')->count();
                                    $rate = $enrCount > 0 ? round(($compCount / $enrCount) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $c->title }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $c->code }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                                        {{ $c->instructors->pluck('name')->implode(', ') ?: 'Faculty' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                        {{ $enrCount }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $rate >= 70 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $rate }}% ({{ $compCount }})
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right space-x-1">
                                        <a
                                            href="{{ route('reports.course', ['course' => $c->id, 'stream' => 0]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] rounded-full shadow-sm"
                                        >
                                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                            Analytics PDF
                                        </a>
                                        <a
                                            href="{{ route('reports.course', ['course' => $c->id, 'stream' => 1]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-bold text-[11px] rounded-full border border-slate-300 dark:border-slate-700"
                                        >
                                            <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                            Preview
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 italic">No courses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
