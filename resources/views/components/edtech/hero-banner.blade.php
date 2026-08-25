@props([
    'category' => 'Active Course',
    'title' => '',
    'description' => '',
    'duration' => null,
    'ctaLabel' => 'Resume Learning',
    'ctaUrl' => '#',
    'secondaryLabel' => null,
    'secondaryUrl' => null,
    'students' => [],
    'accent' => 'teal', // 'teal' | 'indigo' | 'slate'
])

@php
    $bgStyles = [
        'teal' => 'bg-gradient-to-br from-teal-900 via-teal-800 to-emerald-900 text-white',
        'indigo' => 'bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white',
        'slate' => 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-white',
    ][$accent] ?? 'bg-gradient-to-br from-teal-900 via-teal-800 to-emerald-900 text-white';
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden rounded-2xl {$bgStyles} p-6 md:p-8 shadow-md border border-white/10"]) }}>
    {{-- Decorative Background Glow Circles --}}
    <div class="absolute -top-16 -right-16 w-56 h-56 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-12 w-64 h-64 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-3 max-w-2xl">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-white/15 text-teal-200 border border-white/20 backdrop-blur-xs">
                    {{ $category }}
                </span>
                @if ($duration)
                    <span class="inline-flex items-center text-xs font-medium text-slate-300">
                        <svg class="w-3.5 h-3.5 mr-1 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $duration }}
                    </span>
                @endif
            </div>

            <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight leading-snug">
                {{ $title }}
            </h2>

            @if ($description)
                <p class="text-sm text-slate-200/90 leading-relaxed line-clamp-2">
                    {{ $description }}
                </p>
            @endif

            @if (!empty($students) && count($students) > 0)
                <div class="flex items-center gap-2 pt-1">
                    <x-edtech.avatar-group :users="$students" size="sm" :limit="4" />
                    <span class="text-xs text-slate-300 font-medium">enrolled in this cohort</span>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
            @if ($secondaryLabel && $secondaryUrl)
                <a 
                    href="{{ $secondaryUrl }}" 
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-full text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition-colors backdrop-blur-xs"
                >
                    {{ $secondaryLabel }}
                </a>
            @endif

            <a 
                href="{{ $ctaUrl }}" 
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full text-xs font-extrabold text-slate-900 bg-white hover:bg-slate-100 shadow-sm hover:shadow-md transition-all duration-150 transform hover:-translate-y-0.5"
            >
                <span>{{ $ctaLabel }}</span>
                <svg class="w-4 h-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </div>
</div>
