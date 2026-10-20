@php
    $currentUser = auth()->user();
    if (! $currentUser || (! $currentUser->isInstructor() && ! $currentUser->isAdmin())) {
        return;
    }

    $reminderService = app(\App\Services\InstructorReminderService::class);
    $reminders = $reminderService->getRemindersData($currentUser);
    $shouldShowAuto = $reminderService->shouldShowPopup($currentUser, $reminders);

    // If just_logged_in_instructor flag was set in session, clear it now that the page is rendering
    if (session()->has('just_logged_in_instructor')) {
        session()->forget('just_logged_in_instructor');
    }

    $todayStr = \Illuminate\Support\Carbon::today()->toDateString();
@endphp

<div
    x-data="{
        isOpen: false,
        activeTab: 'all',
        dontShowToday: false,
        todayDateStr: @js($todayStr),
        reminders: @js($reminders),
        dismissUrl: @js(route('teach.dismiss-reminder')),
        csrfToken: @js(csrf_token()),

        init() {
            const dismissedDate = localStorage.getItem('thinker_instructor_reminder_dismissed_date');
            const autoTrigger = @js($shouldShowAuto);

            if (autoTrigger && dismissedDate !== this.todayDateStr) {
                // Short organic delay so the dashboard loads smoothly before the reminder appears
                setTimeout(() => {
                    this.isOpen = true;
                }, 450);
            }
        },

        openModal() {
            this.isOpen = true;
        },

        closeModal() {
            this.isOpen = false;
            if (this.dontShowToday) {
                this.dismissForDay();
            } else {
                this.dismissForSession();
            }
        },

        dismissForDay() {
            localStorage.setItem('thinker_instructor_reminder_dismissed_date', this.todayDateStr);
            this.sendDismissRequest('day');
            this.isOpen = false;
        },

        dismissForSession() {
            this.sendDismissRequest('session');
            this.isOpen = false;
        },

        sendDismissRequest(duration) {
            fetch(this.dismissUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ duration: duration })
            }).catch(e => console.debug('Dismiss error:', e));
        }
    }"
    x-on:open-instructor-reminders.window="openModal()"
    x-on:keydown.escape.window="closeModal()"
    class="relative z-[9999]"
    x-cloak
