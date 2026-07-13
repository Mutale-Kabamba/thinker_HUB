<x-filament-panels::page>

    <div class="hub-shell">

        {{-- Tabs --}}
        <section style="padding:0.15rem 0 0.35rem;">
            <div style="display:flex;justify-content:center;">
                <div style="display:flex;gap:0.35rem;max-width:330px;width:100%;padding:0.26rem;border-radius:999px;background:rgba(255,255,255,.5);backdrop-filter:blur(8px);border:1px solid var(--hub-border);box-shadow:0 8px 22px rgba(15,23,42,.07);">
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
                </div>
            </div>
        </section>

        {{-- ===================== FRIENDS TAB ===================== --}}
        @if ($tab === 'friends')
            <section style="padding:0.35rem 0.35rem 0.6rem;">
                <h3 class="hub-title" style="font-size:0.95rem;margin:0 0 0.5rem;">Find people</h3>
                <input type="text" wire:model.live.debounce.400ms="peopleSearch" placeholder="Search by name…"
                    class="hub-input" style="width:100%;font-size:0.85rem;padding:0.45rem 0.6rem;">

                @if ($this->peopleResults->count() > 0)
                    <div style="display:flex;flex-direction:column;gap:0.4rem;margin-top:0.6rem;">
                        @foreach ($this->peopleResults as $person)
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;padding:0.4rem 0.55rem;border:1px solid var(--hub-border);border-radius:0.5rem;">
                                <span style="font-size:0.85rem;color:var(--hub-ink);">{{ $person->name }}</span>
                                @if (auth()->user()->isFriendsWith($person->id))
                                    <span style="font-size:0.72rem;color:var(--hub-muted);">Friends</span>
                                @elseif ($this->sentRequests->contains($person->id))
                                    <span style="font-size:0.72rem;color:var(--hub-muted);">Requested</span>
                                @else
                                    <button type="button" wire:click="sendRequest({{ $person->id }})"
                                        style="font-size:0.74rem;padding:0.3rem 0.7rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.4rem;cursor:pointer;">Add friend</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif (mb_strlen(trim($peopleSearch)) >= 2)
                    <p class="hub-copy" style="color:var(--hub-muted);margin-top:0.5rem;font-size:0.82rem;">No people found.</p>
                @endif
            </section>

            {{-- Pending requests --}}
            @if ($this->pendingRequests->count() > 0)
                <section class="hub-card" style="padding:0.85rem 1rem;">
                    <h3 class="hub-title" style="font-size:0.95rem;margin:0 0 0.5rem;">Friend requests</h3>
                    <div style="display:flex;flex-direction:column;gap:0.4rem;">
                        @foreach ($this->pendingRequests as $req)
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;padding:0.4rem 0.55rem;border:1px solid var(--hub-border);border-radius:0.5rem;">
                                <span style="font-size:0.85rem;color:var(--hub-ink);">{{ $req->requester?->name ?? 'Unknown' }}</span>
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
                                    <p style="margin:0;font-size:0.84rem;font-weight:600;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $friend->name }}</p>
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

        {{-- ===================== CHATS TAB ===================== --}}
        @if ($tab === 'chats')
            <div style="display:grid;grid-template-columns:minmax(200px,280px) 1fr;gap:0.75rem;align-items:start;">

                {{-- Room list --}}
                <section class="hub-card" style="padding:0.5rem;max-height:70vh;overflow-y:auto;">
                    @if ($this->rooms->count() === 0)
                        <p class="hub-copy" style="color:var(--hub-muted);font-size:0.8rem;padding:0.5rem;">No conversations yet. Message a friend from the Friends tab.</p>
                    @else
                        @foreach ($this->rooms as $room)
                            @php
                                $roomAvatar = $room->avatarUrlFor(auth()->user());
                                $roomInitial = strtoupper(substr($room->displayNameFor(auth()->user()), 0, 1));
                            @endphp
                            <button type="button" wire:click="openRoom({{ $room->id }})"
                                style="width:100%;text-align:left;padding:0.58rem 0.68rem;border:none;border-radius:0.62rem;cursor:pointer;margin-bottom:0.25rem;transition:all .12s ease;{{ $selectedRoomId === $room->id ? 'background:#0f172a;color:#e2e8f0;box-shadow:0 10px 22px rgba(2,6,23,.22);' : 'background:transparent;' }}"
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
                <section class="hub-card" style="padding:0;display:flex;flex-direction:column;height:70vh;">
                    @if (! $this->activeRoom)
                        <div style="flex:1;display:flex;align-items:center;justify-content:center;">
                            <p class="hub-copy" style="color:var(--hub-muted);font-size:0.85rem;">Select a conversation to start chatting.</p>
                        </div>
                    @else
                        @php
                            $activeAvatar = $this->activeRoom->avatarUrlFor(auth()->user());
                            $activeInitial = strtoupper(substr($this->activeRoom->displayNameFor(auth()->user()), 0, 1));
                        @endphp
                        <div style="padding:0.55rem 0.85rem;border-bottom:1px solid var(--hub-border);">
                            <div style="display:flex;align-items:center;gap:0.55rem;">
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
                            @forelse ($this->messages as $message)
                                @php $mine = $message->user_id === auth()->id(); @endphp
                                <div style="display:flex;flex-direction:column;{{ $mine ? 'align-items:flex-end;' : 'align-items:flex-start;' }}">
                                    <div style="max-width:70%;padding:0.42rem 0.7rem;border-radius:0.78rem;font-size:13px;line-height:1.35;{{ $mine ? 'background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;border-bottom-right-radius:0.24rem;box-shadow:0 8px 20px rgba(15,118,110,.22);' : 'background:var(--hub-surface);color:var(--hub-ink);border:1px solid var(--hub-border);border-bottom-left-radius:0.24rem;box-shadow:0 4px 14px rgba(2,6,23,.06);' }}">
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

                            <div style="display:flex;gap:0.5rem;align-items:center;padding:0.34rem 0.4rem;border:1px solid color-mix(in oklab, var(--hub-border) 75%, #334155 25%);border-radius:999px;background:color-mix(in oklab, var(--hub-card) 70%, #0f172a 30%);backdrop-filter:blur(10px);box-shadow:0 12px 28px rgba(2,6,23,.3);">
                                <label style="cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0.48rem;border:1px solid color-mix(in oklab, var(--hub-border) 70%, #475569 30%);border-radius:999px;color:var(--hub-muted);background:color-mix(in oklab, var(--hub-card) 82%, #111827 18%);" title="Attach a file">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    <input type="file" wire:model="attachment" accept="image/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.zip" style="display:none;">
                                </label>
                                <input type="text" wire:model="messageBody" placeholder="Type a message…" autocomplete="off"
                                    style="flex:1;font-size:13px;padding:0.45rem 0.5rem;border:0;outline:0;background:transparent;box-shadow:none;color:var(--hub-ink);-webkit-appearance:none;appearance:none;">
                                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage,attachment"
                                    style="padding:0.5rem 1.05rem;background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;border:none;border-radius:999px;cursor:pointer;font-size:0.82rem;font-weight:700;box-shadow:0 8px 20px rgba(14,116,144,.28);">Send</button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>
        @endif

    </div>
</x-filament-panels::page>
