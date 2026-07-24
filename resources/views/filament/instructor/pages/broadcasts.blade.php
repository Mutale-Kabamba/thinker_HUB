<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Broadcasts</p>
            <h2 class="hub-title" style="font-size:1.1rem;">Cohort Broadcasts</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Email every student enrolled in one of your courses. Sends are queued and logged below.</p>
        </section>

        {{-- ===== COMPOSE ===== --}}
        <section class="hub-card" style="padding:1rem;">
            <h3 class="hub-title" style="font-size:1rem;margin-bottom:0.65rem;">📣 New Broadcast</h3>

            @if (count($courseOptions) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">You have no active courses to broadcast to.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:0.65rem;max-width:640px;">
                    <div>
                        <label for="broadcast-course" style="display:block;font-size:0.74rem;font-weight:700;color:var(--hub-muted);margin-bottom:0.25rem;">Course</label>
                        <select id="broadcast-course" wire:model="courseId" class="hub-input" style="width:100%;font-size:0.85rem;padding:0.4rem 0.55rem;">
                            <option value="">Choose a course…</option>
                            @foreach ($courseOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="broadcast-subject" style="display:block;font-size:0.74rem;font-weight:700;color:var(--hub-muted);margin-bottom:0.25rem;">Subject</label>
                        <input id="broadcast-subject" type="text" wire:model="subject" maxlength="255" class="hub-input" style="width:100%;font-size:0.85rem;padding:0.4rem 0.55rem;" placeholder="e.g. Week 3 materials are live">
                    </div>
                    <div>
                        <label for="broadcast-message" style="display:block;font-size:0.74rem;font-weight:700;color:var(--hub-muted);margin-bottom:0.25rem;">Message</label>
                        <textarea id="broadcast-message" wire:model="message" rows="7" class="hub-input" style="width:100%;font-size:0.85rem;padding:0.5rem 0.55rem;resize:vertical;" placeholder="Write your announcement to the class…"></textarea>
                    </div>
                    <div>
                        <button
                            type="button"
                            wire:click="send"
                            wire:confirm="Send this email to all enrolled students in the selected course?"
                            wire:loading.attr="disabled"
                            wire:target="send"
                            class="hub-btn hub-btn-primary"
                            style="font-size:0.85rem;padding:0.45rem 1rem;"
                        >
                            <span wire:loading.remove wire:target="send">Send Broadcast</span>
                            <span wire:loading wire:target="send">Sending…</span>
                        </button>
                    </div>
                </div>
            @endif
        </section>

        {{-- ===== HISTORY ===== --}}
        <section class="hub-card" style="padding:1rem;">
            <h3 class="hub-title" style="font-size:1rem;margin-bottom:0.65rem;">📜 Broadcast History</h3>

            @if (count($history) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">No broadcasts sent yet.</p>
            @else
                <div class="hub-stack">
                    @foreach ($history as $item)
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:0.6rem;border:1px solid var(--hub-border);border-radius:10px;padding:0.6rem 0.75rem;flex-wrap:wrap;">
                            <div style="min-width:0;">
                                <p style="margin:0;font-weight:700;font-size:0.85rem;color:var(--hub-ink);">{{ $item['subject'] }}</p>
                                <p style="margin:0.15rem 0 0;font-size:0.72rem;color:var(--hub-muted);">{{ $item['course'] }} · {{ $item['sent_at'] }}</p>
                            </div>
                            <div style="display:flex;gap:0.4rem;flex-shrink:0;">
                                <span class="hub-chip hub-chip-green" style="font-size:0.68rem;">{{ $item['recipients_count'] }} recipient{{ $item['recipients_count'] === 1 ? '' : 's' }}</span>
                                @if ($item['failed_count'] > 0)
                                    <span class="hub-chip hub-chip-danger" style="font-size:0.68rem;">{{ $item['failed_count'] }} failed</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