>
    {{-- Full-Screen Backdrop Overlay --}}
    <div
        x-show="isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[9998]"
        x-on:click="closeModal()"
    ></div>

    {{-- Modal Dialog --}}
    <div
        x-show="isOpen"
        x-transition:enter="ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-3 sm:p-4 md:p-6"
    >
        <div
            class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh]"
            x-on:click.stop
        >
            {{-- Header with Ambient Glow & Gradient --}}
            <div class="relative px-5 sm:px-6 pt-5 pb-4 bg-gradient-to-r from-teal-700 via-teal-800 to-slate-900 text-white overflow-hidden">
                {{-- Decorative Ambient Circles --}}
                <div class="absolute -right-8 -top-8 w-40 h-40 bg-teal-500/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute right-32 -bottom-10 w-32 h-32 bg-emerald-500/15 rounded-full blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider backdrop-blur-md {{ $reminders['has_overdue'] ? 'bg-rose-500/30 text-rose-200 border border-rose-400/40' : ($reminders['total_actionable'] > 0 ? 'bg-amber-500/30 text-amber-200 border border-amber-400/40' : 'bg-emerald-500/30 text-emerald-200 border border-emerald-400/40') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $reminders['has_overdue'] ? 'bg-rose-400 animate-pulse' : ($reminders['total_actionable'] > 0 ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400') }}"></span>
                                {{ $reminders['has_overdue'] ? 'Action Overdue' : ($reminders['total_actionable'] > 0 ? 'Action Required' : 'All Clear') }}
                            </span>
                            <span class="text-xs text-teal-200/80 font-medium">
                                {{ $reminders['today_date_formatted'] }}
                            </span>
                        </div>

                        <h2 class="text-lg sm:text-xl font-black text-white tracking-tight flex items-center gap-2">
                            <span>Welcome, {{ $reminders['instructor_name'] }}!</span>
                            <span class="text-base font-normal text-teal-200/90">📋 Instructor Dispatch</span>
                        </h2>

                        <p class="text-xs text-teal-100/90 leading-relaxed font-normal">
                            @if ($reminders['total_actionable'] > 0)
                                You have <span class="font-bold underline decoration-amber-300">{{ $reminders['total_actionable'] }} item{{ $reminders['total_actionable'] === 1 ? '' : 's' }}</span> waiting for your sign-off today. Keep your cohorts moving forward!
                            @else
                                You are completely caught up! All class registers are marked and assignments are graded.
                            @endif
                        </p>
                    </div>

                    {{-- Close Button --}}
                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="text-teal-200/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all duration-150 cursor-pointer"
                        title="Close reminder"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Metric Pill Badges Row --}}
                <div class="mt-4 pt-3 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div class="px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-teal-500/20 text-teal-300 flex items-center justify-center font-black text-xs">
                            {{ $reminders['unmarked_count'] }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] text-teal-200/70 uppercase font-bold tracking-wider truncate">Registers</div>
                            <div class="text-[11px] font-bold text-white truncate">Unmarked</div>
                        </div>
                    </div>

                    <div class="px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-300 flex items-center justify-center font-black text-xs">
                            {{ $reminders['pending_assignments_count'] }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] text-teal-200/70 uppercase font-bold tracking-wider truncate">Assignments</div>
                            <div class="text-[11px] font-bold text-white truncate">Pending</div>
                        </div>
                    </div>

                    <div class="px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-black text-xs">
                            {{ $reminders['pending_assessments_count'] }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] text-teal-200/70 uppercase font-bold tracking-wider truncate">Assessments</div>
                            <div class="text-[11px] font-bold text-white truncate">To Review</div>
                        </div>
                    </div>

                    <div class="px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-300 flex items-center justify-center font-black text-xs">
                            {{ $reminders['today_sessions_count'] }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] text-teal-200/70 uppercase font-bold tracking-wider truncate">Classes</div>
                            <div class="text-[11px] font-bold text-white truncate">Today</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Tabs Bar --}}
            <div class="px-5 sm:px-6 pt-3 pb-2 bg-slate-50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 flex items-center gap-1.5 overflow-x-auto text-xs font-bold scrollbar-none">
                <button
                    type="button"
                    x-on:click="activeTab = 'all'"
                    class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                    :class="activeTab === 'all' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800'"
                >
                    <span>All Tasks</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'all' ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'">
                        {{ $reminders['total_actionable'] }}
                    </span>
                </button>

                <button
                    type="button"
                    x-on:click="activeTab = 'register'"
                    class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                    :class="activeTab === 'register' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Register Marking</span>
                    @if ($reminders['unmarked_count'] > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'register' ? 'bg-white/25 text-white' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'">
                            {{ $reminders['unmarked_count'] }}
                        </span>
                    @endif
                </button>

                <button
                    type="button"
                    x-on:click="activeTab = 'assignments'"
                    class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                    :class="activeTab === 'assignments' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Assignments</span>
                    @if ($reminders['pending_assignments_count'] > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'assignments' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'">
                            {{ $reminders['pending_assignments_count'] }}
                        </span>
                    @endif
                </button>

                @if ($reminders['pending_assessments_count'] > 0)
                    <button
                        type="button"
                        x-on:click="activeTab = 'assessments'"
                        class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'assessments' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800'"
                    >
                        <span>Assessments</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'assessments' ? 'bg-white/25 text-white' : 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300'">
                            {{ $reminders['pending_assessments_count'] }}
                        </span>
                    </button>
                @endif

                @if ($reminders['today_sessions_count'] > 0)
                    <button
                        type="button"
                        x-on:click="activeTab = 'today'"
                        class="px-3 py-1.5 rounded-lg transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                        :class="activeTab === 'today' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800'"
                    >
                        <span>Today's Classes</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'today' ? 'bg-white/25 text-white' : 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300'">
                            {{ $reminders['today_sessions_count'] }}
                        </span>
                    </button>
                @endif
            </div>

            {{-- Scrollable List of Actionable Reminders --}}
            <div class="px-5 sm:px-6 py-4 overflow-y-auto space-y-3 flex-1 max-h-[50vh] divide-y divide-slate-100 dark:divide-slate-800/80">

                {{-- All Caught Up Empty State --}}
                @if ($reminders['total_actionable'] === 0 && $reminders['today_sessions_count'] === 0)
                    <div class="py-8 text-center space-y-3">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-inner">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-black text-slate-800 dark:text-white">
                            Zero Pending Tasks!
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                            Every attendance register has been finalized and all assignment submissions have been graded. Your students are in great hands!
                        </p>
                        <div class="pt-2">
                            <button
                                type="button"
                                x-on:click="closeModal()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 transition cursor-pointer"
                            >
                                Continue to Dashboard
                            </button>
                        </div>
                    </div>
                @endif

                {{-- SECTION 1: UNMARKED ATTENDANCE REGISTERS --}}
                @if ($reminders['unmarked_count'] > 0)
                    <div
                        x-show="activeTab === 'all' || activeTab === 'register'"
                        class="space-y-2.5 pt-2 first:pt-0"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                                    Register Marking Needed ({{ $reminders['unmarked_count'] }})
                                </span>
                            </div>
                            <a
                                href="{{ $reminders['attendance_register_url'] }}"
                                class="text-[11px] font-bold text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-0.5"
                            >
                                Open Register Sheet →
                            </a>
                        </div>

                        <div class="space-y-2">
                            @foreach ($reminders['unmarked_sessions'] as $session)
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 hover:border-teal-500/50 transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                    <div class="space-y-1 min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if ($session['is_overdue'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                                    OVERDUE REGISTER
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                    TODAY'S CLASS
                                                </span>
                                            @endif

                                            <span class="text-[11px] font-extrabold text-slate-800 dark:text-slate-200 truncate">
                                                {{ $session['course_title'] }}
                                            </span>
                                            <span class="text-[10px] font-semibold text-slate-400">
                                                • {{ $session['intake_name'] }}
                                            </span>
                                        </div>

                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-1">
                                            {{ $session['title'] }}
                                        </h4>

                                        <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                            <span class="inline-flex items-center gap-1 font-medium">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $session['date_display'] }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 font-medium">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $session['time_display'] }}
                                            </span>
                                            @if ($session['student_name'])
                                                <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400">
                                                    1-on-1: {{ $session['student_name'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex-shrink-0">
                                        <a
                                            href="{{ $session['action_url'] }}"
                                            class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto px-3.5 py-1.5 rounded-xl text-xs font-black text-white bg-teal-600 hover:bg-teal-700 active:scale-95 transition-all shadow-xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Mark Register</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- SECTION 2: PENDING ASSIGNMENT SUBMISSIONS --}}
                @if ($reminders['pending_assignments_count'] > 0)
                    <div
                        x-show="activeTab === 'all' || activeTab === 'assignments'"
                        class="space-y-2.5 pt-3 first:pt-0"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                    Assignments Waiting for Review ({{ $reminders['pending_assignments_count'] }})
                                </span>
                            </div>
                            <a
                                href="{{ $reminders['assignments_url'] }}"
                                class="text-[11px] font-bold text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-0.5"
                            >
                                View Review Queue →
                            </a>
                        </div>

                        <div class="space-y-2">
                            @foreach ($reminders['pending_assignments'] as $assign)
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 hover:border-amber-500/50 transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        {{-- Student Avatar --}}
                                        <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden border border-amber-200 dark:border-amber-800">
                                            @if ($assign['student_photo'])
                                                <img src="{{ $assign['student_photo'] }}" alt="{{ $assign['student_name'] }}" class="w-full h-full object-cover" />
                                            @else
                                                {{ $assign['student_initials'] }}
                                            @endif
                                        </div>

                                        <div class="space-y-0.5 min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-extrabold text-slate-900 dark:text-white truncate">
                                                    {{ $assign['student_name'] }}
                                                </span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                                    {{ $assign['status'] }}
                                                </span>
                                            </div>

                                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                                {{ $assign['assignment_title'] }}
                                                <span class="font-normal text-slate-400">• {{ $assign['course_title'] }}</span>
                                            </h4>

                                            <div class="text-[10px] text-slate-400 flex items-center gap-1">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Submitted {{ $assign['submitted_at_diff'] }} ({{ $assign['submitted_at_formatted'] }})
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex-shrink-0">
                                        <a
                                            href="{{ $assign['grade_url'] }}"
                                            class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto px-3.5 py-1.5 rounded-xl text-xs font-black text-slate-900 dark:text-white bg-amber-400 hover:bg-amber-300 dark:bg-amber-500 dark:hover:bg-amber-400 active:scale-95 transition-all shadow-xs"
                                        >
                                            <span>Grade Submission</span>
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- SECTION 3: PENDING ASSESSMENT SUBMISSIONS --}}
                @if ($reminders['pending_assessments_count'] > 0)
                    <div
                        x-show="activeTab === 'all' || activeTab === 'assessments'"
                        class="space-y-2.5 pt-3 first:pt-0"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                    Assessment Submissions ({{ $reminders['pending_assessments_count'] }})
                                </span>
                            </div>
                            <a
                                href="{{ $reminders['assessments_url'] }}"
                                class="text-[11px] font-bold text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-0.5"
                            >
                                All Assessments →
                            </a>
                        </div>

                        <div class="space-y-2">
                            @foreach ($reminders['pending_assessments'] as $assess)
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 hover:border-indigo-500/50 transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 flex items-center justify-center font-bold text-xs flex-shrink-0 overflow-hidden">
                                            @if ($assess['student_photo'])
                                                <img src="{{ $assess['student_photo'] }}" alt="{{ $assess['student_name'] }}" class="w-full h-full object-cover" />
                                            @else
                                                {{ $assess['student_initials'] }}
                                            @endif
                                        </div>

                                        <div class="space-y-0.5 min-w-0 flex-1">
                                            <div class="text-xs font-extrabold text-slate-900 dark:text-white truncate">
                                                {{ $assess['student_name'] }}
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">
                                                {{ $assess['assessment_title'] }}
                                                <span class="font-normal text-slate-400">• {{ $assess['course_title'] }}</span>
                                            </h4>
                                            <div class="text-[10px] text-slate-400">
                                                Submitted {{ $assess['submitted_at_diff'] }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex-shrink-0">
                                        <a
                                            href="{{ $assess['grade_url'] }}"
                                            class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto px-3.5 py-1.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-xs"
                                        >
                                            <span>Review Assessment</span>
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- SECTION 4: TODAY'S LIVE SESSIONS --}}
                @if ($reminders['today_sessions_count'] > 0)
                    <div
                        x-show="activeTab === 'all' || activeTab === 'today'"
                        class="space-y-2.5 pt-3 first:pt-0"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-sky-600 dark:text-sky-400">
                                    Classes on Schedule Today ({{ $reminders['today_sessions_count'] }})
                                </span>
                            </div>
                            <a
                                href="{{ $reminders['schedule_url'] }}"
                                class="text-[11px] font-bold text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-0.5"
                            >
                                Open Timetable →
                            </a>
                        </div>

                        <div class="space-y-2">
                            @foreach ($reminders['today_sessions'] as $session)
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 hover:border-sky-500/50 transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                    <div class="space-y-1 min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                                                {{ $session['time_display'] }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                                {{ $session['course_title'] }}
                                            </span>
                                        </div>

                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-1">
                                            {{ $session['title'] }}
                                        </h4>
                                    </div>

                                    <div class="flex-shrink-0">
                                        <a
                                            href="{{ $session['action_url'] }}"
                                            class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-sky-700 dark:text-sky-300 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/60 dark:hover:bg-sky-900/60 transition"
                                        >
                                            <span>Open Register</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Area --}}
            <div class="px-5 sm:px-6 py-3.5 bg-slate-50 dark:bg-slate-950/80 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                {{-- Don't show again today toggle --}}
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        x-model="dontShowToday"
                        class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 dark:border-slate-700 dark:bg-slate-800"
                    />
                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                        Don't show this popup again today
                    </span>
                </label>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button
                        type="button"
                        x-on:click="dismissForSession()"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800 transition cursor-pointer"
                    >
                        Remind Me Later
                    </button>

                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="px-4 py-1.5 rounded-xl text-xs font-black text-white bg-slate-900 hover:bg-slate-800 dark:bg-teal-600 dark:hover:bg-teal-500 shadow-sm transition-all cursor-pointer"
                    >
                        Go to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
