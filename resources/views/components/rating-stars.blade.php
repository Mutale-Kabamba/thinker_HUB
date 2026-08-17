@props(['rating' => 0, 'count' => null, 'size' => 'sm', 'showText' => true])

@php
    $numRating = (float) $rating;
    $fullStars = (int) floor($numRating);
    $halfStar = ($numRating - $fullStars) >= 0.3;
    $emptyStars = max(0, 5 - $fullStars - ($halfStar ? 1 : 0));
    $starSize = match($size) {
        'xs' => 'w-3 h-3',
        'lg' => 'w-5 h-5',
        'xl' => 'w-6 h-6',
        default => 'w-4 h-4',
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>
    <div class="flex items-center text-amber-400">
        {{-- Full Stars --}}
        @for ($i = 0; $i < $fullStars; $i++)
            <svg class="{{ $starSize }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        @endfor

        {{-- Half Star --}}
        @if ($halfStar)
            <svg class="{{ $starSize }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" opacity="0.45"/>
            </svg>
        @endif

        {{-- Empty Stars --}}
        @for ($i = 0; $i < $emptyStars; $i++)
            <svg class="{{ $starSize }} text-slate-300 dark:text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        @endfor
    </div>

    @if ($showText)
        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs ml-1">{{ number_format($numRating, 1) }}</span>
        @if ($count !== null)
            <span class="text-slate-500 dark:text-slate-400 text-xs">({{ number_format((int)$count) }})</span>
        @endif
    @endif
</div>
