<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Instructor Dashboard</p>
            <h2 class="hub-title" style="font-size:1.1rem;">Welcome, {{ auth()->user()?->name }}</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">View your assigned courses, student progress, and course materials.</p>
        </section>

        {{-- Stats --}}
        <div class="hub-grid hub-stats-grid">
            <section class="hub-card">
                <p class="hub-eyebrow">My Courses</p>
                <p class="hub-metric">{{ count($courses) }}</p>
                <p class="hub-copy">Assigned courses</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Total Students</p>
                <p class="hub-metric">{{ $totalStudents }}</p>
                <p class="hub-copy">Across all courses</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Assessments</p>
                <p class="hub-metric">{{ $totalAssessments }}</p>
                <p class="hub-copy">In my courses</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Upcoming Sessions</p>
                <p class="hub-metric">{{ $upcomingSessionCount }}</p>
                <p class="hub-copy">Scheduled</p>
            </section>
        </div>

        {{-- Session Calendar --}}
        <section class="hub-card" style="padding:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;flex-wrap:wrap;gap:0.4rem;">
                <div>
                    <h3 class="hub-title" style="font-size:1rem;">Session Calendar</h3>
                    <p class="hub-copy" style="margin-top:0.1rem;">{{ $upcomingSessionCount }} upcoming session{{ $upcomingSessionCount !== 1 ? 's' : '' }}</p>
                </div>
                <a href="{{ route('filament.instructor.pages.schedule') }}" class="hub-btn hub-btn-muted" style="font-size:0.72rem;padding:0.3rem 0.6rem;text-decoration:none;white-space:nowrap;">View Full Schedule →</a>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <button wire:click="previousMonth" class="hub-btn hub-btn-muted" style="font-size:0.75rem;padding:0.25rem 0.5rem;">← Prev</button>
                <span style="font-weight:700;font-size:0.88rem;color:var(--hub-ink);">
                    {{ \Carbon\Carbon::createFromDate($calendarYear, $calendarMonth, 1)->format('F Y') }}
                </span>
                <button wire:click="nextMonth" class="hub-btn hub-btn-muted" style="font-size:0.75rem;padding:0.25rem 0.5rem;">Next →</button>
            </div>

            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
                <table class="hub-calendar-table" style="width:100%;border-collapse:collapse;table-layout:fixed;min-width:320px;">
                    <thead>
                        <tr>
                            @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                                <th style="padding:0.35rem 0.15rem;font-size:0.68rem;font-weight:700;color:var(--hub-muted);text-align:center;border-bottom:1px solid var(--hub-border);">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($calendarWeeks as $week)
                            <tr>
                                @foreach ($week as $dayCell)
                                    <td style="
                                        vertical-align:top;
                                        padding:0.25rem;
                                        height:4.5rem;
                                        border:1px solid var(--hub-border);
                                        {{ ! $dayCell['in_month'] ? 'opacity:0.3;' : '' }}
                                        {{ $dayCell['is_today'] ? 'background:var(--hub-primary-soft);' : '' }}
                                    ">
                                        <div class="hub-calendar-day-num" style="font-size:0.68rem;font-weight:{{ $dayCell['is_today'] ? '800' : '600' }};color:{{ $dayCell['is_today'] ? 'var(--hub-primary)' : 'var(--hub-ink)' }};margin-bottom:0.15rem;">
                                            {{ $dayCell['date'] }}
                                        </div>
                                        @foreach ($dayCell['sessions'] as $calSession)
                                            <div class="hub-calendar-session" style="
                                                margin-bottom:0.1rem;
                                                padding:0.1rem 0.2rem;
                                                border-radius:3px;
                                                font-size:0.55rem;
                                                line-height:1.25;
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

            <div style="display:flex;gap:0.8rem;flex-wrap:wrap;margin-top:0.5rem;font-size:0.6rem;color:var(--hub-muted);">
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#e0f2fe;margin-right:2px;"></span> Scheduled</span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#dcfce7;margin-right:2px;"></span> Completed</span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#fef3c7;margin-right:2px;"></span> Rescheduled</span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#fee2e2;margin-right:2px;"></span> Cancelled</span>
            </div>
        </section>

        {{-- Courses --}}
        <section class="hub-card" style="padding:1rem;">
            <h3 class="hub-title" style="margin-bottom:0.75rem;">My Courses</h3>
            @if (count($courses) > 0)
                <div class="hub-grid hub-grid-2">
                    @foreach ($courses as $course)
                        <div class="hub-card" style="border-left:4px solid {{ $course['is_active'] ? 'var(--hub-primary)' : '#94a3b8' }};">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div>
                                    <p style="font-weight:700;color:var(--hub-ink);font-size:0.9rem;">{{ $course['title'] }}</p>
                                    <p style="font-size:0.75rem;color:var(--hub-muted);">{{ $course['code'] }}</p>
                                </div>
                                <span class="hub-chip {{ $course['is_active'] ? 'hub-chip-green' : 'hub-chip-gray' }}" style="font-size:0.65rem;">{{ $course['is_active'] ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div style="margin-top:0.5rem;display:flex;gap:1rem;">
                                <span style="font-size:0.78rem;color:var(--hub-muted);"><strong>{{ $course['students'] }}</strong> students enrolled</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="hub-copy" style="text-align:center;padding:1rem 0;">No courses assigned yet. Contact admin to be assigned to courses.</p>
            @endif
        </section>
    </div>
</x-filament-panels::page>
