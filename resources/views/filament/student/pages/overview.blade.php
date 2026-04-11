<x-filament-panels::page>
    <div
        x-data="{
            activeSection: 'overview',
            select(section) {
                this.activeSection = section;
                window.location.hash = section;
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

        <section id="overview" class="hub-shell" x-show="activeSection === 'overview'" x-cloak>
            <div class="hub-grid hub-grid-4">
                <section class="hub-card">
                    <p class="hub-eyebrow">Completion</p>
                    <p class="hub-metric">{{ $stats['completion'] ?? 0 }}%</p>
                    <p class="hub-copy">Estimated overall progress</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Submissions</p>
                    <p class="hub-metric">{{ $stats['submissions'] ?? 0 }}</p>
                    <p class="hub-copy">Submitted responses</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Assignments</p>
                    <p class="hub-metric">{{ $stats['assignments'] ?? 0 }}</p>
                    <p class="hub-copy">Visible tasks</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Materials</p>
                    <p class="hub-metric">{{ $stats['materials'] ?? 0 }}</p>
                    <p class="hub-copy">Learning resources</p>
                </section>
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
                            <div style="border:1px solid var(--hub-border);border-radius:12px;padding:0.75rem;background:var(--hub-surface);">
                                <p style="margin:0;font-weight:700;color:var(--hub-ink);">{{ $item['name'] }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.82rem;">Due: {{ $item['due'] }}</p>
                            </div>
                        @empty
                            <p class="hub-copy">No upcoming deadlines.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <h3 class="hub-title">Calendar</h3>
                        <span class="hub-chip hub-chip-gray">{{ $calendar['month'] ?? '' }}</span>
                    </div>
                    <div class="hub-calendar" style="margin-top:0.8rem;">
                        @foreach (($calendar['days'] ?? []) as $day)
                            <div class="hub-day {{ $day['is_today'] ? 'hub-day-today' : ($day['has_due'] ? 'hub-day-due' : '') }}">{{ $day['day'] }}</div>
                        @endforeach
                    </div>
                </section>
            </div>
        </section>

        <section id="assignments" class="hub-shell" x-show="activeSection === 'assignments'" x-cloak>
            <div class="hub-grid hub-grid-4">
                <section class="hub-card">
                    <p class="hub-eyebrow">Total Assignments</p>
                    <p class="hub-metric">{{ $assignmentSummary['total'] ?? 0 }}</p>
                    <p class="hub-copy">Visible to your enrolled courses</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Submitted</p>
                    <p class="hub-metric">{{ $assignmentSummary['submitted'] ?? 0 }}</p>
                    <p class="hub-copy">Assignments you have submitted</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Pending</p>
                    <p class="hub-metric">{{ $assignmentSummary['pending'] ?? 0 }}</p>
                    <p class="hub-copy">Still waiting for submission</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Next Due</p>
                    <p class="hub-metric" style="font-size:1.2rem;">{{ $assignmentSummary['next_due'] ?? '-' }}</p>
                    <p class="hub-copy">Closest upcoming deadline</p>
                </section>
            </div>

            <section class="hub-card">
                <h3 class="hub-title">Upcoming Assignments</h3>
                <p class="hub-copy">High-level upcoming assignment items and statuses.</p>
                <div class="hub-stack" style="margin-top:0.75rem;">
                    @forelse ($upcoming as $item)
                        <div style="border:1px solid var(--hub-border);border-radius:12px;padding:0.75rem;background:var(--hub-surface);display:flex;justify-content:space-between;align-items:center;gap:0.7rem;flex-wrap:wrap;">
                            <div>
                                <p style="margin:0;font-weight:700;color:var(--hub-ink);">{{ $item['name'] }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.82rem;">Due: {{ $item['due'] }}</p>
                            </div>
                            <span class="hub-chip {{ ($item['status'] ?? '') === 'Submitted' ? 'hub-chip-green' : 'hub-chip-amber' }}">{{ $item['status'] ?? 'Not submitted' }}</span>
                        </div>
                    @empty
                        <p class="hub-copy">No upcoming assignments.</p>
                    @endforelse
                </div>
            </section>
        </section>

        <section id="assessments" class="hub-shell" x-show="activeSection === 'assessments'" x-cloak>
            <div class="hub-grid hub-grid-3">
                <section class="hub-card">
                    <p class="hub-eyebrow">Total Assessments</p>
                    <p class="hub-metric">{{ $assessmentSummary['total'] ?? 0 }}</p>
                    <p class="hub-copy">Assessment records assigned to you</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Submitted</p>
                    <p class="hub-metric">{{ $assessmentSummary['submitted'] ?? 0 }}</p>
                    <p class="hub-copy">Assessments with submission entries</p>
                </section>
                <section class="hub-card">
                    <p class="hub-eyebrow">Average Score</p>
                    <p class="hub-metric">{{ $assessmentSummary['average_score'] ?? '-' }}</p>
                    <p class="hub-copy">Average from scored submissions</p>
                </section>
            </div>

            <section class="hub-card">
                <h3 class="hub-title">Recent Assessments</h3>
                <p class="hub-copy">High-level assessment performance and submission progress.</p>
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
                                <tr>
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

        <section id="materials" class="hub-shell" x-show="activeSection === 'materials'" x-cloak>
            <section class="hub-card">
                <h3 class="hub-title">Latest Materials</h3>
                <p class="hub-copy">Most recent course materials available to you.</p>
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
                                <tr>
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
