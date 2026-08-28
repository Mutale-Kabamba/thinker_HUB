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
                    class="relative overflow-hidden rounded-2xl shadow-sm h-[300px] sm:h-[230px] md:h-[220px] min-h-[300px] sm:min-h-[230px] md:min-h-[220px] max-h-[300px] sm:max-h-[230px] md:max-h-[220px] grid" 
                    style="grid-template-areas: 'slide';"
                    x-on:mouseenter="pause()"
                    x-on:mouseleave="resume()"
                >
                    @foreach ($heroBanners as $idx => $banner)
                        <div
                            x-show="currentSlide === {{ $idx }}"
                            x-cloak
                            x-transition:enter="transition-opacity duration-500 ease-in-out"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity duration-500 ease-in-out"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            style="background: {{ $banner['css_gradient'] }} !important; color: #ffffff !important; grid-area: slide;"
                            class="w-full h-full relative overflow-hidden rounded-2xl text-white p-3.5 sm:p-5 md:p-6 border border-white/15 shadow-md flex flex-col-reverse sm:flex-row items-center justify-between sm:justify-between gap-2.5 sm:gap-5 box-border"
                        >
                            {{-- Text Content & Action (Center aligned) --}}
                            <div class="relative z-10 max-w-lg space-y-1.5 sm:space-y-2.5 flex flex-col items-center sm:items-start text-center sm:text-left flex-1 min-w-0 justify-center">
                                <div class="flex items-center justify-center sm:justify-start gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold border backdrop-blur-xs {{ $banner['badge_color'] }}">
                                        {{ $banner['badge'] }}
                                    </span>
                                    <span class="text-[10px] sm:text-[11px] font-bold text-white/80">
                                        {{ $idx + 1 }} of {{ count($heroBanners) }}
                                    </span>
                                </div>

                                <h1 class="text-sm sm:text-xl md:text-2xl font-black text-white tracking-tight leading-snug line-clamp-1 sm:line-clamp-2">
                                    {{ $banner['title'] }}
                                </h1>

                                <p class="text-[11px] sm:text-xs md:text-sm text-white/90 leading-relaxed font-normal line-clamp-2">
                                    {{ $banner['description'] }}
                                </p>

                                <div class="pt-1 flex flex-wrap items-center justify-center sm:justify-start gap-2 sm:gap-3">
                                    <a 
                                        href="{{ $banner['cta_url'] }}" 
                                        class="inline-flex items-center gap-1.5 px-3.5 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs font-extrabold text-slate-800 bg-white hover:bg-slate-50 shadow-sm hover:shadow-md transition-all duration-150 transform hover:-translate-y-0.5"
                                    >
                                        <span>{{ $banner['cta_label'] }}</span>
                                        <svg class="w-3.5 h-3.5 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>

                                    <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-extrabold text-white/90 bg-black/20 backdrop-blur-xs px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full border border-white/10">
                                        <span class="text-white/70">{{ $banner['metric_label'] }}:</span>
                                        <span>{{ $banner['metric_value'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Student Circular Profile Picture Card / Avatar (Center Aligned) --}}
                            <div class="relative flex-shrink-0 flex items-center justify-center">
                                <div class="relative w-20 h-20 sm:w-28 sm:h-28 md:w-32 md:h-32 lg:w-36 lg:h-36 rounded-full p-1 sm:p-1.5 bg-white/20 backdrop-blur-md border-2 border-white/40 shadow-xl flex items-center justify-center">
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
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Interactive Carousel Navigation Bar --}}
                <div class="flex items-center justify-between px-1 py-1">
                    {{-- Compact Step Indicator Pills --}}
                    <div class="flex items-center gap-1.5">
                        @foreach ($heroBanners as $idx => $banner)
                            <button
                                type="button"
                                x-on:click="goToSlide({{ $idx }})"
                                title="{{ $banner['badge'] }}"
                                aria-label="Go to slide {{ $idx + 1 }}"
                                class="carousel-indicator-dot rounded-full transition-all duration-300 focus:outline-none !min-h-0 !min-w-0"
                                :class="currentSlide === {{ $idx }} ? '!w-6 bg-[#7C3AED] dark:bg-purple-400 shadow-xs' : '!w-2 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600'"
                                style="height: 5px !important; min-height: 5px !important; max-height: 5px !important; padding: 0 !important; border: none !important;"
                            ></button>
                        @endforeach
                    </div>

                    {{-- Next & Prev Arrow Controls --}}
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            x-on:click="prevSlide()"
                            class="carousel-ctrl-btn rounded-full bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-2xs transition-colors !min-h-0 !min-w-0"
                            style="width: 28px !important; height: 28px !important; min-height: 28px !important; min-width: 28px !important;"
                            aria-label="Previous slide"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button
                            type="button"
                            x-on:click="nextSlide()"
                            class="carousel-ctrl-btn rounded-full bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-2xs transition-colors !min-h-0 !min-w-0"
                            style="width: 28px !important; height: 28px !important; min-height: 28px !important; min-width: 28px !important;"
                            aria-label="Next slide"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
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

                {{-- STATUS SECTION: 3 PASTEL KPI CARDS (MATERIALS, ASSIGNMENTS, ASSESSMENTS) --}}
                <div class="space-y-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Status</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        
                        {{-- Card 1: Learning Materials (Yellow / Peach Pastel) --}}
                        <a 
                            href="{{ route('filament.student.pages.materials') }}" 
                            class="bg-[#FFF9EC] dark:bg-[#1c1917] border border-[#FEEFD0] dark:border-[#292524] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
                        >
                            <div class="flex items-start justify-between">
                                {{-- Circular Icon Badge --}}
                                <div class="w-9 h-9 rounded-full bg-[#FDE68A] text-[#B45309] dark:bg-amber-900/60 dark:text-amber-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
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
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#B45309] dark:group-hover:text-amber-300 transition-colors">
                                    Learning Materials &rarr;
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                    of {{ $stats['lessons_total'] ?? 0 }} viewed
                                </div>
                            </div>
                        </a>

                        {{-- Card 2: Assignments (Rose / Pink Pastel) --}}
                        <a 
                            href="{{ route('filament.student.pages.assignments') }}" 
                            class="bg-[#FFF0F3] dark:bg-[#201316] border border-[#FDDDE3] dark:border-[#351920] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
                        >
                            <div class="flex items-start justify-between">
                                {{-- Circular Icon Badge --}}
                                <div class="w-9 h-9 rounded-full bg-[#FECDD3] text-[#E11D48] dark:bg-rose-900/60 dark:text-rose-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
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
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#E11D48] dark:group-hover:text-rose-300 transition-colors">
                                    Assignments &rarr;
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                    of {{ $stats['assignments_total'] ?? 0 }} completed
                                </div>
                            </div>
                        </a>

                        {{-- Card 3: Assessments (Mint / Green Pastel) --}}
                        <a 
                            href="{{ route('filament.student.pages.assessments') }}" 
                            class="bg-[#F0FDF4] dark:bg-[#0f1f17] border border-[#DCFCE7] dark:border-[#1a3324] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
                        >
                            <div class="flex items-start justify-between">
                                {{-- Circular Icon Badge --}}
                                <div class="w-9 h-9 rounded-full bg-[#BBF7D0] text-[#16A34A] dark:bg-emerald-900/60 dark:text-emerald-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
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
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#16A34A] dark:group-hover:text-emerald-300 transition-colors">
                                    Assessments &rarr;
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                    of {{ $stats['tests_total'] ?? 0 }} completed
                                </div>
                            </div>
                        </a>

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

                    {{-- Desktop Courses Table --}}
                    <div class="overflow-x-auto hidden md:block">
                        <table class="w-full text-left text-xs">
                            <thead class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-[#233842]">
                                <tr>
                                    <th class="py-2.5 px-2 w-8">#</th>
                                    <th class="py-2.5 px-3">Course Name</th>
                                    <th class="py-2.5 px-3 min-w-[160px]">Progress</th>
                                    <th class="py-2.5 px-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-[#233842] font-medium">
                                @forelse ($enrolledCourses as $idx => $course)
                                    <tr 
                                        x-show="activeTab === 'active' ? !@js($course['is_completed']) : @js($course['is_completed'])"
                                        class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group"
                                    >
                                        <td class="py-3.5 px-2 text-slate-400">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-start gap-3">
                                                <div 
                                                    class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center text-white text-xs font-bold shadow-2xs mt-0.5"
                                                    style="background: {{ $course['gradient'] }};"
                                                >
                                                    {{ substr($course['title'], 0, 1) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <a href="{{ route('filament.student.pages.courses') }}" class="font-bold text-slate-800 dark:text-slate-100 leading-snug break-words group-hover:text-[#7C3AED] dark:group-hover:text-purple-400 transition-colors block">
                                                        {{ $course['title'] }}
                                                    </a>
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                        {{ $course['sessions_completed'] }} of {{ $course['sessions_total'] }} sessions completed
                                                    </p>
                                                </div>
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
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $course['is_completed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800' }}">
                                                {{ $course['status'] }}
                                            </span>
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

                    {{-- Mobile Course Cards --}}
                    <div class="md:hidden space-y-3 pt-1">
                        @forelse ($enrolledCourses as $idx => $course)
                            <div 
                                x-show="activeTab === 'active' ? !@js($course['is_completed']) : @js($course['is_completed'])"
                                class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-100 dark:border-[#233842] space-y-2.5"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                        <div 
                                            class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center text-white text-xs font-bold shadow-2xs mt-0.5"
                                            style="background: {{ $course['gradient'] }};"
                                        >
                                            {{ substr($course['title'], 0, 1) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('filament.student.pages.courses') }}" class="font-bold text-xs text-slate-800 dark:text-slate-100 hover:text-[#7C3AED] leading-snug break-words block">
                                                {{ $course['title'] }}
                                            </a>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                {{ $course['sessions_completed'] }}/{{ $course['sessions_total'] }} sessions
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-[#7C3AED] dark:text-purple-400 flex-shrink-0 pt-0.5">
                                        {{ $course['progress'] }}%
                                    </span>
                                </div>

                                <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full rounded-full transition-all duration-300"
                                        style="width: {{ $course['progress'] }}%; background-color: {{ $course['bar_color'] }};"
                                    ></div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] font-semibold text-slate-400 dark:text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $course['is_completed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800' }}">
                                        {{ $course['status'] }}
                                    </span>
                                    <a href="{{ route('filament.student.pages.courses') }}" class="text-[#7C3AED] dark:text-purple-400 font-bold hover:underline">
                                        View Details &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-slate-400 text-xs">
                                No courses enrolled yet.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- RIGHT RAIL COLUMN (30% / 4 cols on LG) --}}
            <div class="lg:col-span-4 space-y-5">
                
                {{-- RIGHT RAIL TOP: STATUS MINI CALENDAR --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $calendar['month'] ?? 'Schedule' }}</h2>
                            @if ($selectedDate)
                                <p class="text-[10px] text-[#7C3AED] dark:text-purple-400 font-semibold">{{ \Illuminate\Support\Carbon::parse($selectedDate)->format('D, M j') }} selected</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-1 text-slate-400">
                            <button 
                                type="button" 
                                wire:click="navigateCalendar({{ $calendar['prev']['year'] ?? now()->year }}, {{ $calendar['prev']['month'] ?? now()->month }})"
                                class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 transition-colors"
                                title="Previous Month"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button 
                                type="button" 
                                wire:click="navigateCalendar({{ $calendar['next']['year'] ?? now()->year }}, {{ $calendar['next']['month'] ?? now()->month }})"
                                class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 transition-colors"
                                title="Next Month"
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
                            @php
                                $isSelected = $selectedDate === $day['date'];
                                $hasSession = ($day['session_count'] ?? 0) > 0;
                                $hasAssignment = ($day['assignment_count'] ?? 0) > 0;
                                $hasAssessment = ($day['assessment_count'] ?? 0) > 0;

                                if ($day['is_today']) {
                                    $circleBase = 'bg-[#7C3AED] text-white font-extrabold shadow-2xs';
                                } elseif ($hasSession) {
                                    $circleBase = 'border-2 border-[#7C3AED] bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 font-extrabold';
                                } elseif ($hasAssessment && !$hasAssignment) {
                                    $circleBase = 'border-2 border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-extrabold';
                                } elseif ($hasAssignment && !$hasAssessment) {
                                    $circleBase = 'border-2 border-rose-500 bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-extrabold';
                                } elseif ($hasAssignment && $hasAssessment) {
                                    $circleBase = 'border-2 border-rose-500 bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-extrabold';
                                } elseif ($day['is_past']) {
                                    $circleBase = 'text-slate-300 dark:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800/40 font-semibold';
                                } else {
                                    $circleBase = 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold';
                                }

                                $outerRings = '';
                                if ($hasAssessment && $hasSession) {
                                    $outerRings .= ' ring-2 ring-emerald-500 ring-offset-1 dark:ring-offset-[#102028]';
                                }
                                if ($hasAssignment && $hasSession) {
                                    $outerRings .= ' outline outline-2 outline-rose-500 outline-offset-2';
                                }
                                if ($hasAssignment && $hasAssessment && !$hasSession) {
                                    $outerRings .= ' ring-2 ring-rose-500 ring-offset-1 dark:ring-offset-[#102028]';
                                }
                                if ($day['is_today']) {
                                    if ($hasAssessment) {
                                        $outerRings .= ' ring-2 ring-emerald-400 ring-offset-1 dark:ring-offset-[#102028]';
                                    } elseif ($hasAssignment) {
                                        $outerRings .= ' ring-2 ring-rose-400 ring-offset-1 dark:ring-offset-[#102028]';
                                    }
                                }
                            @endphp

                            <button
                                type="button"
                                wire:click="selectDay('{{ $day['date'] }}')"
                                title="{{ $day['has_due'] ? implode(', ', $day['due_names']) : $day['date'] }}"
                                class="h-7 w-7 mx-auto rounded-full flex items-center justify-center text-xs transition-all relative
                                    {{ $isSelected ? 'scale-110 ring-2 ring-[#7C3AED] ring-offset-2 dark:ring-offset-[#102028] font-black z-10' : '' }}
                                    {{ $circleBase }}
                                    {{ $outerRings }}"
                            >
                                {{ $day['day'] }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Calendar Legend --}}
                    <div class="flex items-center justify-center gap-3 pt-2.5 border-t border-slate-100 dark:border-[#233842] text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full border-2 border-[#7C3AED] bg-purple-50"></span>
                            <span>Class</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full ring-2 ring-rose-500 bg-rose-50"></span>
                            <span>Assignment</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full ring-2 ring-emerald-500 bg-emerald-50"></span>
                            <span>Quiz/Test</span>
                        </div>
                    </div>
                </div>

                {{-- RIGHT RAIL BOTTOM: UPCOMING / SELECTED DAY TIMELINE --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                            @if ($selectedDate)
                                <span>{{ \Illuminate\Support\Carbon::parse($selectedDate)->format('M j') }} Agenda</span>
                            @else
                                <span>Upcoming</span>
                            @endif
                        </h2>
                        @if ($selectedDate)
                            <button type="button" wire:click="selectDay(null)" class="text-[11px] font-bold text-[#7C3AED] dark:text-purple-400 hover:underline">
                                Show All &times;
                            </button>
                        @else
                            <a href="{{ route('filament.student.pages.schedule') }}" class="text-[11px] font-bold text-[#7C3AED] dark:text-purple-400 hover:underline">
                                Full Schedule &rarr;
                            </a>
                        @endif
                    </div>

                    @if ($selectedDate)
                        {{-- Events for Selected Date --}}
                        @php
                            $selectedEvents = $calendarEvents[$selectedDate] ?? [];
                        @endphp
                        <div class="space-y-3">
                            @forelse ($selectedEvents as $item)
                                <div class="p-3 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 space-y-1.5 transition-all hover:border-purple-200 dark:hover:border-purple-800">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $item['type'] === 'Session' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : ($item['type'] === 'Assignment' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300') }}">
                                            {{ $item['type'] }}
                                        </span>
                                        @if (!empty($item['time']))
                                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ $item['time'] }}</span>
                                        @endif
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $item['name'] }}</h4>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $item['course'] }}</p>

                                    @if ($item['type'] === 'Session' && !empty($item['session_id']))
                                        <div class="pt-1 flex items-center justify-end">
                                            <button 
                                                type="button" 
                                                wire:click="openSessionDetails({{ $item['session_id'] }})" 
                                                class="text-xs font-extrabold text-[#7C3AED] dark:text-purple-400 hover:underline inline-flex items-center gap-1"
                                            >
                                                <span>View Class Details</span> &rarr;
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="py-6 text-center text-slate-400">
                                    <p class="text-xs font-medium">No sessions or tasks on this day.</p>
                                </div>
                            @endforelse
                        </div>
                    @else
                        {{-- Upcoming General Timeline --}}
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

                                    @if (!empty($item['session_id']))
                                        <button 
                                            type="button" 
                                            wire:click="openSessionDetails({{ $item['session_id'] }})"
                                            class="text-[11px] font-bold text-[#7C3AED] dark:text-purple-400 hover:underline flex-shrink-0"
                                        >
                                            Details
                                        </button>
                                    @endif
                                </div>
                            @empty
                                <div class="py-6 text-center text-slate-400">
                                    <p class="text-xs font-medium">No upcoming deadlines or sessions.</p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>

    {{-- CLASS DETAILS POP-UP MODAL ON OVERVIEW --}}
    @if ($showSessionDetailsModal && $selectedSessionDetails)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in duration-150">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 dark:border-[#233842]">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                            @if ($selectedSessionDetails['course_code'])
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 text-[10px] font-extrabold border border-purple-200 dark:border-purple-900">
                                    {{ $selectedSessionDetails['course_code'] }}
                                </span>
                            @endif
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold">
                                {{ $selectedSessionDetails['type_label'] }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 text-[10px] font-bold border border-emerald-200 dark:border-emerald-800">
                                {{ ucfirst($selectedSessionDetails['status']) }}
                            </span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">{{ $selectedSessionDetails['title'] }}</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ $selectedSessionDetails['course_title'] }}</p>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeSessionDetails"
                        class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                        title="Close"
                    >
                        &times;
                    </button>
                </div>

                {{-- Body details --}}
                <div class="space-y-3">
                    {{-- Date & Time --}}
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $selectedSessionDetails['session_date'] }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $selectedSessionDetails['start_time'] }} – {{ $selectedSessionDetails['end_time'] }}</p>
                        </div>
                    </div>

                    {{-- Instructor Info --}}
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100">{{ $selectedSessionDetails['instructor_name'] }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Course Instructor</p>
                            </div>
                        </div>
                        @if ($selectedSessionDetails['instructor_whatsapp'])
                            <a 
                                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $selectedSessionDetails['instructor_whatsapp']) }}" 
                                target="_blank" 
                                rel="noopener"
                                class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold inline-flex items-center gap-1.5 no-underline transition-colors"
                            >
                                WhatsApp
                            </a>
                        @endif
                    </div>

                    {{-- Virtual Meeting Link --}}
                    @if ($selectedSessionDetails['meeting_link'])
                        <div class="p-3.5 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900 flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-purple-900 dark:text-purple-200">Virtual Classroom</p>
                                <p class="text-[11px] text-purple-700/80 dark:text-purple-300/80 truncate">{{ $selectedSessionDetails['meeting_link'] }}</p>
                            </div>
                            <a 
                                href="{{ $selectedSessionDetails['meeting_link'] }}" 
                                target="_blank" 
                                rel="noopener"
                                class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all no-underline inline-flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Join Class</span>
                            </a>
                        </div>
                    @endif

                    {{-- Notes --}}
                    @if ($selectedSessionDetails['notes'])
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-[#233842]">
                            <p class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 mb-1">Agenda &amp; Notes</p>
                            <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $selectedSessionDetails['notes'] }}</p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-[#233842]">
                    @if ($selectedSessionDetails['google_calendar_url'])
                        <a 
                            href="{{ $selectedSessionDetails['google_calendar_url'] }}" 
                            target="_blank" 
                            rel="noopener"
                            class="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-[#7C3AED] transition-colors inline-flex items-center gap-1 no-underline"
                        >
                            Sync to Google Calendar &rarr;
                        </a>
                    @else
                        <div></div>
                    @endif

                    <div class="flex items-center gap-2">
                        <a 
                            href="{{ route('filament.student.pages.schedule') }}"
                            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold no-underline transition-colors"
                        >
                            Full Schedule
                        </a>
                        <button 
                            type="button" 
                            wire:click="closeSessionDetails"
                            class="px-4 py-1.5 rounded-xl bg-[#7C3AED] hover:bg-[#6D28D9] text-white text-xs font-bold shadow-sm transition-all"
                        >
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
