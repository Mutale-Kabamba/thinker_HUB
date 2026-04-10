<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Recent Activities</x-slot>

        <div style="overflow:auto;">
            <table class="hub-table">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Details</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>{{ $activity['event'] }}</td>
                            <td>{{ $activity['meta'] }}</td>
                            <td>{{ optional($activity['time'])->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="color:var(--hub-muted);">No activities yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
