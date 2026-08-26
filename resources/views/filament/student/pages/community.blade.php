<x-filament-panels::page>

    <div class="hub-shell">

        {{-- My XP summary chip --}}
        <section style="padding:0.15rem 0 0;display:flex;justify-content:center;">
            <button type="button" wire:click="$set('tab','leaderboard')"
                style="display:inline-flex;align-items:center;gap:0.45rem;padding:0.28rem 0.8rem;border-radius:999px;border:1px solid var(--hub-border);background:rgba(255,255,255,.5);backdrop-filter:blur(8px);box-shadow:0 6px 16px rgba(15,23,42,.06);cursor:pointer;font-size:0.76rem;font-weight:600;color:var(--hub-ink);">
                <x-heroicon-s-bolt style="width:0.95rem;height:0.95rem;color:#eab308;" />
                <span>{{ number_format($this->myXp['xp']) }} XP</span>
                <span style="color:var(--hub-muted);">·</span>
                <x-heroicon-s-star style="width:0.9rem;height:0.9rem;color:#f59e0b;" />
                <span>{{ $this->myXp['badge_count'] }} {{ Str::plural('badge', $this->myXp['badge_count']) }}</span>
                @if (count($this->myXp['badge_icons']) > 0)
                    <span style="letter-spacing:0.1em;">{{ implode('', $this->myXp['badge_icons']) }}</span>
                @endif
            </button>
        </section>

        {{-- Tabs --}}
        <section class="py-2 px-1">
            <div class="flex justify-center">
                <div class="flex gap-1 max-w-md w-full p-1 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <button type="button" wire:click="$set('tab','chats')"
                        class="flex-1 py-2 px-1.5 xs:px-2.5 sm:px-3 rounded-xl text-[11px] xs:text-xs font-bold whitespace-nowrap transition-all flex items-center justify-center {{ $tab === 'chats' ? 'bg-[#7C3AED] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">
                        Chats
                    </button>
                    <button type="button" wire:click="$set('tab','friends')"
                        class="flex-1 py-2 px-1.5 xs:px-2.5 sm:px-3 rounded-xl text-[11px] xs:text-xs font-bold whitespace-nowrap transition-all flex items-center justify-center gap-1 {{ $tab === 'friends' ? 'bg-[#7C3AED] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">
                        <span>Friends</span>
                        @if ($this->pendingRequests->count() > 0)
                            <span class="bg-rose-600 text-white rounded-full text-[9px] sm:text-[10px] px-1.5 py-0.5 leading-none shrink-0">{{ $this->pendingRequests->count() }}</span>
                        @endif
                    </button>
                    <button type="button" wire:click="$set('tab','leaderboard')"
                        class="flex-1 py-2 px-1.5 xs:px-2.5 sm:px-3 rounded-xl text-[11px] xs:text-xs font-bold whitespace-nowrap transition-all inline-flex items-center justify-center gap-1 sm:gap-1.5 {{ $tab === 'leaderboard' ? 'bg-[#7C3AED] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">
                        <x-heroicon-o-trophy class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                        <span>Leaderboard</span>
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
                                                style="background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 14%);color:#0f766e;border-radius:999px;padding:0.04rem 0.45rem;font-weight:600;display:inline-flex;align-items:center;gap:0.25rem;">
                                                <x-heroicon-o-book-open style="width:0.75rem;height:0.75rem;" />
                                                <span>{{ $person['shared_count'] }} {{ Str::plural('course', $person['shared_count']) }} together</span>
                                            </span>
                                        @endif
                                        <span style="display:inline-flex;align-items:center;gap:0.2rem;">
                                            <x-heroicon-s-bolt style="width:0.75rem;height:0.75rem;color:#eab308;" />
                                            {{ number_format($person['xp']) }}
                                        </span>
                                        @if (count($person['badge_icons']) > 0)
                                            <span style="letter-spacing:0.08em;">{{ implode('', $person['badge_icons']) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div style="display:flex;gap:0.35rem;flex:0 0 auto;">
                                    @if ($person['friendship']['state'] === 'friends')
                                        <span style="font-size:0.72rem;color:#0f766e;font-weight:600;display:inline-flex;align-items:center;gap:0.2rem;">
                                            <x-heroicon-s-check-circle style="width:0.85rem;height:0.85rem;" />
                                            <span>Friends</span>
                                        </span>
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
                $allRows = $leaderboard['rows'];
                $top5 = $allRows->take(5);
                $remaining = $allRows->slice(5);
                $userInTop5 = $top5->contains('user_id', auth()->id());
                $myRowInList = $allRows->firstWhere('user_id', auth()->id());
                $xpBreakdown = $this->myXpBreakdown;
            @endphp

            {{-- 1. TOP STUDENTS LEADERBOARD (DEFAULT TOP 5 WITH EXPAND/COLLAPSE) --}}
            <section class="hub-card" x-data="{ showAllLeaderboard: false }" style="padding:0.65rem 0.85rem;box-sizing:border-box;width:100%;max-width:100%;overflow:hidden;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.35rem;margin-bottom:0.4rem;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:0.35rem;">
                        <x-heroicon-s-trophy style="width:1rem;height:1rem;color:#f59e0b;" />
                        <h3 class="hub-title" style="font-size:0.88rem;margin:0;">Leaderboard</h3>
                    </div>
                    @if ($allRows->count() > 5)
                        <span class="hub-chip hub-chip-gray" style="font-size:0.58rem;padding:0.05rem 0.3rem;">
                            Top {{ $allRows->count() }} Students
                        </span>
                    @endif
                </div>
                <p class="hub-copy" style="color:var(--hub-muted);font-size:0.72rem;margin:0 0 0.45rem;">Ranked by lifetime XP earned from quizzes, attendance, streaks, and course completions.</p>

                @if ($allRows->count() === 0)
                    <p class="hub-copy" style="color:var(--hub-muted);font-size:0.76rem;margin:0.2rem 0;">No XP earned yet. Pass a quiz or complete a lesson to get on the board!</p>
                @else
                    <div style="display:flex;flex-direction:column;gap:0.28rem;width:100%;box-sizing:border-box;">
                        {{-- Top 5 Rows (Default Display) --}}
                        @foreach ($top5 as $row)
                            @php $isMe = $row['user_id'] === auth()->id(); @endphp
                            <div style="display:flex;align-items:center;gap:0.35rem;padding:0.32rem 0.5rem;border-radius:0.4rem;border:1px solid {{ $isMe ? 'color-mix(in oklab, var(--hub-border) 40%, #0f766e 60%)' : 'var(--hub-border)' }};{{ $isMe ? 'background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 12%);' : 'background:var(--hub-surface);' }}box-sizing:border-box;width:100%;max-width:100%;min-width:0;overflow:hidden;">
                                <span style="width:1.6rem;min-width:1.6rem;max-width:1.6rem;flex-shrink:0;text-align:left;font-size:0.76rem;font-weight:800;color:{{ $row['rank'] <= 3 ? '#0f766e' : 'var(--hub-muted)' }};">
                                    #{{ $row['rank'] }}
                                </span>
                                <button
                                    type="button"
                                    wire:click="showProfile({{ $row['user_id'] }})"
                                    style="flex:1;min-width:0;text-align:left;background:none;border:none;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:0.25rem;font-size:0.78rem;font-weight:{{ $isMe ? '700' : '600' }};color:{{ $isMe ? 'var(--hub-primary, #0f766e)' : 'var(--hub-ink)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:all 0.15s;"
                                    onmouseover="this.style.color='var(--hub-primary, #0f766e)';this.style.textDecoration='underline';"
                                    onmouseout="this.style.color='{{ $isMe ? 'var(--hub-primary, #0f766e)' : 'var(--hub-ink)' }}';this.style.textDecoration='none';"
                                    title="Click to view {{ $row['name'] }}'s XP & Badge earnings"
                                >
                                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row['name'] }}{{ $isMe ? ' (you)' : '' }}</span>
                                    <x-heroicon-m-sparkles style="width:0.7rem;height:0.7rem;color:#f59e0b;opacity:0.75;flex-shrink:0;" />
                                </button>

                                {{-- Right Metrics Cluster (Always Aligned Across All Rows) --}}
                                <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.45rem;flex-shrink:0;margin-left:auto;">
                                    {{-- Badges Slot (Fixed width 3.6rem so 0, 1, 2, or 3 badges never shift adjacent columns) --}}
                                    <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:0.12rem;width:3.6rem;min-width:3.6rem;max-width:3.6rem;flex-shrink:0;" title="{{ $row['badge_count'] }} {{ Str::plural('badge', $row['badge_count']) }}">
                                        @if (!empty($row['badges']))
                                            @foreach (array_slice($row['badges'], 0, 3) as $b)
                                                @php
                                                    $bKey = is_array($b) ? ($b['key'] ?? '') : ($b->key ?? '');
                                                    $bName = is_array($b) ? ($b['name'] ?? '') : ($b->name ?? '');
                                                @endphp
                                                <span class="hub-chip hub-chip-amber" style="width:1.15rem;height:1.15rem;padding:0;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;line-height:1;flex-shrink:0;" title="{{ $bName }}">
                                                    @if ($bKey === 'course_completed')
                                                        <x-heroicon-s-academic-cap style="width:0.68rem;height:0.68rem;color:#0f766e;" />
                                                    @elseif (str_contains($bKey, 'streak'))
                                                        <x-heroicon-s-fire style="width:0.68rem;height:0.68rem;color:#ea580c;" />
                                                    @elseif ($bKey === 'first_perfect_quiz')
                                                        <x-heroicon-s-check-badge style="width:0.68rem;height:0.68rem;color:#10b981;" />
                                                    @elseif ($bKey === 'mastermind')
                                                        <x-heroicon-s-sparkles style="width:0.68rem;height:0.68rem;color:#8b5cf6;" />
                                                    @elseif ($bKey === 'study_networker')
                                                        <x-heroicon-s-user-group style="width:0.68rem;height:0.68rem;color:#0284c7;" />
                                                    @elseif ($bKey === 'active_contributor')
                                                        <x-heroicon-s-chat-bubble-left-right style="width:0.68rem;height:0.68rem;color:#6366f1;" />
                                                    @else
                                                        <x-heroicon-s-trophy style="width:0.68rem;height:0.68rem;color:#d97706;" />
                                                    @endif
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>

                                    {{-- Badge Count Column (Fixed width 1.6rem) --}}
                                    <div style="width:1.6rem;min-width:1.6rem;max-width:1.6rem;display:inline-flex;align-items:center;justify-content:flex-end;gap:0.12rem;font-size:0.72rem;color:var(--hub-muted);flex-shrink:0;" title="{{ $row['badge_count'] }} Badges">
                                        <x-heroicon-s-trophy style="width:0.68rem;height:0.68rem;color:#f59e0b;flex-shrink:0;" />
                                        <span style="font-weight:600;">{{ $row['badge_count'] }}</span>
                                    </div>

                                    {{-- XP Count Column (Fixed width 3.2rem) --}}
                                    <div style="width:3.2rem;min-width:3.2rem;max-width:3.2rem;display:inline-flex;align-items:center;justify-content:flex-end;gap:0.12rem;font-size:0.76rem;font-weight:800;color:#0f766e;text-align:right;flex-shrink:0;">
                                        <x-heroicon-s-bolt style="width:0.68rem;height:0.68rem;color:#eab308;flex-shrink:0;" />
                                        <span>{{ number_format($row['xp']) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Remaining Rows (Collapsible) --}}
                        @if ($remaining->isNotEmpty())
                            <div x-show="showAllLeaderboard" x-collapse style="display:flex;flex-direction:column;gap:0.28rem;width:100%;box-sizing:border-box;">
                                @foreach ($remaining as $row)
                                    @php $isMe = $row['user_id'] === auth()->id(); @endphp
                                    <div style="display:flex;align-items:center;gap:0.35rem;padding:0.32rem 0.5rem;border-radius:0.4rem;border:1px solid {{ $isMe ? 'color-mix(in oklab, var(--hub-border) 40%, #0f766e 60%)' : 'var(--hub-border)' }};{{ $isMe ? 'background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 12%);' : 'background:var(--hub-surface);' }}box-sizing:border-box;width:100%;max-width:100%;min-width:0;overflow:hidden;">
                                        <span style="width:1.6rem;min-width:1.6rem;max-width:1.6rem;flex-shrink:0;text-align:left;font-size:0.76rem;font-weight:700;color:var(--hub-muted);">
                                            #{{ $row['rank'] }}
                                        </span>
                                        <button
                                            type="button"
                                            wire:click="showProfile({{ $row['user_id'] }})"
                                            style="flex:1;min-width:0;text-align:left;background:none;border:none;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:0.25rem;font-size:0.78rem;font-weight:{{ $isMe ? '700' : '600' }};color:{{ $isMe ? 'var(--hub-primary, #0f766e)' : 'var(--hub-ink)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:all 0.15s;"
                                            onmouseover="this.style.color='var(--hub-primary, #0f766e)';this.style.textDecoration='underline';"
                                            onmouseout="this.style.color='{{ $isMe ? 'var(--hub-primary, #0f766e)' : 'var(--hub-ink)' }}';this.style.textDecoration='none';"
                                            title="Click to view {{ $row['name'] }}'s XP & Badge earnings"
                                        >
                                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row['name'] }}{{ $isMe ? ' (you)' : '' }}</span>
                                            <x-heroicon-m-sparkles style="width:0.7rem;height:0.7rem;color:#f59e0b;opacity:0.75;flex-shrink:0;" />
                                        </button>

                                        {{-- Right Metrics Cluster (Always Aligned Across All Rows) --}}
                                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.45rem;flex-shrink:0;margin-left:auto;">
                                            {{-- Badges Slot (Fixed width 3.6rem so 0, 1, 2, or 3 badges never shift adjacent columns) --}}
                                            <div style="display:inline-flex;align-items:center;justify-content:flex-end;gap:0.12rem;width:3.6rem;min-width:3.6rem;max-width:3.6rem;flex-shrink:0;" title="{{ $row['badge_count'] }} Badges">
                                                @if (!empty($row['badges']))
                                                    @foreach (array_slice($row['badges'], 0, 3) as $b)
                                                        @php
                                                            $bKey = is_array($b) ? ($b['key'] ?? '') : ($b->key ?? '');
                                                            $bName = is_array($b) ? ($b['name'] ?? '') : ($b->name ?? '');
                                                        @endphp
                                                        <span class="hub-chip hub-chip-amber" style="width:1.15rem;height:1.15rem;padding:0;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;line-height:1;flex-shrink:0;" title="{{ $bName }}">
                                                            @if ($bKey === 'course_completed')
                                                                <x-heroicon-s-academic-cap style="width:0.68rem;height:0.68rem;color:#0f766e;" />
                                                            @elseif (str_contains($bKey, 'streak'))
                                                                <x-heroicon-s-fire style="width:0.68rem;height:0.68rem;color:#ea580c;" />
                                                            @elseif ($bKey === 'first_perfect_quiz')
                                                                <x-heroicon-s-check-badge style="width:0.68rem;height:0.68rem;color:#10b981;" />
                                                            @elseif ($bKey === 'mastermind')
                                                                <x-heroicon-s-sparkles style="width:0.68rem;height:0.68rem;color:#8b5cf6;" />
                                                            @elseif ($bKey === 'study_networker')
                                                                <x-heroicon-s-user-group style="width:0.68rem;height:0.68rem;color:#0284c7;" />
                                                            @elseif ($bKey === 'active_contributor')
                                                                <x-heroicon-s-chat-bubble-left-right style="width:0.68rem;height:0.68rem;color:#6366f1;" />
                                                            @else
                                                                <x-heroicon-s-trophy style="width:0.68rem;height:0.68rem;color:#d97706;" />
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>

                                            {{-- Badge Count Column (Fixed width 1.6rem) --}}
                                            <div style="width:1.6rem;min-width:1.6rem;max-width:1.6rem;display:inline-flex;align-items:center;justify-content:flex-end;gap:0.12rem;font-size:0.72rem;color:var(--hub-muted);flex-shrink:0;" title="{{ $row['badge_count'] }} Badges">
                                                <x-heroicon-s-trophy style="width:0.68rem;height:0.68rem;color:#f59e0b;flex-shrink:0;" />
                                                <span style="font-weight:600;">{{ $row['badge_count'] }}</span>
                                            </div>

                                            {{-- XP Count Column (Fixed width 3.2rem) --}}
                                            <div style="width:3.2rem;min-width:3.2rem;max-width:3.2rem;display:inline-flex;align-items:center;justify-content:flex-end;gap:0.12rem;font-size:0.76rem;font-weight:800;color:#0f766e;text-align:right;flex-shrink:0;">
                                                <x-heroicon-s-bolt style="width:0.68rem;height:0.68rem;color:#eab308;flex-shrink:0;" />
                                                <span>{{ number_format($row['xp']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Expand / Collapse Button --}}
                            <div style="padding:0.15rem 0;">
                                <button
                                    type="button"
                                    @click="showAllLeaderboard = !showAllLeaderboard"
                                    style="width:100%;padding:0.32rem 0.5rem;border-radius:0.375rem;border:1px dashed var(--hub-border);background:transparent;color:var(--hub-muted);font-size:0.73rem;font-weight:600;display:flex;align-items:center;justify-content:center;gap:0.3rem;cursor:pointer;line-height:1.2;transition:all .15s ease;"
                                    onmouseover="this.style.color='var(--hub-ink)';this.style.borderColor='var(--hub-primary)';"
                                    onmouseout="this.style.color='var(--hub-muted)';this.style.borderColor='var(--hub-border)';"
                                >
                                    <span x-text="showAllLeaderboard ? 'Collapse to Top 5' : 'View More (Rank 6–{{ $allRows->count() }})'"></span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;max-width:12px;max-height:12px;flex-shrink:0;transition:transform .2s ease;" :style="showAllLeaderboard ? 'transform:rotate(180deg);' : ''">
                                        <path d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        {{-- Pinned User Row if outside Top 5 while collapsed --}}
                        @if (! $userInTop5)
                            @if ($myRowInList)
                                <div x-show="!showAllLeaderboard" style="border-top:1px dashed var(--hub-border);margin-top:0.15rem;padding-top:0.3rem;">
                                    <div style="display:flex;align-items:center;gap:0.3rem;padding:0.28rem 0.45rem;border-radius:0.4rem;border:1px solid color-mix(in oklab, var(--hub-border) 40%, #0f766e 60%);background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 12%);box-sizing:border-box;width:100%;max-width:100%;min-width:0;overflow:hidden;">
                                        <span style="min-width:1.5rem;flex-shrink:0;text-align:center;font-size:0.76rem;font-weight:800;color:var(--hub-ink);">#{{ $myRowInList['rank'] }}</span>
                                        <button
                                            type="button"
                                            wire:click="showProfile({{ $myRowInList['user_id'] }})"
                                            style="flex:1;min-width:0;text-align:left;background:none;border:none;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:0.25rem;font-size:0.78rem;font-weight:700;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:all 0.15s;"
                                            onmouseover="this.style.color='var(--hub-primary, #0f766e)';this.style.textDecoration='underline';"
                                            onmouseout="this.style.color='var(--hub-ink)';this.style.textDecoration='none';"
                                            title="Click to view your XP & Badge earnings"
                                        >
                                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $myRowInList['name'] }} (you)</span>
                                            <x-heroicon-m-sparkles style="width:0.7rem;height:0.7rem;color:#f59e0b;opacity:0.75;flex-shrink:0;" />
                                        </button>
                                        @if (!empty($myRowInList['badges']))
                                            <div style="display:inline-flex;align-items:center;gap:0.12rem;flex-shrink:0;" title="{{ $myRowInList['badge_count'] }} Badges">
                                                @foreach (array_slice($myRowInList['badges'], 0, 3) as $b)
                                                    @php
                                                        $bKey = is_array($b) ? ($b['key'] ?? '') : ($b->key ?? '');
                                                        $bName = is_array($b) ? ($b['name'] ?? '') : ($b->name ?? '');
                                                    @endphp
                                                    <span class="hub-chip hub-chip-amber" style="width:1.15rem;height:1.15rem;padding:0;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;line-height:1;flex-shrink:0;" title="{{ $bName }}">
                                                        @if ($bKey === 'course_completed')
                                                            <x-heroicon-s-academic-cap style="width:0.68rem;height:0.68rem;color:#0f766e;" />
                                                        @elseif (str_contains($bKey, 'streak'))
                                                            <x-heroicon-s-fire style="width:0.68rem;height:0.68rem;color:#ea580c;" />
                                                        @elseif ($bKey === 'first_perfect_quiz')
                                                            <x-heroicon-s-check-badge style="width:0.68rem;height:0.68rem;color:#10b981;" />
                                                        @elseif ($bKey === 'mastermind')
                                                            <x-heroicon-s-sparkles style="width:0.68rem;height:0.68rem;color:#8b5cf6;" />
                                                        @elseif ($bKey === 'study_networker')
                                                            <x-heroicon-s-user-group style="width:0.68rem;height:0.68rem;color:#0284c7;" />
                                                        @elseif ($bKey === 'active_contributor')
                                                            <x-heroicon-s-chat-bubble-left-right style="width:0.68rem;height:0.68rem;color:#6366f1;" />
                                                        @else
                                                            <x-heroicon-s-trophy style="width:0.68rem;height:0.68rem;color:#d97706;" />
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                        <span style="font-size:0.68rem;color:var(--hub-muted);display:inline-flex;align-items:center;gap:0.1rem;flex-shrink:0;">
                                            <x-heroicon-s-trophy style="width:0.65rem;height:0.65rem;color:#f59e0b;" />
                                            <span>{{ $myRowInList['badge_count'] }}</span>
                                        </span>
                                        <span style="font-size:0.74rem;font-weight:800;color:#0f766e;text-align:right;display:inline-flex;align-items:center;justify-content:flex-end;gap:0.1rem;flex-shrink:0;">
                                            <x-heroicon-s-bolt style="width:0.65rem;height:0.65rem;color:#eab308;" />
                                            <span>{{ number_format($myRowInList['xp']) }}</span>
                                        </span>
                                    </div>
                                </div>
                            @elseif ($leaderboard['viewer'])
                                @php $row = $leaderboard['viewer']; @endphp
                                <div style="border-top:1px dashed var(--hub-border);margin-top:0.15rem;padding-top:0.3rem;">
                                    <div style="display:flex;align-items:center;gap:0.3rem;padding:0.28rem 0.45rem;border-radius:0.4rem;border:1px solid color-mix(in oklab, var(--hub-border) 40%, #0f766e 60%);background:color-mix(in oklab, var(--hub-surface) 70%, #0f766e 12%);box-sizing:border-box;width:100%;max-width:100%;min-width:0;overflow:hidden;">
                                        <span style="min-width:1.5rem;flex-shrink:0;text-align:center;font-size:0.76rem;font-weight:800;color:var(--hub-ink);">#{{ $row['rank'] }}</span>
                                        <button
                                            type="button"
                                            wire:click="showProfile({{ $row['user_id'] }})"
                                            style="flex:1;min-width:0;text-align:left;background:none;border:none;padding:0;cursor:pointer;display:inline-flex;align-items:center;gap:0.25rem;font-size:0.78rem;font-weight:700;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:all 0.15s;"
                                            onmouseover="this.style.color='var(--hub-primary, #0f766e)';this.style.textDecoration='underline';"
                                            onmouseout="this.style.color='var(--hub-ink)';this.style.textDecoration='none';"
                                            title="Click to view your XP & Badge earnings"
                                        >
                                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row['name'] }} (you)</span>
                                            <x-heroicon-m-sparkles style="width:0.7rem;height:0.7rem;color:#f59e0b;opacity:0.75;flex-shrink:0;" />
                                        </button>
                                        <span style="font-size:0.68rem;color:var(--hub-muted);display:inline-flex;align-items:center;gap:0.1rem;flex-shrink:0;">
                                            <x-heroicon-s-trophy style="width:0.65rem;height:0.65rem;color:#f59e0b;" />
                                            <span>{{ $row['badge_count'] }}</span>
                                        </span>
                                        <span style="font-size:0.74rem;font-weight:800;color:#0f766e;text-align:right;display:inline-flex;align-items:center;justify-content:flex-end;gap:0.1rem;flex-shrink:0;">
                                            <x-heroicon-s-bolt style="width:0.65rem;height:0.65rem;color:#eab308;" />
                                            <span>{{ number_format($row['xp']) }}</span>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </section>

            {{-- 2. COLLAPSIBLE DISPLAY OF XP EARNED & BADGES --}}
            <section class="hub-card" x-data="{ showXpEarned: false }" style="padding:0.65rem 0.85rem;margin-top:0.5rem;box-sizing:border-box;width:100%;max-width:100%;overflow:hidden;">
                <div @click="showXpEarned = !showXpEarned" style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;gap:0.4rem;width:100%;box-sizing:border-box;">
                    <div style="display:flex;align-items:center;gap:0.35rem;min-width:0;flex:1;overflow:hidden;">
                        <x-heroicon-s-bolt style="width:1rem;height:1rem;color:#eab308;flex-shrink:0;" />
                        <div style="min-width:0;flex:1;overflow:hidden;">
                            <h3 class="hub-title" style="font-size:0.84rem;margin:0;display:flex;align-items:center;gap:0.25rem;flex-wrap:wrap;">
                                <span>XP Earned & Badges</span>
                                <span class="hub-chip hub-chip-primary" style="font-size:0.58rem;padding:0.04rem 0.25rem;">
                                    +{{ number_format($xpBreakdown['total_xp']) }} XP
                                </span>
                            </h3>
                            <p class="hub-copy" style="color:var(--hub-muted);font-size:0.68rem;margin:0.05rem 0 0;display:flex;align-items:center;gap:0.2rem;flex-wrap:wrap;line-height:1.2;">
                                <span>Tier: <strong>{{ $xpBreakdown['rank']['rank_name'] }}</strong> ({{ $xpBreakdown['rank']['multiplier'] }}x)</span>
                                <span>•</span>
                                <span style="display:inline-flex;align-items:center;gap:0.1rem;">
                                    <x-heroicon-s-circle-stack style="width:0.6rem;height:0.6rem;color:#d97706;" />
                                    <strong>{{ number_format($xpBreakdown['total_coins']) }}</strong> TC
                                </span>
                                <span>•</span>
                                <span style="display:inline-flex;align-items:center;gap:0.1rem;">
                                    <x-heroicon-s-trophy style="width:0.6rem;height:0.6rem;color:#f59e0b;" />
                                    <strong>{{ $xpBreakdown['earned_badges']->count() }}</strong> Badges
                                </span>
                            </p>
                        </div>
                    </div>

                    <div
                        style="width:1.6rem;height:1.6rem;min-width:1.6rem;max-width:1.6rem;border-radius:9999px;background:var(--hub-surface);border:1px solid var(--hub-border);display:flex;align-items:center;justify-content:center;color:var(--hub-ink);flex-shrink:0;"
                    >
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;max-width:12px;max-height:12px;flex-shrink:0;transition:transform .2s ease;" :style="showXpEarned ? 'transform:rotate(180deg);' : ''">
                            <path d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Collapsible Content --}}
                <div x-show="showXpEarned" x-collapse style="margin-top:0.55rem;border-top:1px solid var(--hub-border);padding-top:0.5rem;width:100%;box-sizing:border-box;overflow:hidden;">
                    {{-- Mini Metrics Strip (Proportional 4 Columns with Zero Overflow) --}}
                    <div style="display:grid;grid-template-columns:repeat(4, minmax(0, 1fr));gap:0.25rem;margin-bottom:0.55rem;width:100%;box-sizing:border-box;">
                        <div style="padding:0.3rem 0.2rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.4rem;text-align:center;min-width:0;overflow:hidden;box-sizing:border-box;">
                            <div style="display:inline-flex;align-items:center;justify-content:center;gap:0.1rem;font-size:0.58rem;color:var(--hub-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">
                                <x-heroicon-s-bolt style="width:0.58rem;height:0.58rem;color:#0f766e;flex-shrink:0;" />
                                <span style="overflow:hidden;text-overflow:ellipsis;">XP</span>
                            </div>
                            <div style="font-size:0.76rem;font-weight:800;color:#0f766e;margin-top:0.04rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">+{{ number_format($xpBreakdown['total_xp']) }}</div>
                        </div>
                        <div style="padding:0.3rem 0.2rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.4rem;text-align:center;min-width:0;overflow:hidden;box-sizing:border-box;">
                            <div style="display:inline-flex;align-items:center;justify-content:center;gap:0.1rem;font-size:0.58rem;color:var(--hub-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">
                                <x-heroicon-s-circle-stack style="width:0.58rem;height:0.58rem;color:#d97706;flex-shrink:0;" />
                                <span style="overflow:hidden;text-overflow:ellipsis;">Coins</span>
                            </div>
                            <div style="font-size:0.76rem;font-weight:800;color:#d97706;margin-top:0.04rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ number_format($xpBreakdown['total_coins']) }} TC</div>
                        </div>
                        <div style="padding:0.3rem 0.2rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.4rem;text-align:center;min-width:0;overflow:hidden;box-sizing:border-box;">
                            <div style="display:inline-flex;align-items:center;justify-content:center;gap:0.1rem;font-size:0.58rem;color:var(--hub-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">
                                <x-heroicon-s-fire style="width:0.58rem;height:0.58rem;color:#ea580c;flex-shrink:0;" />
                                <span style="overflow:hidden;text-overflow:ellipsis;">Streak</span>
                            </div>
                            <div style="font-size:0.76rem;font-weight:800;color:#ea580c;margin-top:0.04rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $xpBreakdown['streak'] }}d</div>
                        </div>
                        <div style="padding:0.3rem 0.2rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.4rem;text-align:center;min-width:0;overflow:hidden;box-sizing:border-box;">
                            <div style="display:inline-flex;align-items:center;justify-content:center;gap:0.1rem;font-size:0.58rem;color:var(--hub-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;">
                                <x-heroicon-s-shield-check style="width:0.58rem;height:0.58rem;color:#8b5cf6;flex-shrink:0;" />
                                <span style="overflow:hidden;text-overflow:ellipsis;">Rank</span>
                            </div>
                            <div style="font-size:0.76rem;font-weight:800;color:#8b5cf6;margin-top:0.04rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $xpBreakdown['rank']['rank_name'] }}</div>
                        </div>
                    </div>

                    {{-- Badges Earned Showcase --}}
                    <div style="margin-bottom:0.55rem;width:100%;box-sizing:border-box;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.3rem;margin-bottom:0.25rem;">
                            <h4 style="font-size:0.74rem;font-weight:700;margin:0;color:var(--hub-ink);display:inline-flex;align-items:center;gap:0.2rem;">
                                <x-heroicon-s-trophy style="width:0.7rem;height:0.7rem;color:#f59e0b;" />
                                <span>Unlocked Badges</span>
                            </h4>
                            <span style="font-size:0.62rem;color:var(--hub-muted);">
                                {{ $xpBreakdown['earned_badges']->count() }} of {{ $xpBreakdown['total_available_badges'] }}
                            </span>
                        </div>

                        @if ($xpBreakdown['earned_badges']->isEmpty())
                            <div style="padding:0.35rem 0.45rem;background:var(--hub-surface);border:1px dashed var(--hub-border);border-radius:0.35rem;font-size:0.7rem;color:var(--hub-muted);display:flex;align-items:center;gap:0.3rem;">
                                <x-heroicon-o-sparkles style="width:0.8rem;height:0.8rem;color:var(--hub-muted);flex-shrink:0;" />
                                <span>No badges unlocked yet. Complete courses, quizzes, and streaks to earn your first badge!</span>
                            </div>
                        @else
                            <div style="display:flex;flex-wrap:wrap;gap:0.25rem;width:100%;box-sizing:border-box;">
                                @foreach ($xpBreakdown['earned_badges'] as $badge)
                                    <div
                                        class="hub-chip hub-chip-amber"
                                        style="font-size:0.65rem;padding:0.15rem 0.4rem;border-radius:0.35rem;display:inline-flex;align-items:center;gap:0.2rem;border:1px solid color-mix(in oklab, var(--hub-border) 60%, #f59e0b 40%);max-width:100%;box-sizing:border-box;"
                                        title="{{ $badge->description }} (Earned {{ $badge->pivot?->earned_at ? \Illuminate\Support\Carbon::parse($badge->pivot->earned_at)->format('M d, Y') : 'recently' }})"
                                    >
                                        @if ($badge->key === 'course_completed')
                                            <x-heroicon-s-academic-cap style="width:0.7rem;height:0.7rem;color:#0f766e;" />
                                        @elseif (str_contains($badge->key, 'streak'))
                                            <x-heroicon-s-fire style="width:0.7rem;height:0.7rem;color:#ea580c;" />
                                        @elseif ($badge->key === 'first_perfect_quiz')
                                            <x-heroicon-s-check-badge style="width:0.7rem;height:0.7rem;color:#10b981;" />
                                        @elseif ($badge->key === 'mastermind')
                                            <x-heroicon-s-sparkles style="width:0.7rem;height:0.7rem;color:#8b5cf6;" />
                                        @elseif ($badge->key === 'study_networker')
                                            <x-heroicon-s-user-group style="width:0.7rem;height:0.7rem;color:#0284c7;" />
                                        @elseif ($badge->key === 'active_contributor')
                                            <x-heroicon-s-chat-bubble-left-right style="width:0.7rem;height:0.7rem;color:#6366f1;" />
                                        @else
                                            <x-heroicon-s-trophy style="width:0.7rem;height:0.7rem;color:#d97706;" />
                                        @endif
                                        <span style="font-weight:700;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $badge->name }}</span>
                                        @if ($badge->xp_reward > 0)
                                            <span style="font-size:0.58rem;color:#0f766e;font-weight:700;white-space:nowrap;">+{{ $badge->xp_reward }} XP</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Recent XP Activity History --}}
                    @php
                        $allTxs = $xpBreakdown['transactions'];
                        $top5Txs = $allTxs->take(5);
                        $remainingTxs = $allTxs->slice(5);
                    @endphp

                    <div style="margin-top:0.2rem;width:100%;box-sizing:border-box;" x-data="{ showAllXpHistory: false }">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.25rem;">
                            <h4 style="font-size:0.74rem;font-weight:700;margin:0;color:var(--hub-ink);display:inline-flex;align-items:center;gap:0.2rem;">
                                <x-heroicon-s-clock style="width:0.7rem;height:0.7rem;color:var(--hub-muted);" />
                                <span>Recent Point Activity</span>
                            </h4>
                            @if ($allTxs->count() > 5)
                                <span style="font-size:0.62rem;color:var(--hub-muted);">
                                    Showing <span x-text="showAllXpHistory ? '{{ $allTxs->count() }}' : '5'"></span> of {{ $allTxs->count() }}
                                </span>
                            @endif
                        </div>

                        @if ($allTxs->isEmpty())
                            <p class="hub-copy" style="color:var(--hub-muted);font-size:0.7rem;margin:0;font-style:italic;">No points accumulated yet. Complete quizzes or lessons to earn XP & Coins!</p>
                        @else
                            <div style="display:flex;flex-direction:column;gap:0.25rem;width:100%;box-sizing:border-box;">
                                {{-- Top 5 Recent Activities --}}
                                @foreach ($top5Txs as $tx)
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.35rem;padding:0.32rem 0.45rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.35rem;font-size:0.7rem;box-sizing:border-box;width:100%;max-width:100%;min-width:0;">
                                        <div style="min-width:0;flex:1;display:flex;align-items:flex-start;gap:0.3rem;">
                                            @if (str_contains($tx->activity_type ?? $tx->source ?? '', 'quiz'))
                                                <x-heroicon-s-academic-cap style="width:0.75rem;height:0.75rem;color:#0ea5e9;flex-shrink:0;margin-top:0.1rem;" />
                                            @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'video'))
                                                <x-heroicon-s-play-circle style="width:0.75rem;height:0.75rem;color:#8b5cf6;flex-shrink:0;margin-top:0.1rem;" />
                                            @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'streak'))
                                                <x-heroicon-s-fire style="width:0.75rem;height:0.75rem;color:#ea580c;flex-shrink:0;margin-top:0.1rem;" />
                                            @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'badge'))
                                                <x-heroicon-s-trophy style="width:0.75rem;height:0.75rem;color:#f59e0b;flex-shrink:0;margin-top:0.1rem;" />
                                            @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'course'))
                                                <x-heroicon-s-check-badge style="width:0.75rem;height:0.75rem;color:#0f766e;flex-shrink:0;margin-top:0.1rem;" />
                                            @else
                                                <x-heroicon-s-bolt style="width:0.75rem;height:0.75rem;color:#eab308;flex-shrink:0;margin-top:0.1rem;" />
                                            @endif
                                            <div style="min-width:0;flex:1;">
                                                <div style="font-weight:600;color:var(--hub-ink);word-break:break-word;overflow-wrap:anywhere;line-height:1.3;font-size:0.72rem;">
                                                    {{ $tx->description ?: ucfirst(str_replace('_', ' ', $tx->source ?: $tx->activity_type ?: 'Point Reward')) }}
                                                </div>
                                                <span style="font-size:0.6rem;color:var(--hub-muted);display:block;margin-top:0.08rem;">
                                                    {{ $tx->created_at ? $tx->created_at->format('M d, Y · H:i') : 'Recently' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:0.18rem;flex-shrink:0;justify-content:flex-end;margin-top:0.05rem;">
                                            @if (($tx->amount_xp ?: $tx->points) > 0)
                                                <span class="hub-chip hub-chip-primary" style="font-size:0.56rem;padding:0.04rem 0.22rem;white-space:nowrap;">
                                                    +{{ number_format($tx->amount_xp ?: $tx->points) }} XP
                                                </span>
                                            @endif
                                            @if (($tx->amount_coins ?: 0) > 0)
                                                <span class="hub-chip hub-chip-amber" style="font-size:0.56rem;padding:0.04rem 0.22rem;display:inline-flex;align-items:center;gap:0.08rem;white-space:nowrap;">
                                                    <x-heroicon-s-circle-stack style="width:0.52rem;height:0.52rem;color:#d97706;" />
                                                    <span>+{{ number_format($tx->amount_coins) }} TC</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Collapsible Remaining Activities --}}
                                @if ($remainingTxs->isNotEmpty())
                                    <div x-show="showAllXpHistory" x-collapse style="display:flex;flex-direction:column;gap:0.25rem;width:100%;box-sizing:border-box;">
                                        @foreach ($remainingTxs as $tx)
                                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.35rem;padding:0.32rem 0.45rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.35rem;font-size:0.7rem;box-sizing:border-box;width:100%;max-width:100%;min-width:0;">
                                                <div style="min-width:0;flex:1;display:flex;align-items:flex-start;gap:0.3rem;">
                                                    @if (str_contains($tx->activity_type ?? $tx->source ?? '', 'quiz'))
                                                        <x-heroicon-s-academic-cap style="width:0.75rem;height:0.75rem;color:#0ea5e9;flex-shrink:0;margin-top:0.1rem;" />
                                                    @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'video'))
                                                        <x-heroicon-s-play-circle style="width:0.75rem;height:0.75rem;color:#8b5cf6;flex-shrink:0;margin-top:0.1rem;" />
                                                    @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'streak'))
                                                        <x-heroicon-s-fire style="width:0.75rem;height:0.75rem;color:#ea580c;flex-shrink:0;margin-top:0.1rem;" />
                                                    @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'badge'))
                                                        <x-heroicon-s-trophy style="width:0.75rem;height:0.75rem;color:#f59e0b;flex-shrink:0;margin-top:0.1rem;" />
                                                    @elseif (str_contains($tx->activity_type ?? $tx->source ?? '', 'course'))
                                                        <x-heroicon-s-check-badge style="width:0.75rem;height:0.75rem;color:#0f766e;flex-shrink:0;margin-top:0.1rem;" />
                                                    @else
                                                        <x-heroicon-s-bolt style="width:0.75rem;height:0.75rem;color:#eab308;flex-shrink:0;margin-top:0.1rem;" />
                                                    @endif
                                                    <div style="min-width:0;flex:1;">
                                                        <div style="font-weight:600;color:var(--hub-ink);word-break:break-word;overflow-wrap:anywhere;line-height:1.3;font-size:0.72rem;">
                                                            {{ $tx->description ?: ucfirst(str_replace('_', ' ', $tx->source ?: $tx->activity_type ?: 'Point Reward')) }}
                                                        </div>
                                                        <span style="font-size:0.6rem;color:var(--hub-muted);display:block;margin-top:0.08rem;">
                                                            {{ $tx->created_at ? $tx->created_at->format('M d, Y · H:i') : 'Recently' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div style="display:flex;align-items:center;gap:0.18rem;flex-shrink:0;justify-content:flex-end;margin-top:0.05rem;">
                                                    @if (($tx->amount_xp ?: $tx->points) > 0)
                                                        <span class="hub-chip hub-chip-primary" style="font-size:0.56rem;padding:0.04rem 0.22rem;white-space:nowrap;">
                                                            +{{ number_format($tx->amount_xp ?: $tx->points) }} XP
                                                        </span>
                                                    @endif
                                                    @if (($tx->amount_coins ?: 0) > 0)
                                                        <span class="hub-chip hub-chip-amber" style="font-size:0.56rem;padding:0.04rem 0.22rem;display:inline-flex;align-items:center;gap:0.08rem;white-space:nowrap;">
                                                            <x-heroicon-s-circle-stack style="width:0.52rem;height:0.52rem;color:#d97706;" />
                                                            <span>+{{ number_format($tx->amount_coins) }} TC</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Toggle Button --}}
                                    <div style="text-align:center;margin-top:0.25rem;">
                                        <button
                                            type="button"
                                            @click="showAllXpHistory = !showAllXpHistory"
                                            class="hub-chip hub-chip-primary"
                                            style="cursor:pointer;background:var(--hub-surface);border:1px solid var(--hub-border);font-size:0.66rem;padding:0.2rem 0.65rem;border-radius:0.4rem;display:inline-flex;align-items:center;gap:0.25rem;font-weight:600;color:var(--hub-primary,#0f766e);transition:all 0.15s;"
                                        >
                                            <span x-text="showAllXpHistory ? 'Collapse to Recent 5' : 'View More / All ({{ $allTxs->count() }} Activities)'"></span>
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:10px;height:10px;flex-shrink:0;transition:transform .2s ease;" :style="showAllXpHistory ? 'transform:rotate(180deg);' : ''">
                                                <path d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
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
                .community-msg-bundle { display:inline-flex; align-items:flex-end; gap:0.35rem; width:fit-content; max-width:100%; }
                .community-msg-bundle-mine { flex-direction:row-reverse; align-self:flex-end; }
                .community-msg-bundle-theirs { flex-direction:row; align-self:flex-start; }
                .community-bubble {
                    width:fit-content;
                    min-width:68px;
                    max-width:min(78vw, 560px);
                    position:relative;
                    box-sizing:border-box;
                    display:flex;
                    flex-direction:column;
                }
                .community-msg-text {
                    margin:0;
                    font-size:13px;
                    line-height:1.36;
                    white-space:pre-wrap;
                    word-break:normal;
                    overflow-wrap:break-word;
                    min-width:min(100%, 150px);
                }
                .community-msg-author {
                    margin:0 0 0.14rem;
                    font-size:0.75rem;
                    font-weight:700;
                    display:flex;
                    align-items:center;
                    gap:0.25rem;
                    white-space:nowrap;
                    word-break:keep-all;
                    overflow:hidden;
                    text-overflow:ellipsis;
                }
                .community-msg-row { position:relative; width:100%; display:flex; flex-direction:column; margin-top:0.24rem; }
                .community-msg-actions {
                    opacity:0;
                    transition:opacity .15s ease, transform .15s ease;
                    pointer-events:none;
                    display:inline-flex;
                    align-items:center;
                    gap:0.12rem;
                    background:color-mix(in oklab, var(--hub-card) 92%, transparent);
                    backdrop-filter:blur(8px);
                    -webkit-backdrop-filter:blur(8px);
                    border:1px solid color-mix(in oklab, var(--hub-border) 75%, transparent);
                    border-radius:999px;
                    padding:0.12rem 0.22rem;
                    box-shadow:0 2px 8px rgba(0,0,0,.08);
                    flex-shrink:0;
                }
                .community-msg-row:hover .community-msg-actions,
                .community-msg-row:focus-within .community-msg-actions,
                .community-msg-actions[data-active="true"] {
                    opacity:1;
                    pointer-events:auto;
                }
                .community-action-btn {
                    background:none;
                    border:none;
                    padding:0;
                    width:1.45rem;
                    height:1.45rem;
                    border-radius:999px;
                    cursor:pointer;
                    color:var(--hub-muted);
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                    font-size:12px;
                    line-height:1;
                    transition:all .12s ease;
                }
                .community-action-btn:hover {
                    color:var(--hub-ink);
                    background:var(--hub-surface);
                    transform:scale(1.1);
                }
                .community-emoji-picker {
                    display:grid;
                    grid-template-columns:repeat(5, 1fr);
                    gap:0.25rem;
                    padding:0.35rem 0.4rem;
                    background:color-mix(in oklab, var(--hub-card) 96%, transparent);
                    backdrop-filter:blur(16px);
                    -webkit-backdrop-filter:blur(16px);
                    border:1px solid var(--hub-border);
                    border-radius:0.85rem;
                    box-shadow:0 10px 28px rgba(0,0,0,.22), 0 2px 8px rgba(0,0,0,.1);
                    position:absolute;
                    bottom:calc(100% + 6px);
                    z-index:40;
                    width:max-content;
                }
                .community-emoji-btn {
                    background:none;
                    border:none;
                    cursor:pointer;
                    font-size:15px;
                    padding:0.2rem 0.25rem;
                    border-radius:0.4rem;
                    line-height:1;
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                    transition:transform .1s ease, background .1s ease;
                }
                .community-emoji-btn:hover {
                    transform:scale(1.25);
                    background:var(--hub-surface);
                }
                .community-action-menu {
                    position:absolute;
                    bottom:calc(100% + 6px);
                    min-width:125px;
                    padding:0.25rem;
                    background:color-mix(in oklab, var(--hub-card) 96%, transparent);
                    backdrop-filter:blur(16px);
                    -webkit-backdrop-filter:blur(16px);
                    border:1px solid var(--hub-border);
                    border-radius:0.75rem;
                    box-shadow:0 10px 28px rgba(0,0,0,.18), 0 2px 6px rgba(0,0,0,.08);
                    z-index:40;
                    display:flex;
                    flex-direction:column;
                }
                .community-menu-item {
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    gap:0.65rem;
                    width:100%;
                    padding:0.42rem 0.65rem;
                    border:none;
                    background:transparent;
                    border-radius:0.5rem;
                    font-size:12.5px;
                    font-weight:500;
                    color:var(--hub-ink);
                    cursor:pointer;
                    transition:background .1s ease, color .1s ease;
                    text-align:left;
                }
                .community-menu-item:hover {
                    background:var(--hub-surface);
                    color:var(--hub-primary, #0d9488);
                }
                .community-menu-divider {
                    height:1px;
                    background:var(--hub-border);
                    margin:0.15rem 0.25rem;
                    opacity:0.6;
                }
                .community-composer-wrap { display:flex; gap:0.5rem; align-items:center; padding:0.34rem 0.4rem; border:1px solid var(--community-composer-border); border-radius:999px; background:var(--community-composer-bg); backdrop-filter:blur(10px); box-shadow:0 12px 28px rgba(2,6,23,.15); }
                .community-message-input { flex:1 1 auto; min-width:0; color:var(--community-composer-input) !important; }
                .community-message-input::placeholder { color:var(--community-composer-placeholder); opacity:1; }
                .community-attach-btn { flex-shrink:0; width:38px; height:38px; border-color: var(--community-composer-border) !important; color: var(--community-composer-attach-icon) !important; background: var(--community-composer-attach-bg) !important; }
                .community-send-btn {
                    flex-shrink: 0 !important;
                    white-space: nowrap !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 0.35rem !important;
                    padding: 0.5rem 1.15rem !important;
                    min-height: 38px !important;
                    border: none !important;
                    border-radius: 999px !important;
                    cursor: pointer !important;
                    font-size: 0.84rem !important;
                    font-weight: 700 !important;
                    color: #ffffff !important;
                    background: linear-gradient(135deg, #0f766e, #0ea5e9) !important;
                    box-shadow: 0 8px 20px rgba(14, 116, 144, .28) !important;
                    transition: transform 0.15s ease, opacity 0.15s ease;
                }
                .community-send-btn:active {
                    transform: scale(0.96);
                }
                .community-send-btn:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                }
                .community-back-btn { display:none; }
                .community-back-btn:focus-visible { outline:2px solid #22d3ee; outline-offset:2px; }

                @media (max-width: 768px) {
                    .community-chat-layout { grid-template-columns:1fr; gap:0.55rem; }
                    .community-chat-layout[data-room-open="true"] .community-room-list { display:none; }
                    .community-chat-layout[data-room-open="false"] .community-thread { display:none; }
                    .community-room-list, .community-thread { height:calc(100vh - var(--community-mobile-offset)); max-height:none; min-height:var(--community-mobile-min-height); }
                    .community-thread-head { position:sticky; top:0; z-index:5; padding:0.72rem 0.75rem; }
                    .community-bubble { max-width:calc(100% - 3.8rem); }
                    .community-msg-actions {
                        opacity: 0.45;
                        pointer-events: auto;
                        padding: 0.1rem 0.18rem;
                    }
                    .community-msg-row:hover .community-msg-actions,
                    .community-msg-row:focus-within .community-msg-actions,
                    .community-msg-row:active .community-msg-actions,
                    .community-msg-actions:hover,
                    .community-msg-actions:focus-within,
                    .community-msg-actions[data-active="true"] {
                        opacity: 1;
                    }
                    .community-action-btn {
                        width: 1.35rem;
                        height: 1.35rem;
                    }
                    .community-back-btn { display:inline-flex; width:2rem; height:2rem; align-items:center; justify-content:center; border:1px solid var(--hub-border); border-radius:999px; background:var(--hub-surface); color:var(--hub-ink); cursor:pointer; flex:0 0 auto; }
                    .community-composer-wrap { gap:0.36rem; padding:0.26rem 0.3rem; }
                    .community-attach-btn { width:34px !important; height:34px !important; padding:0 !important; }
                    .community-composer-wrap .community-message-input { font-size:14px; min-width:0 !important; flex:1 1 auto !important; padding:0.4rem 0.45rem !important; }
                    .community-send-btn {
                        padding: 0.45rem 1rem !important;
                        min-height: 36px !important;
                        font-size: 0.8rem !important;
                    }
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

                        <div wire:poll.4s style="flex:1;overflow-y:auto;padding:0.75rem 0.85rem 1.1rem;display:flex;flex-direction:column;gap:0.75rem;"
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
                                @php
                                    $mine = $message->user_id === auth()->id();
                                    $author = $message->user;
                                    $palette = $author?->chatColorPalette() ?? [
                                        'accent' => '#0d9488',
                                        'name_color' => '#0f766e',
                                        'bg_light' => '#f0fdfa',
                                        'border_light' => '#99f6e4',
                                    ];
                                    $groupedReactions = $message->getGroupedReactions(auth()->id());
                                @endphp
                                <div id="chat-msg-{{ $message->id }}"
                                    x-data="{ showEmojiPicker: false, showMenu: false, copied: false }"
                                    class="community-msg-row"
                                    style="{{ $mine ? 'align-items:flex-end;' : 'align-items:flex-start;' }}">

                                    {{-- Horizontal bundle: Bubble + Action Bar beside it --}}
                                    <div class="community-msg-bundle {{ $mine ? 'community-msg-bundle-mine' : 'community-msg-bundle-theirs' }}">

                                        {{-- Message Bubble --}}
                                        <div class="community-bubble"
                                            style="padding:0.32rem 0.58rem;border-radius:0.85rem;font-size:13px;line-height:1.35;{{ $mine ? 'background:linear-gradient(135deg,#0f766e,#0ea5e9);color:#fff;border-bottom-right-radius:0.22rem;box-shadow:0 6px 16px rgba(15,118,110,.18);' : 'background:color-mix(in oklab, var(--hub-surface) 92%, ' . $palette['accent'] . ' 8%);color:var(--hub-ink);border:1px solid color-mix(in oklab, var(--hub-border) 70%, ' . $palette['accent'] . ' 30%);border-left:3.5px solid ' . $palette['accent'] . ';border-bottom-left-radius:0.22rem;box-shadow:0 2px 8px rgba(2,6,23,.05);' }}">

                                            {{-- Author Name in course/group rooms --}}
                                            @if (! $mine && $this->activeRoom->type === 'course')
                                                <p class="community-msg-author" style="color:{{ $palette['name_color'] }};">
                                                    <span style="white-space:nowrap;word-break:keep-all;">{{ $author?->first_name ?? 'Student' }}</span>
                                                </p>
                                            @endif

                                            {{-- Quoted Parent Reply (if this message is a reply) --}}
                                            @if ($message->replyTo)
                                                @php
                                                    $quotedAuthor = $message->replyTo->user;
                                                    $quotedPalette = $quotedAuthor?->chatColorPalette();
                                                    $quotedMine = $message->replyTo->user_id === auth()->id();
                                                @endphp
                                                <div onclick="const target = document.getElementById('chat-msg-{{ $message->reply_to_id }}'); if(target){ target.scrollIntoView({behavior:'smooth', block:'center'}); target.style.transition='filter 0.4s ease'; target.style.filter='brightness(1.25)'; setTimeout(() => target.style.filter='', 1200); }"
                                                    title="Jump to quoted message"
                                                    style="cursor:pointer;margin-bottom:0.22rem;padding:0.18rem 0.42rem;border-radius:0.35rem;font-size:11px;border-left:2.5px solid {{ $quotedMine ? '#22d3ee' : ($quotedPalette['accent'] ?? '#0d9488') }};background:{{ $mine ? 'rgba(0,0,0,0.22)' : 'color-mix(in oklab, var(--hub-card) 75%, rgba(15,23,42,0.08) 25%)' }};max-width:100%;overflow:hidden;">
                                                    <div style="font-weight:700;font-size:10px;color:{{ $mine ? '#e0f2fe' : ($quotedPalette['name_color'] ?? 'var(--hub-primary)') }};display:flex;align-items:center;gap:0.2rem;margin-bottom:0.04rem;white-space:nowrap;word-break:keep-all;">
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                                                        <span style="white-space:nowrap;word-break:keep-all;">{{ $quotedMine ? 'You' : ($quotedAuthor?->first_name ?? 'Student') }}</span>
                                                    </div>
                                                    <p style="margin:0;color:{{ $mine ? 'rgba(255,255,255,0.88)' : 'var(--hub-muted)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:10.5px;">
                                                        {{ $message->replyTo->body ?: ($message->replyTo->attachment_name ? '📎 '.$message->replyTo->attachment_name : 'Message') }}
                                                    </p>
                                                </div>
                                            @endif

                                            {{-- Attachments --}}
                                            @php
                                                $allAtts = $message->all_attachments;
                                            @endphp
                                            @if (!empty($allAtts))
                                                <div style="display:flex;flex-direction:column;gap:0.3rem;margin-bottom:{{ $message->body ? '0.3rem' : '0' }};">
                                                    @foreach ($allAtts as $att)
                                                        @if ($att['type'] === 'image')
                                                            <a href="{{ $att['url'] }}" target="_blank" rel="noopener noreferrer" style="display:block;">
                                                                <img src="{{ $att['url'] }}" alt="{{ $att['name'] }}"
                                                                    style="max-width:220px;max-height:220px;border-radius:0.45rem;display:block;object-fit:cover;">
                                                            </a>
                                                        @else
                                                            <a href="{{ $att['url'] }}" target="_blank" rel="noopener noreferrer" download="{{ $att['name'] }}"
                                                                style="display:flex;align-items:center;gap:0.35rem;padding:0.3rem 0.5rem;border-radius:0.45rem;text-decoration:none;{{ $mine ? 'background:rgba(255,255,255,.2);color:#fff;' : 'background:var(--hub-card);color:var(--hub-ink);border:1px solid var(--hub-border);' }}">
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                                <span style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">{{ $att['name'] }}</span>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Body Text --}}
                                            @if ($message->body)
                                                <p class="community-msg-text">{{ trim($message->body) }}</p>
                                            @endif
                                        </div>

                                        {{-- Action toolbar beside the bubble (Emoji + Three Dots) --}}
                                        <div class="community-msg-actions"
                                            :data-active="showEmojiPicker || showMenu">

                                            {{-- Quick Emoji Picker trigger --}}
                                            <div style="position:relative;">
                                                <button type="button"
                                                    @click="showEmojiPicker = !showEmojiPicker; showMenu = false"
                                                    class="community-action-btn"
                                                    title="Add reaction">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                                                </button>

                                                {{-- Popup emoji bar --}}
                                                <div x-show="showEmojiPicker"
                                                    @click.outside="showEmojiPicker = false"
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="opacity-0 transform scale-90"
                                                    x-transition:enter-end="opacity-100 transform scale-100"
                                                    class="community-emoji-picker"
                                                    style="{{ $mine ? 'right:0;' : 'left:0;' }}"
                                                    x-cloak>
                                                    @foreach (['👍', '❤️', '🔥', '👏', '🎉', '🚀', '💯', '✨', '😂', '😍', '🤩', '😎', '🤔', '💡', '🙌', '🙏', '😮', '😢', '💪', '🥳'] as $emoji)
                                                        <button type="button"
                                                            wire:click="toggleReaction({{ $message->id }}, '{{ $emoji }}')"
                                                            @click="showEmojiPicker = false"
                                                            class="community-emoji-btn"
                                                            title="React {{ $emoji }}">
                                                            {{ $emoji }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Three Dots Menu Trigger --}}
                                            <div style="position:relative;">
                                                <button type="button"
                                                    @click="showMenu = !showMenu; showEmojiPicker = false"
                                                    class="community-action-btn"
                                                    title="More options">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                                </button>

                                                {{-- Popup Menu for Reply, Copy, Speak --}}
                                                <div x-show="showMenu"
                                                    @click.outside="showMenu = false"
                                                    x-transition:enter="transition ease-out duration-120"
                                                    x-transition:enter-start="opacity-0 transform scale-95"
                                                    x-transition:enter-end="opacity-100 transform scale-100"
                                                    class="community-action-menu"
                                                    style="{{ $mine ? 'right:0;' : 'left:0;' }}"
                                                    x-cloak>

                                                    {{-- Reply Option --}}
                                                    <button type="button"
                                                        wire:click="setReplyTo({{ $message->id }})"
                                                        @click="showMenu = false"
                                                        class="community-menu-item">
                                                        <span>Reply</span>
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                                                    </button>

                                                    <div class="community-menu-divider"></div>

                                                    {{-- Copy Option --}}
                                                    <button type="button"
                                                        @click="
                                                            const text = @js($message->body ?? '');
                                                            if (text) {
                                                                navigator.clipboard.writeText(text);
                                                                copied = true;
                                                                setTimeout(() => { copied = false; showMenu = false; }, 900);
                                                            } else {
                                                                showMenu = false;
                                                            }
                                                        "
                                                        class="community-menu-item">
                                                        <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                                                        <template x-if="!copied">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                                        </template>
                                                        <template x-if="copied">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                        </template>
                                                    </button>

                                                    @if ($message->body)
                                                        <div class="community-menu-divider"></div>

                                                        {{-- Speak Option --}}
                                                        <button type="button"
                                                            @click="
                                                                const text = @js($message->body);
                                                                if ('speechSynthesis' in window) {
                                                                    window.speechSynthesis.cancel();
                                                                    const utter = new SpeechSynthesisUtterance(text);
                                                                    window.speechSynthesis.speak(utter);
                                                                }
                                                                showMenu = false;
                                                            "
                                                            class="community-menu-item">
                                                            <span>Speak</span>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Emoji Reactions Row --}}
                                    @if (count($groupedReactions) > 0)
                                        <div style="display:flex;flex-wrap:wrap;gap:0.2rem;margin-top:0.18rem;{{ $mine ? 'justify-content:flex-end;' : 'justify-content:flex-start;' }}">
                                            @foreach ($groupedReactions as $reaction)
                                                <button type="button"
                                                    wire:click="toggleReaction({{ $message->id }}, '{{ $reaction['emoji'] }}')"
                                                    title="{{ implode(', ', $reaction['names']) }}"
                                                    style="display:inline-flex;align-items:center;gap:0.2rem;padding:0.08rem 0.38rem;border-radius:999px;font-size:11px;line-height:1;cursor:pointer;border:1px solid {{ $reaction['reacted_by_me'] ? 'var(--hub-primary, #0d9488)' : 'var(--hub-border)' }};background:{{ $reaction['reacted_by_me'] ? 'color-mix(in oklab, var(--hub-surface) 75%, #0d9488 25%)' : 'var(--hub-card)' }};color:var(--hub-ink);transition:transform 0.1s ease;"
                                                    onmouseover="this.style.transform='scale(1.08)'"
                                                    onmouseout="this.style.transform='scale(1)'">
                                                    <span>{{ $reaction['emoji'] }}</span>
                                                    <span style="font-weight:700;font-size:10.5px;">{{ $reaction['count'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Timestamp --}}
                                    <span style="font-size:10.5px;color:#94a3b8;margin-top:0.12rem;">{{ $message->created_at?->format('M d, H:i') }}</span>
                                </div>
                            @empty
                                <p class="hub-copy" style="color:var(--hub-muted);font-size:0.82rem;text-align:center;margin:auto;">No messages yet. Say hi!</p>
                            @endforelse
                        </div>

                        {{-- Composer Area --}}
                        <form wire:submit.prevent="sendMessage"
                            x-data
                            x-on:focus-chat-input.window="$nextTick(() => { const input = $el.querySelector('input[type=text]'); if (input) input.focus(); })"
                            style="display:flex;flex-direction:column;gap:0.4rem;padding:0.68rem 0.75rem;border-top:1px solid var(--hub-border);background:linear-gradient(180deg,rgba(148,163,184,.05),rgba(15,23,42,.12));">

                            {{-- Replying to Banner Preview --}}
                            @if ($this->replyingToMessage)
                                @php
                                    $repUser = $this->replyingToMessage->user;
                                    $repPalette = $repUser?->chatColorPalette();
                                    $repMine = $this->replyingToMessage->user_id === auth()->id();
                                @endphp
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;padding:0.35rem 0.65rem;background:color-mix(in oklab, var(--hub-surface) 90%, {{ $repPalette['accent'] ?? '#0d9488' }} 10%);border-left:3.5px solid {{ $repPalette['accent'] ?? '#0d9488' }};border-radius:0.45rem;font-size:12px;">
                                    <div style="min-width:0;flex:1;">
                                        <div style="display:flex;align-items:center;gap:0.3rem;font-weight:700;color:{{ $repPalette['name_color'] ?? 'var(--hub-primary)' }};font-size:11.5px;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                                            <span>Replying to {{ $repMine ? 'yourself' : ($repUser?->first_name ?? 'Student') }}</span>
                                        </div>
                                        <p style="margin:0.08rem 0 0;color:var(--hub-muted);font-size:11.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $this->replyingToMessage->body ?: ($this->replyingToMessage->attachment_name ? '📎 '.$this->replyingToMessage->attachment_name : 'Attachment') }}
                                        </p>
                                    </div>
                                    <button type="button" wire:click="cancelReply" title="Cancel reply"
                                        style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.2rem;line-height:1;padding:0.2rem;"
                                        onmouseover="this.style.color='#ef4444'"
                                        onmouseout="this.style.color='var(--hub-muted)'">&times;</button>
                                </div>
                            @endif

                            {{-- Selected attachment(s) preview --}}
                            @php
                                $previewFiles = !empty($attachments) ? $attachments : ($attachment ? [$attachment] : []);
                            @endphp
                            @if (!empty($previewFiles))
                                <div style="display:flex;flex-wrap:wrap;gap:0.35rem;padding:0.35rem 0.55rem;background:var(--hub-surface);border-radius:0.45rem;">
                                    @foreach ($previewFiles as $pIdx => $pFile)
                                        <div style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.2rem 0.45rem;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.35rem;max-width:100%;">
                                            @if (method_exists($pFile, 'temporaryUrl') && str_starts_with($pFile->getMimeType() ?? '', 'image/'))
                                                <img src="{{ $pFile->temporaryUrl() }}" alt="" style="width:24px;height:24px;object-fit:cover;border-radius:0.2rem;flex-shrink:0;">
                                            @else
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--hub-muted);flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            @endif
                                            <span style="font-size:0.72rem;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px;">{{ $pFile->getClientOriginalName() }}</span>
                                            <button type="button" wire:click="removeAttachment({{ $pIdx }})" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;line-height:1;padding:0 0.15rem;">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @error('attachments.*')
                                <span style="color:#dc2626;font-size:0.72rem;">{{ $message }}</span>
                            @enderror
                            @error('attachments')
                                <span style="color:#dc2626;font-size:0.72rem;">{{ $message }}</span>
                            @enderror
                            @error('attachment')
                                <span style="color:#dc2626;font-size:0.72rem;">{{ $message }}</span>
                            @enderror

                            <div wire:loading wire:target="attachments,attachment" style="font-size:0.72rem;color:var(--hub-muted);">Uploading attachments…</div>

                            <div class="community-composer-wrap">
                                <label class="community-attach-btn" style="cursor:pointer;display:inline-flex;align-items:center;justify-content:center;border:1px solid color-mix(in oklab, var(--hub-border) 70%, #475569 30%);border-radius:999px;color:var(--hub-muted);background:color-mix(in oklab, var(--hub-card) 82%, #111827 18%);flex-shrink:0;" title="Attach file(s)">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    <input type="file" wire:model="attachments" multiple accept="image/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.zip" style="display:none;">
                                </label>
                                <input type="text" wire:model="messageBody" placeholder="Type a message…" autocomplete="off"
                                    class="community-message-input"
                                    style="flex:1 1 auto;min-width:0;font-size:14px;padding:0.45rem 0.5rem;border:0;outline:0;background:transparent;box-shadow:none;color:var(--hub-ink);-webkit-appearance:none;appearance:none;">
                                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage,attachments,attachment"
                                    class="community-send-btn" title="Send message">
                                    <span>Send</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="transform: rotate(45deg); margin-top:-1px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                </button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>
        @endif

        {{-- ===================== STUDENT XP & BADGE EARNINGS / PROFILE MODAL ===================== --}}
        @if ($profileUser)
            <div wire:click="closeProfile"
                @keydown.escape.window="$wire.closeProfile()"
                style="position:fixed;inset:0;z-index:90;background:rgba(2,6,23,0.7);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;">
                <div wire:click.stop
                    style="width:100%;max-width:540px;max-height:90vh;margin:auto;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:1rem;overflow:hidden;box-shadow:0 25px 60px -15px rgba(0,0,0,0.5);display:flex;flex-direction:column;">
                    
                    {{-- Modal Header --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1.1rem;border-bottom:1px solid var(--hub-border);background:linear-gradient(180deg,rgba(15,118,110,0.08),transparent);">
                        <div style="display:inline-flex;align-items:center;gap:0.45rem;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:1.8rem;height:1.8rem;border-radius:9999px;background:rgba(245,158,11,0.15);color:#f59e0b;">
                                <x-heroicon-s-trophy style="width:1rem;height:1rem;" />
                            </span>
                            <div>
                                <h3 style="margin:0;font-size:0.92rem;font-weight:700;color:var(--hub-ink);line-height:1.2;">
                                    {{ 'XP & Badge Earnings' }}
                                </h3>
                                <p style="margin:0;font-size:0.68rem;color:var(--hub-muted);">
                                    {{ 'Learner Performance & Achievements' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeProfile"
                            style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.4rem;line-height:1;padding:0.2rem;border-radius:0.35rem;transition:all 0.15s;"
                            onmouseover="this.style.color='var(--hub-ink)';this.style.background='rgba(148,163,184,0.1)';"
                            onmouseout="this.style.color='var(--hub-muted)';this.style.background='none';"
                            title="Close modal">&times;</button>
                    </div>

                    {{-- Modal Scrollable Body --}}
                    <div style="padding:1.1rem;overflow-y:auto;display:flex;flex-direction:column;gap:0.95rem;max-height:calc(90vh - 4.5rem);">
                        
                        {{-- Hero Section --}}
                        <div style="display:flex;align-items:center;gap:0.85rem;padding:0.85rem 1rem;background:linear-gradient(135deg,color-mix(in oklab, var(--hub-surface) 90%, #0f766e 10%),var(--hub-surface));border:1px solid var(--hub-border);border-radius:0.75rem;">
                            <div style="position:relative;flex-shrink:0;">
                                @if ($profileUser['avatar'])
                                    <img src="{{ $profileUser['avatar'] }}" alt="{{ $profileUser['name'] }}"
                                        style="width:3.6rem;height:3.6rem;border-radius:999px;object-fit:cover;border:2px solid #0f766e;box-shadow:0 0 12px rgba(15,118,110,0.25);">
                                @else
                                    <span style="width:3.6rem;height:3.6rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:linear-gradient(135deg,#0f766e,#14b8a6);color:#ffffff;font-size:1.3rem;font-weight:800;border:2px solid rgba(255,255,255,0.2);box-shadow:0 0 12px rgba(15,118,110,0.25);">
                                        {{ strtoupper(substr($profileUser['name'], 0, 1)) }}
                                    </span>
                                @endif
                                <span style="position:absolute;bottom:-0.2rem;right:-0.2rem;background:#0f766e;color:#fff;border-radius:999px;padding:0.1rem 0.35rem;font-size:0.6rem;font-weight:800;border:1.5px solid var(--hub-card);">
                                    #{{ $profileUser['rank_position'] }}
                                </span>
                            </div>

                            <div style="min-width:0;flex:1;">
                                <div style="display:flex;align-items:center;gap:0.4rem;flex-wrap:wrap;">
                                    <h4 style="margin:0;font-size:1.05rem;font-weight:800;color:var(--hub-ink);line-height:1.2;">
                                        {{ $profileUser['name'] }}
                                    </h4>
                                    @if ($profileUser['is_self'])
                                        <span class="hub-chip hub-chip-primary" style="font-size:0.65rem;padding:0.08rem 0.35rem;font-weight:700;">You</span>
                                    @endif
                                </div>

                                <div style="display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;margin-top:0.35rem;">
                                    <span class="hub-chip hub-chip-primary" style="font-size:0.68rem;padding:0.1rem 0.45rem;display:inline-flex;align-items:center;gap:0.2rem;">
                                        <x-heroicon-s-sparkles style="width:0.75rem;height:0.75rem;color:#f59e0b;" />
                                        <span>{{ $profileUser['rank_tier']['rank_name'] ?? 'Scholar' }} ({{ $profileUser['rank_tier']['multiplier'] ?? '1.0' }}x)</span>
                                    </span>
                                    @if (($profileUser['streak'] ?? 0) > 0)
                                        <span class="hub-chip hub-chip-amber" style="font-size:0.68rem;padding:0.1rem 0.45rem;display:inline-flex;align-items:center;gap:0.2rem;">
                                            <x-heroicon-s-fire style="width:0.75rem;height:0.75rem;color:#ea580c;" />
                                            <span>{{ $profileUser['streak'] }}-Day Streak</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Bio (if present) --}}
                        @if (filled($profileUser['bio']))
                            <div style="padding:0.55rem 0.75rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.5rem;font-size:0.78rem;color:var(--hub-ink);line-height:1.45;">
                                {{ $profileUser['bio'] }}
                            </div>
                        @endif

                        {{-- Key Stats Grid --}}
                        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:0.5rem;">
                            <div style="padding:0.6rem 0.4rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.55rem;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.15rem;">
                                <x-heroicon-s-bolt style="width:1.1rem;height:1.1rem;color:#eab308;" />
                                <span style="font-size:0.88rem;font-weight:800;color:var(--hub-ink);line-height:1;">
                                    {{ number_format($profileUser['xp']) }}
                                </span>
                                <span style="font-size:0.64rem;color:var(--hub-muted);font-weight:600;text-transform:uppercase;">Lifetime XP</span>
                            </div>

                            <div style="padding:0.6rem 0.4rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.55rem;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.15rem;">
                                <x-heroicon-s-circle-stack style="width:1.1rem;height:1.1rem;color:#d97706;" />
                                <span style="font-size:0.88rem;font-weight:800;color:var(--hub-ink);line-height:1;">
                                    {{ number_format($profileUser['coins']) }}
                                </span>
                                <span style="font-size:0.64rem;color:var(--hub-muted);font-weight:600;text-transform:uppercase;">Coins</span>
                            </div>

                            <div style="padding:0.6rem 0.4rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.55rem;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.15rem;">
                                <x-heroicon-s-trophy style="width:1.1rem;height:1.1rem;color:#f59e0b;" />
                                <span style="font-size:0.88rem;font-weight:800;color:var(--hub-ink);line-height:1;">
                                    {{ $profileUser['badge_count'] }}
                                </span>
                                <span style="font-size:0.64rem;color:var(--hub-muted);font-weight:600;text-transform:uppercase;">Badges</span>
                            </div>

                            <div style="padding:0.6rem 0.4rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.55rem;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.15rem;">
                                <x-heroicon-o-book-open style="width:1.1rem;height:1.1rem;color:#0f766e;" />
                                <span style="font-size:0.88rem;font-weight:800;color:var(--hub-ink);line-height:1;">
                                    {{ $profileUser['courses_count'] }}
                                </span>
                                <span style="font-size:0.64rem;color:var(--hub-muted);font-weight:600;text-transform:uppercase;">Courses</span>
                            </div>
                        </div>

                        {{-- Badges Showcase Section --}}
                        <div style="display:flex;flex-direction:column;gap:0.45rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <h4 style="margin:0;font-size:0.8rem;font-weight:700;color:var(--hub-ink);display:inline-flex;align-items:center;gap:0.3rem;">
                                    <x-heroicon-s-trophy style="width:0.85rem;height:0.85rem;color:#f59e0b;" />
                                    <span>{{ 'Earned Badges & Accolades' }} ({{ count($profileUser['badges']) }})</span>
                                </h4>
                            </div>

                            @if (count($profileUser['badges']) > 0)
                                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(230px, 1fr));gap:0.45rem;">
                                    @foreach ($profileUser['badges'] as $badge)
                                        <div style="display:flex;align-items:flex-start;gap:0.45rem;padding:0.45rem 0.6rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.55rem;">
                                            <span class="hub-chip hub-chip-amber" style="width:1.9rem;height:1.9rem;padding:0;border-radius:0.45rem;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:0.05rem;">
                                                @if (($badge['key'] ?? '') === 'course_completed')
                                                    <x-heroicon-s-academic-cap style="width:1.1rem;height:1.1rem;color:#0f766e;" />
                                                @elseif (str_contains($badge['key'] ?? '', 'streak'))
                                                    <x-heroicon-s-fire style="width:1.1rem;height:1.1rem;color:#ea580c;" />
                                                @elseif (($badge['key'] ?? '') === 'first_perfect_quiz')
                                                    <x-heroicon-s-check-badge style="width:1.1rem;height:1.1rem;color:#10b981;" />
                                                @elseif (($badge['key'] ?? '') === 'mastermind')
                                                    <x-heroicon-s-sparkles style="width:1.1rem;height:1.1rem;color:#8b5cf6;" />
                                                @elseif (($badge['key'] ?? '') === 'study_networker')
                                                    <x-heroicon-s-user-group style="width:1.1rem;height:1.1rem;color:#0284c7;" />
                                                @elseif (($badge['key'] ?? '') === 'active_contributor')
                                                    <x-heroicon-s-chat-bubble-left-right style="width:1.1rem;height:1.1rem;color:#6366f1;" />
                                                @else
                                                    <x-heroicon-s-trophy style="width:1.1rem;height:1.1rem;color:#d97706;" />
                                                @endif
                                            </span>
                                            <div style="min-width:0;flex:1;">
                                                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.3rem;">
                                                    <span style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                        {{ $badge['name'] }}
                                                    </span>
                                                    @if (($badge['xp_reward'] ?? 0) > 0)
                                                        <span style="font-size:0.62rem;font-weight:700;color:#0f766e;flex-shrink:0;">
                                                            +{{ $badge['xp_reward'] }} XP
                                                        </span>
                                                    @endif
                                                </div>
                                                <p style="margin:0.05rem 0 0;font-size:0.68rem;color:var(--hub-muted);line-height:1.3;">
                                                    {{ $badge['description'] ?: 'Special achievement earned.' }}
                                                </p>
                                                <span style="font-size:0.62rem;color:var(--hub-muted);opacity:0.8;display:block;margin-top:0.15rem;">
                                                    Earned {{ $badge['earned_at'] ?? 'Recently' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="padding:0.75rem;text-align:center;background:var(--hub-surface);border:1px dashed var(--hub-border);border-radius:0.5rem;">
                                    <x-heroicon-o-trophy style="width:1.5rem;height:1.5rem;color:var(--hub-muted);margin:0 auto 0.2rem;" />
                                    <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);font-style:italic;">No badges unlocked yet.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Recent XP Activity Section --}}
                        <div style="display:flex;flex-direction:column;gap:0.45rem;" x-data="{ showAllModalEarnings: false }">
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <h4 style="margin:0;font-size:0.8rem;font-weight:700;color:var(--hub-ink);display:inline-flex;align-items:center;gap:0.3rem;">
                                    <x-heroicon-s-clock style="width:0.85rem;height:0.85rem;color:var(--hub-muted);" />
                                    <span>{{ 'Recent XP & Point Earnings' }}</span>
                                </h4>
                                @if (!empty($profileUser['recent_transactions']) && count($profileUser['recent_transactions']) > 5)
                                    <span class="hub-chip hub-chip-gray" style="font-size:0.6rem;padding:0.06rem 0.35rem;">
                                        Showing <span x-text="showAllModalEarnings ? '{{ count($profileUser['recent_transactions']) }}' : '5'"></span> of {{ count($profileUser['recent_transactions']) }}
                                    </span>
                                @endif
                            </div>

                            @if (!empty($profileUser['recent_transactions']))
                                @php
                                    $allModalTxs = $profileUser['recent_transactions'];
                                    $top5ModalTxs = array_slice($allModalTxs, 0, 5);
                                    $remainingModalTxs = array_slice($allModalTxs, 5);
                                @endphp

                                <div style="display:flex;flex-direction:column;gap:0.28rem;">
                                    {{-- Top 5 Activities --}}
                                    @foreach ($top5ModalTxs as $tx)
                                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.4rem;padding:0.35rem 0.5rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.45rem;font-size:0.74rem;">
                                            <div style="min-width:0;flex:1;display:flex;align-items:flex-start;gap:0.35rem;">
                                                @if (str_contains($tx['activity_type'] ?? '', 'quiz'))
                                                    <x-heroicon-s-academic-cap style="width:0.85rem;height:0.85rem;color:#0ea5e9;flex-shrink:0;margin-top:0.1rem;" />
                                                @elseif (str_contains($tx['activity_type'] ?? '', 'video'))
                                                    <x-heroicon-s-play-circle style="width:0.85rem;height:0.85rem;color:#8b5cf6;flex-shrink:0;margin-top:0.1rem;" />
                                                @elseif (str_contains($tx['activity_type'] ?? '', 'streak'))
                                                    <x-heroicon-s-fire style="width:0.85rem;height:0.85rem;color:#ea580c;flex-shrink:0;margin-top:0.1rem;" />
                                                @elseif (str_contains($tx['activity_type'] ?? '', 'badge'))
                                                    <x-heroicon-s-trophy style="width:0.85rem;height:0.85rem;color:#f59e0b;flex-shrink:0;margin-top:0.1rem;" />
                                                @elseif (str_contains($tx['activity_type'] ?? '', 'course'))
                                                    <x-heroicon-s-check-badge style="width:0.85rem;height:0.85rem;color:#0f766e;flex-shrink:0;margin-top:0.1rem;" />
                                                @else
                                                    <x-heroicon-s-bolt style="width:0.85rem;height:0.85rem;color:#eab308;flex-shrink:0;margin-top:0.1rem;" />
                                                @endif
                                                <div style="min-width:0;flex:1;">
                                                    <div style="font-weight:600;color:var(--hub-ink);word-break:break-word;overflow-wrap:anywhere;line-height:1.35;font-size:0.75rem;">
                                                        {{ $tx['description'] }}
                                                    </div>
                                                    <span style="font-size:0.62rem;color:var(--hub-muted);display:block;margin-top:0.1rem;">
                                                        {{ $tx['created_at'] }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div style="display:flex;align-items:center;gap:0.25rem;flex-shrink:0;margin-top:0.05rem;">
                                                @if ($tx['amount_xp'] > 0)
                                                    <span class="hub-chip hub-chip-primary" style="font-size:0.62rem;padding:0.06rem 0.3rem;white-space:nowrap;">
                                                        +{{ number_format($tx['amount_xp']) }} XP
                                                    </span>
                                                @endif
                                                @if ($tx['amount_coins'] > 0)
                                                    <span class="hub-chip hub-chip-amber" style="font-size:0.62rem;padding:0.06rem 0.3rem;display:inline-flex;align-items:center;gap:0.1rem;white-space:nowrap;">
                                                        <x-heroicon-s-circle-stack style="width:0.6rem;height:0.6rem;color:#d97706;" />
                                                        <span>+{{ number_format($tx['amount_coins']) }} TC</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Collapsible Remaining Activities --}}
                                    @if (!empty($remainingModalTxs))
                                        <div x-show="showAllModalEarnings" x-collapse style="display:flex;flex-direction:column;gap:0.28rem;">
                                            @foreach ($remainingModalTxs as $tx)
                                                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.4rem;padding:0.35rem 0.5rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.45rem;font-size:0.74rem;">
                                                    <div style="min-width:0;flex:1;display:flex;align-items:flex-start;gap:0.35rem;">
                                                        @if (str_contains($tx['activity_type'] ?? '', 'quiz'))
                                                            <x-heroicon-s-academic-cap style="width:0.85rem;height:0.85rem;color:#0ea5e9;flex-shrink:0;margin-top:0.1rem;" />
                                                        @elseif (str_contains($tx['activity_type'] ?? '', 'video'))
                                                            <x-heroicon-s-play-circle style="width:0.85rem;height:0.85rem;color:#8b5cf6;flex-shrink:0;margin-top:0.1rem;" />
                                                        @elseif (str_contains($tx['activity_type'] ?? '', 'streak'))
                                                            <x-heroicon-s-fire style="width:0.85rem;height:0.85rem;color:#ea580c;flex-shrink:0;margin-top:0.1rem;" />
                                                        @elseif (str_contains($tx['activity_type'] ?? '', 'badge'))
                                                            <x-heroicon-s-trophy style="width:0.85rem;height:0.85rem;color:#f59e0b;flex-shrink:0;margin-top:0.1rem;" />
                                                        @elseif (str_contains($tx['activity_type'] ?? '', 'course'))
                                                            <x-heroicon-s-check-badge style="width:0.85rem;height:0.85rem;color:#0f766e;flex-shrink:0;margin-top:0.1rem;" />
                                                        @else
                                                            <x-heroicon-s-bolt style="width:0.85rem;height:0.85rem;color:#eab308;flex-shrink:0;margin-top:0.1rem;" />
                                                        @endif
                                                        <div style="min-width:0;flex:1;">
                                                            <div style="font-weight:600;color:var(--hub-ink);word-break:break-word;overflow-wrap:anywhere;line-height:1.35;font-size:0.75rem;">
                                                                {{ $tx['description'] }}
                                                            </div>
                                                            <span style="font-size:0.62rem;color:var(--hub-muted);display:block;margin-top:0.1rem;">
                                                                {{ $tx['created_at'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div style="display:flex;align-items:center;gap:0.25rem;flex-shrink:0;margin-top:0.05rem;">
                                                        @if ($tx['amount_xp'] > 0)
                                                            <span class="hub-chip hub-chip-primary" style="font-size:0.62rem;padding:0.06rem 0.3rem;white-space:nowrap;">
                                                                +{{ number_format($tx['amount_xp']) }} XP
                                                            </span>
                                                        @endif
                                                        @if ($tx['amount_coins'] > 0)
                                                            <span class="hub-chip hub-chip-amber" style="font-size:0.62rem;padding:0.06rem 0.3rem;display:inline-flex;align-items:center;gap:0.1rem;white-space:nowrap;">
                                                                <x-heroicon-s-circle-stack style="width:0.6rem;height:0.6rem;color:#d97706;" />
                                                                <span>+{{ number_format($tx['amount_coins']) }} TC</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- View More / View All Toggle Button --}}
                                        <div style="text-align:center;margin-top:0.25rem;">
                                            <button
                                                type="button"
                                                @click="showAllModalEarnings = !showAllModalEarnings"
                                                class="hub-chip hub-chip-primary"
                                                style="cursor:pointer;background:var(--hub-surface);border:1px solid var(--hub-border);font-size:0.7rem;padding:0.25rem 0.8rem;border-radius:0.45rem;display:inline-flex;align-items:center;gap:0.3rem;font-weight:600;color:var(--hub-primary,#0f766e);transition:all 0.15s;"
                                            >
                                                <span x-text="showAllModalEarnings ? 'Collapse to Recent 5' : 'View More / All ({{ count($allModalTxs) }} Activities)'"></span>
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;flex-shrink:0;transition:transform .2s ease;" :style="showAllModalEarnings ? 'transform:rotate(180deg);' : ''">
                                                    <path d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div style="padding:0.55rem;text-align:center;background:var(--hub-surface);border:1px dashed var(--hub-border);border-radius:0.45rem;">
                                    <p style="margin:0;font-size:0.72rem;color:var(--hub-muted);font-style:italic;">No recent point activity recorded.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Shared Courses --}}
                        @if (!empty($profileUser['shared_courses']))
                            <div style="padding:0.45rem 0.6rem;background:color-mix(in oklab, var(--hub-surface) 80%, #0f766e 8%);border:1px solid var(--hub-border);border-radius:0.5rem;font-size:0.74rem;">
                                <strong style="color:#0f766e;">Shared Courses:</strong>
                                <span style="color:var(--hub-ink);">{{ implode(' · ', $profileUser['shared_courses']) }}</span>
                            </div>
                        @endif

                        {{-- Friendship Action (if not self) --}}
                        @if (! $profileUser['is_self'])
                            <div style="display:flex;justify-content:center;gap:0.5rem;padding-top:0.4rem;border-top:1px solid var(--hub-border);">
                                @if ($profileUser['friendship']['state'] === 'friends')
                                    <span style="font-size:0.82rem;color:#0f766e;font-weight:700;padding:0.35rem 0;display:inline-flex;align-items:center;gap:0.3rem;">
                                        <x-heroicon-s-check-circle style="width:1.05rem;height:1.05rem;" />
                                        <span>Connected Friends</span>
                                    </span>
                                @elseif ($profileUser['friendship']['state'] === 'sent')
                                    <span style="font-size:0.8rem;color:var(--hub-muted);padding:0.35rem 0;">Friend request sent</span>
                                    <button type="button" wire:click="removeFriend({{ $profileUser['id'] }})"
                                        style="font-size:0.76rem;padding:0.35rem 0.85rem;background:none;border:1px solid var(--hub-border);color:var(--hub-ink);border-radius:0.45rem;cursor:pointer;">Cancel request</button>
                                @elseif ($profileUser['friendship']['state'] === 'incoming')
                                    <button type="button" wire:click="acceptRequest({{ $profileUser['friendship']['friendship_id'] }})"
                                        style="font-size:0.76rem;padding:0.35rem 0.85rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.45rem;cursor:pointer;font-weight:600;">Accept request</button>
                                    <button type="button" wire:click="declineRequest({{ $profileUser['friendship']['friendship_id'] }})"
                                        style="font-size:0.76rem;padding:0.35rem 0.85rem;background:none;border:1px solid var(--hub-border);color:var(--hub-ink);border-radius:0.45rem;cursor:pointer;">Decline</button>
                                @else
                                    <button type="button" wire:click="sendRequest({{ $profileUser['id'] }})"
                                        style="font-size:0.78rem;padding:0.38rem 1rem;background:var(--hub-primary,#0d9488);color:#fff;border:none;border-radius:0.45rem;cursor:pointer;font-weight:600;display:inline-flex;align-items:center;gap:0.3rem;">
                                        <x-heroicon-s-user-plus style="width:0.85rem;height:0.85rem;" />
                                        <span>Add Friend</span>
                                    </button>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
