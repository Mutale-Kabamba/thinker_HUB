<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">System Search</p>
            <h2 class="hub-title">Find Anything Fast</h2>
            <p class="hub-copy">Search across your courses, assignments, materials, and assessments.</p>
            <div style="margin-top:0.75rem;">
                <input type="text" wire:model.live.debounce.300ms="query" placeholder="Type keywords..." class="hub-input">
            </div>
        </section>

        @if (trim($query) !== '')
            <div class="hub-grid hub-grid-2">
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
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">
                                <p style="margin:0;font-weight:700;">{{ $item['name'] }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.78rem;">Due: {{ $item['due'] }}</p>
                            </div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Materials</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['materials'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">{{ $item['title'] }} ({{ $item['material_type'] }})</div>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Assessments</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['assessments'] as $item)
                            <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">
                                <p style="margin:0;font-weight:700;">{{ $item['name'] ?? 'Assessment' }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.78rem;">Due: {{ $item['due_date'] ?? '-' }} | Score: {{ $item['score'] ?? '-' }}</p>
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
