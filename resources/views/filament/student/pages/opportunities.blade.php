<x-filament-panels::page>

    <div
        x-data="{
            copied: null,
            shareOpen: null,
            emojiOpen: null,
            emojiInput: {},
            descriptionModalOpen: false,
            descriptionModalTitle: '',
            descriptionModalBody: '',
            copy(code, id) {
                navigator.clipboard.writeText(code).then(() => {
                    this.copied = id;
                    setTimeout(() => { if (this.copied === id) this.copied = null; }, 1500);
                });
            },
            copyLink(url, id) {
                if (!url) return;
                navigator.clipboard.writeText(url).then(() => {
                    this.copied = 'link-' + id;
                    setTimeout(() => { if (this.copied === 'link-' + id) this.copied = null; }, 1500);
                });
            },
            whatsapp(url, title) {
                window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + (url || '')), '_blank');
            },
            facebook(url) {
                if (!url) return;
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank');
            },
            sms(url, title) {
                window.open('sms:?&body=' + encodeURIComponent(title + ' ' + (url || '')), '_self');
            },
            openDescription(title, body) {
                this.descriptionModalTitle = title || 'Details';
                this.descriptionModalBody = body || '';
                this.descriptionModalOpen = true;
            }
        }"
    >
    <div class="hub-shell">

        <section style="padding:0.35rem 0.5rem 0.6rem;">
            <p class="hub-eyebrow">Opportunities</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Grow Your Career</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Promo codes, job openings, scholarships, events, and recommended reading — curated for you.</p>
            <div style="height:1px;background:var(--hub-border);margin-top:0.65rem;"></div>
        </section>

        <section style="padding:0.35rem 0.5rem 0.7rem;">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
                <select wire:model.live="filterType" class="hub-input" style="max-width:200px;font-size:0.8rem;padding:0.3rem 0.5rem;">
                    <option value="">All Types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div style="height:1px;background:var(--hub-border);margin:0.7rem 0 0;"></div>
        </section>

        @if (count($opportunities) === 0)
            <section class="hub-card" style="padding:1.25rem 1rem;text-align:center;">
                <p class="hub-copy" style="color:var(--hub-muted);margin:0;">No opportunities available right now. Check back soon.</p>
            </section>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:0.85rem;">
                @foreach ($opportunities as $item)
                    @php
                        $badgeColor = match ($item['type']) {
                            'Promo Code' => '#d97706',
                            'Job' => '#16a34a',
                            'Reading Material' => '#0284c7',
                            'Scholarship' => '#0d9488',
                            'Event' => '#dc2626',
                            default => '#64748b',
                        };
                    @endphp
                    <div class="hub-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">
                        <div style="padding:0.75rem 0.9rem;display:flex;flex-direction:column;gap:0.4rem;flex:1;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;">
                                <span style="background:{{ $badgeColor }};color:#fff;font-size:0.65rem;font-weight:600;padding:0.15rem 0.5rem;border-radius:999px;">{{ $item['type'] }}</span>
                                @if ($item['expires_at'])
                                    <span style="font-size:0.68rem;color:var(--hub-muted);">Expires {{ $item['expires_at'] }}</span>
                                @endif
                            </div>

                            <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.92rem;line-height:1.3;">{{ $item['title'] }}</p>

                            @if ($item['provider'])
                                <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);">{{ $item['provider'] }}</p>
                            @endif

                            @if ($item['description'])
                                @php
                                    $descriptionLength = mb_strlen($item['description']);
                                    $isLongDescription = $descriptionLength > 220;
                                @endphp
                                <p class="hub-copy" style="margin:0.1rem 0 0;font-size:0.8rem;">{{ $isLongDescription ? \Illuminate\Support\Str::limit($item['description'], 220) : $item['description'] }}</p>
                                @if ($isLongDescription)
                                    <button
                                        type="button"
                                        x-on:click="openDescription(@js($item['title']), @js($item['description']))"
                                        style="align-self:flex-start;margin-top:0.1rem;padding:0;border:none;background:transparent;color:#22d3ee;font-size:0.75rem;font-weight:600;cursor:pointer;"
                                    >
                                        Read more
                                    </button>
                                @endif
                            @endif

                            @php
                                $extra = $item['extra'] ?? [];
                            @endphp

                            @if ($item['type'] === 'Job')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.35rem;margin-top:0.2rem;">
                                    @if (!empty($extra['role']))
                                        <div style="font-size:0.72rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Role:</strong> {{ $extra['role'] }}</div>
                                    @endif
                                    @if (!empty($extra['location']))
                                        <div style="font-size:0.72rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Location:</strong> {{ $extra['location'] }}</div>
                                    @endif
                                    @if (!empty($extra['job_mode']))
                                        <div style="font-size:0.72rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Mode:</strong> {{ $extra['job_mode'] }}</div>
                                    @endif
                                    @if (!empty($extra['salary']))
                                        <div style="font-size:0.72rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Pay:</strong> {{ $extra['salary'] }}</div>
                                    @endif
                                </div>
                            @endif

                            @if ($item['type'] === 'Scholarship')
                                <div style="display:flex;flex-direction:column;gap:0.2rem;margin-top:0.2rem;">
                                    @if (!empty($extra['value']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Value:</strong> {{ $extra['value'] }}</p>
                                    @endif
                                    @if (!empty($extra['eligibility']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Eligibility:</strong> {{ $extra['eligibility'] }}</p>
                                    @endif
                                    @if (!empty($extra['host']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Host:</strong> {{ $extra['host'] }}</p>
                                    @endif
                                </div>
                            @endif

                            @if ($item['type'] === 'Event')
                                <div style="display:flex;flex-direction:column;gap:0.2rem;margin-top:0.2rem;">
                                    @if (!empty($extra['event_date']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Date:</strong> {{ \Illuminate\Support\Carbon::parse($extra['event_date'])->format('M d, Y') }}</p>
                                    @endif
                                    @if (!empty($extra['location']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Venue:</strong> {{ $extra['location'] }}</p>
                                    @endif
                                    @if (!empty($extra['host']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Organizer:</strong> {{ $extra['host'] }}</p>
                                    @endif
                                </div>
                            @endif

                            @if ($item['type'] === 'Reading Material')
                                <div style="display:flex;flex-direction:column;gap:0.2rem;margin-top:0.2rem;">
                                    @if (!empty($extra['author']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Author:</strong> {{ $extra['author'] }}</p>
                                    @endif
                                    @if (!empty($extra['format']))
                                        <p style="margin:0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Format:</strong> {{ $extra['format'] }}</p>
                                    @endif
                                </div>
                            @endif

                            @if ($item['promo_code'])
                                <div style="display:flex;align-items:center;gap:0.4rem;margin-top:0.3rem;">
                                    <code style="background:var(--hub-surface);border:1px dashed var(--hub-border);padding:0.25rem 0.55rem;border-radius:0.4rem;font-size:0.82rem;font-weight:700;letter-spacing:0.03em;color:var(--hub-ink);">{{ $item['promo_code'] }}</code>
                                    <button
                                        type="button"
                                        @click="copy(@js($item['promo_code']), {{ $item['id'] }})"
                                        style="font-size:0.72rem;padding:0.25rem 0.5rem;border:1px solid var(--hub-border);border-radius:0.4rem;background:var(--hub-card);cursor:pointer;color:var(--hub-ink);"
                                    >
                                        <span x-show="copied !== {{ $item['id'] }}">Copy</span>
                                        <span x-show="copied === {{ $item['id'] }}" x-cloak>Copied!</span>
                                    </button>
                                </div>
                            @endif

                            @if ($item['type'] === 'Promo Code' && !empty($extra['amount']))
                                <p style="margin:0.15rem 0 0;font-size:0.74rem;color:var(--hub-muted);"><strong style="color:var(--hub-ink);font-weight:600;">Discount:</strong> {{ $extra['amount'] }}</p>
                            @endif

                            <div style="display:flex;align-items:center;justify-content:space-between;gap:0.4rem;margin-top:0.45rem;padding-top:0.45rem;border-top:1px solid var(--hub-border);">
                                <div style="display:flex;align-items:center;gap:0.28rem;flex-wrap:wrap;">
                                    @foreach ($item['reactions'] as $reaction)
                                        <button
                                            type="button"
                                            wire:click="toggleReaction({{ $item['id'] }}, @js($reaction['emoji']))"
                                            title="{{ implode(', ', $reaction['users']) }}"
                                            style="display:inline-flex;align-items:center;gap:0.22rem;padding:0.18rem 0.38rem;border-radius:999px;border:1px solid {{ !empty($reaction['mine']) ? 'color-mix(in oklab, var(--hub-primary) 65%, #0ea5e9 35%)' : 'var(--hub-border)' }};background:{{ !empty($reaction['mine']) ? 'color-mix(in oklab, var(--hub-primary) 16%, transparent 84%)' : 'transparent' }};font-size:0.72rem;color:var(--hub-ink);cursor:pointer;"
                                            x-tooltip.raw="{{ implode(', ', $reaction['users']) ?: 'No reactions yet' }}"
                                        >
                                            <span>{{ $reaction['emoji'] }}</span>
                                            <span style="font-size:0.66rem;color:var(--hub-muted);">{{ $reaction['count'] }}</span>
                                        </button>
                                    @endforeach

                                    <div style="position:relative;">
                                        <button
                                            type="button"
                                            x-on:click="emojiOpen = emojiOpen === {{ $item['id'] }} ? null : {{ $item['id'] }}"
                                            title="React"
                                            style="display:inline-flex;align-items:center;justify-content:center;width:1.9rem;height:1.9rem;border-radius:999px;border:1px solid var(--hub-border);background:transparent;color:var(--hub-ink);cursor:pointer;"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                                        </button>
                                        <div
                                            x-show="emojiOpen === {{ $item['id'] }}"
                                            x-cloak
                                            x-transition.opacity
                                            style="display:none;position:absolute;bottom:2.2rem;left:0;z-index:40;min-width:220px;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.7rem;padding:0.45rem;box-shadow:0 14px 30px rgba(2,6,23,.35);"
                                        >
                                            <div style="display:flex;gap:0.25rem;flex-wrap:wrap;margin-bottom:0.35rem;">
                                                @foreach (['🔥','👍','❤️','🎉','👏','😮','💯','🚀','🙌','🥳'] as $quickEmoji)
                                                    <button type="button" wire:click="toggleReaction({{ $item['id'] }}, @js($quickEmoji))" x-on:click="emojiOpen=null" style="border:none;background:transparent;cursor:pointer;font-size:1rem;">{{ $quickEmoji }}</button>
                                                @endforeach
                                            </div>
                                            <div style="display:flex;gap:0.3rem;align-items:center;">
                                                <input type="text" x-model="emojiInput[{{ $item['id'] }}]" placeholder="Any emoji" style="flex:1;padding:0.25rem 0.4rem;border:1px solid var(--hub-border);border-radius:0.4rem;background:var(--hub-surface);color:var(--hub-ink);font-size:0.72rem;" />
                                                <button type="button" x-on:click="$wire.toggleReaction({{ $item['id'] }}, emojiInput[{{ $item['id'] }}] || ''); emojiInput[{{ $item['id'] }}]=''; emojiOpen=null" style="padding:0.25rem 0.45rem;border-radius:0.4rem;border:1px solid var(--hub-border);background:transparent;color:var(--hub-ink);font-size:0.7rem;">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;gap:0.28rem;">
                                    <div style="position:relative;">
                                        <button
                                            type="button"
                                            x-on:click="shareOpen = shareOpen === {{ $item['id'] }} ? null : {{ $item['id'] }}"
                                            title="Share"
                                            style="width:1.9rem;height:1.9rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid var(--hub-border);background:transparent;color:var(--hub-ink);cursor:pointer;"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                        </button>
                                        <div
                                            x-show="shareOpen === {{ $item['id'] }}"
                                            x-cloak
                                            x-transition.opacity
                                            style="display:none;position:absolute;bottom:2.2rem;right:0;z-index:40;min-width:220px;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.7rem;padding:0.35rem;box-shadow:0 14px 30px rgba(2,6,23,.35);"
                                        >
                                            <button type="button" x-on:click="whatsapp(@js($item['link_url']), @js($item['title'])); shareOpen=null" style="display:block;width:100%;text-align:left;padding:0.32rem 0.45rem;border:none;background:transparent;color:var(--hub-ink);font-size:0.78rem;cursor:pointer;">Share to WhatsApp</button>
                                            <button type="button" x-on:click="facebook(@js($item['link_url'])); shareOpen=null" style="display:block;width:100%;text-align:left;padding:0.32rem 0.45rem;border:none;background:transparent;color:var(--hub-ink);font-size:0.78rem;cursor:pointer;">Share to Facebook</button>
                                            <button type="button" x-on:click="sms(@js($item['link_url']), @js($item['title'])); shareOpen=null" style="display:block;width:100%;text-align:left;padding:0.32rem 0.45rem;border:none;background:transparent;color:var(--hub-ink);font-size:0.78rem;cursor:pointer;">Share to Message</button>
                                            <button type="button" x-on:click="copyLink(@js($item['link_url']), {{ $item['id'] }}); shareOpen=null" style="display:block;width:100%;text-align:left;padding:0.32rem 0.45rem;border:none;background:transparent;color:var(--hub-ink);font-size:0.78rem;cursor:pointer;">Copy Link</button>
                                            <div style="height:1px;background:var(--hub-border);margin:0.25rem 0;"></div>
                                            <p style="margin:0 0 0.2rem;padding:0 0.45rem;font-size:0.68rem;color:var(--hub-muted);">Share in app</p>
                                            <div style="max-height:130px;overflow:auto;">
                                                @foreach ($this->shareFriends as $friend)
                                                    <button type="button" wire:click="shareToFriend({{ $item['id'] }}, {{ $friend['id'] }})" x-on:click="shareOpen=null" style="display:flex;align-items:center;gap:0.4rem;width:100%;text-align:left;padding:0.28rem 0.45rem;border:none;background:transparent;color:var(--hub-ink);font-size:0.76rem;cursor:pointer;">
                                                        @if ($friend['avatar'])
                                                            <img src="{{ $friend['avatar'] }}" alt="{{ $friend['name'] }}" style="width:1.2rem;height:1.2rem;border-radius:999px;object-fit:cover;">
                                                        @else
                                                            <span style="width:1.2rem;height:1.2rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:#0f766e;color:#ccfbf1;font-size:0.62rem;font-weight:700;">{{ strtoupper(substr($friend['name'], 0, 1)) }}</span>
                                                        @endif
                                                        <span>{{ $friend['name'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="toggleComments({{ $item['id'] }})"
                                        title="Comment"
                                        style="width:1.9rem;height:1.9rem;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid var(--hub-border);background:transparent;color:var(--hub-ink);cursor:pointer;"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <p x-show="copied === 'link-' + {{ $item['id'] }}" x-cloak style="margin:0.2rem 0 0;font-size:0.68rem;color:var(--hub-muted);">Link copied</p>
                        </div>

                        @if ($item['link_url'])
                            <a
                                href="{{ $item['link_url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                style="display:block;text-align:center;padding:0.55rem;background:{{ $badgeColor }};color:#fff;font-size:0.82rem;font-weight:600;text-decoration:none;"
                            >
                                {{ $item['type'] === 'Job' ? 'View & Apply' : ($item['type'] === 'Reading Material' ? 'Open Resource' : 'Learn More') }} &rarr;
                            </a>
                        @endif

                        @if ($openComments === $item['id'])
                            <div style="padding:0.7rem 0.85rem;border-top:1px solid var(--hub-border);">
                                @livewire('comment-section', ['type' => 'opportunity', 'id' => $item['id']], key('cs-opp-'.$item['id']))
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <div
        x-show="descriptionModalOpen"
        x-cloak
        x-transition.opacity
        style="display:none;position:fixed;inset:0;z-index:75;background:rgba(2,6,23,.72);padding:1rem;"
        x-on:click.self="descriptionModalOpen = false"
    >
        <div style="max-width:min(92vw,720px);margin:4vh auto 0;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.9rem;overflow:hidden;box-shadow:0 26px 60px rgba(2,6,23,.5);">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:0.6rem;padding:0.65rem 0.9rem;border-bottom:1px solid var(--hub-border);">
                <p x-text="descriptionModalTitle" style="margin:0;font-size:0.9rem;font-weight:700;color:var(--hub-ink);"></p>
                <button type="button" x-on:click="descriptionModalOpen = false" style="background:none;border:none;color:var(--hub-muted);font-size:1.35rem;line-height:1;cursor:pointer;">&times;</button>
            </div>
            <div style="padding:0.9rem;max-height:70vh;overflow:auto;">
                <p x-text="descriptionModalBody" style="margin:0;color:var(--hub-copy, var(--hub-ink));font-size:0.84rem;line-height:1.6;white-space:pre-wrap;"></p>
            </div>
        </div>
    </div>
    </div>
</x-filament-panels::page>
