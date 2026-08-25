<x-filament-panels::page>
    <div
        x-data="{
            activeTab: 'active',
            calendarMonth: @js($calendar['month'] ?? 'Current Month'),
            selectedDate: null,
            events: @js($calendarEvents),
            currentSlide: 0,
            slidesCount: {{ count($heroBanners) }},
            timer: null,
            init() {
                this.startTimer();
            },
            startTimer() {
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => {
                    this.nextSlide();
                }, 14000);
            },
            pause() {
                if (this.timer) clearInterval(this.timer);
            },
            resume() {
                this.startTimer();
            },
            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.slidesCount;
            },
            prevSlide() {
                this.currentSlide = (this.currentSlide - 1 + this.slidesCount) % this.slidesCount;
            },
            goToSlide(index) {
                this.currentSlide = index;
                this.startTimer();
            },
            selectDay(date) {
                this.selectedDate = (this.selectedDate === date) ? null : date;
            }
        }"
        class="space-y-6 font-sans"
    >
        {{-- ============================================================ --}}
        {{-- 1. MAIN TOP HERO BANNER CAROUSEL & MY RANKING ROW            --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            
            {{-- Left Wide Hero Announcement Banner (~70% / 8 cols on LG) --}}
            <div class="lg:col-span-8 flex flex-col justify-between space-y-2.5">
                <div 
                    class="relative overflow-hidden rounded-2xl shadow-sm min-h-[220px] grid" 
                    style="grid-template-areas: 'slide';"
                    x-on:mouseenter="pause()"
                    x-on:mouseleave="resume()"
                >
                    @foreach ($heroBanners as $idx => $banner)
                        <div
                            x-show="currentSlide === {{ $idx }}"
                            x-cloak
                            x-transition:enter="transition-opacity duration-700 ease-in-out"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity duration-700 ease-in-out"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            style="background: {{ $banner['css_gradient'] }} !important; color: #ffffff !important; grid-area: slide;"
                            class="w-full relative overflow-hidden rounded-2xl text-white p-6 sm:p-7 border border-white/15 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-6 min-h-[220px]"
                        >
                            {{-- Text Content & Action --}}
                            <div class="relative z-10 max-w-lg space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border backdrop-blur-xs {{ $banner['badge_color'] }}">
                                        {{ $banner['badge'] }}
                                    </span>
                                    <span class="text-[11px] font-bold text-white/80">
                                        {{ $idx + 1 }} of {{ count($heroBanners) }}
                                    </span>
                                </div>

                                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight leading-snug">
                                    {{ $banner['title'] }}
                                </h1>

                                <p class="text-xs sm:text-sm text-white/90 leading-relaxed font-normal">
                                    {{ $banner['description'] }}
                                </p>

                                <div class="pt-1 flex items-center gap-3">
                                    <a 
                                        href="{{ $banner['cta_url'] }}" 
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-extrabold text-slate-800 bg-white hover:bg-slate-50 shadow-sm hover:shadow-md transition-all duration-150 transform hover:-translate-y-0.5"
                                    >
                                        <span>{{ $banner['cta_label'] }}</span>
                                        <svg class="w-3.5 h-3.5 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>

                                    <div class="hidden sm:flex items-center gap-1.5 text-xs font-extrabold text-white/90 bg-black/20 backdrop-blur-xs px-3 py-2 rounded-full border border-white/10">
                                        <span class="text-white/70">{{ $banner['metric_label'] }}:</span>
                                        <span>{{ $banner['metric_value'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Student Circular Profile Picture Card / Avatar --}}
                            <div class="relative flex-shrink-0 flex items-center justify-center md:justify-end">
                                <div class="relative w-28 h-28 sm:w-36 sm:h-36 rounded-full p-1 bg-white/20 backdrop-blur-md border-2 border-white/40 shadow-xl flex items-center justify-center">
                                    @if (!empty($banner['avatar']))
                                        <img 
                                            src="{{ $banner['avatar'] }}" 
                                            alt="{{ auth()->user()->name }}" 
                                            class="w-full h-full object-cover rounded-full shadow-inner border border-white/50"
                                        />
                                    @else
                                        <img 
                                            src="{{ asset('images/hero/student_mentor_3d.jpg') }}" 
                                            alt="{{ auth()->user()->name }}" 
                                            class="w-full h-full object-cover rounded-full shadow-inner border border-white/50"
                                        />
                                    @endif

                                    {{-- Student Name Tag Overlay --}}
                                    <div class="absolute -bottom-2 bg-slate-950/80 backdrop-blur-md rounded-full py-0.5 px-3 text-center text-[10px] font-bold text-white border border-white/25 truncate max-w-[120px] shadow-sm">
                                        {{ auth()->user()->name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Interactive Carousel Navigation Bar --}}
                <div class="flex items-center justify-between px-1">
                    {{-- 5 Step Indicator Pills --}}
                    <div class="flex items-center gap-1.5">
                        @foreach ($heroBanners as $idx => $banner)
                            <button
                                type="button"
                                x-on:click="goToSlide({{ $idx }})"
                                title="{{ $banner['badge'] }}"
                                class="h-2 rounded-full transition-all duration-300 focus:outline-none"
                                :class="currentSlide === {{ $idx }} ? 'w-8 bg-[#7C3AED] dark:bg-purple-400' : 'w-2.5 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600'"
                            ></button>
                        @endforeach
                    </div>

                    {{-- Next & Prev Arrow Controls --}}
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            x-on:click="prevSlide()"
                            class="w-6 h-6 rounded-full bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-2xs transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button
                            type="button"
                            x-on:click="nextSlide()"
                            class="w-6 h-6 rounded-full bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-2xs transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right "My ranking" Card (~30% / 4 cols on LG) --}}
            <div class="lg:col-span-4 edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col justify-between">
                <div class="space-y-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">My ranking</h2>

                    <div class="divide-y divide-slate-100 dark:divide-[#233842]">
                        @forelse ($rankingList as $ranked)
                            <div class="py-2.5 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if (!empty($ranked['avatar']))
                                        <img src="{{ $ranked['avatar'] }}" alt="{{ $ranked['name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-[#233842]" />
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-950/60 text-[#7C3AED] dark:text-purple-300 font-extrabold text-xs flex items-center justify-center border border-purple-100 dark:border-purple-900/60">
                                            {{ $ranked['initials'] }}
                                        </div>
                                    @endif
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                        {{ $ranked['name'] }}
                                    </span>
                                </div>
                                <span class="text-xs font-extrabold {{ $ranked['is_current_user'] ? 'text-[#7C3AED] dark:text-purple-400' : 'text-slate-500 dark:text-slate-400' }}">
                                    {{ $ranked['rank'] }}
                                </span>
                            </div>
                        @empty
                            <div class="py-4 text-center text-slate-400 text-xs">
                                No ranking data available.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-[#233842] text-center">
                    <a href="{{ url('/learn/community?tab=leaderboard') }}" class="text-xs font-extrabold text-[#7C3AED] dark:text-purple-400 hover:underline">
                        View All
                    </a>
                </div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- 2. WORKSPACE SECTION: RESUME + STATUS + COURSES + CALENDAR    --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            {{-- LEFT WIDE COLUMN (70% / 8 cols on LG) --}}
            <div class="lg:col-span-8 space-y-5">

                {{-- TOP RESUME ACTIVE COURSE FLOATING BANNER --}}
                @if ($activeCourse)
                    <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-4 sm:p-5 border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0">
                            {{-- Gradient Icon / Thumbnail --}}
                            <div 
                                class="w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center shadow-xs"
                                style="background: {{ $activeCourse['gradient'] ?? 'linear-gradient(135deg, #7C3AED, #4F46E5)' }};"
                            >
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>

                            {{-- Title & Progress Bar --}}
                            <div class="min-w-0 space-y-1.5">
                                <h3 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 truncate">
                                    {{ $activeCourse['title'] }}
                                </h3>

                                <div class="flex items-center gap-3">
                                    {{-- Thin Progress Bar --}}
                                    <div class="w-32 sm:w-44 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full rounded-full transition-all duration-300"
                                            style="width: {{ $activeCourse['progress'] }}%; background-color: {{ $activeCourse['bar_color'] ?? '#7C3AED' }};"
                                        ></div>
                                    </div>

                                    {{-- Quick Counter Badges --}}
                                    <div class="hidden sm:flex items-center gap-2.5 text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                                        <span class="flex items-center gap-1" title="Learning Materials">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            {{ $activeCourse['lessons_count'] }}
                                        </span>
                                        <span class="flex items-center gap-1" title="Assignments">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            {{ $activeCourse['assignments_count'] }}
                                        </span>
                                        <span class="flex items-center gap-1" title="Assessments">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $activeCourse['tests_count'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Resume CTA Button --}}
                        <div class="flex items-center justify-end">
                            <a 
                                href="{{ route('filament.student.pages.courses') }}" 
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full text-xs font-bold text-[#7C3AED] dark:text-purple-300 border border-[#7C3AED]/40 dark:border-purple-400/40 hover:bg-[#7C3AED]/10 transition-all shadow-xs"
                            >
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                <span>Resume</span>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- STATUS SECTION: 3 PASTEL KPI CARDS (MATERIALS, ASSIGNMENTS, ASSESSMENTS) --}}
                <div class="space-y-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Status</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        
                        {{-- Card 1: Learning Materials (Yellow / Peach Pastel) --}}
                        <div class="bg-[#FFF9EC] dark:bg-[#1c1917] border border-[#FEEFD0] dark:border-[#292524] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-xs">
                            <div class="flex items-start justify-between">
                                {{-- Circular Icon Badge --}}
                                <div class="w-9 h-9 rounded-full bg-[#FDE68A] text-[#B45309] dark:bg-amber-900/60 dark:text-amber-300 flex items-center justify-center shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>

                                {{-- Circular Percentage Ring --}}
                                <div class="relative w-11 h-11 flex items-center justify-center">
                                    <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-[#FDE68A]/50 dark:text-amber-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="text-[#F59E0B]" stroke-dasharray="{{ $stats['lessons_percent'] ?? 0 }}, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                    <span class="absolute text-[10px] font-extrabold text-[#B45309] dark:text-amber-300">
                                        {{ $stats['lessons_percent'] ?? 0 }}%
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                                    {{ sprintf('%02d', $stats['lessons_completed'] ?? 0) }}
                                </div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                    Learning Materials
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                    of {{ $stats['lessons_total'] ?? 0 }} viewed
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Assignments (Rose / Pink Pastel) --}}
                        <div class="bg-[#FFF0F3] dark:bg-[#201316] border border-[#FDDDE3] dark:border-[#351920] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-xs">
                            <div class="flex items-start justify-between">
                                {{-- Circular Icon Badge --}}
                                <div class="w-9 h-9 rounded-full bg-[#FECDD3] text-[#E11D48] dark:bg-rose-900/60 dark:text-rose-300 flex items-center justify-center shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>

                                {{-- Circular Percentage Ring --}}
                                <div class="relative w-11 h-11 flex items-center justify-center">
                                    <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-[#FECDD3]/50 dark:text-rose-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="text-[#F43F5E]" stroke-dasharray="{{ $stats['assignments_percent'] ?? 0 }}, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                    <span class="absolute text-[10px] font-extrabold text-[#E11D48] dark:text-rose-300">
                                        {{ $stats['assignments_percent'] ?? 0 }}%
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                                    {{ sprintf('%02d', $stats['assignments_completed'] ?? 0) }}
                                </div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                    Assignments
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                    of {{ $stats['assignments_total'] ?? 0 }} completed
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Assessments (Mint / Green Pastel) --}}
                        <div class="bg-[#F0FDF4] dark:bg-[#0f1f17] border border-[#DCFCE7] dark:border-[#1a3324] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-xs">
                            <div class="flex items-start justify-between">
                                {{-- Circular Icon Badge --}}
                                <div class="w-9 h-9 rounded-full bg-[#BBF7D0] text-[#16A34A] dark:bg-emerald-900/60 dark:text-emerald-300 flex items-center justify-center shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                {{-- Circular Percentage Ring --}}
                                <div class="relative w-11 h-11 flex items-center justify-center">
                                    <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-[#BBF7D0]/50 dark:text-emerald-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="text-[#10B981]" stroke-dasharray="{{ $stats['tests_percent'] ?? 0 }}, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                    <span class="absolute text-[10px] font-extrabold text-[#16A34A] dark:text-emerald-300">
                                        {{ $stats['tests_percent'] ?? 0 }}%
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                                    {{ sprintf('%02d', $stats['tests_completed'] ?? 0) }}
                                </div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                    Assessments
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                    of {{ $stats['tests_total'] ?? 0 }} completed
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- MY COURSES SECTION (TABLE / LIST) --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">My Courses</h2>

                        {{-- Active / Completed Pill Tabs --}}
                        <div class="flex items-center gap-1 p-1 bg-slate-100/80 dark:bg-slate-800/90 rounded-xl">
                            <button
                                type="button"
                                @click="activeTab = 'active'"
                                :class="activeTab === 'active' 
                                    ? 'bg-[#7C3AED] text-white shadow-xs' 
                                    : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'"
                                class="px-3.5 py-1 text-xs font-bold rounded-lg transition-all"
                            >
                                Active
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'completed'"
                                :class="activeTab === 'completed' 
                                    ? 'bg-[#7C3AED] text-white shadow-xs' 
                                    : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white'"
                                class="px-3.5 py-1 text-xs font-bold rounded-lg transition-all"
                            >
                                Completed
                            </button>
                        </div>
                    </div>

                    {{-- Courses Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-[#233842]">
                                <tr>
                                    <th class="py-2.5 px-2 w-8">#</th>
                                    <th class="py-2.5 px-3">Course Name</th>
                                    <th class="py-2.5 px-3 min-w-[160px]">Completed</th>
                                    <th class="py-2.5 px-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-[#233842] font-medium">
                                @forelse ($enrolledCourses as $idx => $course)
                                    <tr 
                                        x-show="activeTab === 'active' ? @js($course['progress'] < 100) : @js($course['progress'] >= 100)"
                                        class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group"
                                    >
                                        <td class="py-3.5 px-2 text-slate-400">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-center gap-3">
                                                <div 
                                                    class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center text-white text-xs font-bold shadow-2xs"
                                                    style="background: {{ $course['gradient'] }};"
                                                >
                                                    {{ substr($course['title'], 0, 1) }}
                                                </div>
                                                <a href="{{ route('filament.student.pages.courses') }}" class="font-bold text-slate-800 dark:text-slate-100 line-clamp-1 group-hover:text-[#7C3AED] dark:group-hover:text-purple-400 transition-colors">
                                                    {{ $course['title'] }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            {{-- Smooth Horizontal Progress Bar --}}
                                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                <div 
                                                    class="h-full rounded-full transition-all duration-300"
                                                    style="width: {{ $course['progress'] }}%; background-color: {{ $course['bar_color'] }};"
                                                ></div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2.5 text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                                                <span class="flex items-center gap-1" title="Materials">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                    {{ $course['lessons_count'] }}
                                                </span>
                                                <span class="flex items-center gap-1" title="Assignments">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    {{ $course['assignments_count'] }}
                                                </span>
                                                <span class="flex items-center gap-1" title="Assessments">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $course['tests_count'] }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400">
                                            <p class="text-xs font-semibold">No courses enrolled yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- RIGHT RAIL COLUMN (30% / 4 cols on LG) --}}
            <div class="lg:col-span-4 space-y-5">
                
                {{-- RIGHT RAIL TOP: STATUS MINI CALENDAR --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Status</h2>

                        <div class="flex items-center gap-1 text-slate-400">
                            <button 
                                type="button" 
                                wire:click="navigateCalendar({{ $calendar['prev']['year'] ?? now()->year }}, {{ $calendar['prev']['month'] ?? now()->month }})"
                                class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button 
                                type="button" 
                                wire:click="navigateCalendar({{ $calendar['next']['year'] ?? now()->year }}, {{ $calendar['next']['month'] ?? now()->month }})"
                                class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Day Names Header --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500">
                        @foreach (['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
                            <div>{{ $d }}</div>
                        @endforeach
                    </div>

                    {{-- Day Number Grid --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold">
                        @for ($i = 0; $i < ($calendar['start_day'] ?? 0); $i++)
                            <div class="h-7"></div>
                        @endfor
                        @foreach (($calendar['days'] ?? []) as $day)
                            <button
                                type="button"
                                @click="selectDay(@js($day['date']))"
                                class="h-7 w-7 mx-auto rounded-full flex items-center justify-center text-xs transition-all {{ $day['is_today'] ? 'bg-[#7C3AED] text-white font-extrabold shadow-2xs' : ($day['has_due'] ? 'bg-[#7C3AED] text-white font-bold' : ($day['is_past'] ? 'text-slate-300 dark:text-slate-600' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800')) }}"
                            >
                                {{ $day['day'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- RIGHT RAIL BOTTOM: UPCOMING TIMELINE --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Upcoming</h2>

                    <div class="space-y-3">
                        @forelse ($upcoming as $item)
                            <div class="flex items-center gap-3.5">
                                {{-- Date Chip (Stacked Day on Top / Month Below) --}}
                                <div class="w-11 h-12 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-[#233842] flex flex-col items-center justify-center flex-shrink-0 text-center">
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100 leading-tight">
                                        {{ $item['day'] ?? '—' }}
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 leading-tight">
                                        {{ $item['month'] ?? '—' }}
                                    </span>
                                </div>

                                {{-- Title & Colored Type Dot --}}
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">
                                        {{ $item['name'] }}
                                    </h4>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full {{ explode(' ', $item['color'] ?? 'bg-purple-500 text-purple-500')[0] }}"></span>
                                        <span>{{ $item['type'] ?? 'Task' }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-slate-400">
                                <p class="text-xs font-medium">No upcoming deadlines or sessions.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-filament-panels::page>
