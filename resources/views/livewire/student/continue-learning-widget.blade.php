<div>
    @if(!empty($courseCards) || !empty($recentScores) || $certificates->isNotEmpty())
        <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Resume Learning & Real-Time Course Progress --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Continue Learning</h3>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('offline.library') }}" class="text-[11px] font-semibold text-slate-600 hover:text-teal-600 dark:text-slate-300 dark:hover:text-teal-400 underline">
                            Offline Library
                        </a>
                        <span class="text-[11px] font-semibold text-teal-600 dark:text-teal-400">Real-Time Progress</span>
                    </div>
                </div>
                <div class="space-y-3">
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
                            class="flex flex-col gap-2 rounded-xl border border-slate-100 bg-slate-50/80 p-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-950/60"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-xs font-bold text-slate-900 dark:text-white">{{ $card['title'] }}</p>
                                    <span class="text-xs font-extrabold text-teal-600 dark:text-teal-400">{{ $card['progress'] }}%</span>
                                </div>
                                <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ min(100, $card['progress']) }}%"></div>
                                </div>
                                @if($card['resume_lesson_title'])
                                    <p class="mt-1 truncate text-[11px] text-slate-500">Next: {{ $card['resume_lesson_title'] }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-1.5">
                                <button
                                    type="button"
                                    x-on:click="saveOffline()"
                                    :disabled="saving"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 transition"
                                    x-text="saving ? 'Saving...' : (saved ? 'Saved Offline' : 'Save Offline')"
                                ></button>
                                @if($card['resume_url'])
                                    <a
                                        href="{{ $card['resume_url'] }}"
                                        class="inline-flex shrink-0 items-center justify-center rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition"
                                    >
                                        {{ $card['is_completed'] ? 'Review Course' : 'Resume' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Scores & Certificates --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <div>
                    <h3 class="mb-2 text-sm font-bold text-slate-900 dark:text-white">Recent Scores</h3>
                    @forelse($recentScores as $item)
                        <div class="flex items-center justify-between py-1.5 text-xs border-b border-slate-100 last:border-0 dark:border-slate-800">
                            <div class="min-w-0 pr-2">
                                <p class="truncate font-semibold text-slate-800 dark:text-slate-100">{{ $item['title'] }}</p>
                                <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ $item['type'] }}</span>
                            </div>
                            <span class="rounded-md px-2 py-0.5 font-bold {{ $item['passed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                {{ $item['score'] }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No quiz or SCORM scores yet.</p>
                    @endforelse
                </div>

                @if($certificates->isNotEmpty())
                    <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                        <h3 class="mb-2 text-sm font-bold text-slate-900 dark:text-white">Your Certificates</h3>
                        <div class="space-y-1.5">
                            @foreach($certificates as $cert)
                                <a
                                    href="{{ route('filament.student.pages.certificates') }}"
                                    class="flex items-center justify-between rounded-lg bg-emerald-50/70 px-2.5 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300"
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
