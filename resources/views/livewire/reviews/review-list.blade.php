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

                @auth
                    <div class="mt-4">
                        <a href="{{ route('reviews.create', ['type' => $targetType ?: 'platform', 'id' => $targetId]) }}"
                           class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-md hover:from-teal-700 hover:to-emerald-700 transition">
                            <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Write a Review (+10 XP)
                        </a>
                    </div>
                @endauth
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

    {{-- Reviews Sliding Carousel Feed --}}
    @if ($reviews->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700 bg-white dark:bg-slate-900">
            <div class="w-12 h-12 rounded-full bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">No reviews found</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                {{ $filterRating ? "There are no {$filterRating}-star reviews yet." : 'Learner reviews will appear here once submitted.' }}
            </p>
            @auth
                <a href="{{ route('reviews.create', ['type' => $targetType ?: 'platform', 'id' => $targetId]) }}"
                   class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                    + Write First Review
                </a>
            @endauth
        </div>
    @else
        <div class="space-y-4"
             x-data="{
                 canScrollLeft: false,
                 canScrollRight: true,
                 scrollPosition: 0,
                 maxScroll: 1,
                 updateScrollState() {
                     const el = this.$refs.reviewCarousel;
                     if (!el) return;
                     this.canScrollLeft = el.scrollLeft > 15;
                     this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 15);
                     this.scrollPosition = el.scrollLeft;
                     this.maxScroll = Math.max(el.scrollWidth - el.clientWidth, 1);
                 },
                 slideNext() {
                     const el = this.$refs.reviewCarousel;
                     if (!el) return;
                     const card = el.querySelector('article');
                     const scrollAmount = card ? (card.offsetWidth + 20) : 380;
                     el.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                 },
                 slidePrev() {
                     const el = this.$refs.reviewCarousel;
                     if (!el) return;
                     const card = el.querySelector('article');
                     const scrollAmount = card ? (card.offsetWidth + 20) : 380;
                     el.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                 }
             }"
             x-init="$nextTick(() => updateScrollState()); setTimeout(() => updateScrollState(), 200);"
             @resize.window.debounce.100ms="updateScrollState()">
            
            {{-- Carousel Navigation Header --}}
            <div class="flex items-center justify-between gap-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Verified Testimonials ({{ $reviews->total() }})
                    </span>
                    <span class="hidden sm:inline text-xs text-slate-400 font-medium">
                        • Slide or swipe to explore
                    </span>
                </div>

                {{-- Slider Arrow Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="slidePrev()"
                            :disabled="!canScrollLeft"
                            :class="!canScrollLeft ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-100 dark:bg-slate-800' : 'text-slate-800 dark:text-white bg-white dark:bg-slate-800 hover:bg-teal-50 hover:text-teal-600 dark:hover:bg-teal-950/50 dark:hover:text-teal-300 shadow-sm hover:shadow border-slate-200 dark:border-slate-700'"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-full border transition-all duration-200"
                            aria-label="Previous Reviews">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button type="button"
                            @click="slideNext()"
                            :disabled="!canScrollRight"
                            :class="!canScrollRight ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-100 dark:bg-slate-800' : 'text-slate-800 dark:text-white bg-white dark:bg-slate-800 hover:bg-teal-50 hover:text-teal-600 dark:hover:bg-teal-950/50 dark:hover:text-teal-300 shadow-sm hover:shadow border-slate-200 dark:border-slate-700'"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-full border transition-all duration-200"
                            aria-label="Next Reviews">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Sliding Carousel Track --}}
            <div x-ref="reviewCarousel"
                 @scroll.debounce.40ms="updateScrollState()"
                 class="flex gap-4 sm:gap-5 overflow-x-auto pb-4 pt-1.5 snap-x snap-mandatory scroll-smooth focus:outline-none"
                 style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
                
                @foreach ($reviews as $review)
                    <article class="w-[86vw] max-w-[340px] sm:w-[360px] md:w-[410px] flex-shrink-0 snap-start rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:border-teal-300/80 dark:hover:border-teal-500/40 dark:border-slate-800 dark:bg-slate-900 flex flex-col justify-between select-none relative group">
                        
                        {{-- Top Accent Glow Line on Hover --}}
                        <div class="absolute top-0 left-6 right-6 h-[2px] bg-gradient-to-r from-transparent via-teal-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-full"></div>

                        <div>
                            {{-- Review Top: Stars + Verified Badge + Date --}}
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-3.5">
                                @if ($review->rating)
                                    <div class="flex items-center gap-1.5">
                                        <x-rating-stars :rating="$review->rating" size="sm" :showText="false" />
                                        <span class="text-xs font-black text-slate-800 dark:text-slate-200">
                                            {{ number_format($review->rating, 1) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 px-2 py-0.5 text-[10px] font-bold">
                                        <i class="fa-regular fa-comment-dots"></i> Written Review
                                    </span>
                                @endif

                                <div class="flex items-center gap-2">
                                    @if ($review->is_verified)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                            <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Verified
                                        </span>
                                    @endif
                                    <span class="text-[11px] font-medium text-slate-400">
                                        {{ $review->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            {{-- Headline / Title --}}
                            @if ($review->title)
                                <h4 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white line-clamp-1 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                                    {{ $review->title }}
                                </h4>
                            @endif

                            {{-- Comment Body with Quote Style --}}
                            @if ($review->comment)
                                <p class="mt-2 text-xs sm:text-[13px] leading-relaxed text-slate-600 dark:text-slate-300 line-clamp-4 whitespace-pre-line">
                                    “{{ $review->comment }}”
                                </p>
                            @endif
                        </div>

                        {{-- Review Author Footer --}}
                        <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-teal-600 to-emerald-500 text-white flex items-center justify-center font-black text-xs uppercase shadow-sm flex-shrink-0">
                                    @if ($review->is_anonymous)
                                        A
                                    @else
                                        {{ strtoupper(substr($review->user?->name ?? 'S', 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate">
                                        @if ($review->is_anonymous)
                                            Anonymous Student
                                        @else
                                            {{ $review->user?->name ?? 'Thinker Learner' }}
                                        @endif
                                    </p>
                                    <p class="text-[10px] text-slate-400 truncate">
                                        @if ($review->is_anonymous)
                                            Verified Thinker HUB Member
                                        @else
                                            {{ $review->user?->track ? $review->user->track . ' Track' : 'Thinker HUB Learner' }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if ($review->reviewable)
                                <span class="hidden sm:inline-block text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 truncate max-w-[110px]">
                                    {{ $review->reviewable->title ?? $review->reviewable->name ?? 'Course' }}
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Slider Progress Indicator --}}
            <div class="flex items-center justify-between px-1 text-xs text-slate-400">
                <span class="sm:hidden font-medium">
                    ← Swipe horizontally to see more reviews →
                </span>
                <span class="hidden sm:inline font-medium">
                    Showing {{ $reviews->count() }} of {{ $reviews->total() }} reviews in carousel
                </span>

                <div class="w-24 sm:w-36 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-500 rounded-full transition-all duration-150"
                         :style="`width: ${Math.min(100, Math.max(15, (scrollPosition / maxScroll) * 100))}%;`"></div>
                </div>
            </div>

            {{-- Pagination If Multiple Pages --}}
            @if ($reviews->hasPages())
                <div class="pt-3">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
