<div class="space-y-6">
    {{-- Header & Aggregation Grid --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-6 md:grid-cols-12 items-center">
            
            {{-- Big Score & Summary (4 Cols) --}}
            <div class="md:col-span-4 text-center md:text-left border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-800 pb-6 md:pb-0 md:pr-6">
                <span class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">
                    Community Feedback
                </span>
                <div class="mt-2 flex items-baseline justify-center md:justify-start gap-2">
                    <span class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-white">
                        {{ $avgRating > 0 ? number_format($avgRating, 1) : '0.0' }}
                    </span>
                    <span class="text-slate-400 text-sm font-semibold">/ 5.0</span>
                </div>

                <div class="mt-2 flex justify-center md:justify-start">
                    <x-rating-stars :rating="$avgRating" size="lg" :showText="false" />
                </div>

                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    Based on {{ number_format($totalCount) }} verified {{ Str::plural('rating', $totalCount) }}
                </p>

                <div class="mt-4">
                    <button type="button"
                            wire:click="openSubmitModal"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-md hover:from-teal-700 hover:to-emerald-700 transition">
                        <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Write a Review (+10 XP)
                    </button>
                </div>
            </div>

            {{-- Breakdown Bars (8 Cols) --}}
            <div class="md:col-span-8 space-y-2.5">
                @foreach ([5, 4, 3, 2, 1] as $star)
                    @php
                        $cnt = $starCounts[$star] ?? 0;
                        $pct = $totalCount > 0 ? round(($cnt / $totalCount) * 100) : 0;
                        $isActiveFilter = $filterRating === $star;
                    @endphp
                    <button type="button"
                            wire:click="setFilterRating({{ $star }})"
                            class="w-full flex items-center gap-3 text-left group p-1.5 rounded-lg transition {{ $isActiveFilter ? 'bg-teal-50 dark:bg-teal-950/40 ring-1 ring-teal-500' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 w-12 flex items-center gap-1">
                            {{ $star }} <span class="text-amber-400">★</span>
                        </span>
                        
                        <div class="flex-1 h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $pct }}%;"></div>
                        </div>

                        <span class="text-xs text-slate-500 dark:text-slate-400 w-14 text-right font-medium">
                            {{ $pct }}% ({{ $cnt }})
                        </span>
                    </button>
                @endforeach

                @if ($filterRating)
                    <div class="pt-2 text-right">
                        <button type="button"
                                wire:click="setFilterRating(null)"
                                class="text-xs font-bold text-teal-600 hover:text-teal-700 dark:text-teal-400 underline">
                            Clear Filter (Showing {{ $filterRating }}-Star only)
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Reviews List Feed --}}
    @if ($reviews->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700 bg-white dark:bg-slate-900">
            <div class="w-12 h-12 rounded-full bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">No reviews found</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                {{ $filterRating ? "There are no {$filterRating}-star reviews yet." : 'Be the first to share your learning experience!' }}
            </p>
            <button type="button"
                    wire:click="openSubmitModal"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                + Write First Review
            </button>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($reviews as $review)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900 flex flex-col justify-between">
                    <div>
                        {{-- Review Top: Stars + Verified Badge + Date --}}
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <x-rating-stars :rating="$review->rating" size="sm" :showText="false" />

                            <div class="flex items-center gap-2">
                                @if ($review->is_verified)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Verified Learner
                                    </span>
                                @endif
                                <span class="text-[11px] text-slate-400">
                                    {{ $review->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Headline / Title --}}
                        @if ($review->title)
                            <h4 class="mt-3 text-sm font-extrabold text-slate-900 dark:text-white">
                                {{ $review->title }}
                            </h4>
                        @endif

                        {{-- Comment --}}
                        <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300 whitespace-pre-line">
                            {{ $review->comment }}
                        </p>
                    </div>

                    {{-- Review Author Footer --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-teal-500 to-emerald-400 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                            @if ($review->is_anonymous)
                                A
                            @else
                                {{ strtoupper(substr($review->user?->name ?? 'Student', 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                @if ($review->is_anonymous)
                                    Anonymous Student
                                @else
                                    {{ $review->user?->name ?? 'Thinker Learner' }}
                                @endif
                            </p>
                            <p class="text-[10px] text-slate-400">
                                @if ($review->is_anonymous)
                                    Verified Thinker HUB Member
                                @else
                                    {{ $review->user?->track ? $review->user->track . ' Track' : 'Thinker HUB Learner' }}
                                @endif
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($reviews->hasPages())
            <div class="pt-4">
                {{ $reviews->links() }}
            </div>
        @endif
    @endif
</div>
