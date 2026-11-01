@php
    $currentUser = auth()->user();
    if (! $currentUser || (! $currentUser->isInstructor() && ! $currentUser->isAdmin())) {
        return;
    }

    $reminderService = app(\App\Services\InstructorReminderService::class);
    $reminders = $reminderService->getRemindersData($currentUser);
    $shouldShowAuto = $reminderService->shouldShowPopup($currentUser, $reminders);

    // If auto popup is eligible, mark it as shown in the session now so subsequent page
    // navigations or refreshes during this same login do not pop up again.
    if ($shouldShowAuto) {
        $reminderService->markPopupShownForSession();
    }

    // If just_logged_in_instructor flag was set in session, clear it now that the page is rendering
    if (session()->has('just_logged_in_instructor')) {
        session()->forget('just_logged_in_instructor');
    }

    $todayStr = \Illuminate\Support\Carbon::today()->toDateString();
@endphp

<style>
    .hub-rem-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(10, 22, 28, 0.72);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 9998;
    }
    .hub-rem-wrap {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 0.5rem;
        padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0px));
        box-sizing: border-box;
    }
    @media (min-width: 640px) {
        .hub-rem-wrap {
            align-items: center;
            padding: 1.25rem;
        }
    }
    .hub-rem-dialog {
        position: relative;
        width: 100%;
        max-width: 40rem;
        max-height: calc(100dvh - 1rem);
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        box-shadow: 0 24px 48px -12px rgba(10, 25, 31, 0.45);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }
    @media (min-width: 640px) {
        .hub-rem-dialog {
            max-height: 86dvh;
            border-radius: 24px;
        }
    }
    .dark .hub-rem-dialog {
        background: #111b21;
        border-color: #233842;
    }
    .hub-rem-header {
        position: relative;
        padding: 1rem 1.1rem 0.9rem;
        background: linear-gradient(135deg, #0a2d27 0%, #0f5248 58%, #0f172a 100%);
        color: #ffffff;
        overflow: hidden;
        flex-shrink: 0;
    }
    @media (min-width: 640px) {
        .hub-rem-header {
            padding: 1.35rem 1.5rem 1.15rem;
        }
    }
    .hub-rem-toprow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .hub-rem-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .hub-rem-close {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .hub-rem-close:hover {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }
    .hub-rem-title {
        font-size: 1.05rem;
        font-weight: 900;
        color: #ffffff;
        margin: 0;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }
    @media (min-width: 640px) {
        .hub-rem-title {
            font-size: 1.25rem;
        }
    }
    .hub-rem-subtitle {
        font-size: 0.76rem;
        color: rgba(226, 252, 245, 0.88);
        margin: 0.25rem 0 0 0;
        line-height: 1.4;
    }
    .hub-rem-stats {
        margin-top: 0.75rem;
        padding-top: 0.7rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.4rem;
    }
    @media (min-width: 640px) {
        .hub-rem-stats {
            gap: 0.6rem;
        }
    }
    .hub-rem-stat-box {
        padding: 0.4rem 0.45rem;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 0.15rem;
        min-width: 0;
    }
    @media (min-width: 640px) {
        .hub-rem-stat-box {
            flex-direction: row;
            justify-content: flex-start;
            text-align: left;
            padding: 0.5rem 0.65rem;
            gap: 0.55rem;
        }
    }
    .hub-rem-stat-num {
        width: 1.55rem;
        height: 1.55rem;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 900;
        flex-shrink: 0;
    }
    @media (min-width: 640px) {
        .hub-rem-stat-num {
            width: 1.85rem;
            height: 1.85rem;
            font-size: 0.8rem;
        }
    }
    .hub-rem-stat-label {
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: rgba(204, 251, 241, 0.85);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .hub-rem-stat-sub {
        display: none;
        font-size: 0.7rem;
        font-weight: 800;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (min-width: 640px) {
        .hub-rem-stat-sub {
            display: block;
        }
    }
    .hub-rem-tabs {
        padding: 0.55rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        flex-shrink: 0;
    }
    .hub-rem-tabs::-webkit-scrollbar {
        display: none;
    }
    .dark .hub-rem-tabs {
        background: #0d171d;
        border-bottom-color: #1f2c34;
    }
    .hub-rem-tab {
        padding: 0.38rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        background: transparent;
        color: #475569;
    }
    .dark .hub-rem-tab {
        color: #94a3b8;
    }
    .hub-rem-tab.is-active {
        background: #0d9488;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(13, 148, 136, 0.28);
    }
    .hub-rem-body {
        padding: 0.85rem 1rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    @media (min-width: 640px) {
        .hub-rem-body {
            padding: 1.1rem 1.5rem;
        }
    }
    .hub-rem-card {
        padding: 0.75rem 0.85rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        transition: border-color 0.15s ease;
    }
    @media (min-width: 640px) {
        .hub-rem-card {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem;
        }
    }
    .dark .hub-rem-card {
        background: #16242d;
        border-color: #233842;
    }
    .hub-rem-card-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .hub-rem-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.42rem 0.85rem;
        border-radius: 10px;
        font-size: 0.73rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: transform 0.12s ease, opacity 0.12s ease;
    }
    .hub-rem-action-btn:active {
        transform: scale(0.97);
    }
    .hub-rem-footer {
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        flex-shrink: 0;
    }
    @media (min-width: 640px) {
        .hub-rem-footer {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.5rem;
        }
    }
    .dark .hub-rem-footer {
        background: #0d171d;
        border-top-color: #1f2c34;
    }
    .hub-rem-footer-btns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        width: 100%;
    }
    @media (min-width: 640px) {
        .hub-rem-footer-btns {
            display: flex;
            width: auto;
        }
    }
</style>

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
            const sessionKey = 'thinker_instructor_reminder_shown_' + @js($currentUser->id) + '_' + @js(session()->getId());
            const alreadyShownThisSession = sessionStorage.getItem(sessionKey);
            const autoTrigger = @js($shouldShowAuto);

            if (autoTrigger && !alreadyShownThisSession && dismissedDate !== this.todayDateStr) {
                sessionStorage.setItem(sessionKey, '1');
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
                this.sendDismissRequest('session');
            }
        },

        dismissForSession() {
            this.isOpen = false;
            if (this.dontShowToday) {
                this.dismissForDay();
            } else {
                this.sendDismissRequest('session');
            }
        },

        dismissForDay() {
            localStorage.setItem('thinker_instructor_reminder_dismissed_date', this.todayDateStr);
            this.sendDismissRequest('day');
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
        class="hub-rem-backdrop"
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
        class="hub-rem-wrap"
    >
        <div class="hub-rem-dialog" x-on:click.stop>
            {{-- Header --}}
            <div class="hub-rem-header">
                <div class="hub-rem-toprow">
                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                        <span
                            class="hub-rem-status-pill"
                            style="{{ $reminders['has_overdue']
                                ? 'background:rgba(244,63,94,0.28);color:#fecdd3;border:1px solid rgba(251,113,133,0.45);'
                                : ($reminders['total_actionable'] > 0
                                    ? 'background:rgba(245,158,11,0.28);color:#fde68a;border:1px solid rgba(251,191,36,0.45);'
                                    : 'background:rgba(16,185,129,0.28);color:#a7f3d0;border:1px solid rgba(52,211,153,0.45);') }}"
                        >
                            <span style="width:6px;height:6px;border-radius:999px;background:currentColor;"></span>
                            <span>{{ $reminders['has_overdue'] ? 'Action Overdue' : ($reminders['total_actionable'] > 0 ? 'Action Required' : 'All Clear') }}</span>
                        </span>
                        <span style="font-size:0.7rem;color:rgba(204,251,241,0.8);font-weight:600;">
                            {{ $reminders['today_date_formatted'] }}
                        </span>
                    </div>

                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="hub-rem-close"
                        title="Close reminder"
                        aria-label="Close reminder"
                    >
                        <svg style="width:1.1rem;height:1.1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <h2 class="hub-rem-title">
                    Welcome, {{ $reminders['instructor_name'] }}!
                </h2>

                <p class="hub-rem-subtitle">
                    @if ($reminders['total_actionable'] > 0)
                        You have <strong style="color:#fde047;text-decoration:underline;">{{ $reminders['total_actionable'] }} item{{ $reminders['total_actionable'] === 1 ? '' : 's' }}</strong> waiting for your sign-off today.
                    @else
                        All class registers are marked and assignments are graded.
                    @endif
                </p>

                {{-- Compact 4-Column Metric Strip --}}
                <div class="hub-rem-stats">
                    <div class="hub-rem-stat-box">
                        <div class="hub-rem-stat-num" style="background:rgba(20,184,166,0.25);color:#5eead4;">
                            {{ $reminders['unmarked_count'] }}
                        </div>
                        <div style="min-width:0;">
                            <div class="hub-rem-stat-label">Registers</div>
                            <div class="hub-rem-stat-sub">Unmarked</div>
                        </div>
                    </div>

                    <div class="hub-rem-stat-box">
                        <div class="hub-rem-stat-num" style="background:rgba(245,158,11,0.25);color:#fcd34d;">
                            {{ $reminders['pending_assignments_count'] }}
                        </div>
                        <div style="min-width:0;">
                            <div class="hub-rem-stat-label">Assign.</div>
                            <div class="hub-rem-stat-sub">Pending</div>
                        </div>
                    </div>

                    <div class="hub-rem-stat-box">
                        <div class="hub-rem-stat-num" style="background:rgba(99,102,241,0.28);color:#a5b4fc;">
                            {{ $reminders['pending_assessments_count'] }}
                        </div>
                        <div style="min-width:0;">
                            <div class="hub-rem-stat-label">Assess.</div>
                            <div class="hub-rem-stat-sub">To Review</div>
                        </div>
                    </div>

                    <div class="hub-rem-stat-box">
                        <div class="hub-rem-stat-num" style="background:rgba(14,165,233,0.25);color:#7dd3fc;">
                            {{ $reminders['today_sessions_count'] }}
                        </div>
                        <div style="min-width:0;">
                            <div class="hub-rem-stat-label">Classes</div>
                            <div class="hub-rem-stat-sub">Today</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Tabs Bar --}}
            <div class="hub-rem-tabs">
                <button
                    type="button"
                    x-on:click="activeTab = 'all'"
                    class="hub-rem-tab"
                    :class="activeTab === 'all' ? 'is-active' : ''"
                >
                    <span>All Tasks</span>
                    <span style="padding:0.05rem 0.42rem;border-radius:999px;font-size:0.65rem;background:rgba(15,23,42,0.12);">
                        {{ $reminders['total_actionable'] }}
                    </span>
                </button>

                <button
                    type="button"
                    x-on:click="activeTab = 'register'"
                    class="hub-rem-tab"
                    :class="activeTab === 'register' ? 'is-active' : ''"
                >
                    <span>Registers</span>
                    @if ($reminders['unmarked_count'] > 0)
                        <span style="padding:0.05rem 0.42rem;border-radius:999px;font-size:0.65rem;background:rgba(225,29,72,0.16);color:inherit;">
                            {{ $reminders['unmarked_count'] }}
                        </span>
                    @endif
                </button>

                <button
                    type="button"
                    x-on:click="activeTab = 'assignments'"
                    class="hub-rem-tab"
                    :class="activeTab === 'assignments' ? 'is-active' : ''"
                >
                    <span>Assignments</span>
                    @if ($reminders['pending_assignments_count'] > 0)
                        <span style="padding:0.05rem 0.42rem;border-radius:999px;font-size:0.65rem;background:rgba(245,158,11,0.2);color:inherit;">
                            {{ $reminders['pending_assignments_count'] }}
                        </span>
                    @endif
                </button>

                @if ($reminders['pending_assessments_count'] > 0)
                    <button
                        type="button"
                        x-on:click="activeTab = 'assessments'"
                        class="hub-rem-tab"
                        :class="activeTab === 'assessments' ? 'is-active' : ''"
                    >
                        <span>Assessments</span>
                        <span style="padding:0.05rem 0.42rem;border-radius:999px;font-size:0.65rem;background:rgba(99,102,241,0.2);color:inherit;">
                            {{ $reminders['pending_assessments_count'] }}
                        </span>
                    </button>
                @endif

                @if ($reminders['today_sessions_count'] > 0)
                    <button
                        type="button"
                        x-on:click="activeTab = 'today'"
                        class="hub-rem-tab"
                        :class="activeTab === 'today' ? 'is-active' : ''"
                    >
                        <span>Today's Classes</span>
                        <span style="padding:0.05rem 0.42rem;border-radius:999px;font-size:0.65rem;background:rgba(14,165,233,0.2);color:inherit;">
                            {{ $reminders['today_sessions_count'] }}
                        </span>
                    </button>
                @endif
            </div>

            {{-- Scrollable List of Actionable Reminders --}}
            <div class="hub-rem-body">

                {{-- All Caught Up Empty State --}}
                @if ($reminders['total_actionable'] === 0 && $reminders['today_sessions_count'] === 0)
                    <div style="padding:1.75rem 1rem;text-align:center;">
                        <div style="width:3.25rem;height:3.25rem;margin:0 auto 0.65rem;border-radius:16px;background:#ecfdf5;color:#059669;display:inline-flex;align-items:center;justify-content:center;">
                            <svg style="width:1.75rem;height:1.75rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 style="font-size:0.95rem;font-weight:800;margin:0;color:inherit;">
                            Zero Pending Tasks!
                        </h3>
                        <p style="font-size:0.76rem;color:#64748b;max-width:22rem;margin:0.35rem auto 0;line-height:1.45;">
                            Every attendance register has been finalized and all assignment submissions have been graded.
                        </p>
                    </div>
                @endif

                {{-- SECTION 1: UNMARKED ATTENDANCE REGISTERS --}}
                @if ($reminders['unmarked_count'] > 0)
                    <div x-show="activeTab === 'all' || activeTab === 'register'" style="display:flex;flex-direction:column;gap:0.5rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                            <div style="display:flex;align-items:center;gap:0.4rem;">
                                <span style="width:7px;height:7px;border-radius:999px;background:#e11d48;"></span>
                                <span style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;color:#e11d48;">
                                    Register Marking ({{ $reminders['unmarked_count'] }})
                                </span>
                            </div>
                            <a href="{{ $reminders['attendance_register_url'] }}" style="font-size:0.7rem;font-weight:700;color:#0d9488;text-decoration:none;">
                                Open Sheet &rarr;
                            </a>
                        </div>

                        @foreach ($reminders['unmarked_sessions'] as $session)
                            <div class="hub-rem-card">
                                <div style="min-width:0;flex:1;">
                                    <div style="display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;margin-bottom:0.25rem;">
                                        @if ($session['is_overdue'])
                                            <span style="padding:0.12rem 0.45rem;border-radius:6px;font-size:0.62rem;font-weight:800;background:#ffe4e6;color:#be123c;">
                                                OVERDUE
                                            </span>
                                        @else
                                            <span style="padding:0.12rem 0.45rem;border-radius:6px;font-size:0.62rem;font-weight:800;background:#d1fae5;color:#047857;">
                                                TODAY
                                            </span>
                                        @endif
                                        <span style="font-size:0.7rem;font-weight:800;color:#334155;" class="dark:text-slate-200 truncate">
                                            {{ $session['course_title'] }}
                                        </span>
                                        <span style="font-size:0.65rem;color:#94a3b8;">
                                            &bull; {{ $session['intake_name'] }}
                                        </span>
                                    </div>

                                    <h4 style="font-size:0.8rem;font-weight:800;margin:0;line-height:1.3;" class="text-slate-900 dark:text-white truncate">
                                        {{ $session['title'] }}
                                    </h4>
                                </div>

                                <div class="hub-rem-card-bottom">
                                    <div style="font-size:0.68rem;color:#64748b;display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                        <span>{{ $session['date_display'] }}</span>
                                        <span>&bull; {{ $session['time_display'] }}</span>
                                        @if ($session['student_name'])
                                            <span style="color:#0d9488;font-weight:700;">1-on-1: {{ $session['student_name'] }}</span>
                                        @endif
                                    </div>

                                    <a
                                        href="{{ $session['action_url'] }}"
                                        class="hub-rem-action-btn"
                                        style="background:#0d9488;color:#ffffff;"
                                    >
                                        <span>Mark Register</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- SECTION 2: PENDING ASSIGNMENT SUBMISSIONS --}}
                @if ($reminders['pending_assignments_count'] > 0)
                    <div x-show="activeTab === 'all' || activeTab === 'assignments'" style="display:flex;flex-direction:column;gap:0.5rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                            <div style="display:flex;align-items:center;gap:0.4rem;">
                                <span style="width:7px;height:7px;border-radius:999px;background:#f59e0b;"></span>
                                <span style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;color:#d97706;">
                                    Assignments to Grade ({{ $reminders['pending_assignments_count'] }})
                                </span>
                            </div>
                            <a href="{{ $reminders['assignments_url'] }}" style="font-size:0.7rem;font-weight:700;color:#0d9488;text-decoration:none;">
                                Review Queue &rarr;
                            </a>
                        </div>

                        @foreach ($reminders['pending_assignments'] as $assign)
                            <div class="hub-rem-card">
                                <div style="display:flex;align-items:center;gap:0.65rem;min-width:0;flex:1;">
                                    <div style="width:2.1rem;height:2.1rem;border-radius:999px;background:#fef3c7;color:#92400e;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:0.72rem;flex-shrink:0;overflow:hidden;">
                                        @if ($assign['student_photo'])
                                            <img src="{{ $assign['student_photo'] }}" alt="{{ $assign['student_name'] }}" style="width:100%;height:100%;object-fit:cover;" />
                                        @else
                                            {{ $assign['student_initials'] }}
                                        @endif
                                    </div>

                                    <div style="min-width:0;flex:1;">
                                        <div style="display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;">
                                            <span style="font-size:0.78rem;font-weight:800;" class="text-slate-900 dark:text-white truncate">
                                                {{ $assign['student_name'] }}
                                            </span>
                                            <span style="padding:0.08rem 0.4rem;border-radius:4px;font-size:0.62rem;font-weight:700;background:#fef3c7;color:#b45309;">
                                                {{ $assign['status'] }}
                                            </span>
                                        </div>
                                        <div style="font-size:0.72rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $assign['assignment_title'] }} &bull; {{ $assign['course_title'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="hub-rem-card-bottom">
                                    <span style="font-size:0.66rem;color:#94a3b8;">
                                        Submitted {{ $assign['submitted_at_diff'] }}
                                    </span>

                                    <a
                                        href="{{ $assign['grade_url'] }}"
                                        class="hub-rem-action-btn"
                                        style="background:#f59e0b;color:#0f172a;"
                                    >
                                        <span>Grade</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- SECTION 3: PENDING ASSESSMENT SUBMISSIONS --}}
                @if ($reminders['pending_assessments_count'] > 0)
                    <div x-show="activeTab === 'all' || activeTab === 'assessments'" style="display:flex;flex-direction:column;gap:0.5rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                            <div style="display:flex;align-items:center;gap:0.4rem;">
                                <span style="width:7px;height:7px;border-radius:999px;background:#6366f1;"></span>
                                <span style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;color:#4f46e5;">
                                    Assessments ({{ $reminders['pending_assessments_count'] }})
                                </span>
                            </div>
                            <a href="{{ $reminders['assessments_url'] }}" style="font-size:0.7rem;font-weight:700;color:#0d9488;text-decoration:none;">
                                View All &rarr;
                            </a>
                        </div>

                        @foreach ($reminders['pending_assessments'] as $assess)
                            <div class="hub-rem-card">
                                <div style="display:flex;align-items:center;gap:0.65rem;min-width:0;flex:1;">
                                    <div style="width:2.1rem;height:2.1rem;border-radius:999px;background:#e0e7ff;color:#3730a3;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:0.72rem;flex-shrink:0;overflow:hidden;">
                                        @if ($assess['student_photo'])
                                            <img src="{{ $assess['student_photo'] }}" alt="{{ $assess['student_name'] }}" style="width:100%;height:100%;object-fit:cover;" />
                                        @else
                                            {{ $assess['student_initials'] }}
                                        @endif
                                    </div>

                                    <div style="min-width:0;flex:1;">
                                        <div style="font-size:0.78rem;font-weight:800;" class="text-slate-900 dark:text-white truncate">
                                            {{ $assess['student_name'] }}
                                        </div>
                                        <div style="font-size:0.72rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $assess['assessment_title'] }} &bull; {{ $assess['course_title'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="hub-rem-card-bottom">
                                    <span style="font-size:0.66rem;color:#94a3b8;">
                                        Submitted {{ $assess['submitted_at_diff'] }}
                                    </span>

                                    <a
                                        href="{{ $assess['grade_url'] }}"
                                        class="hub-rem-action-btn"
                                        style="background:#4f46e5;color:#ffffff;"
                                    >
                                        <span>Review</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- SECTION 4: TODAY'S LIVE SESSIONS --}}
                @if ($reminders['today_sessions_count'] > 0)
                    <div x-show="activeTab === 'all' || activeTab === 'today'" style="display:flex;flex-direction:column;gap:0.5rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                            <div style="display:flex;align-items:center;gap:0.4rem;">
                                <span style="width:7px;height:7px;border-radius:999px;background:#0ea5e9;"></span>
                                <span style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;color:#0284c7;">
                                    Today's Classes ({{ $reminders['today_sessions_count'] }})
                                </span>
                            </div>
                            <a href="{{ $reminders['schedule_url'] }}" style="font-size:0.7rem;font-weight:700;color:#0d9488;text-decoration:none;">
                                Timetable &rarr;
                            </a>
                        </div>

                        @foreach ($reminders['today_sessions'] as $session)
                            <div class="hub-rem-card">
                                <div style="min-width:0;flex:1;">
                                    <div style="display:flex;align-items:center;gap:0.4rem;margin-bottom:0.2rem;">
                                        <span style="padding:0.1rem 0.45rem;border-radius:5px;font-size:0.64rem;font-weight:800;background:#e0f2fe;color:#0369a1;">
                                            {{ $session['time_display'] }}
                                        </span>
                                        <span style="font-size:0.72rem;font-weight:700;color:#475569;" class="dark:text-slate-300 truncate">
                                            {{ $session['course_title'] }}
                                        </span>
                                    </div>
                                    <h4 style="font-size:0.8rem;font-weight:800;margin:0;" class="text-slate-900 dark:text-white truncate">
                                        {{ $session['title'] }}
                                    </h4>
                                </div>

                                <div style="flex-shrink:0;">
                                    <a
                                        href="{{ $session['action_url'] }}"
                                        class="hub-rem-action-btn"
                                        style="background:#e0f2fe;color:#0369a1;width:100%;"
                                    >
                                        <span>Open Register</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Footer Area --}}
            <div class="hub-rem-footer">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;user-select:none;">
                    <input
                        type="checkbox"
                        x-model="dontShowToday"
                        style="width:1rem;height:1rem;border-radius:4px;accent-color:#0d9488;cursor:pointer;"
                    />
                    <span style="font-size:0.74rem;color:#64748b;font-weight:600;">
                        Don't show again today
                    </span>
                </label>

                <div class="hub-rem-footer-btns">
                    <button
                        type="button"
                        x-on:click="dismissForSession()"
                        class="hub-btn hub-btn-secondary"
                        style="padding:0.48rem 0.9rem;font-size:0.75rem;justify-content:center;"
                    >
                        Remind Later
                    </button>

                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="hub-btn hub-btn-primary"
                        style="padding:0.48rem 1rem;font-size:0.75rem;justify-content:center;"
                    >
                        Go to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
