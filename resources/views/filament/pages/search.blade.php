<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Admin Search</p>
            <h2 class="hub-title">Global Query Search</h2>
            <p class="hub-copy">Search students, courses, assignments, assessments, and materials.</p>
            <div style="margin-top:0.75rem;">
                <input type="text" wire:model.live.debounce.300ms="query" placeholder="Type keywords..." class="hub-input">
            </div>
        </section>

        @if (trim($query) !== '')
            <div class="hub-grid hub-grid-2">
                <section class="hub-card">
                    <h3 class="hub-title">Students</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['students'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">{{ $item['name'] }} ({{ $item['email'] }})</div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Courses</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['courses'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">{{ $item['code'] }} - {{ $item['title'] }}</div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Assignments</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['assignments'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">{{ $item['name'] }} ({{ $item['scope'] }})</div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Assessments</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['assessments'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">Status: {{ $item['status'] }} | Score: {{ $item['score'] ?? '-' }}</div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
