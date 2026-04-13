<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Resource Library</p>
            <h2 class="hub-title">Materials</h2>
            <p class="hub-copy">Browse all materials currently visible to your role, course, and level.</p>
        </section>

        {{-- ======================== DESKTOP TABLE ======================== --}}
        <section class="hub-card hub-desktop-only">
            <div style="overflow:auto;">
                <table class="hub-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Scope</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $material)
                            <tr>
                                <td>{{ $material['course'] }}</td>
                                <td>{{ $material['title'] }}</td>
                                <td><span class="hub-chip hub-chip-primary">{{ $material['type'] }}</span></td>
                                <td>{{ $material['scope'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="color:var(--hub-muted);">No materials available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ======================== MOBILE CARDS ======================== --}}
        <div class="hub-mobile-only">
            @forelse ($materials as $material)
                <div class="hub-mobile-card">
                    <div class="hub-mobile-card-row">
                        <div style="flex:1;min-width:0;">
                            <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.88rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $material['title'] }}</p>
                            <p style="margin:0.1rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $material['course'] }}</p>
                        </div>
                        <span class="hub-chip hub-chip-primary" style="font-size:0.68rem;flex-shrink:0;">{{ $material['type'] }}</span>
                    </div>
                    <div class="hub-mobile-card-meta">
                        <span style="color:var(--hub-muted);"><strong>Scope:</strong> {{ $material['scope'] }}</span>
                    </div>
                </div>
            @empty
                <div class="hub-mobile-card">
                    <p class="hub-copy" style="text-align:center;">No materials available.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
