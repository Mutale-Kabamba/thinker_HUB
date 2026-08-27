<x-filament-panels::page>

    @if ($tab === 'chats')
        <style>
            .fi-header,
            header.fi-header,
            .fi-page-header {
                display: none !important;
            }
            .fi-main-ctn,
            .fi-page,
            .fi-main {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
            .hub-shell {
                padding: 0 !important;
                margin: 0 !important;
            }
        </style>
    @endif

    <div class="hub-shell">

    @if ($tab !== 'chats')
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
                <div class="grid grid-cols-4 gap-1 max-w-xl w-full p-1 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <button type="button" wire:click="$set('tab','chats')"
                        class="py-2 px-1 rounded-xl text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center gap-1 sm:gap-1.5 {{ $tab === 'chats' ? 'hub-tab-chats-active text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}"
                        style="{{ $tab === 'chats' ? 'background-color:#008069;color:#ffffff;' : '' }}">
                        <x-heroicon-s-chat-bubble-left-right class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                        <span>Chats</span>
                    </button>
                    <button type="button" wire:click="$set('tab','results')"
                        class="py-2 px-1 rounded-xl text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center gap-1 sm:gap-1.5 {{ $tab === 'results' ? 'bg-[#7C3AED] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}"
                        style="{{ $tab === 'results' ? 'background-color:#7C3AED;color:#ffffff;' : '' }}">
                        <x-heroicon-s-chart-bar class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                        <span class="hidden xs:inline">Score Board</span>
                        <span class="xs:hidden">Scores</span>
                    </button>
                    <button type="button" wire:click="$set('tab','friends')"
                        class="py-2 px-1 rounded-xl text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center gap-1 sm:gap-1.5 {{ $tab === 'friends' ? 'bg-[#7C3AED] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}"
                        style="{{ $tab === 'friends' ? 'background-color:#7C3AED;color:#ffffff;' : '' }}">
                        <x-heroicon-s-user-group class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                        <span>Friends</span>
                        @if ($this->pendingRequests->count() > 0)
                            <span class="bg-rose-600 text-white rounded-full text-[9px] px-1.5 py-0.5 leading-none shrink-0">{{ $this->pendingRequests->count() }}</span>
                        @endif
                    </button>
                    <button type="button" wire:click="$set('tab','leaderboard')"
                        class="py-2 px-1 rounded-xl text-[11px] sm:text-xs font-bold transition-all flex items-center justify-center gap-1 sm:gap-1.5 {{ $tab === 'leaderboard' ? 'bg-[#7C3AED] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}"
                        style="{{ $tab === 'leaderboard' ? 'background-color:#7C3AED;color:#ffffff;' : '' }}">
                        <x-heroicon-s-trophy class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                        <span class="hidden xs:inline">Leaderboard</span>
                        <span class="xs:hidden">Ranks</span>
                    </button>
                </div>
            </div>
        </section>
    @endif

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

        {{-- ===================== SCORE BOARD TAB ===================== --}}
        @if ($tab === 'results')
            @php
                $resultsData = $this->results;
                $resStats = $resultsData['stats'];
                $resItems = $resultsData['items'];
                $resTasks = $resultsData['tasks'] ?? collect();
                $activeTask = $resultsData['active_task'] ?? $resTasks->first();
            @endphp
            <div class="space-y-4" x-data="{ showMyRecords: false }">
                {{-- Recent Graded Tasks Filter Selector (Mobile-first, no search bar) --}}
                @if ($resTasks->isNotEmpty())
                    <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#111b21] border border-slate-200 dark:border-slate-800 shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-[#7C3AED] dark:text-purple-300 flex items-center justify-center shrink-0 border border-purple-200/50 dark:border-purple-800/40">
                                <x-heroicon-s-funnel class="w-4 h-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <label for="taskFilterDropdown" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-0.5">
                                    Recent Graded Tasks
                                </label>
                                <select 
                                    id="taskFilterDropdown"
                                    wire:change="selectTask($event.target.value)"
                                    class="w-full text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-800 dark:text-white py-2 pl-3 pr-8 focus:ring-2 focus:ring-[#7C3AED] focus:border-transparent outline-none transition cursor-pointer">
                                    @foreach ($resTasks as $task)
                                        <option value="{{ $task['id'] }}" @selected(($activeTask['id'] ?? '') === $task['id'])>
                                            {{ $task['short_title'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Active Graded Task Score Board Card & Candidates List --}}
                    @if ($activeTask)
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111b21] shadow-xs overflow-hidden">
                            {{-- Task Header Banner --}}
                            <div class="p-3 sm:p-4 bg-slate-50 dark:bg-[#111b21] border-b border-slate-200/80 dark:border-slate-800/80 flex flex-col xs:flex-row xs:items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase {{
                                            $activeTask['type'] === 'quiz' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300' :
                                             ($activeTask['type'] === 'assignment' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300')
                                        }}">
                                            {{ $activeTask['short_title'] }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate">{{ $activeTask['course'] }}</span>
                                    </div>
                                    <h3 class="text-xs sm:text-sm md:text-base font-black text-slate-900 dark:text-white leading-snug break-words">
                                        {{ $activeTask['title'] }}
                                    </h3>
                                </div>

                                <div class="flex items-center gap-3 shrink-0 pt-1 xs:pt-0 border-t xs:border-t-0 border-slate-100 dark:border-slate-800/60 text-right">
                                    <div>
                                        <span class="text-[8px] xs:text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Candidates</span>
                                        <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">{{ $activeTask['candidates_count'] }} graded</span>
                                    </div>
                                    <div class="border-l border-slate-200 dark:border-slate-700 pl-2.5">
                                        <span class="text-[8px] xs:text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Class Avg</span>
                                        <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $activeTask['average_score'] }}%</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Candidates Score List (Mobile-safe fit, no clipping) --}}
                            <div class="p-1.5 sm:p-3 divide-y divide-slate-100 dark:divide-slate-800/60">
                                @if (!empty($activeTask['candidates']) && count($activeTask['candidates']) > 0)
                                    @foreach ($activeTask['candidates'] as $candidate)
                                        <div class="py-2 px-1.5 sm:px-2.5 flex items-center justify-between gap-1.5 xs:gap-2 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 rounded-xl transition {{ $candidate['is_self'] ? 'bg-purple-50/40 dark:bg-purple-950/20' : '' }}">
                                            <div class="flex items-center gap-1.5 xs:gap-2 min-w-0 flex-1">
                                                {{-- Candidate Rank Badge --}}
                                                <div class="w-5 h-5 xs:w-6 xs:h-6 rounded-md xs:rounded-lg shrink-0 flex items-center justify-center font-black text-[10px] xs:text-[11px] {{
                                                    $candidate['rank'] === 1 ? 'bg-amber-400 text-white shadow-xs' :
                                                    ($candidate['rank'] === 2 ? 'bg-slate-300 dark:bg-slate-600 text-slate-800 dark:text-white' :
                                                    ($candidate['rank'] === 3 ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'))
                                                }}">
                                                    #{{ $candidate['rank'] }}
                                                </div>

                                                <img src="{{ $candidate['candidate_avatar'] }}" alt="{{ $candidate['candidate_name'] }}" class="w-6 h-6 xs:w-7 xs:h-7 sm:w-8 sm:h-8 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-700" />

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1 flex-wrap">
                                                        <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white break-words leading-tight">
                                                            {{ $candidate['candidate_name'] }}
                                                        </span>
                                                        @if ($candidate['is_self'])
                                                            <span class="px-1 py-0.1 rounded text-[8px] xs:text-[9px] font-extrabold bg-[#7C3AED] text-white shrink-0">YOU</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-[9px] xs:text-[10px] text-slate-400 dark:text-slate-500 block truncate">Graded {{ $candidate['graded_at'] }}</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-1 xs:gap-1.5 shrink-0 text-right">
                                                <span class="px-1 xs:px-1.5 py-0.5 rounded text-[8px] xs:text-[9px] sm:text-[10px] font-bold {{ 
                                                    $candidate['status_color'] === 'success' 
                                                        ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' 
                                                        : ($candidate['status_color'] === 'warning' 
                                                            ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' 
                                                            : 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300') 
                                                }}">
                                                    {{ $candidate['status'] }}
                                                </span>
                                                <div class="text-xs xs:text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 min-w-[34px] xs:min-w-[40px] text-right">
                                                    {{ $candidate['score'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-6 text-center text-xs text-slate-500 dark:text-slate-400">
                                        No candidates found for this task.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="p-8 text-center rounded-2xl bg-white dark:bg-[#111b21] border border-slate-200 dark:border-slate-800">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-[#7C3AED] dark:text-purple-400 mx-auto flex items-center justify-center mb-3">
                            <x-heroicon-o-document-magnifying-glass class="w-6 h-6" />
                        </div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">No recent graded tasks found</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Graded quizzes, assignments, and assessments will appear here.</p>
                    </div>
                @endif

                {{-- Optional Toggle: My Personal Evaluation Records --}}
                @if ($resItems->isNotEmpty())
                    <div class="pt-2">
                        <button type="button" @click="showMyRecords = !showMyRecords"
                            class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <x-heroicon-s-clipboard-document-check class="w-4 h-4 text-[#7C3AED]" />
                                <span>My Personal Records ({{ $resStats['total_completed'] }})</span>
                            </span>
                            <span x-text="showMyRecords ? 'Hide ▲' : 'Show ▼'" class="text-[10px] text-slate-400 font-semibold"></span>
                        </button>

                        <div x-show="showMyRecords" class="mt-3 space-y-3" style="display: none;">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($resItems as $item)
                                    <div class="p-3.5 rounded-2xl bg-white dark:bg-[#111b21] border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between gap-2.5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                                <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center font-black text-xs {{ 
                                                    $item['type'] === 'quiz' 
                                                        ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50' 
                                                        : ($item['type'] === 'assignment' 
                                                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' 
                                                            : 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50') 
                                                }}">
                                                    {{ $item['type_badge'] }}
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded {{ 
                                                            $item['type'] === 'quiz' 
                                                                ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' 
                                                                : ($item['type'] === 'assignment' 
                                                                    ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' 
                                                                    : 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300') 
                                                        }}">
                                                            {{ $item['type_label'] }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ $item['date_formatted'] }}</span>
                                                    </div>

                                                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white leading-snug break-words mt-1">
                                                        {{ $item['title'] }}
                                                    </h4>
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $item['course'] }}</p>
                                                </div>
                                            </div>

                                            <div class="flex flex-col items-end shrink-0 gap-1 text-right">
                                                <div class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400">
                                                    {{ $item['score_display'] }}
                                                </div>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ 
                                                    $item['status_color'] === 'success' 
                                                        ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' 
                                                        : ($item['status_color'] === 'warning' 
                                                            ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' 
                                                            : 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300') 
                                                }}">
                                                    {{ $item['status'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===================== CHATS TAB (WHATSAPP FULL-SCREEN UI) ===================== --}}
        @if ($tab === 'chats')
            @php
                $allRooms = $this->rooms;
                $activeRoom = $this->activeRoom;
            @endphp

            @if ($selectedRoomId && $activeRoom)
                {{-- 1. FULLSCREEN LOCKED CHAT ROOM (ZERO PAGE SCROLLING, WHATSAPP IMMERSIVE) --}}
                <div class="fixed inset-0 top-0 sm:top-[64px] z-50 flex flex-col bg-[#efeae2] dark:bg-[#0b141a] overflow-hidden text-gray-900 dark:text-gray-100">

                    {{-- FIXED TOP CHAT HEADER --}}
                    <header class="h-14 bg-white dark:bg-[#202c33] px-3 sm:px-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700/60 flex-shrink-0 z-10 shadow-xs">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                            <button type="button" wire:click="closeRoom" class="p-1.5 -ml-1 text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition shrink-0" title="Back to chat list">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            
                            @php
                                $displayName = $activeRoom->displayNameFor(auth()->user());
                                $isCourse = $activeRoom->type === 'course';
                                $otherUser = $activeRoom->members->firstWhere('id', '!=', auth()->id());
                                $activeAvatar = $isCourse ? null : $otherUser?->getFilamentAvatarUrl();
                            @endphp

                            @if ($isCourse)
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#00a884] flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-xs">
                                    <x-heroicon-s-academic-cap class="w-5 h-5" />
                                </div>
                            @elseif ($activeAvatar)
                                <img src="{{ $activeAvatar }}" alt="{{ $displayName }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                            @else
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#00a884] flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-xs">
                                    {{ strtoupper(substr($displayName, 0, 2)) }}
                                </div>
                            @endif
                            
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate leading-tight">{{ $displayName }}</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate leading-tight">
                                    @if ($isCourse)
                                        {{ $activeRoom->members->count() }} cohort members · {{ $activeRoom->course?->title ?? 'Course' }}
                                    @else
                                        Direct peer conversation
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0 ml-2">
                            <button type="button" wire:click="$refresh" class="p-1.5 text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Refresh messages">
                                <x-heroicon-o-arrow-path class="w-5 h-5" />
                            </button>
                        </div>
                    </header>

                    {{-- SCROLLABLE MESSAGES STREAM (ONLY THIS SCROLLS) --}}
                    <main 
                        class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-3 bg-repeat" 
                        style="background-image: radial-gradient(rgba(0,0,0,0.04) 1px, transparent 0); background-size: 16px 16px;"
                        x-data="{
                            scrollToBottom() {
                                $el.scrollTop = $el.scrollHeight;
                            }
                        }"
                        x-init="$nextTick(() => scrollToBottom())"
                        x-on:message-sent.window="$nextTick(() => scrollToBottom())"
                    >
                        @if ($this->hasMoreMessages)
                            <div class="text-center my-1.5">
                                <button type="button" wire:click="loadMoreMessages" class="px-3 py-1 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 shadow-xs hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    Load earlier messages
                                </button>
                            </div>
                        @endif

                        @forelse ($this->messages as $msg)
                            @php
                                $isMe = $msg->user_id === auth()->id();
                                $groupedReactions = $msg->getGroupedReactions(auth()->id());
                                $author = $msg->user;
                                $authorName = $author ? ($author->first_name ?: $author->name) : 'Participant';
                                $palette = $author?->chatColorPalette() ?? ['name_color' => '#d97706'];
                            @endphp
                            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $msg->id }}">
                                <div 
                                    class="group relative max-w-[85%] sm:max-w-[70%] rounded-2xl px-3.5 py-2 shadow-xs text-sm {{ 
                                        $isMe 
                                            ? 'bg-[#d9fdd3] dark:bg-[#005c4b] text-gray-900 dark:text-gray-100 rounded-tr-none' 
                                            : 'bg-white dark:bg-[#202c33] text-gray-900 dark:text-gray-100 rounded-tl-none' 
                                    }}"
                                    x-data="{ showMenu: false, copied: false }"
                                >
                                    {{-- Author Name for Received Messages --}}
                                    @if (! $isMe)
                                        <p class="text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-0.5" style="color: {{ $palette['name_color'] ?? '#d97706' }};">
                                            {{ $authorName }}
                                        </p>
                                    @endif

                                    {{-- Replying to Quote Preview --}}
                                    @if ($msg->replyTo)
                                        @php
                                            $repUser = $msg->replyTo->user;
                                            $repMine = $msg->replyTo->user_id === auth()->id();
                                        @endphp
                                        <div class="p-1.5 px-2 mb-1.5 rounded-lg bg-black/5 dark:bg-white/10 border-l-3 text-xs space-y-0.5" style="border-left-color:#00a884;">
                                            <span class="font-bold text-[#00a884] block leading-tight">
                                                {{ $repMine ? 'You' : ($repUser?->first_name ?? 'Participant') }}
                                            </span>
                                            <p class="text-gray-600 dark:text-gray-300 truncate leading-tight">
                                                {{ $msg->replyTo->body ?: 'Attachment' }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Message Body Text --}}
                                    @if ($msg->body)
                                        <p class="leading-relaxed break-words whitespace-pre-line text-xs sm:text-sm">
                                            {{ $msg->body }}
                                        </p>
                                    @endif

                                    {{-- Attachments & Files --}}
                                    @php
                                        $rawAtts = $msg->attachments;
                                        $attachmentsList = is_array($rawAtts) ? $rawAtts : (is_string($rawAtts) ? json_decode($rawAtts, true) : []);
                                        if (empty($attachmentsList) && $msg->attachment_path) {
                                            $attachmentsList = [[
                                                'path' => $msg->attachment_path,
                                                'name' => $msg->attachment_name ?: 'File',
                                                'type' => $msg->attachment_type ?: 'file',
                                            ]];
                                        }
                                    @endphp
                                    @if (!empty($attachmentsList))
                                        <div class="space-y-1.5 my-1.5">
                                            @foreach ($attachmentsList as $att)
                                                @php
                                                    $url = \Illuminate\Support\Facades\Storage::disk('public')->url($att['path'] ?? '');
                                                    $isImage = ($att['type'] ?? '') === 'image' || preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $att['path'] ?? '');
                                                @endphp
                                                @if ($isImage)
                                                    <a href="{{ $url }}" target="_blank" class="block rounded-lg overflow-hidden border border-black/10 dark:border-white/10">
                                                        <img src="{{ $url }}" alt="{{ $att['name'] ?? 'Image' }}" class="max-h-60 w-full object-cover">
                                                    </a>
                                                @else
                                                    <a href="{{ $url }}" target="_blank" download class="p-2 rounded-lg bg-black/5 dark:bg-black/20 hover:bg-black/10 dark:hover:bg-black/30 flex items-center gap-2 text-xs transition">
                                                        <span class="text-base font-bold">📄</span>
                                                        <span class="truncate flex-1 font-medium">{{ $att['name'] ?? basename($att['path'] ?? 'Document') }}</span>
                                                        <x-heroicon-s-arrow-down-tray class="w-4 h-4 text-[#00a884] shrink-0" />
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Footer: Timestamp & Dual Checkmarks for Self --}}
                                    <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-gray-500 dark:text-gray-400 select-none leading-none">
                                        <span>{{ $msg->created_at?->format('H:i') }}</span>
                                        @if ($isMe)
                                            <span class="text-sky-500 font-bold tracking-tighter" title="Delivered">✓✓</span>
                                        @endif
                                    </div>

                                    {{-- Quick Action Hover Menu --}}
                                    <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-0.5 bg-white/90 dark:bg-[#111b21]/90 backdrop-blur-md rounded-full px-1 py-0.5 shadow-xs border border-gray-200 dark:border-gray-700">
                                        <button type="button" wire:click="setReplyTo({{ $msg->id }})" class="text-gray-500 hover:text-gray-800 dark:hover:text-white p-0.5 transition" title="Reply">
                                            <x-heroicon-m-arrow-uturn-left class="w-3 h-3" />
                                        </button>
                                        <button type="button" wire:click="toggleReaction({{ $msg->id }}, '👍')" class="text-gray-500 hover:text-gray-800 dark:hover:text-white p-0.5 transition text-[10px]" title="Like">
                                            👍
                                        </button>
                                        <div class="relative">
                                            <button type="button" @click="showMenu = !showMenu" class="text-gray-500 hover:text-gray-800 dark:hover:text-white p-0.5 transition">
                                                <x-heroicon-m-chevron-down class="w-3 h-3" />
                                            </button>
                                            <div x-show="showMenu" @click.away="showMenu = false" x-transition class="absolute right-0 top-full mt-1 w-28 bg-white dark:bg-[#202c33] rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-0.5 z-30 text-xs">
                                                <button type="button" wire:click="setReplyTo({{ $msg->id }})" @click="showMenu = false" class="w-full text-left px-2.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between text-gray-700 dark:text-gray-200 text-[11px]">
                                                    <span>Reply</span>
                                                    <x-heroicon-m-arrow-uturn-left class="w-3 h-3" />
                                                </button>
                                                <button type="button" @click="navigator.clipboard.writeText(@js($msg->body ?? '')); copied = true; setTimeout(() => { copied = false; showMenu = false; }, 900);" class="w-full text-left px-2.5 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between text-gray-700 dark:text-gray-200 text-[11px]">
                                                    <span x-text="copied ? 'Copied!' : 'Copy'">Copy</span>
                                                    <x-heroicon-m-clipboard class="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Emoji Reactions Row --}}
                            @if (count($groupedReactions) > 0)
                                <div class="flex flex-wrap gap-1 -mt-1.5 {{ $isMe ? 'justify-end' : 'justify-start' }}">
                                    @foreach ($groupedReactions as $reaction)
                                        <button 
                                            type="button" 
                                            wire:click="toggleReaction({{ $msg->id }}, '{{ $reaction['emoji'] }}')" 
                                            title="{{ implode(', ', $reaction['names']) }}" 
                                            class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded-full text-[10px] border transition {{ 
                                                $reaction['reacted_by_me'] 
                                                    ? 'bg-[#00a884]/15 border-[#00a884] text-[#008069] dark:text-emerald-300 font-bold' 
                                                    : 'bg-white/80 dark:bg-gray-800/80 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300' 
                                            }}"
                                        >
                                            <span>{{ $reaction['emoji'] }}</span>
                                            <span class="text-[9px] font-bold">{{ $reaction['count'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @empty
                            <div class="m-auto text-center p-6 text-gray-400 dark:text-gray-500 text-xs">
                                <p>No messages yet in this chat. Start the conversation!</p>
                            </div>
                        @endforelse
                    </main>

                    {{-- Replying-To Active Banner --}}
                    @if ($this->replyingToMessage)
                        @php
                            $repUser = $this->replyingToMessage->user;
                            $repMine = $this->replyingToMessage->user_id === auth()->id();
                        @endphp
                        <div class="bg-gray-50 dark:bg-[#202c33] px-3.5 py-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2 shrink-0">
                            <div class="border-l-3 border-[#00a884] pl-2 min-w-0 flex-1">
                                <span class="text-[11px] font-bold text-[#00a884] block">
                                    Replying to {{ $repMine ? 'yourself' : ($repUser?->first_name ?? 'Participant') }}
                                </span>
                                <p class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                    {{ $this->replyingToMessage->body ?: 'Attachment' }}
                                </p>
                            </div>
                            <button type="button" wire:click="cancelReply" class="text-gray-400 hover:text-rose-500 text-lg leading-none p-1">
                                &times;
                            </button>
                        </div>
                    @endif

                    {{-- FIXED BOTTOM INPUT BAR --}}
                    <footer class="p-2 sm:p-3 bg-white dark:bg-[#202c33] flex items-center gap-2 flex-shrink-0 border-t border-gray-200 dark:border-gray-700/60 pb-[calc(env(safe-area-inset-bottom,0.75rem)+0.25rem)] z-10">
                        {{-- Attachment Trigger --}}
                        <label class="p-2 rounded-full text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition shrink-0" title="Attach file">
                            <x-heroicon-o-paper-clip class="w-5 h-5" />
                            <input type="file" wire:model="attachments" multiple class="hidden">
                        </label>

                        {{-- Pill Input Field --}}
                        <input 
                            type="text" 
                            wire:model="messageBody" 
                            wire:keydown.enter="sendMessage" 
                            placeholder="Type a message..." 
                            class="flex-1 bg-gray-100 dark:bg-[#2a3942] border-0 rounded-full px-4 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-1 focus:ring-[#00a884] outline-none"
                        />

                        {{-- Round Emerald Send Button --}}
                        <button 
                            type="button" 
                            wire:click="sendMessage" 
                            wire:loading.attr="disabled"
                            class="w-10 h-10 rounded-full bg-[#00a884] hover:bg-[#008f6f] text-white flex items-center justify-center flex-shrink-0 shadow-xs transition-colors disabled:opacity-50"
                            title="Send message"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </footer>

                </div>
            @else
                {{-- 2. COMMUNITY CHAT LIST (FIXED STICKY HEADER, INDEPENDENTLY SCROLLING CONVERSATION LIST) --}}
                <div class="w-full h-[calc(100dvh-64px)] flex flex-col overflow-hidden bg-slate-50 dark:bg-[#0b141a]">

                    <!-- 1. STATIC / STICKY TOP SECTION (DOES NOT SCROLL) -->
                    <div class="flex-shrink-0 px-4 pt-3 pb-2 space-y-3 bg-slate-50 dark:bg-[#0b141a] border-b border-gray-200/60 dark:border-gray-800/60">
                        
                        <!-- Header & XP Badge -->
                        <div class="flex items-center justify-between">
                            <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Community</h1>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs text-xs font-semibold">
                                <span class="text-amber-500">⚡</span>
                                <span class="text-gray-900 dark:text-white">{{ number_format($this->myXp['xp'] ?? 500) }} XP</span>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <span class="text-amber-500">⭐</span>
                                <span class="text-gray-900 dark:text-white">{{ $this->myXp['badge_count'] ?? 2 }} badges</span>
                                <span>💯</span>
                            </div>
                        </div>

                        <!-- Navigation Tabs -->
                        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                            <button type="button" wire:click="$set('tab', 'chats')" class="flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold transition-colors {{ $tab === 'chats' ? 'bg-[#008069] text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <span>💬</span> Chats
                            </button>
                            <button type="button" wire:click="$set('tab', 'results')" class="flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span>📊</span> Scores
                            </button>
                            <button type="button" wire:click="$set('tab', 'friends')" class="flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span>👥</span> Friends
                                @if ($this->pendingRequests->count() > 0)
                                    <span class="bg-rose-600 text-white rounded-full text-[9px] px-1.5 py-0.5 leading-none shrink-0">{{ $this->pendingRequests->count() }}</span>
                                @endif
                            </button>
                            <button type="button" wire:click="$set('tab', 'leaderboard')" class="flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span>🏆</span> Ranks
                            </button>
                        </div>

                        <!-- Card Header & Filter Bar -->
                        <div class="bg-white dark:bg-[#111b21] rounded-t-2xl p-3 border-t border-x border-gray-200 dark:border-gray-800 space-y-2.5 shadow-xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-bold text-sm">
                                        💻
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">Community Chats</h3>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Thinker HUB</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="$set('tab','friends')" class="p-1.5 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition" title="Friends & Directory">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </button>
                            </div>

                            <!-- Search Field -->
                            <div class="relative">
                                <input 
                                    type="search" 
                                    wire:model.live.debounce.300ms="chatSearch" 
                                    placeholder="Search or start new chat" 
                                    class="w-full bg-gray-100 dark:bg-[#202c33] border-0 rounded-xl pl-9 pr-3 py-2 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-1 focus:ring-[#00a884] outline-none"
                                >
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <!-- Filter Pills -->
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="$set('chatFilter', 'all')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $chatFilter === 'all' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">All</button>
                                <button type="button" wire:click="$set('chatFilter', 'groups')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $chatFilter === 'groups' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">Cohorts / Groups</button>
                                <button type="button" wire:click="$set('chatFilter', 'direct')" class="px-3 py-1 rounded-full text-xs font-semibold transition-colors {{ $chatFilter === 'direct' ? 'bg-[#008069] text-white shadow-xs' : 'bg-gray-100 dark:bg-[#202c33] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">Direct DMs</button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. SCROLLABLE CHAT ITEMS (ONLY THIS LIST SCROLLS) -->
                    <div class="flex-1 overflow-y-auto overscroll-contain bg-white dark:bg-[#111b21] divide-y divide-gray-100 dark:divide-gray-800/60 px-2 pb-[env(safe-area-inset-bottom,1.5rem)] border-b border-x border-gray-200 dark:border-gray-800 rounded-b-2xl">
                        @forelse($allRooms as $chat)
                            @php
                                $isCourse = $chat->type === 'course';
                                $displayName = $chat->displayNameFor(auth()->user());
                                $lastMsg = $chat->latestMessage;
                                $otherUser = $chat->members->firstWhere('id', '!=', auth()->id());
                                $avatar = $isCourse ? null : $otherUser?->getFilamentAvatarUrl();
                                $initials = strtoupper(substr($displayName, 0, 2));
                                $lastSender = $lastMsg ? ($lastMsg->user_id === auth()->id() ? 'You' : ($lastMsg->user?->first_name ?? 'Member')) : null;
                                $lastMsgPreview = $lastMsg ? ($lastMsg->body ?: ($lastMsg->attachment_name ? '📎 '.$lastMsg->attachment_name : 'Sent an attachment')) : 'No messages yet';
                                $badgeLabel = $isCourse ? ($chat->course?->code ?? 'Cohort') : null;
                            @endphp
                            <div 
                                wire:click="selectRoom({{ $chat->id }})"
                                class="flex items-center gap-3.5 px-3 py-3.5 hover:bg-gray-50 dark:hover:bg-[#202c33]/70 active:bg-gray-100 dark:active:bg-[#202c33] cursor-pointer transition-colors"
                                wire:key="chat-room-{{ $chat->id }}"
                            >
                                <!-- Avatar with Online Dot -->
                                <div class="relative flex-shrink-0">
                                    @if ($isCourse)
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#00a884] to-[#008069] text-white font-bold flex items-center justify-center text-base shadow-xs">
                                            <x-heroicon-s-academic-cap class="w-6 h-6" />
                                        </div>
                                    @elseif ($avatar)
                                        <img src="{{ $avatar }}" alt="{{ $displayName }}" class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-base shadow-xs">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    @if(!$isCourse)
                                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white dark:border-[#111b21] rounded-full"></span>
                                    @endif
                                </div>

                                <!-- Chat Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $displayName }}</h4>
                                        @if ($lastMsg)
                                            <span class="text-[11px] text-gray-400 font-medium shrink-0">{{ $lastMsg->created_at?->format('H:i') }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            @if ($lastSender)
                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $lastSender }}:</span>
                                            @endif
                                            {{ $lastMsgPreview }}
                                        </p>
                                        @if($badgeLabel)
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded shrink-0">
                                                {{ $badgeLabel }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-xs text-gray-400">
                                No chats found.
                            </div>
                        @endforelse
                    </div>

                </div>
            @endif
        @endif

        {{-- ===================== STUDENT XP & BADGE EARNINGS / PROFILE MODAL ===================== --}}
        @if ($profileUser)
            <div wire:click="closeProfile"
                @keydown.escape.window="$wire.closeProfile()"
                style="position:fixed;inset:0;z-index:90;background:rgba(2,6,23,0.7);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;">
                <div wire:click.stop
                    style="width:100%;max-width:540px;max-height:90vh;margin:auto;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:1rem;overflow:hidden;box-shadow:0 25px 60px -15px rgba(0,0,0,0.5);display:flex;flex-direction:column;">
                    
                    {{-- Modal Header --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1.1rem;border-bottom:1px solid var(--hub-border);background:var(--hub-surface);">
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
                        <div style="display:flex;align-items:center;gap:0.85rem;padding:0.85rem 1rem;background:var(--hub-surface-soft);border:1px solid var(--hub-border);border-radius:0.75rem;">
                            <div style="position:relative;flex-shrink:0;">
                                @if ($profileUser['avatar'])
                                    <img src="{{ $profileUser['avatar'] }}" alt="{{ $profileUser['name'] }}"
                                        style="width:3.6rem;height:3.6rem;border-radius:999px;object-fit:cover;border:2px solid #0f766e;box-shadow:0 0 12px rgba(15,118,110,0.25);">
                                @else
                                    <span style="width:3.6rem;height:3.6rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:#0f766e;color:#ffffff;font-size:1.3rem;font-weight:800;border:2px solid rgba(255,255,255,0.2);box-shadow:0 0 12px rgba(15,118,110,0.25);">
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
