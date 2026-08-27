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
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4 z-50">
                <div class="w-full sm:max-w-2xl max-h-[90dvh] overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-t-2xl sm:rounded-2xl p-4 sm:p-6 shadow-2xl safe-pb">
                    <div class="flex justify-between items-start gap-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-wider font-bold text-teal-600 dark:text-teal-400">Reschedule Decision Wizard</p>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-0.5">Request From {{ $decisionStudentName }}</h3>
                        </div>
                        <button wire:click="closeDecisionWizard" class="min-h-[40px] px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition active:scale-95">Close</button>
                    </div>

                    @if ($decisionStep === 'review')
                        <div class="mt-4 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-800/50">
                            <p class="text-xs text-slate-500 dark:text-slate-400">Session ID: <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ $decisionSessionId }}</span></p>
                            <p class="text-sm text-slate-800 dark:text-slate-200 mt-2"><strong>Reason:</strong> {{ $decisionReason ?: 'No reason provided.' }}</p>
                            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 mt-2"><strong>Preferred:</strong>
                                {{ $decisionPreferredDate ?: 'No preferred date' }}
                                @if ($decisionPreferredTime)
                                    at {{ $decisionPreferredTime }}
                                @endif
                            </p>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row gap-2.5">
                            <button wire:click="setDecisionStep('accept')" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs sm:text-sm font-bold shadow-md transition active:scale-95">Accept Request</button>
                            <button wire:click="setDecisionStep('decline')" class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition active:scale-95">Decline Request</button>
                        </div>
                    @endif

                    @if ($decisionStep === 'accept')
                        <div class="mt-4 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-800/50">
                            <p class="font-bold text-sm text-slate-900 dark:text-white">Accept and Apply New Time</p>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Rescheduled Date</label>
                                    <input type="date" wire:model="decisionDate" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Start Time</label>
                                    <input type="time" wire:model="decisionStartTime" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">End Time</label>
                                    <input type="time" wire:model="decisionEndTime" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row gap-2.5">
                            <button wire:click="acceptRequest" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs sm:text-sm font-bold shadow-md transition active:scale-95">Confirm Accept</button>
                            <button wire:click="setDecisionStep('review')" class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition active:scale-95">Back</button>
                        </div>
                    @endif

                    @if ($decisionStep === 'decline')
                        <div class="mt-4 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-800/50">
                            <p class="font-bold text-sm text-slate-900 dark:text-white">Decline Request</p>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mt-2 mb-1">Optional message to student</label>
                            <textarea wire:model="declineReason" rows="3" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 resize-y" placeholder="Add context for the student"></textarea>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row gap-2.5">
                            <button wire:click="declineRequest" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs sm:text-sm font-bold shadow-md transition active:scale-95">Confirm Decline</button>
                            <button wire:click="setDecisionStep('review')" class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition active:scale-95">Back</button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
