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

                                            @if (!empty($s['student_name']))
                                                <div class="hub-week-session-user">
                                                    <x-heroicon-o-user class="hub-user-icon" />
                                                    <span>{{ $s['student_name'] }}</span>
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
                                <h3 class="hub-day-title">{{ $dayViewData['day_name'] ?? '' }}, {{ $dayViewData['formatted_date'] ?? '' }}</h3>
                                <p class="hub-day-subtitle">{{ count($dayViewData['sessions'] ?? []) }} class session(s) scheduled</p>
                            </div>
                            @if (!empty($dayViewData['is_today']))
                                <span class="hub-today-badge-subtle">Today</span>
                            @endif
                        </div>

                        <div class="hub-day-agenda-list">
                            @forelse ($dayViewData['sessions'] ?? [] as $s)
                                <article wire:click="openSessionDetails({{ $s['id'] }})"
                                         class="hub-day-session-card is-{{ $s['status'] ?? 'scheduled' }}">
                                    <div class="hub-day-time-badge">
                                        <span class="hub-day-time-start">{{ $s['start_time'] ?? '—' }}</span>
                                        <span class="hub-day-time-end">{{ $s['end_time'] ?? '—' }}</span>
                                    </div>

                                    <div class="hub-day-card-body">
                                        <div class="hub-day-card-meta">
                                            @if (!empty($s['course_code']))
                                                <span class="hub-code-badge">{{ $s['course_code'] }}</span>
                                            @endif
                                            <span class="hub-type-badge">{{ $s['type_label'] ?? 'Group' }}</span>
                                            <span class="hub-status-micro-pill is-{{ $s['status'] ?? 'scheduled' }}">
                                                <span class="hub-status-dot is-{{ $s['status'] ?? 'scheduled' }}"></span>
                                                {{ ucfirst($s['status'] ?? 'scheduled') }}
                                            </span>
                                        </div>
                                        <h4 class="hub-day-session-title">{{ $s['title'] ?? 'Session' }}</h4>
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
                                @if (!empty($s['student_name']))
                                    <div class="hub-side-card-meta-item">
                                        <x-heroicon-o-user style="width:0.75rem;height:0.75rem;shrink:0;" />
                                        <span>{{ $s['student_name'] }}</span>
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

        {{-- 3. BOTTOM SECTION: STUDENT RESCHEDULE REQUESTS --}}
        @if (count($pendingRescheduleRequests) > 0)
            <div class="hub-reschedule-card" style="border-left: 3.5px solid var(--hub-accent);">
                <div class="hub-reschedule-header">
                    <div class="hub-reschedule-title-group">
                        <x-heroicon-o-bell-alert class="hub-pane-icon" style="color:var(--hub-accent);" />
                        <h3 class="hub-pane-title">Student Reschedule Requests</h3>
                    </div>
                    <span class="hub-counter-badge">{{ count($pendingRescheduleRequests) }} action required</span>
                </div>

                <div class="hub-reschedule-list">
                    @foreach ($pendingRescheduleRequests as $req)
                        <div class="hub-reschedule-item">
                            <div class="hub-reschedule-meta">
                                <span class="hub-reschedule-session">{{ $req['student_name'] }} (Session #{{ $req['session_id'] }})</span>
                                <span class="hub-reschedule-msg">Reason: {{ $req['reason'] ?: 'None provided' }}</span>
                                @if ($req['preferred_date'])
                                    <span class="hub-reschedule-pref">Requested: {{ $req['preferred_date'] }} @if ($req['preferred_time']) at {{ $req['preferred_time'] }} @endif</span>
                                @endif
                            </div>
                            <button type="button"
                                    wire:click="openDecisionWizard('{{ $req['id'] }}')"
                                    class="hub-btn hub-btn-primary"
                                    style="font-size:0.7rem;padding:0.22rem 0.65rem;border-radius:999px;cursor:pointer;">
                                Review Request
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 4. POP-UP MODAL 1: CLASS DETAILS POP-UP --}}
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

                        {{-- Student / Cohort Info --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.55rem 0.75rem;background:var(--hub-surface-soft);border-radius:8px;border:1px solid var(--hub-border);flex-wrap:wrap;gap:0.4rem;">
                            <div style="display:flex;align-items:center;gap:0.45rem;">
                                <x-heroicon-o-user-group style="width:1.15rem;height:1.15rem;color:var(--hub-primary);" />
                                <div>
                                    <p style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);margin:0;">{{ $selectedSessionDetails['student_name'] }}</p>
                                    <p style="font-size:0.65rem;color:var(--hub-muted);margin:0.02rem 0 0;">Learner / Audience</p>
                                </div>
                            </div>
                            @if ($selectedSessionDetails['student_whatsapp'])
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $selectedSessionDetails['student_whatsapp']) }}"
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
                                    <p style="font-size:0.75rem;font-weight:700;color:var(--hub-ink);margin:0;">Class Meeting Link</p>
                                    <p style="font-size:0.65rem;color:var(--hub-muted);margin:0.02rem 0 0;overflow:hidden;text-overflow:ellipsis;max-width:240px;white-space:nowrap;">{{ $selectedSessionDetails['meeting_link'] }}</p>
                                </div>
                                <a href="{{ $selectedSessionDetails['meeting_link'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="hub-btn hub-btn-primary"
                                   style="font-size:0.72rem;padding:0.28rem 0.75rem;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                                    <x-heroicon-o-video-camera style="width:0.85rem;height:0.85rem;" />
                                    <span>Start Class</span>
                                </a>
                            </div>
                        @endif

                        {{-- Lesson Agenda & Notes --}}
                        @if ($selectedSessionDetails['notes'])
                            <div style="padding:0.55rem 0.75rem;background:var(--hub-surface-soft);border-radius:8px;border:1px solid var(--hub-border);">
                                <p style="font-size:0.68rem;font-weight:700;color:var(--hub-muted);margin:0 0 0.15rem;text-transform:uppercase;">Lesson Agenda / Plan</p>
                                <p style="font-size:0.75rem;color:var(--hub-ink);margin:0;line-height:1.4;">{{ $selectedSessionDetails['notes'] }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer Actions --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.4rem;flex-wrap:wrap;padding-top:0.75rem;border-top:1px solid var(--hub-border);">
                        <div style="display:flex;align-items:center;gap:0.35rem;">
                            @if ($selectedSessionDetails['status'] !== 'completed')
                                <button type="button"
                                        wire:click="markCompleted({{ $selectedSessionDetails['id'] }})"
                                        class="hub-btn hub-btn-muted"
                                        style="font-size:0.72rem;padding:0.25rem 0.65rem;border-radius:999px;border:1px solid var(--hub-border);cursor:pointer;display:inline-flex;align-items:center;gap:0.25rem;">
                                    <x-heroicon-o-check-circle style="width:0.8rem;height:0.8rem;color:var(--hub-success);" />
                                    <span>Mark Completed</span>
                                </button>

                                <button type="button"
                                        wire:click="openRescheduleFromDetails"
                                        class="hub-btn hub-btn-muted"
                                        style="font-size:0.72rem;padding:0.25rem 0.65rem;border-radius:999px;border:1px solid var(--hub-border);cursor:pointer;display:inline-flex;align-items:center;gap:0.25rem;">
                                    <x-heroicon-o-arrow-path style="width:0.78rem;height:0.78rem;" />
                                    <span>Reschedule</span>
                                </button>
                            @endif
                        </div>

                        <button type="button"
                                wire:click="closeSessionDetails"
                                class="hub-btn hub-btn-primary"
                                style="font-size:0.72rem;padding:0.28rem 0.85rem;border-radius:999px;cursor:pointer;">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- 5. POP-UP MODAL 2: INSTRUCTOR RESCHEDULE MODAL --}}
        @if ($rescheduleSessionId)
            <div class="hub-modal-overlay">
                <div class="hub-modal-card" style="max-width:440px;padding:1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.65rem;padding-bottom:0.55rem;border-bottom:1px solid var(--hub-border);">
                        <div style="display:flex;align-items:center;gap:0.35rem;">
                            <x-heroicon-o-arrow-path style="width:1rem;height:1rem;color:var(--hub-primary);" />
                            <h3 class="hub-title" style="font-size:0.95rem;margin:0;">Reschedule Session</h3>
                        </div>
                        <button type="button" wire:click="cancelReschedule" style="background:transparent;border:none;color:var(--hub-muted);cursor:pointer;">
                            <x-heroicon-o-x-mark style="width:0.95rem;height:0.95rem;" />
                        </button>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:0.65rem;">
                        <div>
                            <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                New Date <span style="color:var(--hub-danger);">*</span>
                            </label>
                            <input type="date"
                                   wire:model="rescheduleDate"
                                   class="fi-input"
                                   style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.45rem;">
                            <div>
                                <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                    Start Time <span style="color:var(--hub-danger);">*</span>
                                </label>
                                <input type="time"
                                       wire:model="rescheduleStartTime"
                                       class="fi-input"
                                       style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                    End Time
                                </label>
                                <input type="time"
                                       wire:model="rescheduleEndTime"
                                       class="fi-input"
                                       style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:0.4rem;margin-top:1rem;padding-top:0.65rem;border-top:1px solid var(--hub-border);">
                        <button type="button"
                                wire:click="cancelReschedule"
                                class="hub-btn hub-btn-muted"
                                style="font-size:0.72rem;padding:0.3rem 0.75rem;border-radius:999px;cursor:pointer;">
                            Cancel
                        </button>
                        <button type="button"
                                wire:click="submitReschedule"
                                class="hub-btn hub-btn-primary"
                                style="font-size:0.72rem;padding:0.3rem 0.95rem;border-radius:999px;cursor:pointer;">
                            Save & Notify Students
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- 6. POP-UP MODAL 3: DECISION WIZARD MODAL --}}
        @if ($decisionNotificationId)
            <div class="hub-modal-overlay">
                <div class="hub-modal-card" style="max-width:440px;padding:1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.65rem;padding-bottom:0.55rem;border-bottom:1px solid var(--hub-border);">
                        <div style="display:flex;align-items:center;gap:0.35rem;">
                            <x-heroicon-o-bell-alert style="width:1rem;height:1rem;color:var(--hub-primary);" />
                            <h3 class="hub-title" style="font-size:0.95rem;margin:0;">Student Reschedule Request</h3>
                        </div>
                        <button type="button" wire:click="closeDecisionWizard" style="background:transparent;border:none;color:var(--hub-muted);cursor:pointer;">
                            <x-heroicon-o-x-mark style="width:0.95rem;height:0.95rem;" />
                        </button>
                    </div>

                    @if ($decisionStep === 'review')
                        <div style="display:flex;flex-direction:column;gap:0.55rem;">
                            <div style="padding:0.55rem 0.75rem;background:var(--hub-surface-soft);border-radius:8px;border:1px solid var(--hub-border);">
                                <p style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);margin:0;">{{ $decisionStudentName }}</p>
                                <p style="font-size:0.7rem;color:var(--hub-muted);margin:0.15rem 0 0;">Reason: {{ $decisionReason ?: 'None provided' }}</p>
                                @if ($decisionPreferredDate)
                                    <p style="font-size:0.7rem;color:var(--hub-primary);margin:0.15rem 0 0;font-weight:700;">
                                        Requested: {{ $decisionPreferredDate }} @if ($decisionPreferredTime) at {{ $decisionPreferredTime }} @endif
                                    </p>
                                @endif
                            </div>

                            <div style="display:flex;justify-content:flex-end;gap:0.4rem;margin-top:0.65rem;">
                                <button type="button"
                                        wire:click="setDecisionStep('decline')"
                                        class="hub-btn hub-btn-danger"
                                        style="font-size:0.72rem;padding:0.3rem 0.75rem;border-radius:999px;cursor:pointer;">
                                    Decline Request
                                </button>
                                <button type="button"
                                        wire:click="setDecisionStep('accept')"
                                        class="hub-btn hub-btn-primary"
                                        style="font-size:0.72rem;padding:0.3rem 0.95rem;border-radius:999px;cursor:pointer;">
                                    Accept & Set Time
                                </button>
                            </div>
                        </div>
                    @elseif ($decisionStep === 'accept')
                        <div style="display:flex;flex-direction:column;gap:0.55rem;">
                            <div>
                                <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                    Confirmed Date <span style="color:var(--hub-danger);">*</span>
                                </label>
                                <input type="date"
                                       wire:model="decisionDate"
                                       class="fi-input"
                                       style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.45rem;">
                                <div>
                                    <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                        Start Time <span style="color:var(--hub-danger);">*</span>
                                    </label>
                                    <input type="time"
                                           wire:model="decisionStartTime"
                                           class="fi-input"
                                           style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                                </div>
                                <div>
                                    <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                        End Time
                                    </label>
                                    <input type="time"
                                           wire:model="decisionEndTime"
                                           class="fi-input"
                                           style="width:100%;font-size:0.72rem;padding:0.3rem 0.45rem;border-radius:6px;">
                                </div>
                            </div>

                            <div style="display:flex;justify-content:space-between;margin-top:0.65rem;">
                                <button type="button"
                                        wire:click="setDecisionStep('review')"
                                        class="hub-btn hub-btn-muted"
                                        style="font-size:0.72rem;padding:0.3rem 0.75rem;border-radius:999px;cursor:pointer;">
                                    Back
                                </button>
                                <button type="button"
                                        wire:click="acceptRescheduleRequest"
                                        class="hub-btn hub-btn-primary"
                                        style="font-size:0.72rem;padding:0.3rem 0.95rem;border-radius:999px;cursor:pointer;">
                                    Confirm Acceptance
                                </button>
                            </div>
                        </div>
                    @elseif ($decisionStep === 'decline')
                        <div style="display:flex;flex-direction:column;gap:0.55rem;">
                            <div>
                                <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.15rem;">
                                    Decline Note (Optional for Student)
                                </label>
                                <textarea wire:model="declineReason"
                                          rows="3"
                                          placeholder="Explain why this time slot cannot be accommodated..."
                                          class="fi-input"
                                          style="width:100%;font-size:0.72rem;padding:0.35rem;border-radius:6px;"></textarea>
                            </div>

                            <div style="display:flex;justify-content:space-between;margin-top:0.65rem;">
                                <button type="button"
                                        wire:click="setDecisionStep('review')"
                                        class="hub-btn hub-btn-muted"
                                        style="font-size:0.72rem;padding:0.3rem 0.75rem;border-radius:999px;cursor:pointer;">
                                    Back
                                </button>
                                <button type="button"
                                        wire:click="declineRescheduleRequest"
                                        class="hub-btn hub-btn-danger"
                                        style="font-size:0.72rem;padding:0.3rem 0.95rem;border-radius:999px;cursor:pointer;">
                                    Confirm Decline
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
