<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.8rem;flex-wrap:wrap;">
                <div>
                    <p class="hub-eyebrow">Course Catalog</p>
                    <h2 class="hub-title">Available Courses</h2>
                </div>
                <span class="hub-chip hub-chip-primary">Enrolled: {{ $enrolledCount }}/2</span>
            </div>
            <p class="hub-copy">Pick up to two active courses and manage enrollment from this panel.</p>
        </section>

        <div class="hub-grid hub-grid-2">
            @forelse ($courses as $course)
                <article class="hub-card">
                    <div style="display:flex;justify-content:space-between;gap:0.7rem;align-items:flex-start;">
                        <div>
                            <p class="hub-eyebrow">{{ $course['code'] }}</p>
                            <h3 class="hub-title" style="margin-top:0.25rem;">{{ $course['title'] }}</h3>
                        </div>
                        @if (! $course['is_active'])
                            <span class="hub-chip hub-chip-gray">Inactive</span>
                        @elseif ($course['enrolled'])
                            <span class="hub-chip hub-chip-green">Enrolled</span>
                        @else
                            <span class="hub-chip hub-chip-amber">Open</span>
                        @endif
                    </div>

                    <p class="hub-copy">{{ $course['summary'] }}</p>

                    <details style="margin-top:0.65rem;">
                        <summary style="cursor:pointer;color:#0f766e;font-weight:700;font-size:0.82rem;">View full description</summary>
                        <p class="hub-copy" style="margin-top:0.5rem;">{{ $course['description'] }}</p>
                    </details>

                    <div style="margin-top:0.9rem;display:flex;gap:0.55rem;flex-wrap:wrap;">
                        @if (! $course['is_active'])
                            <button type="button" disabled class="hub-btn hub-btn-muted" style="opacity:0.6;cursor:not-allowed;">Unavailable</button>
                        @elseif ($course['enrolled'])
                            <button type="button" wire:click="unenroll({{ $course['id'] }})" class="hub-btn hub-btn-danger">Unenroll</button>
                        @else
                            <button type="button" wire:click="enroll({{ $course['id'] }})" class="hub-btn hub-btn-primary">Enroll Now</button>
                        @endif
                    </div>
                </article>
            @empty
                <section class="hub-card">
                    <p class="hub-copy">No courses available.</p>
                </section>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
