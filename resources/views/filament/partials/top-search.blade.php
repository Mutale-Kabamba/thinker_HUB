<div class="hub-top-bar-group" style="display: flex; align-items: center; gap: 0.6rem;">
    @include('filament.partials.workspace-badge', ['position' => 'topbar'])

    @if (! empty($action))
        <form method="GET" action="{{ $action }}" class="hub-top-search-form">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search everything..."
                class="hub-top-search"
            >
        </form>
    @endif
</div>
