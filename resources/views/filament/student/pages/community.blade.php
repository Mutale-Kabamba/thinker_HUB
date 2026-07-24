<x-filament-panels::page>

    <div class="hub-shell">

        {{-- My XP summary chip --}}
        <section style="padding:0.15rem 0 0;display:flex;justify-content:center;">
            <button type="button" wire:click="$set('tab','leaderboard')"
                style="display:inline-flex;align-items:center;gap:0.45rem;padding:0.28rem 0.8rem;border-radius:999px;border:1px solid var(--hub-border);background:rgba(255,255,255,.5);backdrop-filter:blur(8px);box-shadow:0 6px 16px rgba(15,23,42,.06);cursor:pointer;font-size:0.76rem;font-weight:600;color:var(--hub-ink);">
                <span style="font-size:0.9rem;">⚡</span>
                <span>{{ number_format($this->myXp['xp']) }} XP</span>
                <span style="color:var(--hub-muted);">·</span>
                <span>🏅 {{ $this->myXp['badge_count'] }} {{ Str::plural('badge', $this->myXp['badge_count']) }}</span>
                @if (count($this->myXp['badge_icons']) > 0)
                    <span style="letter-spacing:0.1em;">{{ implode('', $this->myXp['badge_icons']) }}</span>
                @endif
            </button>
        </section>

        {{-- Tabs --}}
        <section style="padding:0.15rem 0 0.35rem;">
            <div style="display:flex;justify-content:center;">
                <div style="display:flex;gap:0.35rem;max-width:460px;width:100%;padding:0.26rem;border-radius:999px;background:rgba(255,255,255,.5);backdrop-filter:blur(8px);border:1px solid var(--hub-border);box-shadow:0 8px 22px rgba(15,23,42,.07);">
                <button type="button" wire:click="$set('tab','chats')"
                    style="flex:1;padding:0.4rem 0.5rem;border-radius:999px;border:none;cursor:pointer;font-size:0.8rem;font-weight:700;letter-spacing:.01em;{{ $tab === 'chats' ? 'background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;box-shadow:0 8px 18px rgba(14,116,144,.26);' : 'background:transparent;color:var(--hub-ink);' }}">
                    Chats
                </button>
                <button type="button" wire:click="$set('tab','friends')"
                    style="flex:1;padding:0.4rem 0.5rem;border-radius:999px;border:none;cursor:pointer;font-size:0.8rem;font-weight:700;letter-spacing:.01em;{{ $tab === 'friends' ? 'background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;box-shadow:0 8px 18px rgba(14,116,144,.26);' : 'background:transparent;color:var(--hub-ink);' }}">
                    Friends
                    @if ($this->pendingRequests->count() > 0)
                        <span style="background:#dc2626;color:#fff;border-radius:999px;font-size:0.65rem;padding:0.05rem 0.4rem;margin-left:0.3rem;">{{ $this->pendingRequests->count() }}</span>
                    @endif
                </button>
                <button type="button" wire:click="$set('tab','leaderboard')"
                    style="flex:1;padding:0.4rem 0.5rem;border-radius:999px;border:none;cursor:pointer;font-size:0.8rem;font-weight:700;letter-spacing:.01em;{{ $tab === 'leaderboard' ? 'background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;box-shadow:0 8px 18px rgba(14,116,144,.26);' : 'background:transparent;color:var(--hub-ink);' }}">
                    🏆 Leaderboard
                </button>
                </div>
            </div>
        </section>

        {{-- ===================== FRIENDS TAB ===================== --}}
        @if ($tab === 'friends')
            @php $directory = $this->directory; @endphp
            <section style="padding:0.35rem 0.35rem 0.6rem;">
                <div style="display:flex;justify-content:space-between;align-items:baseline;gap:0.5rem;margin:0 0 0.5rem;">
                    <h3 class="hub-title" style="font-size:0.95rem;margin:0;">Student directory</h3>
                    <span style="font-size:0.72rem;color:var(--hub-muted);">showing {{ $directory['shown'] }} of {{ $directory['total'] }}</span>
                </div>
                <input type="text" wire:model.live.debounce.300ms="directorySearch" placeholder="Filter by name…"
                    class="hub-input" style="width:100%;font-size:0.85rem;padding:0.45rem 0.6rem;">

                @if ($directory['rows']->count() > 0)
                    <div style="display:flex;flex-direction:column;gap:0.4rem;margin-top:0.6rem;">
                        @foreach ($directory['rows'] as $person)
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;padding:0.4rem 0.55rem;border:1px solid var(--hub-border);border-radius:0.5rem;">
                                <div style="min-width:0;flex:1;">
                                    <button type="button" wire:click="showProfile({{ $person['id'] }})"
                                        style="background:none;border:none;padding:0;cursor:pointer;font-size:0.85rem;font-weight:600;color:var(--hub-ink);text-align:left;">
                                        {{ $person['name'] }}
                                    </button>
                                    <div style="display:flex;align-items:center;gap:0.4rem;margin-top:0.12rem;font-size:0.72rem;color:var(--hub-muted);">
                                        @if ($person['shared_count'] > 0)
                                            <span title="{{ implode(' · ', $person['shared_courses']) }}"
                                                style="background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 14%);color:#0f766e;border-radius:999px;padding:0.04rem 0.45rem;font-weight:600;">
                                                📚 {{ $person['shared_count'] }} {{ Str::plural('course', $person['shared_count']) }} together
                                            </span>
                                        @endif
                                        <span>⚡ {{ number_format($person['xp']) }}</span>
                                        @if (count($person['badge_icons']) > 0)
                                            <span style="letter-spacing:0.08em;">{{ implode('', $person['badge_icons']) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div style="display:flex;gap:0.35rem;flex:0 0 auto;">
                                    @if ($person['friendship']['state'] === 'friends')
                                        <span style="font-size:0.72rem;color:#0f766e;font-weight:600;">Friends ✓</span>
                                    @elseif ($person['friendship']['state'] === 'sent')
                                        <span style="font-size:0.72rem;color:var(--hub-muted);">Request sent</span>
                                        <button type="button" wire:click="removeFriend({{ $person['id'] }})"
                                            style="font-size:0.74rem;padding:0.3rem 0.7rem;background:none;border:1px solid var(--hub-border);color:var(--hub-ink);border-radius:0.4rem;cursor:pointer;">Cancel</button>
                                    @elseif ($person['friendship']['state'] === 'incoming')
                                        <button type="button" wire:click="acceptRequest({{ $person['friendship']['friendship_id'] }})"
                                            style="font-size:0.74rem;padding:0.3rem 0.7rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.4rem;cursor:pointer;">Accept</button>
                                        <button type="button" wire:click="declineRequest({{ $person['friendship']['friendship_id'] }})"
                                            style="font-size:0.74rem;padding:0.3rem 0.7rem;background:none;border:1px solid var(--hub-border);color:var(--hub-ink);border-radius:0.4rem;cursor:pointer;">Decline</button>
                                    @else
                                        <button type="button" wire:click="sendRequest({{ $person['id'] }})"
                                            style="font-size:0.74rem;padding:0.3rem 0.7rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.4rem;cursor:pointer;">Add friend</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="hub-copy" style="color:var(--hub-muted);margin-top:0.5rem;font-size:0.82rem;">No students match.</p>
                @endif
            </section>

            {{-- Pending requests --}}
            @if ($this->pendingRequests->count() > 0)
                <section class="hub-card" style="padding:0.85rem 1rem;">
                    <h3 class="hub-title" style="font-size:0.95rem;margin:0 0 0.5rem;">Friend requests</h3>
                    <div style="display:flex;flex-direction:column;gap:0.4rem;">
                        @foreach ($this->pendingRequests as $req)
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;padding:0.4rem 0.55rem;border:1px solid var(--hub-border);border-radius:0.5rem;">
                                <button type="button" wire:click="showProfile({{ $req->requester?->id ?? 0 }})"
                                    style="background:none;border:none;padding:0;cursor:pointer;font-size:0.85rem;font-weight:600;color:var(--hub-ink);">{{ $req->requester?->name ?? 'Unknown' }}</button>
                                <div style="display:flex;gap:0.35rem;">
                                    <button type="button" wire:click="acceptRequest({{ $req->id }})"
                                        style="font-size:0.74rem;padding:0.3rem 0.7rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.4rem;cursor:pointer;">Accept</button>
                                    <button type="button" wire:click="declineRequest({{ $req->id }})"
                                        style="font-size:0.74rem;padding:0.3rem 0.7rem;background:none;border:1px solid var(--hub-border);color:var(--hub-ink);border-radius:0.4rem;cursor:pointer;">Decline</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Friends list --}}
            <section style="padding:0.35rem 0.35rem 0.6rem;">
                <h3 class="hub-title" style="font-size:0.95rem;margin:0 0 0.5rem;">My friends ({{ $this->friends->count() }})</h3>
                <div style="height:1px;background:var(--hub-border);margin:0 0 0.6rem;"></div>
                @if ($this->friends->count() === 0)
                    <p class="hub-copy" style="color:var(--hub-muted);font-size:0.82rem;">No friends yet. Search above to connect.</p>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:0.65rem;">
                        @foreach ($this->friends as $friend)
                            @php
                                $friendAvatar = $friend->getFilamentAvatarUrl();
                                $friendInitial = strtoupper(substr($friend->name, 0, 1));
                                $friendCourseCode = optional($friend->courses()->select('code')->first())->code;
                                $friendLevel = $friend->proficiency ?: $friend->track;
                            @endphp
                            <div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-start;gap:0.52rem;padding:0.72rem 0.55rem 0.82rem;border:none;border-bottom:1px solid var(--hub-border);border-radius:0;background:transparent;">
                                <div style="display:flex;align-items:center;justify-content:center;min-width:0;">
                                    @if ($friendAvatar)
                                        <img src="{{ $friendAvatar }}" alt="{{ $friend->name }}"
                                            style="width:2.4rem;height:2.4rem;border-radius:999px;object-fit:cover;border:1px solid var(--hub-border);flex:0 0 auto;">
                                    @else
                                        <span style="width:2.4rem;height:2.4rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:transparent;color:#ccfbf1;font-size:0.82rem;font-weight:700;flex:0 0 auto;border:1px solid color-mix(in oklab, var(--hub-border) 70%, #0f766e 30%);">{{ $friendInitial }}</span>
                                    @endif
                                </div>
                                <div style="text-align:center;max-width:100%;">
                                    <button type="button" wire:click="showProfile({{ $friend->id }})"
                                        style="background:none;border:none;padding:0;cursor:pointer;margin:0;font-size:0.84rem;font-weight:600;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">{{ $friend->name }}</button>
                                    <p style="margin:0.08rem 0 0;font-size:0.72rem;color:var(--hub-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $friendCourseCode ?: 'No course' }} | {{ $friendLevel ?: 'No level' }}</p>
                                </div>
                                <div style="display:flex;gap:0.4rem;justify-content:center;flex:0 0 auto;">
                                    <button type="button" wire:click="openDirect({{ $friend->id }})" title="Message"
                                        style="width:2rem;height:2rem;display:inline-flex;align-items:center;justify-content:center;background:transparent;color:#22d3ee;border:1px solid color-mix(in oklab, var(--hub-border) 62%, #22d3ee 38%);border-radius:999px;cursor:pointer;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    </button>
                                    <button type="button" wire:click="removeFriend({{ $friend->id }})" wire:confirm="Remove this friend?" title="Remove friend"
                                        style="width:2rem;height:2rem;display:inline-flex;align-items:center;justify-content:center;background:transparent;border:1px solid color-mix(in oklab, var(--hub-border) 70%, #ef4444 30%);color:#ef4444;border-radius:999px;cursor:pointer;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        {{-- ===================== LEADERBOARD TAB ===================== --}}
        @if ($tab === 'leaderboard')
            @php
                $leaderboard = $this->leaderboard;
                $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
            @endphp
            <section class="hub-card" style="padding:0.85rem 1rem;">
                <h3 class="hub-title" style="font-size:0.95rem;margin:0 0 0.5rem;">🏆 Leaderboard</h3>
                <p class="hub-copy" style="color:var(--hub-muted);font-size:0.76rem;margin:0 0 0.6rem;">Top students by XP — earn XP by passing quizzes, keeping streaks, and completing courses.</p>

                @if ($leaderboard['rows']->count() === 0)
                    <p class="hub-copy" style="color:var(--hub-muted);font-size:0.82rem;">No XP earned yet. Pass a quiz to get on the board!</p>
                @else
                    <div style="display:flex;flex-direction:column;gap:0.4rem;">
                        @foreach ($leaderboard['rows'] as $row)
                            @php $isMe = $row['user_id'] === auth()->id(); @endphp
                            <div style="display:flex;align-items:center;gap:0.55rem;padding:0.42rem 0.6rem;border-radius:0.5rem;border:1px solid {{ $isMe ? 'color-mix(in oklab, var(--hub-border) 40%, #0f766e 60%)' : 'var(--hub-border)' }};{{ $isMe ? 'background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 12%);' : '' }}">
                                <span style="min-width:1.9rem;text-align:center;font-size:0.85rem;font-weight:700;color:var(--hub-ink);">
                                    {{ $medals[$row['rank']] ?? '#'.$row['rank'] }}
                                </span>
                                <span style="flex:1;font-size:0.85rem;font-weight:{{ $isMe ? '700' : '500' }};color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $row['name'] }}{{ $isMe ? ' (you)' : '' }}
                                </span>
                                @if (count($row['badge_icons']) > 0)
                                    <span style="font-size:0.82rem;letter-spacing:0.08em;" title="{{ $row['badge_count'] }} {{ Str::plural('badge', $row['badge_count']) }}">{{ implode('', $row['badge_icons']) }}</span>
                                @endif
                                <span style="font-size:0.74rem;color:var(--hub-muted);">🏅 {{ $row['badge_count'] }}</span>
                                <span style="font-size:0.8rem;font-weight:700;color:#0f766e;min-width:3.6rem;text-align:right;">⚡ {{ number_format($row['xp']) }}</span>
                            </div>
                        @endforeach

                        @if ($leaderboard['viewer'])
                            @php $row = $leaderboard['viewer']; @endphp
                            <div style="border-top:1px dashed var(--hub-border);margin-top:0.25rem;padding-top:0.45rem;">
                                <div style="display:flex;align-items:center;gap:0.55rem;padding:0.42rem 0.6rem;border-radius:0.5rem;border:1px solid color-mix(in oklab, var(--hub-border) 40%, #0f766e 60%);background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 12%);">
                                    <span style="min-width:1.9rem;text-align:center;font-size:0.85rem;font-weight:700;color:var(--hub-ink);">#{{ $row['rank'] }}</span>
                                    <span style="flex:1;font-size:0.85rem;font-weight:700;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $row['name'] }} (you)</span>
                                    @if (count($row['badge_icons']) > 0)
                                        <span style="font-size:0.82rem;letter-spacing:0.08em;">{{ implode('', $row['badge_icons']) }}</span>
                                    @endif
                                    <span style="font-size:0.74rem;color:var(--hub-muted);">🏅 {{ $row['badge_count'] }}</span>
                                    <span style="font-size:0.8rem;font-weight:700;color:#0f766e;min-width:3.6rem;text-align:right;">⚡ {{ number_format($row['xp']) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        @endif

        {{-- ===================== CHATS TAB ===================== --}}
        @if ($tab === 'chats')
            <style>
                .community-chat-layout {
                    /* 14rem approximates top nav, page title, and tab switcher stack on small screens. */
                    --community-mobile-base-offset: 14rem;
                    --community-mobile-offset: calc(var(--community-mobile-base-offset) + env(safe-area-inset-bottom, 0px));
                    --community-desktop-height: 70vh;
                    --community-mobile-min-height: 24rem;
                    --community-deep-bg: #0f172a;
                    --community-active-text: #e2e8f0;
                    --community-head-surface-ratio: 88%;
                    --community-head-ink-ratio: 12%;
                    --community-composer-bg: #f8fafc;
                    --community-composer-border: #cbd5e1;
                    --community-composer-input: #0f172a;
                    --community-composer-placeholder: #64748b;
                    --community-composer-attach-bg: #ffffff;
                    --community-composer-attach-icon: #475569;
                }
                .dark .community-chat-layout {
                    --community-composer-bg: color-mix(in oklab, var(--hub-card) 70%, #0f172a 30%);
                    --community-composer-border: color-mix(in oklab, var(--hub-border) 75%, #334155 25%);
                    --community-composer-input: var(--hub-ink);
                    --community-composer-placeholder: #94a3b8;
                    --community-composer-attach-bg: color-mix(in oklab, var(--hub-card) 82%, #111827 18%);
                    --community-composer-attach-icon: var(--hub-muted);
                }
                .community-chat-layout { display:grid; grid-template-columns:minmax(210px,300px) 1fr; gap:0.75rem; align-items:start; }
                .community-room-list { padding:0.5rem; max-height:var(--community-desktop-height); overflow-y:auto; border-radius:1rem; }
                .community-thread { padding:0; display:flex; flex-direction:column; height:var(--community-desktop-height); border-radius:1rem; overflow:hidden; }
                .community-thread-head { padding:0.62rem 0.85rem; border-bottom:1px solid var(--hub-border); background:color-mix(in oklab, var(--hub-card) var(--community-head-surface-ratio), var(--community-deep-bg) var(--community-head-ink-ratio)); }
                .community-room-item { width:100%; text-align:left; padding:0.65rem 0.72rem; border:none; border-radius:0.85rem; cursor:pointer; margin-bottom:0.25rem; transition:all .12s ease; }
                .community-room-item-active { background:var(--community-deep-bg); color:var(--community-active-text); box-shadow:0 10px 22px rgba(2,6,23,.22); }
                .community-bubble { max-width:70%; }
                .community-composer-wrap { display:flex; gap:0.5rem; align-items:center; padding:0.34rem 0.4rem; border:1px solid var(--community-composer-border); border-radius:999px; background:var(--community-composer-bg); backdrop-filter:blur(10px); box-shadow:0 12px 28px rgba(2,6,23,.15); }
                .community-message-input { color:var(--community-composer-input) !important; }
                .community-message-input::placeholder { color:var(--community-composer-placeholder); opacity:1; }
                .community-attach-btn { border-color: var(--community-composer-border) !important; color: var(--community-composer-attach-icon) !important; background: var(--community-composer-attach-bg) !important; }
                .community-back-btn { display:none; }
                .community-back-btn:focus-visible { outline:2px solid #22d3ee; outline-offset:2px; }

                @media (max-width: 768px) {
                    .community-chat-layout { grid-template-columns:1fr; gap:0.55rem; }
                    .community-chat-layout[data-room-open="true"] .community-room-list { display:none; }
                    .community-chat-layout[data-room-open="false"] .community-thread { display:none; }
                    .community-room-list, .community-thread { height:calc(100vh - var(--community-mobile-offset)); max-height:none; min-height:var(--community-mobile-min-height); }
                    .community-thread-head { position:sticky; top:0; z-index:5; padding:0.72rem 0.75rem; }
                    .community-bubble { max-width:86%; }
                    .community-back-btn { display:inline-flex; width:2rem; height:2rem; align-items:center; justify-content:center; border:1px solid var(--hub-border); border-radius:999px; background:var(--hub-surface); color:var(--hub-ink); cursor:pointer; flex:0 0 auto; }
                    .community-composer-wrap { gap:0.36rem; padding:0.26rem 0.3rem; }
                    .community-composer-wrap .community-message-input { font-size:14px; }
                }
            </style>

            <div class="community-chat-layout" data-room-open="{{ $this->activeRoom ? 'true' : 'false' }}">

                {{-- Room list --}}
                <section class="hub-card community-room-list">
                    @if ($this->rooms->count() === 0)
                        <p class="hub-copy" style="color:var(--hub-muted);font-size:0.8rem;padding:0.5rem;">No conversations yet. Message a friend from the Friends tab.</p>
                    @else
                        @foreach ($this->rooms as $room)
                            @php
                                $roomAvatar = $room->avatarUrlFor(auth()->user());
                                $roomInitial = strtoupper(substr($room->displayNameFor(auth()->user()), 0, 1));
                            @endphp
                            <button type="button" wire:click="openRoom({{ $room->id }})"
                                @class([
                                    'community-room-item',
                                    'community-room-item-active' => $selectedRoomId === $room->id,
                                ])
                                onmouseover="if(!this.dataset.active){this.style.background='var(--hub-surface)'}"
                                onmouseout="if(!this.dataset.active){this.style.background='transparent'}"
                                @if ($selectedRoomId === $room->id)
                                    data-active="1"
                                @endif
                            >
                                <div style="display:flex;align-items:center;gap:0.4rem;">
                                    @if ($roomAvatar)
                                        <img src="{{ $roomAvatar }}" alt="{{ $room->displayNameFor(auth()->user()) }}"
                                            style="width:1.75rem;height:1.75rem;border-radius:999px;object-fit:cover;border:1px solid {{ $selectedRoomId === $room->id ? 'rgba(148,163,184,.45)' : 'var(--hub-border)' }};">
                                    @else
                                        <span style="width:1.75rem;height:1.75rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:0.72rem;font-weight:700;{{ $room->type === 'course' ? 'background:#0369a1;color:#e0f2fe;' : 'background:#0f766e;color:#ccfbf1;' }}">{{ $roomInitial }}</span>
                                    @endif
                                    <span style="font-size:0.83rem;font-weight:600;{{ $selectedRoomId === $room->id ? 'color:#e2e8f0;' : 'color:var(--hub-ink);' }}">{{ $room->displayNameFor(auth()->user()) }}</span>
                                </div>
                                @if ($room->latestMessage)
                                    <p style="margin:0.2rem 0 0;font-size:0.72rem;{{ $selectedRoomId === $room->id ? 'color:#94a3b8;' : 'color:var(--hub-muted);' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ \Illuminate\Support\Str::limit($room->latestMessage->body, 34) }}</p>
                                @endif
                            </button>
                        @endforeach
                    @endif
                </section>

                {{-- Message thread --}}
                <section class="hub-card community-thread">
                    @if (! $this->activeRoom)
                        <div style="flex:1;display:flex;align-items:center;justify-content:center;">
                            <p class="hub-copy" style="color:var(--hub-muted);font-size:0.85rem;">Select a conversation to start chatting.</p>
                        </div>
                    @else
                        @php
                            $activeAvatar = $this->activeRoom->avatarUrlFor(auth()->user());
                            $activeInitial = strtoupper(substr($this->activeRoom->displayNameFor(auth()->user()), 0, 1));
                        @endphp
                        <div class="community-thread-head">
                            <div style="display:flex;align-items:center;gap:0.55rem;">
                                <button type="button" wire:click="$set('selectedRoomId', null)" class="community-back-btn" aria-label="Back to chat rooms">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                                </button>
                                @if ($activeAvatar)
                                    <img src="{{ $activeAvatar }}" alt="{{ $this->activeRoom->displayNameFor(auth()->user()) }}"
                                        style="width:2rem;height:2rem;border-radius:999px;object-fit:cover;border:1px solid var(--hub-border);">
                                @else
                                    <span style="width:2rem;height:2rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:0.78rem;font-weight:700;{{ $this->activeRoom->type === 'course' ? 'background:#0369a1;color:#e0f2fe;' : 'background:#0f766e;color:#ccfbf1;' }}">{{ $activeInitial }}</span>
                                @endif
                                <div>
                                    <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.9rem;">{{ $this->activeRoom->displayNameFor(auth()->user()) }}</p>
                                    @if ($this->activeRoom->type === 'course')
                                        <p style="margin:0;font-size:0.72rem;color:var(--hub-muted);">{{ $this->activeRoom->members->count() }} members</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div wire:poll.4s style="flex:1;overflow-y:auto;padding:0.75rem 0.85rem 1.1rem;display:flex;flex-direction:column;gap:0.56rem;"
                            x-data x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                            x-on:scroll-bottom.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                            @if ($this->hasMoreMessages)
                                <div style="text-align:center;padding:0.3rem 0;">
                                    <button type="button" wire:click="loadMoreMessages" wire:loading.attr="disabled"
                                        style="font-size:0.78rem;padding:0.35rem 1rem;background:var(--hub-surface);color:var(--hub-muted);border:1px solid var(--hub-border);border-radius:999px;cursor:pointer;">
                                        <span wire:loading.remove wire:target="loadMoreMessages">Load older messages</span>
                                        <span wire:loading wire:target="loadMoreMessages">Loading…</span>
                                    </button>
                                </div>
                            @endif
                            @forelse ($this->messages as $message)
                                @php $mine = $message->user_id === auth()->id(); @endphp
                                <div style="display:flex;flex-direction:column;{{ $mine ? 'align-items:flex-end;' : 'align-items:flex-start;' }}">
                                    <div class="community-bubble" style="padding:0.42rem 0.7rem;border-radius:0.78rem;font-size:13px;line-height:1.35;{{ $mine ? 'background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;border-bottom-right-radius:0.24rem;box-shadow:0 8px 20px rgba(15,118,110,.22);' : 'background:var(--hub-surface);color:var(--hub-ink);border:1px solid var(--hub-border);border-bottom-left-radius:0.24rem;box-shadow:0 4px 14px rgba(2,6,23,.06);' }}">
                                        @if (! $mine && $this->activeRoom->type === 'course')
                                            <p style="margin:0 0 0.15rem;font-size:0.875rem;font-weight:600;opacity:0.92;">{{ $message->user?->name }}</p>
                                        @endif

                                        @if ($message->attachment_path)
                                            @if ($message->attachment_type === 'image')
                                                <a href="{{ $message->attachment_url }}" target="_blank" rel="noopener noreferrer" style="display:block;">
                                                    <img src="{{ $message->attachment_url }}" alt="{{ $message->attachment_name }}"
                                                        style="max-width:230px;max-height:230px;border-radius:0.5rem;margin-bottom:{{ $message->body ? '0.3rem' : '0' }};display:block;object-fit:cover;">
                                                </a>
                                            @else
                                                <a href="{{ $message->attachment_url }}" target="_blank" rel="noopener noreferrer" download="{{ $message->attachment_name }}"
                                                    style="display:flex;align-items:center;gap:0.4rem;padding:0.35rem 0.55rem;border-radius:0.5rem;text-decoration:none;margin-bottom:{{ $message->body ? '0.3rem' : '0' }};{{ $mine ? 'background:rgba(255,255,255,.2);color:#fff;' : 'background:var(--hub-card);color:var(--hub-ink);border:1px solid var(--hub-border);' }}">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                    <span style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px;">{{ $message->attachment_name }}</span>
                                                </a>
                                            @endif
                                        @endif

                                        @if ($message->body)
                                            <p style="margin:0;font-size:13px;white-space:pre-wrap;">{{ $message->body }}</p>
                                        @endif
                                    </div>
                                    <span style="font-size:11px;color:#94a3b8;margin-top:0.18rem;">{{ $message->created_at?->format('M d, H:i') }}</span>
                                </div>
                            @empty
                                <p class="hub-copy" style="color:var(--hub-muted);font-size:0.82rem;text-align:center;margin:auto;">No messages yet. Say hi!</p>
                            @endforelse
                        </div>

                        <form wire:submit.prevent="sendMessage" style="display:flex;flex-direction:column;gap:0.4rem;padding:0.68rem 0.75rem;border-top:1px solid var(--hub-border);background:linear-gradient(180deg,rgba(148,163,184,.05),rgba(15,23,42,.12));">
                            {{-- Selected attachment preview --}}
                            @if ($attachment)
                                <div style="display:flex;align-items:center;gap:0.5rem;padding:0.35rem 0.55rem;background:var(--hub-surface);border-radius:0.45rem;">
                                    @if (method_exists($attachment, 'temporaryUrl') && str_starts_with($attachment->getMimeType() ?? '', 'image/'))
                                        <img src="{{ $attachment->temporaryUrl() }}" alt="" style="width:38px;height:38px;object-fit:cover;border-radius:0.3rem;">
                                    @else
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--hub-muted);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    @endif
                                    <span style="flex:1;font-size:0.76rem;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="$set('attachment', null)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1.1rem;line-height:1;">&times;</button>
                                </div>
                            @endif

                            @error('attachment')
                                <span style="color:#dc2626;font-size:0.72rem;">{{ $message }}</span>
                            @enderror

                            <div wire:loading wire:target="attachment" style="font-size:0.72rem;color:var(--hub-muted);">Uploading…</div>

                            <div class="community-composer-wrap">
                                <label class="community-attach-btn" style="cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0.48rem;border:1px solid color-mix(in oklab, var(--hub-border) 70%, #475569 30%);border-radius:999px;color:var(--hub-muted);background:color-mix(in oklab, var(--hub-card) 82%, #111827 18%);" title="Attach a file">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    <input type="file" wire:model="attachment" accept="image/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.zip" style="display:none;">
                                </label>
                                <input type="text" wire:model="messageBody" placeholder="Type a message…" autocomplete="off"
                                    class="community-message-input"
                                    style="flex:1;font-size:13px;padding:0.45rem 0.5rem;border:0;outline:0;background:transparent;box-shadow:none;color:var(--hub-ink);-webkit-appearance:none;appearance:none;">
                                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage,attachment"
                                    style="padding:0.5rem 1.05rem;background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;border:none;border-radius:999px;cursor:pointer;font-size:0.82rem;font-weight:700;box-shadow:0 8px 20px rgba(14,116,144,.28);">Send</button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>
        @endif

        {{-- ===================== STUDENT PROFILE MODAL ===================== --}}
        @if ($profileUser)
            <div wire:click="closeProfile"
                style="position:fixed;inset:0;z-index:60;background:rgba(2,6,23,.55);backdrop-filter:blur(3px);display:flex;padding:1rem;">
                <div wire:click.stop
                    style="width:100%;max-width:420px;margin:auto;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.9rem;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.35);">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;padding:0.6rem 0.9rem;border-bottom:1px solid var(--hub-border);">
                        <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.9rem;">Student profile</p>
                        <button type="button" wire:click="closeProfile" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.4rem;line-height:1;">&times;</button>
                    </div>

                    <div style="padding:0.95rem 1rem;display:flex;flex-direction:column;gap:0.7rem;">
                        {{-- Identity --}}
                        <div style="display:flex;align-items:center;gap:0.65rem;">
                            @if ($profileUser['avatar'])
                                <img src="{{ $profileUser['avatar'] }}" alt="{{ $profileUser['name'] }}"
                                    style="width:2.9rem;height:2.9rem;border-radius:999px;object-fit:cover;border:1px solid var(--hub-border);">
                            @else
                                <span style="width:2.9rem;height:2.9rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:#0f766e;color:#ccfbf1;font-size:1rem;font-weight:700;flex:0 0 auto;">{{ strtoupper(substr($profileUser['name'], 0, 1)) }}</span>
                            @endif
                            <div style="min-width:0;">
                                <p style="margin:0;font-size:0.98rem;font-weight:700;color:var(--hub-ink);">{{ $profileUser['name'] }}</p>
                                <p style="margin:0.05rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $profileUser['role_label'] }}{{ $profileUser['is_self'] ? ' · this is you' : '' }}</p>
                            </div>
                        </div>

                        {{-- Bio --}}
                        @if (filled($profileUser['bio']))
                            <p style="margin:0;font-size:0.82rem;color:var(--hub-ink);line-height:1.45;">{{ $profileUser['bio'] }}</p>
                        @else
                            <p style="margin:0;font-size:0.78rem;color:var(--hub-muted);font-style:italic;">No bio yet.</p>
                        @endif

                        {{-- Stats row --}}
                        <div style="display:flex;gap:0.5rem;">
                            <span style="flex:1;text-align:center;padding:0.4rem 0.3rem;border:1px solid var(--hub-border);border-radius:0.5rem;font-size:0.76rem;color:var(--hub-ink);">⚡ <strong>{{ number_format($profileUser['xp']) }}</strong> XP</span>
                            <span style="flex:1;text-align:center;padding:0.4rem 0.3rem;border:1px solid var(--hub-border);border-radius:0.5rem;font-size:0.76rem;color:var(--hub-ink);">🏅 <strong>{{ $profileUser['badge_count'] }}</strong> {{ Str::plural('badge', $profileUser['badge_count']) }}</span>
                            <span style="flex:1;text-align:center;padding:0.4rem 0.3rem;border:1px solid var(--hub-border);border-radius:0.5rem;font-size:0.76rem;color:var(--hub-ink);">📚 <strong>{{ $profileUser['courses_count'] }}</strong> {{ Str::plural('course', $profileUser['courses_count']) }}</span>
                        </div>

                        {{-- Badge showcase --}}
                        @if (count($profileUser['badges']) > 0)
                            <div>
                                <p style="margin:0 0 0.3rem;font-size:0.72rem;font-weight:700;color:var(--hub-muted);text-transform:uppercase;letter-spacing:0.04em;">Badges</p>
                                <div style="display:flex;flex-wrap:wrap;gap:0.35rem;">
                                    @foreach ($profileUser['badges'] as $badge)
                                        <span title="{{ $badge['description'] }}"
                                            style="font-size:0.76rem;padding:0.22rem 0.6rem;border-radius:999px;border:1px solid var(--hub-border);background:var(--hub-surface);color:var(--hub-ink);">
                                            {{ $badge['icon'] }} {{ $badge['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Shared courses --}}
                        <div>
                            <p style="margin:0 0 0.3rem;font-size:0.72rem;font-weight:700;color:var(--hub-muted);text-transform:uppercase;letter-spacing:0.04em;">Courses in common</p>
                            @if (count($profileUser['shared_courses']) > 0)
                                <p style="margin:0;font-size:0.82rem;color:var(--hub-ink);">You share: {{ implode(' · ', $profileUser['shared_courses']) }}</p>
                            @else
                                <p style="margin:0;font-size:0.78rem;color:var(--hub-muted);">No courses in common.</p>
                            @endif
                        </div>

                        {{-- Friendship action --}}
                        @if (! $profileUser['is_self'])
                            <div style="display:flex;justify-content:center;gap:0.4rem;padding-top:0.2rem;border-top:1px solid var(--hub-border);">
                                @if ($profileUser['friendship']['state'] === 'friends')
                                    <span style="font-size:0.8rem;color:#0f766e;font-weight:600;padding:0.35rem 0;">Friends ✓</span>
                                @elseif ($profileUser['friendship']['state'] === 'sent')
                                    <span style="font-size:0.8rem;color:var(--hub-muted);padding:0.35rem 0;">Request sent</span>
                                    <button type="button" wire:click="removeFriend({{ $profileUser['id'] }})"
                                        style="font-size:0.78rem;padding:0.35rem 0.9rem;background:none;border:1px solid var(--hub-border);color:var(--hub-ink);border-radius:0.45rem;cursor:pointer;">Cancel request</button>
                                @elseif ($profileUser['friendship']['state'] === 'incoming')
                                    <button type="button" wire:click="acceptRequest({{ $profileUser['friendship']['friendship_id'] }})"
                                        style="font-size:0.78rem;padding:0.35rem 0.9rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.45rem;cursor:pointer;">Accept request</button>
                                    <button type="button" wire:click="declineRequest({{ $profileUser['friendship']['friendship_id'] }})"
                                        style="font-size:0.78rem;padding:0.35rem 0.9rem;background:none;border:1px solid var(--hub-border);color:var(--hub-ink);border-radius:0.45rem;cursor:pointer;">Decline</button>
                                @else
                                    <button type="button" wire:click="sendRequest({{ $profileUser['id'] }})"
                                        style="font-size:0.78rem;padding:0.35rem 0.9rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.45rem;cursor:pointer;">Add friend</button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
