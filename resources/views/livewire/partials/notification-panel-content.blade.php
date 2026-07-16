{{-- Header --}}
<div style="
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0.65rem 0.85rem;
    border-bottom:1px solid var(--hub-border);
">
    <span style="font-weight:700;font-size:0.85rem;color:var(--hub-ink);">
        Notifications
        @if ($this->unreadCount > 0)
            <span style="
                display:inline-flex;
                align-items:center;
                justify-content:center;
                min-width:18px;
                height:18px;
                border-radius:999px;
                background:#0f766e;
                color:#fff;
                font-size:0.62rem;
                font-weight:800;
                padding:0 4px;
                margin-left:4px;
            ">{{ $this->unreadCount }}</span>
        @endif
    </span>
    <div style="display:flex;gap:0.5rem;">
        @if ($this->unreadCount > 0)
            <button
                wire:click="markAllAsRead"
                type="button"
                style="background:none;border:none;cursor:pointer;font-size:0.72rem;color:var(--hub-primary);font-weight:600;"
            >Mark all read</button>
        @endif
        @if ($this->notifications->count() > 0)
            <button
                wire:click="clearAll"
                type="button"
                style="background:none;border:none;cursor:pointer;font-size:0.72rem;color:var(--hub-danger);font-weight:600;"
            >Clear all</button>
        @endif
    </div>
</div>

{{-- Notification List --}}
<div style="overflow-y:auto;flex:1;">
    @forelse ($this->notifications as $notification)
        @php
            $data = $notification->data;
            $isUnread = is_null($notification->read_at);
        @endphp
        <div
            wire:click="markAsRead('{{ $notification->id }}')"
            style="
                padding:0.6rem 0.85rem;
                border-bottom:1px solid var(--hub-border);
                cursor:pointer;
                transition:background 0.15s;
                {{ $isUnread ? 'background:var(--hub-primary-soft);' : '' }}
            "
            @if($isUnread)
                onmouseenter="this.style.background='var(--hub-surface-soft)'"
                onmouseleave="this.style.background='var(--hub-primary-soft)'"
            @else
                onmouseenter="this.style.background='var(--hub-surface-soft)'"
                onmouseleave="this.style.background=''"
            @endif
        >
            <div style="display:flex;align-items:flex-start;gap:0.5rem;">
                @if ($isUnread)
                    <span style="
                        flex-shrink:0;
                        width:8px;
                        height:8px;
                        border-radius:50%;
                        background:#0f766e;
                        margin-top:5px;
                    "></span>
                @else
                    <span style="flex-shrink:0;width:8px;height:8px;margin-top:5px;"></span>
                @endif
                <div style="min-width:0;flex:1;">
                    <p style="margin:0;font-size:0.78rem;font-weight:{{ $isUnread ? '700' : '500' }};color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $data['title'] ?? 'Notification' }}
                    </p>
                    <p style="margin:0.15rem 0 0;font-size:0.72rem;color:var(--hub-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $data['message'] ?? '' }}
                    </p>
                    <p style="margin:0.2rem 0 0;font-size:0.65rem;color:var(--hub-muted);">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>
    @empty
        <div style="padding:2rem 1rem;text-align:center;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:2.5rem;height:2.5rem;color:var(--hub-muted);margin:0 auto 0.5rem;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <p style="margin:0;font-size:0.8rem;color:var(--hub-muted);font-weight:600;">No notifications yet</p>
            <p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--hub-muted);">You're all caught up!</p>
        </div>
    @endforelse
</div>
