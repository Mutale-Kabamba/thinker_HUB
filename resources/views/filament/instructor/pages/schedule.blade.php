<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                <div>
                    <p class="hub-eyebrow">Session Timetable</p>
                    <h2 class="hub-title" style="font-size:1.1rem;">Manage Your Schedule</h2>
                </div>
                <div style="display:flex;gap:0.4rem;">
                    <button wire:click="$set('viewMode', 'calendar')" class="hub-btn {{ $viewMode === 'calendar' ? 'hub-btn-primary' : 'hub-btn-muted' }}" style="font-size:0.72rem;padding:0.3rem 0.6rem;">📅 Calendar</button>
                    <button wire:click="$set('viewMode', 'list')" class="hub-btn {{ $viewMode === 'list' ? 'hub-btn-primary' : 'hub-btn-muted' }}" style="font-size:0.72rem;padding:0.3rem 0.6rem;">📋 List</button>
                </div>
            </div>
        </section>

        {{-- ===== CALENDAR VIEW ===== --}}
        @if ($viewMode === 'calendar')
            <section class="hub-card" style="padding:0.75rem 1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                    <button wire:click="previousMonth" class="hub-btn hub-btn-muted" style="font-size:0.78rem;padding:0.3rem 0.6rem;">← Prev</button>
                    <h3 style="font-weight:700;font-size:0.95rem;color:var(--hub-ink);">
                        {{ \Carbon\Carbon::createFromDate($calendarYear, $calendarMonth, 1)->format('F Y') }}
                    </h3>
                    <button wire:click="nextMonth" class="hub-btn hub-btn-muted" style="font-size:0.78rem;padding:0.3rem 0.6rem;">Next →</button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="hub-calendar-table" style="width:100%;border-collapse:collapse;table-layout:fixed;min-width:500px;">
                        <thead>
                            <tr>
                                @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                                    <th style="padding:0.4rem 0.2rem;font-size:0.7rem;font-weight:700;color:var(--hub-muted);text-align:center;border-bottom:1px solid var(--hub-border);">{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($calendarWeeks as $week)
                                <tr>
                                    @foreach ($week as $day)
                                        <td style="
                                            vertical-align:top;
                                            padding:0.3rem;
                                            height:5.5rem;
                                            border:1px solid var(--hub-border);
                                            {{ ! $day['in_month'] ? 'opacity:0.35;' : '' }}
                                            {{ $day['is_today'] ? 'background:var(--hub-primary-soft);' : '' }}
                                        ">
                                            <div class="hub-calendar-day-num" style="font-size:0.7rem;font-weight:{{ $day['is_today'] ? '800' : '600' }};color:{{ $day['is_today'] ? 'var(--hub-primary)' : 'var(--hub-ink)' }};margin-bottom:0.2rem;">
                                                {{ $day['date'] }}
                                            </div>
                                            @foreach ($day['sessions'] as $calSession)
                                                <div class="hub-calendar-session" style="
                                                    margin-bottom:0.15rem;
                                                    padding:0.15rem 0.25rem;
                                                    border-radius:4px;
                                                    font-size:0.6rem;
                                                    line-height:1.3;
                                                    overflow:hidden;
                                                    white-space:nowrap;
                                                    text-overflow:ellipsis;
                                                    cursor:default;
                                                    background:{{ match($calSession['status']) {
                                                        'completed' => '#dcfce7',
                                                        'rescheduled' => '#fef3c7',
                                                        'cancelled' => '#fee2e2',
                                                        default => '#e0f2fe',
                                                    } }};
                                                    color:{{ match($calSession['status']) {
                                                        'completed' => '#166534',
                                                        'rescheduled' => '#92400e',
                                                        'cancelled' => '#991b1b',
                                                        default => '#0c4a6e',
                                                    } }};
                                                " title="{{ $calSession['title'] }} ({{ $calSession['start_time'] }}) — {{ ucfirst($calSession['status']) }}{{ $calSession['student_name'] ? ' — '.$calSession['student_name'] : '' }}">
                                                    <span style="font-weight:700;">{{ $calSession['start_time'] }}</span>
                                                    {{ $calSession['course_code'] ?: $calSession['title'] }}
                                                </div>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="hub-legend" style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:0.6rem;font-size:0.65rem;color:var(--hub-muted);">
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#e0f2fe;margin-right:3px;"></span> Scheduled</span>
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#dcfce7;margin-right:3px;"></span> Completed</span>
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#fef3c7;margin-right:3px;"></span> Rescheduled</span>
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#fee2e2;margin-right:3px;"></span> Cancelled</span>
                </div>
            </section>
        @endif

        {{-- Filters --}}
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <div class="hub-schedule-filters" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--hub-muted);">Status</label>
                    <select wire:model.live="filterStatus" style="display:block;margin-top:0.15rem;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                        <option value="">All</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="rescheduled">Rescheduled</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--hub-muted);">Type</label>
                    <select wire:model.live="filterType" style="display:block;margin-top:0.15rem;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                        <option value="">All</option>
                        <option value="group">Group</option>
                        <option value="one_on_one">One-On-One</option>
                    </select>
                </div>
                <div class="hub-filter-count" style="margin-left:auto;align-self:flex-end;">
                    <span class="hub-chip hub-chip-primary">{{ count($sessions) }} sessions</span>
                </div>
            </div>
        </section>

        {{-- ===== LIST VIEW ===== --}}
        @if ($viewMode === 'list')
            @forelse ($sessions as $session)
                <article class="hub-card" style="border-left:4px solid {{ match($session['status']) { 'completed' => '#16a34a', 'rescheduled' => '#d97706', 'cancelled' => '#dc2626', default => 'var(--hub-primary)' } }};">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;flex-wrap:wrap;">
                        <div>
                            <p style="font-weight:700;color:var(--hub-ink);font-size:0.88rem;">
                                {{ $session['course_title'] }}
                                @if ($session['title'])
                                    <span style="font-weight:400;color:var(--hub-muted);"> — {{ $session['title'] }}</span>
                                @endif
                            </p>
                            <p style="font-size:0.72rem;color:var(--hub-muted);margin-top:0.15rem;">{{ $session['course_code'] }}</p>
                        </div>
                        <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                            <span class="hub-chip {{ $session['type'] === 'group' ? 'hub-chip-green' : 'hub-chip-primary' }}" style="font-size:0.65rem;">{{ $session['type_label'] }}</span>
                            <span class="hub-chip {{ match($session['status']) { 'completed' => 'hub-chip-green', 'rescheduled' => 'hub-chip-amber', 'cancelled' => 'hub-chip-gray', default => 'hub-chip-primary' } }}" style="font-size:0.65rem;">{{ ucfirst($session['status']) }}</span>
                        </div>
                    </div>

                    <div class="hub-session-meta" style="margin-top:0.5rem;display:flex;gap:1.2rem;flex-wrap:wrap;font-size:0.78rem;color:var(--hub-muted);">
                        <span>📅 {{ $session['session_date'] }}</span>
                        <span>🕐 {{ $session['start_time'] }} – {{ $session['end_time'] }}</span>
                        @if ($session['student_name'])
                            <span>👤 {{ $session['student_name'] }}</span>
                        @endif
                    </div>

                    @if ($session['status'] === 'rescheduled' && $session['rescheduled_date'])
                        <div style="margin-top:0.4rem;padding:0.4rem 0.6rem;background:var(--hub-primary-soft);border-radius:6px;font-size:0.75rem;color:var(--hub-ink);">
                            <strong>Rescheduled to:</strong> {{ $session['rescheduled_date'] }}
                            at {{ $session['rescheduled_start_time'] }}
                            @if ($session['rescheduled_end_time']) – {{ $session['rescheduled_end_time'] }} @endif
                        </div>
                    @endif

                    @if ($session['notes'])
                        <p style="margin-top:0.4rem;font-size:0.75rem;color:var(--hub-muted);font-style:italic;">{{ $session['notes'] }}</p>
                    @endif

                    {{-- Actions --}}
                    @if ($session['status'] === 'scheduled')
                        <div style="margin-top:0.6rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                            <button wire:click="markCompleted({{ $session['id'] }})" class="hub-btn hub-btn-primary" style="font-size:0.75rem;padding:0.35rem 0.7rem;">
                                ✅ Mark Completed
                            </button>
                            <button wire:click="openReschedule({{ $session['id'] }})" class="hub-btn hub-btn-muted" style="font-size:0.75rem;padding:0.35rem 0.7rem;">
                                📅 Reschedule
                            </button>
                        </div>
                    @endif

                    {{-- Reschedule form --}}
                    @if ($rescheduleSessionId === $session['id'])
                        <div style="margin-top:0.6rem;padding:0.7rem;border:1px solid var(--hub-border);border-radius:8px;background:var(--hub-surface-soft);">
                            <p style="font-weight:700;font-size:0.8rem;color:var(--hub-ink);margin-bottom:0.5rem;">Reschedule Session</p>
                            <div class="hub-form-row" style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                                <div>
                                    <label style="font-size:0.7rem;font-weight:600;color:var(--hub-muted);">New Date</label>
                                    <input type="date" wire:model="rescheduleDate" style="display:block;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                                </div>
                                <div>
                                    <label style="font-size:0.7rem;font-weight:600;color:var(--hub-muted);">Start Time</label>
                                    <input type="time" wire:model="rescheduleStartTime" style="display:block;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                                </div>
                                <div>
                                    <label style="font-size:0.7rem;font-weight:600;color:var(--hub-muted);">End Time</label>
                                    <input type="time" wire:model="rescheduleEndTime" style="display:block;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                                </div>
                            </div>
                            <div style="margin-top:0.5rem;display:flex;gap:0.5rem;">
                                <button wire:click="submitReschedule" class="hub-btn hub-btn-primary" style="font-size:0.75rem;padding:0.35rem 0.7rem;">Confirm</button>
                                <button wire:click="cancelReschedule" class="hub-btn hub-btn-muted" style="font-size:0.75rem;padding:0.35rem 0.7rem;">Cancel</button>
                            </div>
                        </div>
                    @endif
                </article>
            @empty
                <section class="hub-card" style="text-align:center;padding:2rem 1rem;">
                    <p class="hub-copy">No sessions found. Sessions are created by the admin from the Session Timetable.</p>
                </section>
            @endforelse
        @endif
    </div>
</x-filament-panels::page>
