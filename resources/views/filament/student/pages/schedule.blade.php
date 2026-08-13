<x-filament-panels::page>
    <div class="hub-schedule-workspace">

        {{-- 1. TOP INTERACTIVE TIMETABLE CONTROL BAR --}}
        <header class="hub-schedule-topbar">
            <div class="hub-topbar-left">
                <h2 class="hub-topbar-title">
                    <x-heroicon-o-calendar-days class="hub-topbar-title-icon" />
                    <span>Interactive Timetable</span>
                </h2>
            </div>

            <div class="hub-topbar-right">
                <nav class="hub-segmented-tabs">
                    <button type="button"
                            wire:click="setRangeMode('month')"
                            class="hub-tab {{ $rangeMode === 'month' ? 'is-active' : '' }}">
                        Month
                    </button>
                    <button type="button"
                            wire:click="setRangeMode('week')"
                            class="hub-tab {{ $rangeMode === 'week' ? 'is-active' : '' }}">
                        Week
                    </button>
                    <button type="button"
                            wire:click="setRangeMode('day')"
                            class="hub-tab {{ $rangeMode === 'day' ? 'is-active' : '' }}">
                        Day
                    </button>
                    <button type="button"
                            wire:click="setRangeMode('custom')"
                            class="hub-tab {{ $rangeMode === 'custom' ? 'is-active' : '' }}">
                        Custom
                    </button>
                </nav>

                <div class="hub-nav-cluster">
                    <div class="hub-nav-arrows">
                        <button type="button"
                                wire:click="previousPeriod"
                                class="hub-nav-arrow-btn"
                                title="Previous Period">
                            <x-heroicon-o-chevron-left style="width:0.85rem;height:0.85rem;" />
                        </button>
                        <button type="button"
                                wire:click="goToToday"
                                class="hub-today-btn">
                            Today
                        </button>
                        <button type="button"
                                wire:click="nextPeriod"
                                class="hub-nav-arrow-btn"
                                title="Next Period">
                            <x-heroicon-o-chevron-right style="width:0.85rem;height:0.85rem;" />
                        </button>
                    </div>

                    <h3 class="hub-current-period-title">
                        {{ $periodTitle }}
                    </h3>
                </div>
            </div>
        </header>

        {{-- 2. MAIN 2-COLUMN SCHEDULE GRID --}}
        <div class="hub-schedule-main-grid">

            {{-- LEFT COLUMN: CALENDAR / TIMETABLE PANE --}}
            <section class="hub-schedule-card">
                <div class="hub-pane-header">
                    <div class="hub-pane-title-group">
                        @if ($rangeMode === 'month')
                            <x-heroicon-o-calendar class="hub-pane-icon" />
                            <h3 class="hub-pane-title">Monthly Calendar</h3>
                        @elseif ($rangeMode === 'week')
                            <x-heroicon-o-calendar-days class="hub-pane-icon" />
                            <h3 class="hub-pane-title">Weekly Timetable</h3>
                        @elseif ($rangeMode === 'day')
                            <x-heroicon-o-clock class="hub-pane-icon" />
                            <h3 class="hub-pane-title">Daily Agenda</h3>
                        @else
                            <x-heroicon-o-calendar-days class="hub-pane-icon" />
                            <h3 class="hub-pane-title">Custom Timetable</h3>
                        @endif
                    </div>
                    <span class="hub-pane-hint">Click class for details</span>
                </div>

                {{-- Custom Date Bounds Toolbar (When rangeMode === 'custom') --}}
                @if ($rangeMode === 'custom')
                    <div class="hub-custom-date-bar">
                        <label>
                            <span>From:</span>
                            <input type="date" wire:model.live="customStartDate">
                        </label>
                        <label>
                            <span>To:</span>
                            <input type="date" wire:model.live="customEndDate">
                        </label>
                        <span class="hub-custom-date-hint">Showing dates within your bounds.</span>
                    </div>
                @endif

                {{-- MONTH VIEW --}}
                @if ($rangeMode === 'month')
                    <div class="hub-month-grid">
                        <div class="hub-month-dow-header">
                            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                        </div>

                        <div class="hub-month-body">
                            @foreach ($calendarWeeks as $week)
                                <div class="hub-month-row">
                                    @foreach ($week as $day)
                                        <div class="hub-month-cell {{ $day['is_today'] ? 'is-today' : '' }} {{ ! $day['in_month'] ? 'is-dimmed' : '' }}"
                                             @if (count($day['sessions']) > 0)
                                                 wire:click="openSessionDetails({{ $day['sessions'][0]['id'] }})"
                                                 title="{{ count($day['sessions']) }} session(s) on {{ $day['date_full'] }}"
                                             @endif
                                        >
                                            <div class="hub-month-cell-header">
                                                <span class="hub-month-day-num {{ $day['is_today'] ? 'is-today-badge' : '' }}">{{ $day['date'] }}</span>
                                                @if (count($day['sessions']) > 1)
                                                    <span class="hub-month-more-badge">+{{ count($day['sessions']) }}</span>
                                                @endif
                                            </div>

                                            <div class="hub-month-cell-events">
                                                @foreach ($day['sessions'] as $s)
                                                    <button type="button"
                                                            wire:click.stop="openSessionDetails({{ $s['id'] }})"
                                                            class="hub-month-event-item"
                                                            title="{{ $s['course_code'] ? $s['course_code'].' · ' : '' }}{{ $s['title'] }} ({{ $s['start_time'] }})">
                                                        <span class="hub-event-indicator is-{{ $s['status'] }}"></span>
                                                        <span class="hub-event-time">{{ $s['start_time'] }}</span>
                                                        <span class="hub-event-title">{{ $s['course_code'] ? $s['course_code'].' · ' : '' }}{{ $s['title'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- WEEK VIEW --}}
                @if ($rangeMode === 'week')
                    <div class="hub-week-grid">
                        @foreach ($weekDays as $day)
                            <div class="hub-week-column {{ $day['is_today'] ? 'is-today' : '' }}">
                                <div class="hub-week-column-header">
                                    <span class="hub-week-day-name">{{ $day['day_name'] }}</span>
                                    <span class="hub-week-day-num {{ $day['is_today'] ? 'is-today-pill' : '' }}">{{ $day['date_num'] }}</span>
                                    @if ($day['is_today'])
                                        <span class="hub-week-today-tag">TODAY</span>
                                    @endif
                                </div>

                                <div class="hub-week-column-sessions">
                                    @forelse ($day['sessions'] as $s)
                                        <article wire:click="openSessionDetails({{ $s['id'] }})"
                                                 class="hub-week-session-card is-{{ $s['status'] }}">
                                            <div class="hub-week-session-top">
                                                <span class="hub-week-session-time">{{ $s['start_time'] }}</span>
                                                <span class="hub-status-micro-pill is-{{ $s['status'] }}">
                                                    {{ ucfirst($s['status']) }}
                                                </span>
                                            </div>

                                            <h4 class="hub-week-session-title">
                                                @if ($s['course_code'])
                                                    <span class="hub-code-prefix">{{ $s['course_code'] }}</span> ·
                                                @endif
                                                {{ $s['title'] }}
                                            </h4>

                                            @if (!empty($s['instructor_name']))
                                                <div class="hub-week-session-user">
                                                    <x-heroicon-o-user class="hub-user-icon" />
                                                    <span>{{ $s['instructor_name'] }}</span>
                                                </div>
                                            @endif
                                        </article>
                                    @empty
                                        <div class="hub-week-empty-day">
                                            <span>—</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- DAY VIEW --}}
                @if ($rangeMode === 'day')
                    <div class="hub-day-agenda">
                        <div class="hub-day-agenda-header">
                            <div>
                                <h3 class="hub-day-title">{{ $dayViewData['day_name'] }}, {{ $dayViewData['formatted_date'] }}</h3>
                                <p class="hub-day-subtitle">{{ count($dayViewData['sessions']) }} class session(s) scheduled</p>
                            </div>
                            @if ($dayViewData['is_today'])
                                <span class="hub-today-badge-subtle">Today</span>
                            @endif
                        </div>

                        <div class="hub-day-agenda-list">
                            @forelse ($dayViewData['sessions'] as $s)
                                <article wire:click="openSessionDetails({{ $s['id'] }})"
                                         class="hub-day-session-card is-{{ $s['status'] }}">
                                    <div class="hub-day-time-badge">
                                        <span class="hub-day-time-start">{{ $s['start_time'] }}</span>
                                        <span class="hub-day-time-end">{{ $s['end_time'] }}</span>
                                    </div>

                                    <div class="hub-day-card-body">
                                        <div class="hub-day-card-meta">
                                            @if ($s['course_code'])
                                                <span class="hub-code-badge">{{ $s['course_code'] }}</span>
                                            @endif
                                            <span class="hub-type-badge">{{ $s['type_label'] ?? 'Group' }}</span>
                                            <span class="hub-status-micro-pill is-{{ $s['status'] }}">
                                                <span class="hub-status-dot is-{{ $s['status'] }}"></span>
                                                {{ ucfirst($s['status']) }}
                                            </span>
                                        </div>
                                        <h4 class="hub-day-session-title">{{ $s['title'] }}</h4>
                                        <p class="hub-day-course-name">{{ $s['course_title'] ?? '' }}</p>
                                    </div>

                                    <div class="hub-day-card-action">
                                        <x-heroicon-o-chevron-right class="hub-chevron-icon" />
                                    </div>
                                </article>
                            @empty
                                <div class="hub-empty-state">
                                    <x-heroicon-o-calendar class="hub-empty-icon" />
                                    <p style="margin:0;font-size:0.75rem;">No classes scheduled on this day.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- CUSTOM VIEW --}}
                @if ($rangeMode === 'custom')
                    <div class="hub-custom-grid">
                        @foreach ($customDays as $day)
                            <div class="hub-custom-day-cell {{ $day['is_today'] ? 'is-today' : '' }}">
                                <div class="hub-custom-day-header">
                                    <span class="hub-custom-day-name">{{ $day['day_name'] }}</span>
                                    <span class="hub-custom-day-num {{ $day['is_today'] ? 'is-today-badge' : '' }}">{{ $day['date_num'] }}</span>
                                </div>
                                <div class="hub-custom-day-sessions">
                                    @forelse ($day['sessions'] as $s)
                                        <button type="button"
                                                wire:click="openSessionDetails({{ $s['id'] }})"
                                                class="hub-custom-session-btn"
                                                title="{{ $s['title'] }} ({{ $s['start_time'] }})">
                                            <span class="hub-event-indicator is-{{ $s['status'] }}"></span>
                                            <span class="hub-custom-time">{{ $s['start_time'] }}</span>
                                            <span class="hub-custom-title">{{ $s['title'] }}</span>
                                        </button>
                                    @empty
                                        <span class="hub-custom-empty">—</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- RIGHT COLUMN: FILTER BY STATUS & SESSION LIST PANE --}}
            <aside class="hub-schedule-card">
                <div class="hub-pane-header">
                    <div class="hub-pane-title-group">
                        <x-heroicon-o-funnel class="hub-pane-icon" />
                        <h3 class="hub-pane-title">Filter by Status</h3>
                    </div>
                    <span class="hub-pane-count-badge">{{ count($filteredSessions) }} in view</span>
                </div>

                {{-- Status Filter Pills --}}
                <div class="hub-side-status-filters">
                    <button type="button"
                            wire:click="setFilterStatus('')"
                            class="hub-filter-pill {{ $filterStatus === '' ? 'is-active' : '' }}">
                        <span>All ({{ $statusCounts['all'] }})</span>
                    </button>
                    <button type="button"
                            wire:click="setFilterStatus('scheduled')"
                            class="hub-filter-pill {{ $filterStatus === 'scheduled' ? 'is-active' : '' }}">
                        <span class="hub-status-dot is-scheduled"></span>
                        <span>Scheduled ({{ $statusCounts['scheduled'] }})</span>
                    </button>
                    <button type="button"
                            wire:click="setFilterStatus('completed')"
                            class="hub-filter-pill {{ $filterStatus === 'completed' ? 'is-active' : '' }}">
                        <span class="hub-status-dot is-completed"></span>
                        <span>Completed ({{ $statusCounts['completed'] }})</span>
                    </button>
                    <button type="button"
                            wire:click="setFilterStatus('rescheduled')"
                            class="hub-filter-pill {{ $filterStatus === 'rescheduled' ? 'is-active' : '' }}">
                        <span class="hub-status-dot is-rescheduled"></span>
                        <span>Rescheduled ({{ $statusCounts['rescheduled'] }})</span>
                    </button>
                    <button type="button"
                            wire:click="setFilterStatus('cancelled')"
                            class="hub-filter-pill {{ $filterStatus === 'cancelled' ? 'is-active' : '' }}">
                        <span class="hub-status-dot is-cancelled"></span>
                        <span>Cancelled ({{ $statusCounts['cancelled'] }})</span>
                    </button>
                </div>

                {{-- Quick Search Input --}}
                <div class="hub-side-search-box">
                    <x-heroicon-o-magnifying-glass class="hub-search-icon" />
                    <input type="text"
                           wire:model.live.debounce.300ms="searchSession"
                           placeholder="Search topic, course or code..."
                           class="hub-search-input">
                </div>

                {{-- Scrollable Session Card List --}}
                <div class="hub-side-session-list">
                    @forelse ($filteredSessions as $s)
                        <article wire:click="openSessionDetails({{ $s['id'] }})"
                                 class="hub-side-session-card is-{{ $s['status'] }}">
                            <div class="hub-side-card-top">
                                <div>
                                    @if ($s['course_code'])
                                        <span class="hub-side-card-code">{{ $s['course_code'] }}</span>
                                    @endif
                                    <span class="hub-side-card-type">· {{ $s['type_label'] ?? 'Group' }}</span>
                                </div>
                                <span class="hub-status-micro-pill is-{{ $s['status'] }}">
                                    {{ ucfirst($s['status']) }}
                                </span>
                            </div>

                            <h4 class="hub-side-card-title">{{ $s['title'] }}</h4>

                            <div class="hub-side-card-meta">
                                <div class="hub-side-card-meta-item">
                                    <x-heroicon-o-calendar style="width:0.75rem;height:0.75rem;shrink:0;" />
                                    <span>{{ $s['session_date'] }} · {{ $s['start_time'] }} - {{ $s['end_time'] }}</span>
                                </div>
                                @if (!empty($s['instructor_name']))
                                    <div class="hub-side-card-meta-item">
                                        <x-heroicon-o-user style="width:0.75rem;height:0.75rem;shrink:0;" />
                                        <span>{{ $s['instructor_name'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="hub-empty-state" style="padding:1.75rem 1rem;">
                            <x-heroicon-o-calendar-days class="hub-empty-icon" />
                            <p style="margin:0;font-size:0.75rem;">No sessions match your filter.</p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>

        {{-- 3. BOTTOM SECTION 1: RESCHEDULE REQUESTS --}}
        <div class="hub-reschedule-card">
            <div class="hub-reschedule-header">
                <div class="hub-reschedule-title-group">
                    <x-heroicon-o-arrow-path class="hub-pane-icon" />
                    <h3 class="hub-pane-title">Reschedule Requests</h3>
                </div>
                <div style="display:flex;align-items:center;gap:0.6rem;">
                    <label class="hub-toggle-history-label">
                        <input type="checkbox" wire:model.live="showRequestHistory">
                        <span>Show history</span>
                    </label>
                    <span class="hub-counter-badge">{{ count($rescheduleRequests) }} {{ $showRequestHistory ? 'in history' : 'pending' }}</span>
                </div>
            </div>

            @if (count($rescheduleRequests) > 0)
                <div class="hub-reschedule-list">
                    @foreach ($rescheduleRequests as $request)
                        <div class="hub-reschedule-item">
                            <div class="hub-reschedule-meta">
                                <span class="hub-reschedule-session">Session #{{ $request['session_id'] }}</span>
                                <span class="hub-reschedule-msg">{{ $request['message'] }}</span>
                                @if ($request['preferred_date'])
                                    <span class="hub-reschedule-pref">Prefers {{ $request['preferred_date'] }} @if ($request['preferred_time']) at {{ $request['preferred_time'] }} @endif</span>
                                @endif
                            </div>
                            <span class="hub-status-micro-pill is-{{ $request['decision_status'] }}">
                                {{ ucfirst($request['decision_status']) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="hub-reschedule-empty-text">No pending reschedule requests.</p>
            @endif
        </div>

        {{-- 4. BOTTOM SECTION 2: COURSE PROGRESS & ATTENDANCE --}}
        @if (count($courseProgress) > 0 || count($attendanceSummary) > 0)
            <div class="hub-progress-card">
                <div class="hub-pane-header" style="border-bottom:none;padding-bottom:0.15rem;">
                    <div class="hub-pane-title-group">
                        <x-heroicon-o-chart-bar-square class="hub-pane-icon" />
                        <h3 class="hub-pane-title">Course Progress</h3>
                    </div>
                </div>

                <div class="hub-progress-items">
                    @foreach ($courseProgress as $progress)
                        <div class="hub-progress-item">
                            <div class="hub-progress-meta">
                                <span class="hub-progress-course-name">{{ $progress['course_title'] }} <span style="color:var(--hub-muted);font-weight:400;">({{ $progress['course_code'] }})</span></span>
                                <span class="hub-progress-fraction">{{ $progress['completed'] }}/{{ $progress['total'] }} ({{ $progress['percentage'] }}%)</span>
                            </div>
                            <div class="hub-progress-track">
                                <div class="hub-progress-fill" style="width: {{ $progress['percentage'] }}%;"></div>
                            </div>
                        </div>
                    @endforeach

                    @if (count($attendanceSummary) > 0)
                        @foreach ($attendanceSummary as $summary)
                            <div class="hub-progress-item" style="margin-top:0.35rem;padding-top:0.35rem;border-top:1px solid var(--hub-border);">
                                <div class="hub-progress-meta">
                                    <span class="hub-progress-course-name">{{ $summary['course_title'] }} (Attendance)</span>
                                    <span class="hub-progress-fraction {{ $summary['percentage'] >= 75 ? 'is-good' : 'is-warning' }}">
                                        {{ $summary['attended'] }}/{{ $summary['total'] }} ({{ $summary['percentage'] }}%)
                                    </span>
                                </div>
                                <div class="hub-progress-track">
                                    <div class="hub-progress-fill {{ $summary['percentage'] >= 75 ? 'is-good' : 'is-warning' }}" style="width: {{ $summary['percentage'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach

                        @if (count($attendanceRecords) > 0)
                            <div class="hub-attendance-log">
                                @foreach ($attendanceRecords as $rec)
                                    <div class="hub-attendance-log-row">
                                        <span class="hub-log-session-name">{{ $rec['session_title'] }}</span>
                                        <span class="hub-status-micro-pill is-{{ $rec['status'] }}">{{ ucfirst($rec['status']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        {{-- 5. POP-UP MODAL 1: CLASS DETAILS POP-UP --}}
        @if ($showSessionDetailsModal && $selectedSessionDetails)
            <div class="hub-modal-overlay">
                <div class="hub-modal-card" style="max-width:480px;padding:1.25rem;">
                    {{-- Header --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem;padding-bottom:0.75rem;border-bottom:1px solid var(--hub-border);">
                        <div>
                            <div style="display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;margin-bottom:0.25rem;">
                                @if ($selectedSessionDetails['course_code'])
                                    <span class="hub-code-badge">{{ $selectedSessionDetails['course_code'] }}</span>
                                @endif
                                <span class="hub-type-badge">{{ $selectedSessionDetails['type_label'] }}</span>
                                <span class="hub-status-micro-pill is-{{ $selectedSessionDetails['status'] }}">
                                    <span class="hub-status-dot is-{{ $selectedSessionDetails['status'] }}"></span>
                                    {{ ucfirst($selectedSessionDetails['status']) }}
                                </span>
                            </div>
                            <h3 class="hub-title" style="font-size:1.05rem;margin:0.2rem 0 0;">{{ $selectedSessionDetails['title'] }}</h3>
                            <p style="font-size:0.75rem;color:var(--hub-muted);margin:0.1rem 0 0;">{{ $selectedSessionDetails['course_title'] }}</p>
                        </div>
                        <button type="button"
                                wire:click="closeSessionDetails"
                                style="background:var(--hub-surface-soft);border:1px solid var(--hub-border);border-radius:999px;width:1.65rem;height:1.65rem;display:flex;align-items:center;justify-content:center;color:var(--hub-muted);cursor:pointer;"
                                title="Close dialog">
                            <x-heroicon-o-x-mark style="width:0.95rem;height:0.95rem;" />
                        </button>
                    </div>

                    {{-- Body --}}
                    <div style="display:flex;flex-direction:column;gap:0.55rem;margin:0.85rem 0;">
                        {{-- Date & Time --}}
                        <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.75rem;background:var(--hub-surface-soft);border-radius:8px;border:1px solid var(--hub-border);">
                            <x-heroicon-o-calendar-days style="width:1.15rem;height:1.15rem;color:var(--hub-primary);shrink:0;" />
                            <div>
                                <p style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);margin:0;">{{ $selectedSessionDetails['session_date'] }}</p>
                                <p style="font-size:0.7rem;color:var(--hub-muted);margin:0.05rem 0 0;">{{ $selectedSessionDetails['start_time'] }} – {{ $selectedSessionDetails['end_time'] }}</p>
                            </div>
                        </div>

                        {{-- Instructor Info --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.55rem 0.75rem;background:var(--hub-surface-soft);border-radius:8px;border:1px solid var(--hub-border);flex-wrap:wrap;gap:0.4rem;">
                            <div style="display:flex;align-items:center;gap:0.45rem;">
                                <x-heroicon-o-academic-cap style="width:1.15rem;height:1.15rem;color:var(--hub-primary);" />
                                <div>
                                    <p style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);margin:0;">{{ $selectedSessionDetails['instructor_name'] }}</p>
                                    <p style="font-size:0.65rem;color:var(--hub-muted);margin:0.02rem 0 0;">Course Instructor</p>
                                </div>
                            </div>
                            @if ($selectedSessionDetails['instructor_whatsapp'])
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $selectedSessionDetails['instructor_whatsapp']) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="hub-btn hub-btn-muted"
                                   style="font-size:0.7rem;padding:0.22rem 0.55rem;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:0.25rem;">
                                    <x-heroicon-o-chat-bubble-left-right style="width:0.8rem;height:0.8rem;" />
                                    <span>WhatsApp</span>
                                </a>
                            @endif
                        </div>

                        {{-- Virtual Meeting Link --}}
                        @if ($selectedSessionDetails['meeting_link'])
                            <div style="padding:0.55rem 0.75rem;background:color-mix(in oklab, var(--hub-primary-soft) 20%, var(--hub-surface));border-radius:8px;border:1px solid var(--hub-primary);display:flex;justify-content:space-between;align-items:center;gap:0.4rem;flex-wrap:wrap;">
                                <div style="min-width:0;flex:1;">
                                    <p style="font-size:0.75rem;font-weight:700;color:var(--hub-ink);margin:0;">Virtual Classroom Link</p>
                                    <p style="font-size:0.65rem;color:var(--hub-muted);margin:0.02rem 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selectedSessionDetails['meeting_link'] }}</p>
                                </div>
                                <a href="{{ $selectedSessionDetails['meeting_link'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="hub-btn hub-btn-primary"
                                   style="font-size:0.72rem;padding:0.28rem 0.75rem;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                                    <x-heroicon-o-video-camera style="width:0.85rem;height:0.85rem;" />
                                    <span>Join Class</span>
                                </a>
                            </div>
                        @endif

                        {{-- Agenda & Notes --}}
                        @if ($selectedSessionDetails['notes'])
                            <div style="padding:0.55rem 0.75rem;background:var(--hub-surface-soft);border-radius:8px;border:1px solid var(--hub-border);">
                                <p style="font-size:0.68rem;font-weight:700;color:var(--hub-muted);margin:0 0 0.15rem;text-transform:uppercase;">Session Agenda / Notes</p>
                                <p style="font-size:0.75rem;color:var(--hub-ink);margin:0;line-height:1.4;">{{ $selectedSessionDetails['notes'] }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.4rem;flex-wrap:wrap;padding-top:0.75rem;border-top:1px solid var(--hub-border);">
                        <div>
                            @if ($selectedSessionDetails['status'] === 'scheduled')
                                <button type="button"
                                        wire:click="openRescheduleFromDetails"
                                        class="hub-btn hub-btn-muted"
                                        style="font-size:0.72rem;padding:0.25rem 0.65rem;border-radius:999px;border:1px solid var(--hub-border);cursor:pointer;display:inline-flex;align-items:center;gap:0.25rem;">
                                    <x-heroicon-o-arrow-path style="width:0.78rem;height:0.78rem;" />
                                    <span>Request Reschedule</span>
                                </button>
                            @endif
                        </div>

                        <div style="display:flex;align-items:center;gap:0.35rem;">
                            @if ($selectedSessionDetails['google_calendar_url'])
                                <a href="{{ $selectedSessionDetails['google_calendar_url'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="hub-btn hub-btn-muted"
                                   style="font-size:0.72rem;padding:0.25rem 0.65rem;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:0.25rem;">
                                    <x-heroicon-o-arrow-top-right-on-square style="width:0.78rem;height:0.78rem;" />
                                    <span>Sync Calendar</span>
                                </a>
                            @endif
                            <button type="button"
                                    wire:click="closeSessionDetails"
                                    class="hub-btn hub-btn-primary"
                                    style="font-size:0.72rem;padding:0.28rem 0.85rem;border-radius:999px;cursor:pointer;">
                                Done
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 6. POP-UP MODAL 2: RESCHEDULE REQUEST FORM --}}
        @if ($rescheduleRequestSessionId)
            <div class="hub-modal-overlay">
                <div class="hub-modal-card" style="max-width:440px;padding:1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.65rem;padding-bottom:0.55rem;border-bottom:1px solid var(--hub-border);">
                        <div style="display:flex;align-items:center;gap:0.35rem;">
                            <x-heroicon-o-arrow-path style="width:1rem;height:1rem;color:var(--hub-primary);" />
                            <h3 class="hub-title" style="font-size:0.95rem;margin:0;">Request Session Reschedule</h3>
                        </div>
                        <button type="button" wire:click="cancelRescheduleRequest" style="background:transparent;border:none;color:var(--hub-muted);cursor:pointer;">
                            <x-heroicon-o-x-mark style="width:0.95rem;height:0.95rem;" />
                        </button>
                    </div>

                    <p style="font-size:0.72rem;color:var(--hub-muted);margin-bottom:0.75rem;">
                        Your instructor and administration will be notified with your requested date and rationale.
                    </p>

                    <div style="display:flex;flex-direction:column;gap:0.65rem;">
                        <div>
                            <label style="display:block;font-size:0.72rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.2rem;">
                                Reason for Rescheduling <span style="color:var(--hub-danger);">*</span>
                            </label>
                            <textarea wire:model="rescheduleRequestReason"
                                      rows="3"
                                      placeholder="Please explain why you need to reschedule..."
                                      class="fi-input"
                                      style="width:100%;font-size:0.75rem;padding:0.45rem;border-radius:6px;"></textarea>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.45rem;">
                            <div>
                                <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                    Preferred Date
                                </label>
                                <input type="date"
                                       wire:model="reschedulePreferredDate"
                                       class="fi-input"
                                       style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                    Preferred Time
                                </label>
                                <input type="time"
                                       wire:model="reschedulePreferredTime"
                                       class="fi-input"
                                       style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:0.4rem;margin-top:1rem;padding-top:0.65rem;border-top:1px solid var(--hub-border);">
                        <button type="button"
                                wire:click="cancelRescheduleRequest"
                                class="hub-btn hub-btn-muted"
                                style="font-size:0.72rem;padding:0.3rem 0.75rem;border-radius:999px;cursor:pointer;">
                            Cancel
                        </button>
                        <button type="button"
                                wire:click="submitRescheduleRequest"
                                class="hub-btn hub-btn-primary"
                                style="font-size:0.72rem;padding:0.3rem 0.95rem;border-radius:999px;cursor:pointer;">
                            Send Request
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
