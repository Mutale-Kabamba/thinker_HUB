<x-filament-panels::page>

    <div
        x-data="{
            copied: null,
            copy(code, id) {
                navigator.clipboard.writeText(code).then(() => {
                    this.copied = id;
                    setTimeout(() => { if (this.copied === id) this.copied = null; }, 1500);
                });
            }
        }"
    >
    <div class="hub-shell">

        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Opportunities</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Grow Your Career</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Promo codes, job openings, scholarships, events, and recommended reading — curated for you.</p>
        </section>

        <section class="hub-card" style="padding:0.65rem 1rem;">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
                <select wire:model.live="filterType" class="hub-input" style="max-width:200px;font-size:0.8rem;padding:0.3rem 0.5rem;">
                    <option value="">All Types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
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
                                <p class="hub-copy" style="margin:0.1rem 0 0;font-size:0.8rem;">{{ $item['description'] }}</p>
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

                        <button
                            type="button"
                            wire:click="toggleComments({{ $item['id'] }})"
                            style="display:block;width:100%;text-align:center;padding:0.45rem;background:var(--hub-surface);border:none;border-top:1px solid var(--hub-border);font-size:0.76rem;font-weight:600;color:var(--hub-ink);cursor:pointer;"
                        >
                            {{ $openComments === $item['id'] ? 'Hide comments' : 'Comments' }}
                        </button>

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
    </div>
</x-filament-panels::page>
