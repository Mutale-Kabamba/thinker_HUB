@php
    $user = auth()->user();
    if (! $user || (! $user->isInstructor() && ! $user->isAdmin())) {
        return;
    }

    $reminderService = app(\App\Services\InstructorReminderService::class);
    $reminders = $reminderService->getRemindersData($user);
    $pendingCount = (int) ($reminders['total_actionable'] ?? 0);
    $todayCount = (int) ($reminders['today_sessions_count'] ?? 0);
    $hasOverdue = (bool) ($reminders['has_overdue'] ?? false);
@endphp

<div class="hub-topbar-reminders-wrapper" style="display: inline-flex; align-items: center; margin-right: 0.45rem;">
    <button
        type="button"
        onclick="window.dispatchEvent(new CustomEvent('open-instructor-reminders'))"
        class="hub-topbar-reminder-btn group relative inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer border {{ $pendingCount > 0 ? ($hasOverdue ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-300 dark:border-rose-800/60' : 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:hover:bg-amber-900/60 dark:text-amber-300 dark:border-amber-800/60') : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}"
        title="{{ $pendingCount > 0 ? "{$pendingCount} pending items require attention (Register Marking / Assignments)" : "All class registers and assignments are caught up" }}"
        style="text-decoration: none;"
    >
        {{-- Animated Ping Dot if pending items exist --}}
        @if ($pendingCount > 0)
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $hasOverdue ? 'bg-rose-500' : 'bg-amber-500' }}"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 {{ $hasOverdue ? 'bg-rose-600' : 'bg-amber-500' }}"></span>
            </span>
        @else
            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        @endif

        {{-- Icon --}}
        <svg class="w-3.5 h-3.5 {{ $pendingCount > 0 ? ($hasOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400') : 'text-slate-500 dark:text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>

        {{-- Label --}}
        <span class="hidden sm:inline">Tasks</span>

        {{-- Count Badge --}}
        @if ($pendingCount > 0)
            <span class="inline-flex items-center justify-center px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $hasOverdue ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white' }}">
                {{ $pendingCount }}
            </span>
        @endif
    </button>
</div>
