<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Communications</p>
            <h2 class="hub-title" style="font-size:1.1rem;">Cohort Broadcasts</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Send an email announcement and in-app dashboard notification to all students enrolled in your course.</p>
        </section>

        {{-- ===== COMPOSE ===== --}}
        <section class="hub-card" style="padding:1.25rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;">
                <div style="width:34px;height:34px;border-radius:8px;background:rgba(13,148,136,0.1);display:flex;align-items:center;justify-content:center;">
                    <x-heroicon-o-megaphone style="width:1.2rem;height:1.2rem;color:var(--hub-primary);" />
                </div>
                <div>
                    <h3 class="hub-title" style="font-size:1rem;margin:0;">Compose Broadcast</h3>
                    <p style="font-size:0.75rem;color:var(--hub-muted);margin:0;">Deliver instantly via email and student portal notifications</p>
                </div>
            </div>

            @if (count($courseOptions) === 0)
                <div style="padding:1.25rem;background:#f8fafc;border:1px dashed var(--hub-border);border-radius:10px;text-align:center;">
                    <p class="hub-copy" style="color:var(--hub-muted);margin:0;">You have no active courses assigned to broadcast to.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:0.9rem;max-width:680px;">
                    <div>
                        <label for="broadcast-course" style="display:block;font-size:0.78rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.35rem;">Target Course <span style="color:#ef4444;">*</span></label>
                        <select id="broadcast-course" wire:model.live="courseId" class="hub-input" style="width:100%;font-size:0.88rem;padding:0.5rem 0.65rem;border-radius:8px;">
                            <option value="">Select a course…</option>
                            @foreach ($courseOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>

                        @if ($courseId)
                            <div style="margin-top:0.4rem;font-size:0.76rem;display:flex;align-items:center;gap:0.35rem;">
                                @if ($this->enrolledCount > 0)
                                    <span style="color:#0f766e;font-weight:600;display:inline-flex;align-items:center;gap:0.3rem;">
                                        <x-heroicon-s-user-group style="width:1rem;height:1rem;" />
                                        {{ $this->enrolledCount }} enrolled student{{ $this->enrolledCount === 1 ? '' : 's' }} will receive this broadcast
                                    </span>
                                @else
                                    <span style="color:#d97706;font-weight:600;display:inline-flex;align-items:center;gap:0.3rem;">
                                        <x-heroicon-s-exclamation-triangle style="width:1rem;height:1rem;" />
                                        No enrolled students with email found in this course.
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.35rem;">
                            <label for="broadcast-subject" style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);">Subject <span style="color:#ef4444;">*</span></label>
                            <span style="font-size:0.72rem;color:var(--hub-muted);">{{ mb_strlen($subject) }}/255</span>
                        </div>
                        <input id="broadcast-subject" type="text" wire:model="subject" maxlength="255" class="hub-input" style="width:100%;font-size:0.88rem;padding:0.5rem 0.65rem;border-radius:8px;" placeholder="e.g. Project Phase 2 Guidelines & Live Q&A Session">
                    </div>

                    <div>
                        <label for="broadcast-message" style="display:block;font-size:0.78rem;font-weight:700;color:var(--hub-ink);margin-bottom:0.35rem;">Announcement Message <span style="color:#ef4444;">*</span></label>
                        <textarea id="broadcast-message" wire:model="message" rows="7" class="hub-input" style="width:100%;font-size:0.88rem;padding:0.6rem 0.65rem;resize:vertical;border-radius:8px;" placeholder="Write your announcement to the class… Students will receive this formatted in their email inbox and in-app dashboard."></textarea>
                    </div>

                    <div style="display:flex;align-items:center;gap:0.75rem;margin-top:0.25rem;">
                        <button
                            type="button"
                            wire:click="send"
                            wire:confirm="Send this broadcast to all enrolled students in the selected course?"
                            wire:loading.attr="disabled"
                            wire:target="send"
                            class="hub-btn hub-btn-primary"
                            style="font-size:0.88rem;padding:0.55rem 1.4rem;display:inline-flex;align-items:center;gap:0.4rem;"
                        >
                            <span wire:loading.remove wire:target="send" style="display:inline-flex;align-items:center;gap:0.35rem;">
                                <x-heroicon-m-paper-airplane style="width:1rem;height:1rem;" />
                                Send Broadcast Now
                            </span>
                            <span wire:loading wire:target="send" style="display:inline-flex;align-items:center;gap:0.35rem;">
                                <x-heroicon-m-arrow-path style="width:1rem;height:1rem;" class="animate-spin" />
                                Sending to Students…
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </section>

        {{-- ===== HISTORY ===== --}}
        <section class="hub-card" style="padding:1.25rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.9rem;">
                <div style="width:34px;height:34px;border-radius:8px;background:rgba(15,23,42,0.06);display:flex;align-items:center;justify-content:center;">
                    <x-heroicon-o-clock style="width:1.2rem;height:1.2rem;color:var(--hub-ink);" />
                </div>
                <div>
                    <h3 class="hub-title" style="font-size:1rem;margin:0;">Broadcast History</h3>
                    <p style="font-size:0.75rem;color:var(--hub-muted);margin:0;">Past cohort communications and delivery logs</p>
                </div>
            </div>

            @if (count($history) === 0)
                <div style="padding:1.25rem;background:#f8fafc;border:1px dashed var(--hub-border);border-radius:10px;text-align:center;">
                    <p class="hub-copy" style="color:var(--hub-muted);margin:0;">No broadcasts sent yet.</p>
                </div>
            @else
                <div class="hub-stack" style="gap:0.75rem;">
                    @foreach ($history as $item)
                        <div x-data="{ expanded: false }" style="border:1px solid var(--hub-border);border-radius:10px;padding:0.75rem 0.9rem;background:#ffffff;transition:all 0.15s;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem;flex-wrap:wrap;">
                                <div style="min-width:0;flex:1;">
                                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                        <p style="margin:0;font-weight:700;font-size:0.9rem;color:var(--hub-ink);">{{ $item['subject'] }}</p>
                                        <span class="hub-chip" style="font-size:0.68rem;background:#f1f5f9;color:#475569;">{{ $item['course'] }}</span>
                                    </div>
                                    <p style="margin:0.25rem 0 0;font-size:0.74rem;color:var(--hub-muted);">
                                        Sent: {{ $item['sent_at'] }}
                                    </p>
                                </div>
                                <div style="display:flex;align-items:center;gap:0.4rem;flex-shrink:0;">
                                    <span class="hub-chip hub-chip-green" style="font-size:0.72rem;display:inline-flex;align-items:center;gap:0.25rem;">
                                        <x-heroicon-s-check-circle style="width:0.85rem;height:0.85rem;" />
                                        {{ $item['recipients_count'] }} recipient{{ $item['recipients_count'] === 1 ? '' : 's' }}
                                    </span>
                                    @if ($item['failed_count'] > 0)
                                        <span class="hub-chip hub-chip-danger" style="font-size:0.72rem;">
                                            {{ $item['failed_count'] }} failed
                                        </span>
                                    @endif
                                    <button
                                        type="button"
                                        @click="expanded = !expanded"
                                        style="background:none;border:none;cursor:pointer;color:var(--hub-primary);font-size:0.75rem;font-weight:600;padding:0.2rem 0.4rem;"
                                    >
                                        <span x-show="!expanded">View Details ↓</span>
                                        <span x-show="expanded">Hide ↑</span>
                                    </button>
                                </div>
                            </div>
                            <div x-show="expanded" x-cloak style="margin-top:0.65rem;padding-top:0.65rem;border-top:1px solid var(--hub-border);font-size:0.82rem;color:#334155;line-height:1.6;white-space:pre-line;background:#f8fafc;padding:0.6rem 0.8rem;border-radius:6px;">
                                {{ $item['body'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
