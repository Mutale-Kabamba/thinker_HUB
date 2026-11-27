<div>
    @if(!empty($courseCards) || !empty($recentScores) || $certificates->isNotEmpty())
        <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Resume Learning & Real-Time Course Progress --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-3.5 flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">Continue Learning</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Pick up right where you left off</p>
                    </div>
                    <a
                        href="{{ route('offline.library') }}"
                        class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700 hover:border-teal-300 hover:text-teal-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition"
                    >
                        <span>Offline Library</span>
                    </a>
                </div>
                <div class="space-y-2.5">
                    @foreach($courseCards as $card)
                        <div
                            x-data="{
                                saving: false,
                                saved: false,
                                async saveOffline() {
                                    if (!window.ThinkerOffline || this.saving) return;
                                    this.saving = true;
                                    try {
                                        await window.ThinkerOffline.downloadCourse(@js($card['bundle_url']));
                                        this.saved = true;
                                    } catch (e) {
                                        console.warn('Offline save failed', e);
                                    } finally {
                                        this.saving = false;
                                    }
                                }
                            }"
                            class="flex flex-col gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 sm:flex-row sm:items-center sm:justify-between hover:border-slate-200 dark:border-slate-800/90 dark:bg-slate-950/50 dark:hover:border-slate-700 transition"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <p class="truncate text-xs sm:text-sm font-semibold text-slate-900 dark:text-white">{{ $card['title'] }}</p>
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $card['delivery_mode'] === 'cohort' ? 'bg-slate-200/80 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : ($card['delivery_mode'] === 'hybrid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300') }}">
                                            {{ $card['delivery_label'] }}
                                        </span>
                                    </div>
                                    @if($card['uses_classroom'] || $card['progress'] > 0)
                                        <span class="shrink-0 text-xs font-bold text-teal-600 dark:text-teal-400">{{ $card['progress'] }}%</span>
                                    @endif
                                </div>
                                @if($card['uses_classroom'] || $card['progress'] > 0)
                                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-200/80 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-teal-600 transition-all duration-300" style="width: {{ min(100, $card['progress']) }}%"></div>
                                    </div>
                                @endif
                                @if($card['uses_classroom'] && $card['resume_lesson_title'])
                                    <p class="mt-1.5 truncate text-[11px] text-slate-500 dark:text-slate-400">Next: {{ $card['resume_lesson_title'] }}</p>
                                @elseif(!$card['uses_classroom'])
                                    <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">Instructor-led sessions, materials, assignments &amp; quizzes</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if($card['uses_classroom'] && $card['bundle_url'])
                                    <button
                                        type="button"
                                        x-on:click="saveOffline()"
                                        :disabled="saving"
                                        class="inline-flex min-h-[36px] items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 active:scale-[0.98] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 transition"
                                        x-text="saving ? 'Saving...' : (saved ? 'Saved' : 'Save Offline')"
                                    ></button>
                                @endif
                                @if($card['resume_url'])
                                    <a
                                        href="{{ $card['resume_url'] }}"
                                        class="inline-flex min-h-[36px] shrink-0 items-center justify-center rounded-lg bg-teal-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 active:scale-[0.98] transition"
                                    >
                                        @if($card['uses_classroom'])
                                            {{ $card['is_completed'] ? 'Review' : 'Resume' }}
                                        @else
                                            Open Course Hub
                                        @endif
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Scores & Certificates --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <div>
                    <h3 class="mb-2.5 text-sm font-bold tracking-tight text-slate-900 dark:text-white">Recent Scores</h3>
                    @forelse($recentScores as $item)
                        <div class="flex items-center justify-between py-2 text-xs border-b border-slate-100 last:border-0 dark:border-slate-800">
                            <div class="min-w-0 pr-2">
                                <p class="truncate font-semibold text-slate-800 dark:text-slate-100">{{ $item['title'] }}</p>
                                <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ $item['type'] }}</span>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $item['passed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                {{ $item['score'] }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No quiz or SCORM scores yet.</p>
                    @endforelse
                </div>

                @if($certificates->isNotEmpty())
                    <div class="border-t border-slate-100 pt-3.5 dark:border-slate-800">
                        <h3 class="mb-2 text-sm font-bold tracking-tight text-slate-900 dark:text-white">Certificates</h3>
                        <div class="space-y-1.5">
                            @foreach($certificates as $cert)
                                <a
                                    href="{{ route('filament.student.pages.certificates') }}"
                                    class="flex items-center justify-between rounded-xl bg-emerald-50/70 px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 transition"
                                >
                                    <span class="truncate">{{ $cert->course?->title ?? 'Certificate' }}</span>
                                    <span class="ml-2 shrink-0 underline">Download</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
