<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Instructor Dashboard</p>
            <h2 class="hub-title" style="font-size:1.1rem;">Welcome, {{ auth()->user()?->name }}</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">View your assigned courses, student progress, and course materials.</p>
        </section>

        {{-- Stats --}}
        <div class="hub-grid hub-stats-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
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
        </div>

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
