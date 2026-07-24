<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Analytics</p>
            <h2 class="hub-title" style="font-size:1.1rem;">Course Analytics</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Completion, quiz performance, grading turnaround, and at-risk students across your courses.</p>
        </section>

        {{-- Headline stats --}}
        <div class="hub-grid hub-stats-grid">
            <section class="hub-card">
                <p class="hub-eyebrow">Avg Quiz Score</p>
                <p class="hub-metric">{{ $overallAvgScore !== null ? $overallAvgScore.'%' : '—' }}</p>
                <p class="hub-copy">All graded attempts</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Assessment Turnaround</p>
                <p class="hub-metric" style="font-size:1.4rem;">{{ $turnaround['assessments']['label'] ?? '—' }}</p>
                <p class="hub-copy">{{ $turnaround['assessments']['count'] ?? 0 }} graded assessments</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Assignment Turnaround</p>
                <p class="hub-metric" style="font-size:1.4rem;">{{ $turnaround['assignments']['label'] ?? '—' }}</p>
                <p class="hub-copy">{{ $turnaround['assignments']['count'] ?? 0 }} graded assignments</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">At-Risk Students</p>
                <p class="hub-metric">{{ count($atRiskStudents) }}</p>
                <p class="hub-copy">Inactive 14+ days</p>
            </section>
        </div>

        {{-- Completion rate per course --}}
        <section class="hub-card" style="padding:1rem;">
            <h3 class="hub-title" style="font-size:1rem;margin-bottom:0.1rem;">Completion Rate per Course</h3>
            <p class="hub-copy" style="margin-bottom:0.7rem;">Students who passed every active quiz (enrollment counts as complete when a course has no active quizzes).</p>
            @if (count($completionRows) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">No courses assigned yet.</p>
            @else
                <div class="hub-stack">
                    @foreach ($completionRows as $row)
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;gap:0.5rem;flex-wrap:wrap;">
                                <div>
                                    <span style="font-weight:700;font-size:0.85rem;color:var(--hub-ink);">{{ $row['course'] }}</span>
                                    <span style="font-size:0.7rem;color:var(--hub-muted);margin-left:0.3rem;">{{ $row['code'] }} · {{ $row['active_quizzes'] }} active quiz{{ $row['active_quizzes'] === 1 ? '' : 'zes' }}</span>
                                </div>
                                <span style="font-size:0.76rem;font-weight:700;color:var(--hub-primary);">{{ $row['completed'] }}/{{ $row['enrolled'] }} ({{ $row['percentage'] }}%)</span>
                            </div>
                            <div style="height:8px;background:var(--hub-border);border-radius:99px;overflow:hidden;">
                                <div style="height:100%;width:{{ $row['percentage'] }}%;background:var(--hub-primary);border-radius:99px;transition:width 0.3s;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Quiz scores --}}
        <section class="hub-card" style="padding:1rem;">
            <h3 class="hub-title" style="font-size:1rem;margin-bottom:0.1rem;">Average Quiz Scores</h3>
            <p class="hub-copy" style="margin-bottom:0.7rem;">Completed attempts only.</p>

            @if (count($courseScoreRows) > 0)
                <div style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-bottom:0.8rem;">
                    @foreach ($courseScoreRows as $row)
                        <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.5rem 0.75rem;">
                            <p style="margin:0;font-size:0.72rem;color:var(--hub-muted);">{{ $row['course'] }}</p>
                            <p style="margin:0.15rem 0 0;font-weight:700;font-size:0.95rem;color:var(--hub-ink);">{{ $row['avg_percentage'] }}%</p>
                            <p style="margin:0.1rem 0 0;font-size:0.68rem;color:var(--hub-muted);">{{ $row['attempts'] }} attempt{{ $row['attempts'] === 1 ? '' : 's' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (count($quizScoreRows) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">No completed quiz attempts yet.</p>
            @else
                <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                    <thead>
                        <tr style="background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                            <th style="padding:0.5rem 0.6rem;text-align:left;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em;color:var(--hub-ink);">Quiz</th>
                            <th style="padding:0.5rem 0.6rem;text-align:left;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em;color:var(--hub-ink);">Course</th>
                            <th style="padding:0.5rem 0.6rem;text-align:center;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em;color:var(--hub-ink);">Attempts</th>
                            <th style="padding:0.5rem 0.6rem;text-align:center;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em;color:var(--hub-ink);">Avg Score</th>
                            <th style="padding:0.5rem 0.6rem;text-align:center;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em;color:var(--hub-ink);">Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quizScoreRows as $row)
                            <tr style="border-bottom:1px solid var(--hub-border);">
                                <td style="padding:0.5rem 0.6rem;font-weight:600;color:var(--hub-ink);">{{ $row['quiz'] }}</td>
                                <td style="padding:0.5rem 0.6rem;color:var(--hub-muted);">{{ $row['course'] }}</td>
                                <td style="padding:0.5rem 0.6rem;text-align:center;color:var(--hub-muted);">{{ $row['attempts'] }}</td>
                                <td style="padding:0.5rem 0.6rem;text-align:center;font-weight:700;color:var(--hub-ink);">{{ $row['avg_percentage'] }}%</td>
                                <td style="padding:0.5rem 0.6rem;text-align:center;">
                                    <span class="hub-chip {{ $row['pass_rate'] >= 70 ? 'hub-chip-green' : ($row['pass_rate'] >= 40 ? 'hub-chip-amber' : 'hub-chip-danger') }}" style="font-size:0.68rem;">{{ $row['pass_rate'] }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        {{-- Submission turnaround --}}
        <section class="hub-card" style="padding:1rem;">
            <h3 class="hub-title" style="font-size:1rem;margin-bottom:0.1rem;">Submission Turnaround</h3>
            <p class="hub-copy" style="margin-bottom:0.7rem;">Average time from submission to grading (grading time approximated by the submission's last update once graded).</p>
            <div style="display:flex;gap:0.6rem;flex-wrap:wrap;">
                <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.6rem 0.9rem;">
                    <p style="margin:0;font-size:0.72rem;color:var(--hub-muted);">Assessments</p>
                    <p style="margin:0.15rem 0 0;font-weight:700;font-size:1rem;color:var(--hub-ink);">{{ $turnaround['assessments']['label'] ?? '—' }}</p>
                    <p style="margin:0.1rem 0 0;font-size:0.68rem;color:var(--hub-muted);">{{ $turnaround['assessments']['count'] ?? 0 }} graded</p>
                </div>
                <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.6rem 0.9rem;">
                    <p style="margin:0;font-size:0.72rem;color:var(--hub-muted);">Assignments</p>
                    <p style="margin:0.15rem 0 0;font-weight:700;font-size:1rem;color:var(--hub-ink);">{{ $turnaround['assignments']['label'] ?? '—' }}</p>
                    <p style="margin:0.1rem 0 0;font-size:0.68rem;color:var(--hub-muted);">{{ $turnaround['assignments']['count'] ?? 0 }} graded</p>
                </div>
            </div>
        </section>

        {{-- At-risk students --}}
        <section class="hub-card" style="padding:1rem;">
            <h3 class="hub-title" style="font-size:1rem;margin-bottom:0.1rem;">At-Risk Students</h3>
            <p class="hub-copy" style="margin-bottom:0.7rem;">No quiz, submission, chat, or attendance activity in the last 14 days. Most inactive first.</p>
            @if (count($atRiskStudents) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">Everyone is active — no at-risk students right now. 🎉</p>
            @else
                <div class="hub-stack">
                    @foreach ($atRiskStudents as $student)
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;border:1px solid var(--hub-border);border-radius:10px;padding:0.55rem 0.7rem;flex-wrap:wrap;">
                            <div style="min-width:0;">
                                <span style="font-weight:700;font-size:0.84rem;color:var(--hub-ink);">{{ $student['name'] }}</span>
                                <span style="font-size:0.72rem;color:var(--hub-muted);margin-left:0.35rem;">{{ $student['courses'] }}</span>
                            </div>
                            <span class="hub-chip {{ $student['days_inactive'] === null || $student['days_inactive'] >= 30 ? 'hub-chip-danger' : 'hub-chip-amber' }}" style="font-size:0.68rem;">
                                {{ $student['days_inactive'] === null ? 'Never active' : $student['days_inactive'].' days inactive' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
