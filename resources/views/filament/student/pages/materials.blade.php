<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">Resource Library</p>
            <h2 class="hub-title">Materials</h2>
            <p class="hub-copy">Browse all materials currently visible to your role, course, and level.</p>
        </section>

        <section class="hub-card">
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
    </div>
</x-filament-panels::page>
