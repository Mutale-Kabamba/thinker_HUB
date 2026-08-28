<div class="space-y-6">
    {{-- Header & Aggregation Grid --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
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
                    Based on {{ number_format($ratingCount) }} verified {{ Str::plural('rating', $ratingCount) }}
                </p>

                @auth
                    <div class="mt-4">
                        <a href="{{ route('reviews.create', ['type' => $targetType ?: 'platform', 'id' => $targetId]) }}"
                           class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-teal-700 transition">
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
                        $pct = $ratingCount > 0 ? round(($cnt / $ratingCount) * 100) : 0;
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
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">No written reviews found</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                {{ $filterRating ? "There are no {$filterRating}-star written reviews yet." : 'Learner written reviews will appear here once submitted.' }}
            </p>
            @auth
                <a href="{{ route('reviews.create', ['type' => $targetType ?: 'platform', 'id' => $targetId]) }}"
                   class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                    + Write First Review
                </a>
            @endauth
        </div>
    @else
        <div class="space-y-6"
             x-data="{
                 canScrollLeft: false,
                 canScrollRight: true,
                 scrollPosition: 0,
                 maxScroll: 1,
                 isPaused: false,
                 timer: null,
                 init() {
                     this.$nextTick(() => {
                         this.updateScrollState();
                         this.startAutoScroll();
                     });
                 },
                 startAutoScroll() {
                     this.stopAutoScroll();
                     this.timer = setInterval(() => {
                         if (this.isPaused) return;
                         this.slideNext(true);
                     }, 4000);
                 },
                 stopAutoScroll() {
                     if (this.timer) {
                         clearInterval(this.timer);
                         this.timer = null;
                     }
                 },
                 updateScrollState() {
                     const el = this.$refs.reviewCarousel;
                     if (!el) return;
                     this.canScrollLeft = el.scrollLeft > 15;
                     this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 15);
                     this.scrollPosition = el.scrollLeft;
                     this.maxScroll = Math.max(el.scrollWidth - el.clientWidth, 1);
                 },
                 slideNext(isAuto = false) {
                     const el = this.$refs.reviewCarousel;
                     if (!el) return;
                     const card = el.querySelector('article');
                     const scrollAmount = card ? (card.offsetWidth + 24) : 380;
                     
                     if (el.scrollLeft >= (el.scrollWidth - el.clientWidth - 20)) {
                         el.scrollTo({ left: 0, behavior: 'smooth' });
                     } else {
                         el.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                     }
                     if (!isAuto) {
                         this.startAutoScroll();
                     }
                 },
                 slidePrev() {
                     const el = this.$refs.reviewCarousel;
                     if (!el) return;
                     const card = el.querySelector('article');
                     const scrollAmount = card ? (card.offsetWidth + 24) : 380;
                     
                     if (el.scrollLeft <= 20) {
                         el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
                     } else {
                         el.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                     }
                     this.startAutoScroll();
                 }
             }"
             @mouseenter="isPaused = true"
             @mouseleave="isPaused = false"
             @touchstart="isPaused = true"
             @touchend="setTimeout(() => isPaused = false, 2500)"
             @resize.window.debounce.100ms="updateScrollState()">
            
            {{-- Carousel Subtitle Header --}}
            <div class="text-center">
                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Verified Testimonials ({{ $reviews->total() }})
                </span>
            </div>

            {{-- Sliding Carousel Track (Centered when few reviews, scrollable when many) --}}
            <div x-ref="reviewCarousel"
                 @scroll.debounce.40ms="updateScrollState()"
                 class="flex gap-6 overflow-x-auto py-2 px-4 snap-x snap-mandatory scroll-smooth focus:outline-none {{ $reviews->count() <= 2 ? 'justify-center' : 'justify-start lg:justify-center' }}"
                 style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
                
                @foreach ($reviews as $review)
                    <article class="w-full max-w-[360px] sm:w-[360px] md:w-[380px] flex-shrink-0 snap-center rounded-2xl border border-slate-300 bg-white p-6 sm:p-7 transition-all duration-200 flex flex-col justify-between select-none relative group">
                        
                        {{-- Top Row: Avatar + Info (Left) and Double Quote Icon (Right) --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-sm uppercase flex-shrink-0">
                                    @if ($review->is_anonymous)
                                        A
                                    @else
                                        {{ strtoupper(substr($review->user?->name ?? 'S', 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-bold text-slate-900 truncate">
                                        @if ($review->is_anonymous)
                                            Anonymous Learner
                                        @else
                                            {{ $review->user?->name ?? 'Verified Learner' }}
                                        @endif
                                    </h4>
                                    <p class="text-xs text-slate-500 truncate font-medium mt-0.5">
                                        @if ($review->is_anonymous)
                                            Verified Community Member
                                        @else
                                            {{ $review->user?->track ? $review->user->track . ' Track' : 'Livingstone, Zambia' }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Large Double Quotation Mark Icon --}}
                            <div class="text-slate-700 flex-shrink-0 leading-none pl-2">
                                <i class="fa-solid fa-quote-right text-2xl sm:text-3xl"></i>
                            </div>
                        </div>

                        {{-- Middle Review Body --}}
                        <div class="my-5 min-h-[5.5rem] flex flex-col justify-center">
                            @if ($review->title)
                                <h5 class="text-xs sm:text-sm font-extrabold text-slate-900 mb-1 line-clamp-1">
                                    {{ $review->title }}
                                </h5>
                            @endif
                            <p class="text-xs sm:text-[13px] leading-relaxed text-slate-600 line-clamp-4 whitespace-pre-line">
                                {{ $review->comment ?: ($review->title ?: 'Excellent practical course experience with clear tutor instruction and immediate skill improvement.') }}
                            </p>
                        </div>

                        {{-- Bottom Row: Centered Star Rating --}}
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-center">
                            @php
                                $rScore = round((float) ($review->rating ?: 5));
                            @endphp
                            <div class="flex items-center justify-center gap-1 text-slate-800 text-sm">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $rScore)
                                        <i class="fa-solid fa-star text-xs sm:text-sm text-slate-800"></i>
                                    @else
                                        <i class="fa-regular fa-star text-xs sm:text-sm text-slate-400"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Bottom Center Slider Controls --}}
            <div class="flex items-center justify-center gap-4 pt-4">
                {{-- Left Outline Arrow Button --}}
                <button type="button"
                        @click="slidePrev()"
                        class="inline-flex items-center justify-center w-11 h-11 rounded-full border-2 border-slate-800 text-slate-800 hover:bg-slate-100 transition-all duration-200 cursor-pointer active:scale-95"
                        aria-label="Previous Reviews">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </button>

                {{-- Right Solid Dark Arrow Button --}}
                <button type="button"
                        @click="slideNext()"
                        class="inline-flex items-center justify-center w-11 h-11 rounded-full bg-slate-800 text-white hover:bg-slate-900 transition-all duration-200 cursor-pointer active:scale-95"
                        aria-label="Next Reviews">
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </button>
            </div>

            {{-- Pagination If Multiple Pages --}}
            @if ($reviews->hasPages())
                <div class="pt-3 text-center">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
