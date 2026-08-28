@props([
    'variant' => 'mint', // 'mint' | 'sky' | 'coral' | 'amber' | 'violet' | 'slate'
    'size' => 'md', // 'sm' | 'md'
    'dot' => false,
])

@php
    $styles = [
        'mint' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50 dot-emerald-500',
        'sky' => 'bg-sky-50 text-sky-700 border-sky-200/60 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800/50 dot-sky-500',
        'coral' => 'bg-rose-50 text-rose-700 border-rose-200/60 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50 dot-rose-500',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50 dot-amber-500',
        'violet' => 'bg-indigo-50 text-indigo-700 border-indigo-200/60 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/50 dot-indigo-500',
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dot-slate-400',
    ];

    $sizeClasses = [
        'sm' => 'text-[11px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-1',
    ][$size] ?? 'text-xs px-2.5 py-1';

    $style = $styles[$variant] ?? $styles['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-bold rounded-full border {$sizeClasses} {$style}"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80 animate-pulse"></span>
    @endif
    {{ $slot }}
</span>
