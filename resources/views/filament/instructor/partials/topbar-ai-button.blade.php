@php
    $isUnpinned = (bool) session('ai_unpinned_instructor', false);
    $isFullAiPage = request()->is('teach/ai-assistant*');
@endphp

<a
    href="{{ route('filament.instructor.pages.ai-assistant') }}"
    @if ($isUnpinned && ! $isFullAiPage)
        x-data
        x-on:click.prevent="$dispatch('toggle-portal-ai-floating')"
    @endif
    class="inline-flex items-center justify-center gap-1.5 p-2 sm:px-3 sm:py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold transition ml-2.5 sm:ml-0 mr-1 sm:mr-2 cursor-pointer shadow-2xs shrink-0"
    title="{{ $isUnpinned && ! $isFullAiPage ? 'Toggle Floating AI Co-Pilot' : 'Open Instructor AI Co-Pilot' }}"
    aria-label="Instructor AI Co-Pilot"
>
    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" />
    </svg>
    <span class="hidden sm:inline">AI Co-Pilot</span>
</a>

