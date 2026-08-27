@php
    $panelId = filament()->getCurrentPanel()?->getId();
    $user = auth()->user();

    $roleInfo = match ($panelId) {
        'admin' => [
            'label' => 'Admin Workspace',
            'short' => 'Admin',
            'icon' => 'fa-shield-halved',
            'color' => '#0d9488',
            'bg' => 'rgba(13, 148, 136, 0.08)',
            'border' => 'rgba(13, 148, 136, 0.2)',
            'dot' => '#0d9488',
        ],
        'instructor' => [
            'label' => 'Instructor Workspace',
            'short' => 'Instructor',
            'icon' => 'fa-chalkboard-user',
            'color' => '#059669',
            'bg' => 'rgba(5, 150, 105, 0.08)',
            'border' => 'rgba(5, 150, 105, 0.2)',
            'dot' => '#10b981',
        ],
        'student' => [
            'label' => 'Student Portal',
            'short' => 'Student',
            'icon' => 'fa-graduation-cap',
            'color' => '#0284c7',
            'bg' => 'rgba(2, 132, 199, 0.08)',
            'border' => 'rgba(2, 132, 199, 0.2)',
            'dot' => '#0ea5e9',
        ],
        'contributor' => match ($user?->role) {
            'blogger' => [
                'label' => 'Blogger Workspace',
                'short' => 'Blogger',
                'icon' => 'fa-pen-nib',
                'color' => '#7c3aed',
                'bg' => 'rgba(124, 58, 237, 0.08)',
                'border' => 'rgba(124, 58, 237, 0.2)',
                'dot' => '#8b5cf6',
            ],
            'researcher' => [
                'label' => 'Researcher Workspace',
                'short' => 'Researcher',
                'icon' => 'fa-flask-vial',
                'color' => '#d97706',
                'bg' => 'rgba(217, 119, 6, 0.08)',
                'border' => 'rgba(217, 119, 6, 0.2)',
                'dot' => '#f59e0b',
            ],
            'employer' => [
                'label' => 'Employer Workspace',
                'short' => 'Employer',
                'icon' => 'fa-briefcase',
                'color' => '#0284c7',
                'bg' => 'rgba(2, 132, 199, 0.08)',
                'border' => 'rgba(2, 132, 199, 0.2)',
                'dot' => '#0ea5e9',
            ],
            default => [
                'label' => 'Contributor Portal',
                'short' => 'Contributor',
                'icon' => 'fa-sparkles',
                'color' => '#0d9488',
                'bg' => 'rgba(13, 148, 136, 0.08)',
                'border' => 'rgba(13, 148, 136, 0.2)',
                'dot' => '#0d9488',
            ],
        },
        default => [
            'label' => ucfirst((string) $panelId) . ' Portal',
            'short' => ucfirst((string) $panelId),
            'icon' => 'fa-user',
            'color' => '#475569',
            'bg' => 'rgba(71, 85, 105, 0.08)',
            'border' => 'rgba(71, 85, 105, 0.2)',
            'dot' => '#64748b',
        ],
    };

    $position = $position ?? 'topbar';
@endphp

@if ($position === 'sidebar')
    <div class="hub-sidebar-badge-wrapper my-1 px-1">
        <div class="hub-sidebar-badge flex items-center justify-center gap-2 w-full py-1.5 px-3 rounded-lg text-xs font-semibold tracking-wide transition-all shadow-2xs"
             style="background: {{ $roleInfo['bg'] }}; color: {{ $roleInfo['color'] }}; border: 1px solid {{ $roleInfo['border'] }};">
            <span class="hub-badge-dot inline-block w-1.5 h-1.5 rounded-full animate-pulse"
                  style="background: {{ $roleInfo['dot'] ?? $roleInfo['color'] }}; box-shadow: 0 0 6px {{ $roleInfo['dot'] ?? $roleInfo['color'] }};"></span>
            <i class="fa-solid {{ $roleInfo['icon'] }} text-[11px]"></i>
            <span class="truncate">{{ $roleInfo['label'] }}</span>
        </div>
        <button 
            type="button"
            x-data="{}"
            x-on:click="$store.sidebar.isOpen = false; $store.sidebar.close(); document.querySelectorAll('.fi-sidebar, .fi-main-sidebar').forEach(el => el.classList.remove('fi-sidebar-open'))"
            class="hub-sidebar-mobile-close lg:hidden fixed top-3 right-3 z-9999 flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white border border-slate-200/80 dark:border-slate-700/80 shadow-xs cursor-pointer transition-all active:scale-95"
            aria-label="Close navigation"
            title="Close navigation"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@else
    <div class="hub-topbar-badge flex items-center gap-1.5 py-1 px-2.5 rounded-lg text-xs font-semibold tracking-wide shadow-2xs"
         style="background: {{ $roleInfo['bg'] }}; color: {{ $roleInfo['color'] }}; border: 1px solid {{ $roleInfo['border'] }};">
        <span class="hub-badge-dot inline-block w-1.5 h-1.5 rounded-full"
              style="background: {{ $roleInfo['dot'] ?? $roleInfo['color'] }};"></span>
        <i class="fa-solid {{ $roleInfo['icon'] }} text-[10px]"></i>
        <span>{{ $roleInfo['label'] }}</span>
    </div>
@endif
