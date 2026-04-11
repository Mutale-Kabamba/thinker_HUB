<div class="space-y-6 text-sm text-gray-700">
    <section class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Overview</h3>
        <p class="mt-2 leading-relaxed text-gray-700">{{ $record->overview ?: 'No overview added yet.' }}</p>
    </section>

    <div class="grid gap-4 md:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Timeline</h3>
            <p class="mt-2 font-medium text-gray-800">{{ $record->timeline ?: 'Not specified yet.' }}</p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Key Outcome</h3>
            <p class="mt-2 leading-relaxed text-gray-700">{{ $record->key_outcome ?: 'No key outcome added yet.' }}</p>
        </section>
    </div>

    <section class="space-y-3">
        @forelse ($feeSections as $section)
            <div class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-4">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-indigo-800">{{ $section['label'] }}</h3>
                    @if (($section['key'] ?? '') === 'one_on_one')
                        <span class="rounded-full border border-indigo-300 bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-indigo-700">1:1 Focus</span>
                    @endif
                </div>

                <div class="mt-3 overflow-hidden rounded-xl border border-indigo-200 bg-white">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-indigo-100/80 text-indigo-800">
                            <tr>
                                <th class="px-3 py-2 font-semibold uppercase tracking-wide">Level</th>
                                <th class="px-3 py-2 font-semibold uppercase tracking-wide">Amount</th>
                                <th class="px-3 py-2 font-semibold uppercase tracking-wide">Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($section['rows'] as $row)
                                <tr class="border-t border-indigo-100">
                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $row['level'] !== '' ? $row['level'] : '-' }}</td>
                                    <td class="px-3 py-2 font-semibold text-indigo-700">{{ $row['amount'] !== '' ? $row['amount'] : '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $row['duration'] !== '' ? $row['duration'] : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-gray-500">No fee details added yet.</div>
        @endforelse
    </section>

    <section class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Levels and Progression</h3>
        <div class="mt-3 grid gap-3 md:grid-cols-3">
            @foreach ($progressionCards as $card)
                <article class="rounded-2xl border border-gray-200 bg-white px-4 py-3">
                    <h4 class="text-sm font-bold text-gray-800">{{ $card['level'] }}</h4>
                    <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ $card['details'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Requirements</h3>
        @php
            $requirementLines = collect(preg_split('/\R+/', (string) ($record->requirements ?? '')))
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->values();
        @endphp

        @if ($requirementLines->isNotEmpty())
            <ul class="mt-2 list-disc space-y-1.5 pl-5 text-gray-700">
                @foreach ($requirementLines as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        @else
            <p class="mt-2 text-gray-600">No requirements added yet.</p>
        @endif
    </section>
</div>
