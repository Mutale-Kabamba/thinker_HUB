<x-filament-widgets::widget>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Recent Platform Activities
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Live system audit events, registrations, and submissions.
                </p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                Audit Stream
            </span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($activities as $activity)
                <div class="py-3.5 flex items-start justify-between gap-3 first:pt-1 last:pb-1">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 dark:bg-teal-950/60 dark:text-teal-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                {{ $activity['event'] }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $activity['meta'] }}
                            </p>
                        </div>
                    </div>
                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 whitespace-nowrap flex-shrink-0">
                        {{ optional($activity['time'])->diffForHumans() }}
                    </span>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-slate-400">
                    No recent activities recorded yet.
                </div>
            @endforelse
        </div>
    </div>
</x-filament-widgets::widget>
