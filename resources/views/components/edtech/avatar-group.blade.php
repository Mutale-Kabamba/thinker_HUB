@props([
    'users' => [], // Collection or array of users or avatar URLs
    'limit' => 3,
    'size' => 'md', // 'sm' | 'md' | 'lg'
    'overflowCount' => null,
])

@php
    $sizeClasses = [
        'sm' => 'w-6 h-6 text-[10px]',
        'md' => 'w-8 h-8 text-xs',
        'lg' => 'w-10 h-10 text-sm',
    ][$size] ?? 'w-8 h-8 text-xs';

    $items = collect($users);
    $visible = $items->take($limit);
    $extra = $overflowCount ?? ($items->count() > $limit ? ($items->count() - $limit) : null);
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center -space-x-2 overflow-hidden py-0.5']) }}>
    @foreach ($visible as $user)
        @php
            $photo = is_object($user) ? ($user->profile_photo_path ?? null) : ($user['avatar'] ?? null);
            $name = is_object($user) ? ($user->name ?? 'Student') : ($user['name'] ?? 'Student');
            $initial = strtoupper(substr($name, 0, 1));
            $avatarUrl = $photo ? \App\Support\PublicDiskPath::url($photo) : null;
        @endphp

        @if ($avatarUrl)
            <img 
                src="{{ $avatarUrl }}" 
                alt="{{ $name }}" 
                title="{{ $name }}"
                class="inline-block {{ $sizeClasses }} rounded-full ring-2 ring-white dark:ring-slate-900 object-cover shadow-xs" 
            />
        @else
            <div 
                title="{{ $name }}"
                class="inline-flex items-center justify-center {{ $sizeClasses }} rounded-full ring-2 ring-white dark:ring-slate-900 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold shadow-xs select-none"
            >
                {{ $initial }}
            </div>
        @endif
    @endforeach

    @if ($extra && $extra > 0)
        <div class="inline-flex items-center justify-center {{ $sizeClasses }} rounded-full ring-2 ring-white dark:ring-slate-900 bg-slate-900 dark:bg-slate-800 text-white font-bold tracking-tight shadow-xs select-none">
            +{{ $extra }}
        </div>
    @endif
</div>
