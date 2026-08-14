@php
    $panelId = filament()->getCurrentPanel()?->getId();
    $user = auth()->user();

    $roleInfo = match ($panelId) {
        'admin' => [
            'label' => 'Admin Workspace',
            'short' => 'Admin',
            'icon' => 'fa-shield-halved',
            'color' => '#0f766e',
            'bg' => '#ccfbf1',
            'border' => '#99f6e4',
        ],
        'instructor' => [
            'label' => 'Instructor Workspace',
            'short' => 'Instructor',
            'icon' => 'fa-chalkboard-user',
            'color' => '#047857',
            'bg' => '#d1fae5',
            'border' => '#a7f3d0',
        ],
        'student' => [
            'label' => 'Student Portal',
            'short' => 'Student',
            'icon' => 'fa-graduation-cap',
            'color' => '#0369a1',
            'bg' => '#e0f2fe',
            'border' => '#bae6fd',
        ],
        'contributor' => match ($user?->role) {
            'blogger' => [
                'label' => 'Blogger Workspace',
                'short' => 'Blogger',
                'icon' => 'fa-pen-nib',
                'color' => '#7c3aed',
                'bg' => '#ede9fe',
                'border' => '#ddd6fe',
            ],
            'researcher' => [
                'label' => 'Researcher Workspace',
                'short' => 'Researcher',
                'icon' => 'fa-flask-vial',
                'color' => '#b45309',
                'bg' => '#fef3c7',
                'border' => '#fde68a',
            ],
            'employer' => [
                'label' => 'Employer Workspace',
                'short' => 'Employer',
                'icon' => 'fa-briefcase',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
                'border' => '#bae6fd',
            ],
            default => [
                'label' => 'Contributor Portal',
                'short' => 'Contributor',
                'icon' => 'fa-sparkles',
                'color' => '#0f766e',
                'bg' => '#ccfbf1',
                'border' => '#99f6e4',
            ],
        },
        default => [
            'label' => ucfirst((string) $panelId) . ' Portal',
            'short' => ucfirst((string) $panelId),
            'icon' => 'fa-user',
            'color' => '#374151',
            'bg' => '#f3f4f6',
            'border' => '#e5e7eb',
        ],
    };

    $position = $position ?? 'topbar';
@endphp

@if ($position === 'sidebar')
    <div class="hub-sidebar-badge-container">
        <div class="hub-sidebar-badge" style="background: {{ $roleInfo['bg'] }}; color: {{ $roleInfo['color'] }}; border: 1px solid {{ $roleInfo['border'] }};">
            <i class="fa-solid {{ $roleInfo['icon'] }}"></i>
            <span>{{ $roleInfo['label'] }}</span>
        </div>
    </div>
@else
    <div class="hub-topbar-badge" style="background: {{ $roleInfo['bg'] }}; color: {{ $roleInfo['color'] }}; border: 1px solid {{ $roleInfo['border'] }};">
        <i class="fa-solid {{ $roleInfo['icon'] }}"></i>
        <span>{{ $roleInfo['label'] }}</span>
    </div>
@endif
