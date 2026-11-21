<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- ============================================================ --}}
        {{-- HEADER WITH COURSE & INTAKE SELECTOR                         --}}
        {{-- ============================================================ --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                        <x-heroicon-o-trophy class="w-7 h-7 text-amber-500" />
                        <span>Kids Course Leaderboard</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Celebrating effort, course milestone badges, and academic accomplishments in a supportive, private class circle.
                    </p>
                </div>

                {{-- Course Selector Dropdown --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    @if (!empty($courses))
                        <div class="w-full sm:w-64">
                            <label class="block text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 mb-1">Select Course</label>
                            <select 
                                wire:model.live="selectedCourseId" 
                                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-semibold"
                            >
                                @foreach ($courses as $c)
                                    <option value="{{ $c['id'] }}">
                                        {{ $c['title'] }} {{ $c['is_for_kids'] ? '[Kids Track]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if (!empty($intakes))
                        <div class="w-full sm:w-48">
                            <label class="block text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 mb-1">Class Cohort</label>
                            <select 
                                wire:model.live="selectedIntakeId" 
                                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-semibold"
                            >
                                <option value="">All Cohorts</option>
                                @foreach ($intakes as $intake)
                                    <option value="{{ $intake['id'] }}">{{ $intake['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Privacy Assurance Callout --}}
            <div class="flex items-center gap-2.5 text-xs text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 p-3 rounded-xl border border-indigo-200 dark:border-indigo-800/80">
                <x-heroicon-o-shield-check class="w-5 h-5 shrink-0 text-indigo-600 dark:text-indigo-400" />
                <span>
                    <strong>Child Privacy Protected:</strong> This leaderboard is strictly isolated to parents and students enrolled in this course. Kids in kids courses are hidden from the platform's public community.
                </span>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MY CHILD SPOTLIGHT BANNER                                    --}}
        {{-- ============================================================ --}}
        @if ($myChildHighlight)
            <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white rounded-2xl p-6 sm:p-7 shadow-lg relative overflow-hidden border border-indigo-700/50">
                <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 p-1 backdrop-blur-xs shrink-0 border border-white/20">
                            <div class="w-full h-full rounded-xl overflow-hidden bg-indigo-600 flex items-center justify-center text-white font-black text-2xl">
                                @if ($myChildHighlight['avatar'])
                                    <img src="{{ $myChildHighlight['avatar'] }}" alt="{{ $myChildHighlight['full_name'] }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ strtoupper(substr($myChildHighlight['full_name'], 0, 1)) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-950">
                                    <x-heroicon-o-star class="w-3 h-3" />
                                    <span>Your Child</span>
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/20">
                                    {{ $myChildHighlight['kid_tier']['name'] }}
                                </span>
                            </div>

                            <h2 class="text-xl sm:text-2xl font-black text-white">
                                {{ $myChildHighlight['full_name'] }}
                            </h2>

                            <p class="text-xs text-indigo-200">
                                Fantastic progress! Completed {{ $myChildHighlight['evaluations_count'] }} learning assessments with {{ $myChildHighlight['passed_count'] }} passed.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 sm:gap-6 shrink-0 bg-white/10 p-4 rounded-xl border border-white/10 backdrop-blur-xs justify-around md:justify-end">
                        <div class="text-center px-2">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-200">Rank</div>
                            <div class="text-2xl sm:text-3xl font-black text-amber-300">
                                #{{ $myChildHighlight['rank'] }}
                            </div>
                        </div>

                        <div class="w-px h-10 bg-white/20"></div>

                        <div class="text-center px-2">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-200">Course XP</div>
                            <div class="text-2xl sm:text-3xl font-black text-white">
                                {{ number_format($myChildHighlight['course_xp']) }}
                            </div>
                        </div>

                        <div class="w-px h-10 bg-white/20"></div>

                        <div class="text-center px-2">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-200">Avg Score</div>
                            <div class="text-2xl sm:text-3xl font-black text-emerald-300">
                                {{ $myChildHighlight['avg_score'] }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- TOP 3 PODIUM                                                 --}}
        {{-- ============================================================ --}}
        @if (count($leaderboardRows) >= 3)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- 2nd Place --}}
                @php $second = $leaderboardRows[1] ?? null; @endphp
                @if ($second)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 flex flex-col items-center text-center shadow-xs order-2 md:order-1 {{ $second['is_my_child'] ? 'ring-2 ring-indigo-500' : '' }}">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300 font-black text-sm mb-3 border border-slate-300 dark:border-slate-600">
                            #2
                        </div>
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-white font-black text-xl mb-2">
                            @if ($second['avatar'])
                                <img src="{{ $second['avatar'] }}" alt="{{ $second['display_name'] }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ strtoupper(substr($second['display_name'], 0, 1)) }}</span>
                            @endif
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">
                            {{ $second['display_name'] }}
                            @if ($second['is_my_child'])
                                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-extrabold">(Your Child)</span>
                            @endif
                        </h4>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $second['kid_tier']['name'] }}
                        </span>
                        <div class="mt-3 flex items-center gap-3 text-xs font-bold">
                            <span class="text-amber-500">{{ number_format($second['course_xp']) }} XP</span>
                            <span class="text-slate-300 dark:text-slate-600">·</span>
                            <span class="text-emerald-600 dark:text-emerald-400">{{ $second['avg_score'] }}% Avg</span>
                        </div>
                    </div>
                @endif

                {{-- 1st Place --}}
                @php $first = $leaderboardRows[0] ?? null; @endphp
                @if ($first)
                    <div class="bg-gradient-to-b from-amber-50 to-white dark:from-amber-950/30 dark:to-slate-800 rounded-2xl border-2 border-amber-300 dark:border-amber-700/60 p-6 flex flex-col items-center text-center shadow-md order-1 md:order-2 {{ $first['is_my_child'] ? 'ring-2 ring-indigo-500' : '' }}">
                        <div class="w-12 h-12 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center font-black text-base mb-3 shadow-sm">
                            <x-heroicon-o-trophy class="w-6 h-6 text-slate-950" />
                        </div>
                        <div class="w-20 h-20 rounded-full overflow-hidden bg-amber-200 dark:bg-amber-900 flex items-center justify-center text-amber-900 dark:text-amber-200 font-black text-2xl mb-2 border-2 border-amber-400 shadow-xs">
                            @if ($first['avatar'])
                                <img src="{{ $first['avatar'] }}" alt="{{ $first['display_name'] }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ strtoupper(substr($first['display_name'], 0, 1)) }}</span>
                            @endif
                        </div>
                        <h4 class="font-black text-slate-900 dark:text-white text-base">
                            {{ $first['display_name'] }}
                            @if ($first['is_my_child'])
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-extrabold">(Your Child)</span>
                            @endif
                        </h4>
                        <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 mt-0.5">
                            {{ $first['kid_tier']['name'] }}
                        </span>
                        <div class="mt-4 flex items-center gap-3 text-xs sm:text-sm font-extrabold">
                            <span class="text-amber-500">{{ number_format($first['course_xp']) }} XP</span>
                            <span class="text-slate-300 dark:text-slate-600">·</span>
                            <span class="text-emerald-600 dark:text-emerald-400">{{ $first['avg_score'] }}% Avg</span>
                        </div>
                    </div>
                @endif

                {{-- 3rd Place --}}
                @php $third = $leaderboardRows[2] ?? null; @endphp
                @if ($third)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 flex flex-col items-center text-center shadow-xs order-3 {{ $third['is_my_child'] ? 'ring-2 ring-indigo-500' : '' }}">
                        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 flex items-center justify-center font-black text-sm mb-3 border border-amber-200 dark:border-amber-800">
                            #3
                        </div>
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-white font-black text-xl mb-2">
                            @if ($third['avatar'])
                                <img src="{{ $third['avatar'] }}" alt="{{ $third['display_name'] }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ strtoupper(substr($third['display_name'], 0, 1)) }}</span>
                            @endif
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">
                            {{ $third['display_name'] }}
                            @if ($third['is_my_child'])
                                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-extrabold">(Your Child)</span>
                            @endif
                        </h4>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $third['kid_tier']['name'] }}
                        </span>
                        <div class="mt-3 flex items-center gap-3 text-xs font-bold">
                            <span class="text-amber-500">{{ number_format($third['course_xp']) }} XP</span>
                            <span class="text-slate-300 dark:text-slate-600">·</span>
                            <span class="text-emerald-600 dark:text-emerald-400">{{ $third['avg_score'] }}% Avg</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- FULL LEADERBOARD TABLE                                       --}}
        {{-- ============================================================ --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                    <x-heroicon-o-bars-3-bottom-left class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                    <span>Cohort Standings ({{ count($leaderboardRows) }} Enrolled Students)</span>
                </h3>
                <span class="text-xs text-slate-400">Ranked by Course XP & Performance</span>
            </div>

            @if (empty($leaderboardRows))
                <div class="p-12 text-center space-y-3">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400">
                        <x-heroicon-o-academic-cap class="w-8 h-8" />
                    </div>
                    <div class="max-w-sm mx-auto space-y-1">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No Enrolled Students Yet</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Select a course above or enroll your child to view cohort rankings and activities.
                        </p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-750 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-700">
                            <tr>
                                <th class="py-3.5 px-4 w-16 text-center">Rank</th>
                                <th class="py-3.5 px-4">Student</th>
                                <th class="py-3.5 px-4">Milestone Tier</th>
                                <th class="py-3.5 px-4 text-center">Evaluations</th>
                                <th class="py-3.5 px-4 text-center">Average</th>
                                <th class="py-3.5 px-4 text-right">Course XP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                            @foreach ($leaderboardRows as $row)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-750/50 transition-colors {{ $row['is_my_child'] ? 'bg-indigo-50/50 dark:bg-indigo-950/20 font-semibold' : '' }}">
                                    {{-- Rank --}}
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($row['rank'] === 1)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-slate-950 font-black text-xs shadow-xs">1</span>
                                        @elseif ($row['rank'] === 2)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300 dark:bg-slate-600 text-slate-900 dark:text-white font-black text-xs">2</span>
                                        @elseif ($row['rank'] === 3)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-950 dark:text-amber-200 font-black text-xs">3</span>
                                        @else
                                            <span class="text-slate-400 font-bold">#{{ $row['rank'] }}</span>
                                        @endif
                                    </td>

                                    {{-- Student Display Name & Avatar --}}
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-700 dark:text-white font-black text-xs shrink-0">
                                                @if ($row['avatar'])
                                                    <img src="{{ $row['avatar'] }}" alt="{{ $row['display_name'] }}" class="w-full h-full object-cover">
                                                @else
                                                    <span>{{ strtoupper(substr($row['display_name'], 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                                    <span>{{ $row['display_name'] }}</span>
                                                    @if ($row['is_my_child'])
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-indigo-600 text-white">
                                                            Your Child
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Milestone Tier --}}
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold 
                                            @if ($row['kid_tier']['color'] === 'amber') bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                            @elseif ($row['kid_tier']['color'] === 'indigo') bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800
                                            @elseif ($row['kid_tier']['color'] === 'emerald') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                            @else bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600
                                            @endif"
                                        >
                                            <x-heroicon-o-sparkles class="w-3 h-3" />
                                            <span>{{ $row['kid_tier']['name'] }}</span>
                                        </span>
                                    </td>

                                    {{-- Evaluations --}}
                                    <td class="py-3.5 px-4 text-center text-slate-600 dark:text-slate-300">
                                        {{ $row['evaluations_count'] }} tasks
                                    </td>

                                    {{-- Average Score --}}
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="font-bold {{ $row['avg_score'] >= 75 ? 'text-emerald-600 dark:text-emerald-400' : ($row['avg_score'] >= 50 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500') }}">
                                            {{ $row['avg_score'] }}%
                                        </span>
                                    </td>

                                    {{-- Course XP --}}
                                    <td class="py-3.5 px-4 text-right">
                                        <span class="font-black text-amber-500 text-sm">
                                            {{ number_format($row['course_xp']) }} XP
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
