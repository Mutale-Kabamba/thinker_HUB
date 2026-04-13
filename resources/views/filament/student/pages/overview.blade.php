<x-filament-panels::page>
    <div
        x-data="{
            activeSection: 'overview',
            calendarTooltip: null,
            calendarTooltipX: 0,
            calendarTooltipY: 0,
            select(section) {
                this.activeSection = section;
                window.location.hash = section;
            },
            showTooltip(event, names) {
                if (!names.length) return;
                this.calendarTooltip = names;
                const rect = event.target.getBoundingClientRect();
                this.calendarTooltipX = rect.left + rect.width / 2;
                this.calendarTooltipY = rect.top - 8;
            },
            hideTooltip() {
                this.calendarTooltip = null;
            }
        }"
        x-init="if (window.location.hash) { const selected = window.location.hash.replace('#', ''); if (['overview', 'assignments', 'assessments', 'materials'].includes(selected)) { activeSection = selected; } }"
        class="hub-shell"
    >
        <section id="overview-hero" class="hub-card">
            <p class="hub-eyebrow">Overview</p>
            <h2 class="hub-title" style="margin-top:0.35rem;font-size:1.2rem;">{{ $stats['greeting'] ?? 'Hello' }}</h2>
            <p class="hub-copy">Course: {{ $stats['course'] ?? '-' }} | Level: {{ $stats['track'] ?? '-' }}</p>
            <div class="hub-links">
                @foreach ($quickLinks as $link)
                    <button
                        type="button"
                        @click="select('{{ $link['section'] }}')"
                        class="hub-btn"
                        :class="activeSection === '{{ $link['section'] }}' ? 'hub-btn-primary' : 'hub-btn-muted'"
                    >
                        {{ $link['label'] }}
                    </button>
                @endforeach
            </div>
        </section>

        {{-- ===== OVERVIEW TAB ===== --}}
        <section id="overview" class="hub-shell" x-show="activeSection === 'overview'" x-cloak>
            <div class="hub-grid hub-grid-4">
                <a href="{{ route('filament.student.pages.assignments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Completion</p>
                    <p class="hub-metric">{{ $stats['completion'] ?? 0 }}%</p>
                    <p class="hub-copy">Estimated overall progress</p>
                </a>
                <a href="{{ route('filament.student.pages.assessments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Submissions</p>
                    <p class="hub-metric">{{ $stats['submissions'] ?? 0 }}</p>
                    <p class="hub-copy">Submitted responses</p>
                </a>
                <a href="{{ route('filament.student.pages.assignments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Assignments</p>
                    <p class="hub-metric">{{ $stats['assignments'] ?? 0 }}</p>
                    <p class="hub-copy">Visible tasks</p>
                </a>
                <a href="{{ route('filament.student.pages.materials') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Materials</p>
                    <p class="hub-metric">{{ $stats['materials'] ?? 0 }}</p>
                    <p class="hub-copy">Learning resources</p>
                </a>
            </div>

            <div class="hub-grid hub-grid-3">
                <section class="hub-card" style="grid-column: span 2;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                        <h3 class="hub-title">Upcoming Timeline</h3>
                        <span class="hub-chip hub-chip-amber">Next Due: {{ $stats['next_due'] ?? '-' }}</span>
                    </div>
                    <p class="hub-copy">Overdue items: <strong style="color:#b91c1c;">{{ $stats['overdue'] ?? 0 }}</strong></p>

                    <div class="hub-stack" style="margin-top:0.75rem;">
                        @forelse ($upcoming as $item)
                            <a href="{{ route('filament.student.pages.assignments') }}" style="text-decoration:none;display:block;border:1px solid var(--hub-border);border-radius:12px;padding:0.75rem;background:var(--hub-surface);transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                    <div>
                                        <p style="margin:0;font-weight:700;color:var(--hub-ink);">{{ $item['name'] }}</p>
                                        <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.82rem;">Due: {{ $item['due'] }}</p>
                                    </div>
                                    <span class="hub-chip {{ ($item['status'] ?? '') === 'Submitted' ? 'hub-chip-green' : 'hub-chip-amber' }}">{{ $item['status'] ?? 'Not submitted' }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="hub-copy">No upcoming deadlines.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card" x-data="{
                    selectedDate: null,
                    events: @js($calendarEvents),
                    get selectedEvents() {
                        return this.selectedDate ? (this.events[this.selectedDate] || []) : [];
                    },
                    selectDay(date, hasItems) {
                        if (this.selectedDate === date) {
                            this.selectedDate = null;
                        } else {
                            this.selectedDate = date;
                        }
                    },
                    formatDate(d) {
                        const dt = new Date(d + 'T00:00:00');
                        return dt.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
                    }
                }">
                    {{-- Calendar Header with Navigation --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <button type="button" wire:click="navigateCalendar({{ $calendar['prev']['year'] ?? now()->year }}, {{ $calendar['prev']['month'] ?? now()->month }})" style="background:none;border:1px solid var(--hub-border);border-radius:8px;padding:0.3rem 0.6rem;cursor:pointer;color:var(--hub-ink);font-size:0.8rem;transition:background 0.15s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background='none'" title="Previous month">&larr;</button>
                        <h3 class="hub-title" style="font-size:0.95rem;">{{ $calendar['month'] ?? '' }}</h3>
                        <button type="button" wire:click="navigateCalendar({{ $calendar['next']['year'] ?? now()->year }}, {{ $calendar['next']['month'] ?? now()->month }})" style="background:none;border:1px solid var(--hub-border);border-radius:8px;padding:0.3rem 0.6rem;cursor:pointer;color:var(--hub-ink);font-size:0.8rem;transition:background 0.15s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background='none'" title="Next month">&rarr;</button>
                    </div>

                    {{-- Calendar Grid --}}
                    <div style="margin-top:0.8rem;">
                        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;margin-bottom:4px;">
                            @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
                                <div style="font-size:0.68rem;font-weight:700;color:var(--hub-muted);text-transform:uppercase;letter-spacing:0.04em;">{{ $dow }}</div>
                            @endforeach
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;text-align:center;">
                            @for ($i = 0; $i < ($calendar['start_day'] ?? 0); $i++)
                                <div></div>
                            @endfor
                            @foreach (($calendar['days'] ?? []) as $day)
                                <div
                                    @click="selectDay(@js($day['date']), {{ $day['has_due'] ? 'true' : 'false' }})"
                                    @mouseenter="showTooltip($event, @js($day['due_names']))"
                                    @mouseleave="hideTooltip()"
                                    :class="{ 'hub-day-selected': selectedDate === @js($day['date']) }"
                                    class="hub-day {{ $day['is_today'] ? 'hub-day-today' : ($day['has_due'] ? 'hub-day-due' : ($day['is_past'] ? 'hub-day-past' : '')) }}"
                                    style="cursor:pointer;position:relative;transition:transform 0.1s,box-shadow 0.1s;"
                                    onmouseover="this.style.transform='scale(1.15)';this.style.boxShadow='0 2px 6px rgba(0,0,0,0.1)'"
                                    onmouseout="this.style.transform='';this.style.boxShadow=''"
                                >
                                    {{ $day['day'] }}
                                    @if ($day['has_due'])
                                        <span style="position:absolute;bottom:1px;left:50%;transform:translateX(-50%);display:flex;gap:2px;">
                                            @for ($dot = 0; $dot < min($day['assignment_count'] + $day['assessment_count'], 3); $dot++)
                                                <span style="width:4px;height:4px;border-radius:50%;background:{{ $dot < $day['assignment_count'] ? '#f59e0b' : '#6366f1' }};"></span>
                                            @endfor
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div style="margin-top:0.65rem;display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;font-size:0.72rem;color:var(--hub-muted);">
                        <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;display:inline-block;"></span> Today</span>
                        <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;"></span> Assignment due</span>
                        <span style="display:flex;align-items:center;gap:4px;"><span style="width:8px;height:8px;border-radius:50%;background:#6366f1;display:inline-block;"></span> Assessment due</span>
                    </div>

                    {{-- Selected Day Detail Panel --}}
                    <div x-show="selectedDate" x-cloak x-transition.opacity.duration.200ms style="margin-top:0.75rem;border-top:1px solid var(--hub-border);padding-top:0.75rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                            <p style="margin:0;font-size:0.82rem;font-weight:700;color:var(--hub-ink);" x-text="formatDate(selectedDate)"></p>
                            <button @click="selectedDate = null" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.1rem;line-height:1;" title="Close">&times;</button>
                        </div>

                        <template x-if="selectedEvents.length > 0">
                            <div style="display:flex;flex-direction:column;gap:0.4rem;">
                                <template x-for="(item, idx) in selectedEvents" :key="idx">
                                    <a href="{{ route('filament.student.pages.assignments') }}" style="text-decoration:none;display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;padding:0.55rem 0.7rem;border:1px solid var(--hub-border);border-radius:10px;background:var(--hub-surface);transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 2px 6px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                                        <div style="min-width:0;">
                                            <div style="display:flex;align-items:center;gap:0.4rem;flex-wrap:wrap;">
                                                <span x-show="item.type === 'Assignment'" style="font-size:0.68rem;font-weight:700;padding:0.15rem 0.45rem;border-radius:6px;background:#fef3c7;color:#92400e;">Assignment</span>
                                                <span x-show="item.type === 'Assessment'" style="font-size:0.68rem;font-weight:700;padding:0.15rem 0.45rem;border-radius:6px;background:#e0e7ff;color:#3730a3;">Assessment</span>
                                                <p style="margin:0;font-size:0.82rem;font-weight:600;color:var(--hub-ink);" x-text="item.name"></p>
                                            </div>
                                            <p style="margin:0.2rem 0 0;font-size:0.74rem;color:var(--hub-muted);" x-text="item.course"></p>
                                        </div>
                                        <div style="text-align:right;flex-shrink:0;">
                                            <span
                                                style="font-size:0.72rem;font-weight:600;padding:0.2rem 0.5rem;border-radius:6px;"
                                                :style="item.status === 'Not submitted'
                                                    ? 'background:#fef3c7;color:#92400e;'
                                                    : (item.status === 'Submitted' ? 'background:#dbeafe;color:#1e40af;' : 'background:#dcfce7;color:#166534;')"
                                                x-text="item.status"
                                            ></span>
                                            <template x-if="item.grade !== null && item.grade !== undefined">
                                                <p style="margin:0.2rem 0 0;font-size:0.74rem;font-weight:700;color:#15803d;" x-text="item.grade + '%'"></p>
                                            </template>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <template x-if="selectedEvents.length === 0">
                            <p style="margin:0;font-size:0.8rem;color:var(--hub-muted);">No items due on this day.</p>
                        </template>
                    </div>

                    {{-- Calendar tooltip --}}
                    <div
                        x-show="calendarTooltip && !selectedDate"
                        x-cloak
                        x-transition.opacity.duration.150ms
                        :style="`position:fixed;left:${calendarTooltipX}px;top:${calendarTooltipY}px;transform:translate(-50%,-100%);z-index:9999;`"
                        style="background:#1f2937;color:white;border-radius:8px;padding:0.5rem 0.75rem;font-size:0.78rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);pointer-events:none;max-width:220px;"
                    >
                        <p style="margin:0 0 0.2rem;font-weight:700;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em;color:#9ca3af;">Due this day:</p>
                        <template x-for="name in calendarTooltip" :key="name">
                            <p style="margin:0.15rem 0 0;font-size:0.78rem;" x-text="name"></p>
                        </template>
                    </div>
                </section>
            </div>
        </section>

        {{-- ===== ASSIGNMENTS TAB ===== --}}
        <section id="assignments" class="hub-shell" x-show="activeSection === 'assignments'" x-cloak>
            <div class="hub-grid hub-grid-4">
                <a href="{{ route('filament.student.pages.assignments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Total Assignments</p>
                    <p class="hub-metric">{{ $assignmentSummary['total'] ?? 0 }}</p>
                    <p class="hub-copy">Visible to your enrolled courses</p>
                </a>
                <a href="{{ route('filament.student.pages.assignments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Submitted</p>
                    <p class="hub-metric">{{ $assignmentSummary['submitted'] ?? 0 }}</p>
                    <p class="hub-copy">Assignments you have submitted</p>
                </a>
                <a href="{{ route('filament.student.pages.assignments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Pending</p>
                    <p class="hub-metric">{{ $assignmentSummary['pending'] ?? 0 }}</p>
                    <p class="hub-copy">Still waiting for submission</p>
                </a>
                <section class="hub-card">
                    <p class="hub-eyebrow">Next Due</p>
                    <p class="hub-metric" style="font-size:1.2rem;">{{ $assignmentSummary['next_due'] ?? '-' }}</p>
                    <p class="hub-copy">Closest upcoming deadline</p>
                </section>
            </div>

            <section class="hub-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                    <div>
                        <h3 class="hub-title">Upcoming Assignments</h3>
                        <p class="hub-copy">High-level upcoming assignment items and statuses.</p>
                    </div>
                    <a href="{{ route('filament.student.pages.assignments') }}" class="hub-btn hub-btn-primary" style="font-size:0.8rem;">View All Assignments</a>
                </div>
                <div class="hub-stack" style="margin-top:0.75rem;">
                    @forelse ($upcoming as $item)
                        <a href="{{ route('filament.student.pages.assignments') }}" style="text-decoration:none;display:block;border:1px solid var(--hub-border);border-radius:12px;padding:0.75rem;background:var(--hub-surface);display:flex;justify-content:space-between;align-items:center;gap:0.7rem;flex-wrap:wrap;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                            <div>
                                <p style="margin:0;font-weight:700;color:var(--hub-ink);">{{ $item['name'] }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.82rem;">Due: {{ $item['due'] }}</p>
                            </div>
                            <span class="hub-chip {{ ($item['status'] ?? '') === 'Submitted' ? 'hub-chip-green' : 'hub-chip-amber' }}">{{ $item['status'] ?? 'Not submitted' }}</span>
                        </a>
                    @empty
                        <p class="hub-copy">No upcoming assignments.</p>
                    @endforelse
                </div>
            </section>
        </section>

        {{-- ===== ASSESSMENTS TAB ===== --}}
        <section id="assessments" class="hub-shell" x-show="activeSection === 'assessments'" x-cloak>
            <div class="hub-grid hub-grid-3">
                <a href="{{ route('filament.student.pages.assessments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Total Assessments</p>
                    <p class="hub-metric">{{ $assessmentSummary['total'] ?? 0 }}</p>
                    <p class="hub-copy">Assessment records assigned to you</p>
                </a>
                <a href="{{ route('filament.student.pages.assessments') }}" class="hub-card" style="text-decoration:none;cursor:pointer;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <p class="hub-eyebrow">Submitted</p>
                    <p class="hub-metric">{{ $assessmentSummary['submitted'] ?? 0 }}</p>
                    <p class="hub-copy">Assessments with submission entries</p>
                </a>
                <section class="hub-card">
                    <p class="hub-eyebrow">Average Score</p>
                    <p class="hub-metric">{{ $assessmentSummary['average_score'] ?? '-' }}</p>
                    <p class="hub-copy">Average from scored submissions</p>
                </section>
            </div>

            <section class="hub-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                    <div>
                        <h3 class="hub-title">Recent Assessments</h3>
                        <p class="hub-copy">High-level assessment performance and submission progress.</p>
                    </div>
                    <a href="{{ route('filament.student.pages.assessments') }}" class="hub-btn hub-btn-primary" style="font-size:0.8rem;">View All Assessments</a>
                </div>
                <div style="overflow:auto;margin-top:0.75rem;">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Assessment</th>
                                <th>Course</th>
                                <th>Due</th>
                                <th>Submission</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($assessmentSummary['items'] ?? []) as $item)
                                <tr style="cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background=''" onclick="window.location='{{ route('filament.student.pages.assessments') }}'">
                                    <td>{{ $item['name'] ?? 'Assessment' }}</td>
                                    <td>{{ $item['course'] }}</td>
                                    <td>{{ $item['due_date'] ?? '-' }}</td>
                                    <td>
                                        <span class="hub-chip {{ ($item['submission_status'] ?? '') === 'Submitted' ? 'hub-chip-green' : 'hub-chip-amber' }}">
                                            {{ $item['submission_status'] }}
                                        </span>
                                    </td>
                                    <td>{{ $item['score'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="color:var(--hub-muted);">No assessments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </section>

        {{-- ===== MATERIALS TAB ===== --}}
        <section id="materials" class="hub-shell" x-show="activeSection === 'materials'" x-cloak>
            <section class="hub-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                    <div>
                        <h3 class="hub-title">Latest Materials</h3>
                        <p class="hub-copy">Most recent course materials available to you.</p>
                    </div>
                    <a href="{{ route('filament.student.pages.materials') }}" class="hub-btn hub-btn-primary" style="font-size:0.8rem;">View All Materials</a>
                </div>
                <div style="overflow:auto;margin-top:0.75rem;">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Name</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($materials as $item)
                                <tr style="cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background=''" onclick="window.location='{{ route('filament.student.pages.materials') }}'">
                                    <td>{{ $item['course'] }}</td>
                                    <td>{{ $item['name'] }}</td>
                                    <td><span class="hub-chip hub-chip-primary">{{ $item['type'] }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="color:var(--hub-muted);">No materials yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </div>
</x-filament-panels::page>
