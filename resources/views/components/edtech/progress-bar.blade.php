@props([
    'percentage' => 0,
    'color' => 'teal', // 'teal' | 'indigo' | 'rose' | 'amber' | 'blue'
    'height' => 'h-2', // 'h-1.5' | 'h-2' | 'h-2.5' | 'h-3'
    'showLabel' => false,
])

@php
    $clamped = max(0, min(100, (int) $percentage));

    $gradients = [
        'teal' => 'bg-gradient-to-r from-teal-500 to-emerald-400',
        'indigo' => 'bg-gradient-to-r from-indigo-500 to-purple-500',
        'rose' => 'bg-gradient-to-r from-rose-500 to-pink-500',
        'amber' => 'bg-gradient-to-r from-amber-500 to-orange-400',
        'blue' => 'bg-gradient-to-r from-sky-500 to-blue-600',
    ];

    $fillClass = $gradients[$color] ?? $gradients['teal'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full flex items-center gap-2']) }}>
    <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full {{ $height }} overflow-hidden">
        <div 
            class="{{ $fillClass }} {{ $height }} rounded-full transition-all duration-500 ease-out" 
            style="width: {{ $clamped }}%;"
        ></div>
    </div>
    @if ($showLabel)
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 min-w-[32px] text-right">
            {{ $clamped }}%
        </span>
    @endif
</div>
