<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Pending Reschedule Requests</x-slot>

        <div style="display:flex;flex-direction:column;gap:0.55rem;">
            @forelse ($requests as $request)
                <div class="hub-mobile-card" style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.7rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:15rem;">
                        <p style="margin:0;font-weight:700;color:var(--hub-ink);font-size:0.82rem;">{{ $request['student_name'] }} · Session #{{ $request['session_id'] }}</p>
                        <p style="margin:0.15rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $request['reason'] ?: 'No reason provided.' }}</p>
                        <p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--hub-muted);">
                            Preferred: {{ $request['preferred_date'] ?: 'N/A' }}
                            @if ($request['preferred_time']) at {{ $request['preferred_time'] }} @endif
                            @if ($request['created_at']) · {{ $request['created_at'] }} @endif
                        </p>
                    </div>
                    <button wire:click="openDecisionWizard('{{ $request['id'] }}')" class="hub-btn hub-btn-primary" style="font-size:0.72rem;padding:0.3rem 0.6rem;white-space:nowrap;">Review</button>
                </div>
            @empty
                <p class="hub-copy" style="color:var(--hub-muted);text-align:center;">No pending reschedule requests.</p>
            @endforelse
        </div>

        @if ($decisionNotificationId)
            <div style="position:fixed;inset:0;background:rgba(15,23,42,0.45);display:flex;align-items:center;justify-content:center;padding:1rem;z-index:60;">
                <div style="width:min(100%,680px);max-height:90vh;overflow:auto;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:14px;padding:1rem;box-shadow:0 20px 45px rgba(2,6,23,0.28);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
                        <div>
                            <p class="hub-eyebrow">Reschedule Decision Wizard</p>
                            <h3 class="hub-title" style="font-size:1rem;">Request From {{ $decisionStudentName }}</h3>
                        </div>
                        <button wire:click="closeDecisionWizard" class="hub-btn hub-btn-muted" style="font-size:0.72rem;padding:0.3rem 0.6rem;">Close</button>
                    </div>

                    @if ($decisionStep === 'review')
                        <div style="margin-top:0.75rem;border:1px solid var(--hub-border);border-radius:10px;padding:0.7rem;background:var(--hub-surface-soft);">
                            <p style="font-size:0.74rem;color:var(--hub-muted);">Session ID: {{ $decisionSessionId }}</p>
                            <p style="font-size:0.8rem;color:var(--hub-ink);margin-top:0.3rem;"><strong>Reason:</strong> {{ $decisionReason ?: 'No reason provided.' }}</p>
                            <p style="font-size:0.78rem;color:var(--hub-ink);margin-top:0.35rem;"><strong>Preferred:</strong>
                                {{ $decisionPreferredDate ?: 'No preferred date' }}
                                @if ($decisionPreferredTime)
                                    at {{ $decisionPreferredTime }}
                                @endif
                            </p>
                        </div>

                        <div style="margin-top:0.75rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                            <button wire:click="setDecisionStep('accept')" class="hub-btn hub-btn-primary" style="font-size:0.75rem;padding:0.35rem 0.75rem;">Accept Request</button>
                            <button wire:click="setDecisionStep('decline')" class="hub-btn hub-btn-muted" style="font-size:0.75rem;padding:0.35rem 0.75rem;">Decline Request</button>
                        </div>
                    @endif

                    @if ($decisionStep === 'accept')
                        <div style="margin-top:0.75rem;border:1px solid var(--hub-border);border-radius:10px;padding:0.7rem;background:var(--hub-surface-soft);">
                            <p style="font-weight:700;font-size:0.82rem;color:var(--hub-ink);">Accept and Apply New Time</p>
                            <div style="margin-top:0.55rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                                <div>
                                    <label style="font-size:0.7rem;font-weight:600;color:var(--hub-muted);">Rescheduled Date</label>
                                    <input type="date" wire:model="decisionDate" style="display:block;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                                </div>
                                <div>
                                    <label style="font-size:0.7rem;font-weight:600;color:var(--hub-muted);">Start Time</label>
                                    <input type="time" wire:model="decisionStartTime" style="display:block;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                                </div>
                                <div>
                                    <label style="font-size:0.7rem;font-weight:600;color:var(--hub-muted);">End Time</label>
                                    <input type="time" wire:model="decisionEndTime" style="display:block;padding:0.35rem 0.5rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:0.75rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                            <button wire:click="acceptRequest" class="hub-btn hub-btn-primary" style="font-size:0.75rem;padding:0.35rem 0.75rem;">Confirm Accept</button>
                            <button wire:click="setDecisionStep('review')" class="hub-btn hub-btn-muted" style="font-size:0.75rem;padding:0.35rem 0.75rem;">Back</button>
                        </div>
                    @endif

                    @if ($decisionStep === 'decline')
                        <div style="margin-top:0.75rem;border:1px solid var(--hub-border);border-radius:10px;padding:0.7rem;background:var(--hub-surface-soft);">
                            <p style="font-weight:700;font-size:0.82rem;color:var(--hub-ink);">Decline Request</p>
                            <label style="display:block;font-size:0.7rem;font-weight:600;color:var(--hub-muted);margin-top:0.45rem;">Optional message to student</label>
                            <textarea wire:model="declineReason" rows="3" style="display:block;width:100%;margin-top:0.2rem;padding:0.45rem 0.55rem;border:1px solid var(--hub-border);border-radius:6px;font-size:0.8rem;background:var(--hub-surface);color:var(--hub-ink);resize:vertical;" placeholder="Add context for the student"></textarea>
                        </div>

                        <div style="margin-top:0.75rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                            <button wire:click="declineRequest" class="hub-btn hub-btn-primary" style="font-size:0.75rem;padding:0.35rem 0.75rem;">Confirm Decline</button>
                            <button wire:click="setDecisionStep('review')" class="hub-btn hub-btn-muted" style="font-size:0.75rem;padding:0.35rem 0.75rem;">Back</button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
