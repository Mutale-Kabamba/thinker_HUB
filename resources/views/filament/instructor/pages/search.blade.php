<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Instructor Search</p>
            <h2 class="hub-title">Find Anything Fast</h2>
            <p class="hub-copy">Search across your courses, students, and sessions.</p>
            <div style="margin-top:0.75rem;">
                <input type="text" wire:model.live.debounce.300ms="query" placeholder="Type keywords..." class="hub-input">
            </div>
        </section>

        @if (trim($query) !== '')
            <div class="hub-grid hub-grid-2">
                <section class="hub-card">
                    <h3 class="hub-title">My Courses</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['courses'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">
                                <p style="margin:0;font-weight:700;">{{ $item['code'] }} — {{ $item['title'] }}</p>
                                <span class="hub-chip {{ $item['is_active'] ? 'hub-chip-green' : 'hub-chip-gray' }}" style="margin-top:0.3rem;font-size:0.65rem;">{{ $item['is_active'] ? 'Active' : 'Inactive' }}</span>
                            </div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Students</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['students'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">
                                <p style="margin:0;font-weight:700;">{{ $item['name'] }}</p>
                                <p style="margin:0.2rem 0 0;color:var(--hub-muted);font-size:0.78rem;">{{ $item['email'] }}</p>
                            </div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card hub-span-2">
                    <h3 class="hub-title">Sessions</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['sessions'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">
                                <p style="margin:0;font-weight:700;">{{ $item['title'] }}</p>
                                <p style="margin:0.2rem 0 0;color:var(--hub-muted);font-size:0.78rem;">{{ $item['course'] }} · {{ $item['date'] }} · {{ $item['status'] }}</p>
                            </div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
