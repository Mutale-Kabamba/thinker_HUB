<div class="hub-top-bar-group">
    <form method="GET" action="{{ $action }}" class="hub-top-search-form">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search everything..."
            class="hub-top-search"
        >
    </form>
    <livewire:notification-bell />
</div>
