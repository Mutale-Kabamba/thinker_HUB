<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Recent Activities</x-slot>

        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            @forelse ($activities as $activity)
                <div class="hub-mobile-card">
                    <div class="hub-mobile-card-row">
                        <div style="flex:1;min-width:0;">
                            <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.85rem;">{{ $activity['event'] }}</p>
                            <p style="margin:0.1rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $activity['meta'] }}</p>
                        </div>
                        <span style="font-size:0.72rem;color:var(--hub-muted);white-space:nowrap;flex-shrink:0;">{{ optional($activity['time'])->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <p class="hub-copy" style="color:var(--hub-muted);text-align:center;">No activities yet.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
