<x-filament-widgets::widget>
    <div 
        x-data="{ isCollapsed: false }"
        class="bg-white dark:bg-[#111b21] rounded-2xl border border-gray-200/80 dark:border-gray-800/80 shadow-sm overflow-hidden transition-colors"
    >
        <!-- Widget Header with Toggle Button -->
        <div 
            @click="isCollapsed = !isCollapsed"
            class="px-5 py-4 flex items-center justify-between cursor-pointer select-none hover:bg-gray-50/50 dark:hover:bg-[#16222a] transition-colors border-b border-gray-100 dark:border-gray-800/60"
        >
            <div class="space-y-0.5">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>Recent Platform Activities</span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Live system audit events, registrations, and submissions.</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Audit Stream
                </span>

                <!-- Chevron Icon -->
                <button 
                    type="button" 
                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-transform duration-200"
                    :class="{ 'rotate-180': isCollapsed }"
                    aria-label="Toggle Recent Activities"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Collapsible Activities Body -->
        <div 
            x-show="!isCollapsed" 
            x-collapse
            class="divide-y divide-gray-100 dark:divide-gray-800/60 max-h-[420px] overflow-y-auto"
        >
            @forelse($activities as $activity)
                <div class="px-5 py-3.5 flex items-center justify-between gap-3 hover:bg-gray-50 dark:hover:bg-[#16222a]/50 transition-colors">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <!-- Event Icon -->
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">
                                {{ is_array($activity) ? ($activity['event'] ?? $activity['title'] ?? '') : ($activity->title ?? $activity->description ?? '') }}
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ is_array($activity) ? ($activity['meta'] ?? $activity['description'] ?? 'System Event') : ($activity->subject_name ?? $activity->causer?->name ?? 'System Event') }}
                            </p>
                        </div>
                    </div>

                    <!-- Timestamp -->
                    <span class="text-[11px] font-medium text-gray-400 flex-shrink-0">
                        @if (is_array($activity))
                            {{ isset($activity['time']) && $activity['time'] instanceof \Carbon\CarbonInterface ? $activity['time']->diffForHumans() : '' }}
                        @else
                            {{ $activity->created_at?->diffForHumans() ?? '' }}
                        @endif
                    </span>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-gray-400">
                    No recent activities recorded.
                </div>
            @endforelse
        </div>
    </div>
</x-filament-widgets::widget>
