@php
    $portal = $portal ?? 'admin';
    $user = auth()->user();

    $canAccess = match ($portal) {
        'admin' => (bool) $user?->isAdmin(),
        'instructor' => (bool) ($user && ($user->isInstructor() || $user->isAdmin())),
        'student' => (bool) ($user && ($user->isStudent() || $user->isAdmin())),
        default => false,
    };

    $isFullAiPage = match ($portal) {
        'admin' => request()->is('manage/ai-copilot*'),
        'instructor' => request()->is('teach/ai-assistant*'),
        'student' => request()->is('learn/study-buddy*'),
        default => false,
    };

    $isUnpinned = (bool) session('ai_unpinned_' . $portal, false);
@endphp

@if ($canAccess && ! $isFullAiPage && $isUnpinned)
    @livewire(\App\Livewire\Ai\PortalFloatingChatWidget::class, ['portal' => $portal], key('floating-ai-' . $portal))
@endif

