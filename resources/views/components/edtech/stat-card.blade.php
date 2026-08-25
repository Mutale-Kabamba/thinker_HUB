@props([
    'title' => '',
    'value' => '0',
    'delta' => null,
    'deltaType' => 'positive', // 'positive' | 'negative' | 'neutral'
    'subtitle' => '',
    'icon' => null,
    'color' => 'teal', // 'teal' | 'indigo' | 'rose' | 'amber' | 'sky'
    'href' => null,
    'sparkline' => null, // array of numbers e.g. [30, 45, 32, 60, 50, 75, 80] or type 'bar' / 'line'
])

@php
    $colorMap = [
        'teal' => [
            'bg' => 'bg-emerald-50/60 dark:bg-emerald-950/30',
            'icon_bg' => 'bg-emerald-100 dark:bg-emerald-900/50',
            'icon_text' => 'text-emerald-600 dark:text-emerald-400',
            'badge_bg' => 'bg-emerald-50 dark:bg-emerald-900/40',
            'badge_text' => 'text-emerald-700 dark:text-emerald-300',
            'badge_border' => 'border-emerald-200/60 dark:border-emerald-800/40',
            'stroke' => '#0d9488',
            'fill' => 'rgba(13, 148, 136, 0.12)',
        ],
        'indigo' => [
            'bg' => 'bg-indigo-50/60 dark:bg-indigo-950/30',
            'icon_bg' => 'bg-indigo-100 dark:bg-indigo-900/50',
            'icon_text' => 'text-indigo-600 dark:text-indigo-400',
            'badge_bg' => 'bg-indigo-50 dark:bg-indigo-900/40',
            'badge_text' => 'text-indigo-700 dark:text-indigo-300',
            'badge_border' => 'border-indigo-200/60 dark:border-indigo-800/40',
            'stroke' => '#6366f1',
            'fill' => 'rgba(99, 102, 241, 0.12)',
        ],
        'sky' => [
            'bg' => 'bg-sky-50/60 dark:bg-sky-950/30',
            'icon_bg' => 'bg-sky-100 dark:bg-sky-900/50',
            'icon_text' => 'text-sky-600 dark:text-sky-400',
            'badge_bg' => 'bg-sky-50 dark:bg-sky-900/40',
            'badge_text' => 'text-sky-700 dark:text-sky-300',
            'badge_border' => 'border-sky-200/60 dark:border-sky-800/40',
            'stroke' => '#0284c7',
            'fill' => 'rgba(2, 132, 199, 0.12)',
        ],
        'amber' => [
            'bg' => 'bg-amber-50/60 dark:bg-amber-950/30',
            'icon_bg' => 'bg-amber-100 dark:bg-amber-900/50',
            'icon_text' => 'text-amber-600 dark:text-amber-400',
            'badge_bg' => 'bg-amber-50 dark:bg-amber-900/40',
            'badge_text' => 'text-amber-700 dark:text-amber-300',
            'badge_border' => 'border-amber-200/60 dark:border-amber-800/40',
            'stroke' => '#d97706',
            'fill' => 'rgba(217, 119, 6, 0.12)',
        ],
        'rose' => [
            'bg' => 'bg-rose-50/60 dark:bg-rose-950/30',
            'icon_bg' => 'bg-rose-100 dark:bg-rose-900/50',
            'icon_text' => 'text-rose-600 dark:text-rose-400',
            'badge_bg' => 'bg-rose-50 dark:bg-rose-900/40',
            'badge_text' => 'text-rose-700 dark:text-rose-300',
            'badge_border' => 'border-rose-200/60 dark:border-rose-800/40',
            'stroke' => '#e11d48',
            'fill' => 'rgba(225, 29, 72, 0.12)',
        ],
    ];

    $scheme = $colorMap[$color] ?? $colorMap['teal'];
@endphp

<div {{ $attributes->merge(['class' => 'edtech-stat-card group relative bg-white dark:bg-slate-900/90 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden']) }}>
    {{-- Top Row: Title & Details Link / Icon --}}
    <div class="flex items-center justify-between gap-2 mb-3">
        <span class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
            {{ $title }}
        </span>

        @if ($href)
            <a href="{{ $href }}" class="inline-flex items-center text-xs font-semibold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 transition-colors">
                View details
                <svg class="w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @elseif ($icon)
            <div class="w-8 h-8 rounded-xl {{ $scheme['icon_bg'] }} {{ $scheme['icon_text'] }} flex items-center justify-center text-sm shadow-xs">
                {!! $icon !!}
            </div>
        @endif
    </div>

    {{-- Middle: Large Number & Sparkline / Chart --}}
    <div class="flex items-end justify-between gap-3">
        <div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ $value }}
            </div>
            
            @if ($subtitle || $delta)
                <div class="flex items-center gap-2 mt-2">
                    @if ($delta)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $scheme['badge_bg'] }} {{ $scheme['badge_text'] }} {{ $scheme['badge_border'] }}">
                            @if ($deltaType === 'positive')
                                <svg class="w-3 h-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            @elseif ($deltaType === 'negative')
                                <svg class="w-3 h-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            @endif
                            {{ $delta }}
                        </span>
                    @endif

                    @if ($subtitle)
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            {{ $subtitle }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- SVG Sparkline --}}
        <div class="w-20 h-10 flex-shrink-0">
            @if (is_array($sparkline) && count($sparkline) > 1)
                @php
                    $min = min($sparkline);
                    $max = max($sparkline);
                    $range = ($max - $min) ?: 1;
                    $points = [];
                    $count = count($sparkline);
                    foreach ($sparkline as $i => $val) {
                        $x = ($i / ($count - 1)) * 76 + 2;
                        $y = 36 - (($val - $min) / $range) * 28;
                        $points[] = round($x, 1) . ',' . round($y, 1);
                    }
                    $polylinePoints = implode(' ', $points);
                    $firstX = explode(',', $points[0])[0];
                    $lastX = explode(',', end($points))[0];
                    $polygonPoints = "$polylinePoints $lastX,38 $firstX,38";
                @endphp
                <svg viewBox="0 0 80 40" class="w-full h-full overflow-visible">
                    <polygon points="{{ $polygonPoints }}" fill="{{ $scheme['fill'] }}" />
                    <polyline points="{{ $polylinePoints }}" fill="none" stroke="{{ $scheme['stroke'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            @else
                {{-- Decorative Default Sparkline Wave --}}
                <svg viewBox="0 0 80 40" class="w-full h-full overflow-visible">
                    <path d="M2,28 C15,28 20,10 35,16 C48,22 55,6 78,8" fill="none" stroke="{{ $scheme['stroke'] }}" stroke-width="2.2" stroke-linecap="round" />
                    <path d="M2,28 C15,28 20,10 35,16 C48,22 55,6 78,8 L78,38 L2,38 Z" fill="{{ $scheme['fill'] }}" />
                </svg>
            @endif
        </div>
    </div>
</div>
