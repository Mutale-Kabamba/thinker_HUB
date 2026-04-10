<form method="GET" action="{{ $action }}" class="hub-top-search-wrap">
    <input
        type="text"
        name="q"
        value="{{ request('q') }}"
        placeholder="Search dashboards..."
        class="hub-top-search"
    >
</form>
