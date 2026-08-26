<x-filament-panels::page>
    <div
        x-data="{
            currentSlide: 0,
            slidesCount: {{ count($heroBanners) }},
            timer: null,
            selectedDate: null,
            calendarMonth: @js($calendarMonthName),
            events: @js($calendarEvents),
            activeTab: 'active',
            init() {
                this.startTimer();
            },
            startTimer() {
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => {
                    this.nextSlide();
                }, 12000);
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
        {{-- 1. MAIN TOP HERO BANNER CAROUSEL & INSTRUCTOR PROFILE CARD   --}}
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
                            class="w-full h-full relative overflow-hidden rounded-2xl text-white p-4 sm:p-5 md:p-6 border border-white/15 shadow-md flex flex-col-reverse sm:flex-row items-center justify-between sm:justify-between gap-3 sm:gap-5 box-border"
                        >
                            {{-- Text Content & Action --}}
                            <div class="relative z-10 max-w-lg space-y-2 flex flex-col items-center sm:items-start text-center sm:text-left flex-1 min-w-0 justify-center">
                                <div class="flex items-center justify-center sm:justify-start gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold border backdrop-blur-xs {{ $banner['badge_color'] }}">
                                        {{ $banner['badge'] }}
                                    </span>
                                    <span class="text-[10px] sm:text-[11px] font-bold text-white/80">
                                        {{ $idx + 1 }} of {{ count($heroBanners) }}
                                    </span>
                                </div>

                                <h1 class="text-base sm:text-xl md:text-2xl font-black text-white tracking-tight leading-snug line-clamp-1 sm:line-clamp-2">
                                    {{ $banner['title'] }}
                                </h1>

                                <p class="text-[11px] sm:text-xs md:text-sm text-white/90 leading-relaxed font-normal line-clamp-2">
                                    {{ $banner['description'] }}
                                </p>

                                <div class="pt-1 flex flex-wrap items-center justify-center sm:justify-start gap-2 sm:gap-3">
                                    <a 
                                        href="{{ $banner['cta_url'] }}" 
                                        class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs font-extrabold text-slate-900 bg-white hover:bg-slate-50 shadow-sm hover:shadow-md transition-all duration-150 transform hover:-translate-y-0.5"
                                    >
                                        <span>{{ $banner['cta_label'] }}</span>
                                        <svg class="w-3.5 h-3.5 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>

                                    <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-extrabold text-white/90 bg-black/20 backdrop-blur-xs px-3 py-1.5 rounded-full border border-white/10">
                                        <span class="text-white/70">{{ $banner['metric_label'] }}:</span>
                                        <span>{{ $banner['metric_value'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Instructor Avatar / Photo --}}
                            <div class="relative flex-shrink-0 flex items-center justify-center">
                                <div class="relative w-20 h-20 sm:w-28 sm:h-28 md:w-32 md:h-32 lg:w-36 lg:h-36 rounded-full p-1 sm:p-1.5 bg-white/20 backdrop-blur-md border-2 border-white/40 shadow-xl flex items-center justify-center">
                                    @if (!empty($banner['avatar']))
                                        <img 
                                            src="{{ $banner['avatar'] }}" 
                                            alt="{{ auth()->user()->name }}" 
                                            class="w-full h-full object-cover rounded-full shadow-inner border border-white/50"
                                        />
                                    @else
                                        <div class="w-full h-full rounded-full bg-teal-800 text-white font-black text-2xl sm:text-3xl md:text-4xl flex items-center justify-center shadow-inner border border-white/50">
                                            {{ Str::upper(substr(auth()->user()->name ?? 'IN', 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Carousel Indicator Dots & Controls --}}
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-1.5">
                        @foreach ($heroBanners as $idx => $banner)
                            <button
                                type="button"
                                x-on:click="goToSlide({{ $idx }})"
                                class="h-2 rounded-full transition-all duration-300 focus:outline-none"
                                :class="currentSlide === {{ $idx }} ? 'w-8 bg-[#7C3AED] dark:bg-purple-400' : 'w-2.5 bg-slate-300 dark:bg-slate-700 hover:bg-slate-400 dark:hover:bg-slate-600'"
                                aria-label="Go to slide {{ $idx + 1 }}"
                            ></button>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            x-on:click="prevSlide()"
                            class="w-6 h-6 rounded-full bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-2xs transition-colors"
                            aria-label="Previous slide"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            x-on:click="nextSlide()"
                            class="w-6 h-6 rounded-full bg-white dark:bg-[#102028] border border-slate-200 dark:border-[#233842] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-2xs transition-colors"
                            aria-label="Next slide"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Column: Teaching Profile Card (~30% / 4 cols on LG) --}}
            <div class="lg:col-span-4 edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#233842] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">Teaching Profile</h2>
                    </div>
                    <span class="text-xs font-extrabold text-[#7C3AED] dark:text-purple-300 bg-purple-50 dark:bg-purple-950/50 px-2.5 py-0.5 rounded-full border border-purple-100 dark:border-purple-900/50">
                        {{ count($courses) }} Classes
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        @if (auth()->user()->profile_photo_path)
                            <img src="{{ auth()->user()->getFilamentAvatarUrl() }}" alt="{{ auth()->user()->name }}" class="w-12 h-12 rounded-xl object-cover shadow-2xs border border-slate-200 dark:border-[#233842]" />
                        @else
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#7C3AED] to-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-2xs">
                                {{ Str::upper(substr(auth()->user()->name ?? 'IN', 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-slate-900 dark:text-white text-sm truncate">
                                {{ auth()->user()->name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                {{ auth()->user()->email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-100 dark:border-slate-800 text-center">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Learners</span>
                            <span class="text-lg font-black text-slate-900 dark:text-white">{{ $totalStudents }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-100 dark:border-slate-800 text-center">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Pending</span>
                            <span class="text-lg font-black {{ $pendingSubmissionsCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $pendingSubmissionsCount }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-[#233842]">
                    <a 
                        href="{{ \App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl() }}" 
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-extrabold text-white bg-[#7C3AED] hover:bg-[#6D28D9] dark:bg-purple-600 dark:hover:bg-purple-500 shadow-sm hover:shadow-md transition transform hover:-translate-y-0.5"
                    >
                        <span>Open Submission Queue</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- 2. STATUS KPI CARDS WITH CIRCULAR PERCENTAGE RINGS           --}}
        {{-- ============================================================ --}}
        <div class="space-y-3">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Status Overview</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Card 1: Total Classes (Amber / Peach Pastel) --}}
                <a 
                    href="#classrooms-section" 
                    class="bg-[#FFF9EC] dark:bg-[#1c1917] border border-[#FEEFD0] dark:border-[#292524] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
                >
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#FDE68A] text-[#B45309] dark:bg-amber-900/60 dark:text-amber-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#FDE68A]/50 dark:text-amber-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#F59E0B]" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#B45309] dark:text-amber-300">
                                100%
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ sprintf('%02d', count($courses)) }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#B45309] dark:group-hover:text-amber-300 transition-colors">
                            Total Classes &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            Active assigned cohorts
                        </div>
                    </div>
                </a>

                {{-- Card 2: Total Students (Sky Pastel) --}}
                <a 
                    href="{{ route('filament.instructor.pages.student-results') }}" 
                    class="bg-[#F0F9FF] dark:bg-[#0c1f2d] border border-[#E0F2FE] dark:border-[#0f3b56] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
                >
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#BAE6FD] text-[#0284C7] dark:bg-sky-900/60 dark:text-sky-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#BAE6FD]/50 dark:text-sky-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#0284C7]" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#0284C7] dark:text-sky-300">
                                {{ $totalStudents }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ sprintf('%02d', $totalStudents) }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#0284C7] dark:group-hover:text-sky-300 transition-colors">
                            Total Students &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            Enrolled across cohorts
                        </div>
                    </div>
                </a>

                {{-- Card 3: Pending Reviews (Rose / Pink Pastel) --}}
                <a 
                    href="{{ \App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl() }}" 
                    class="bg-[#FFF0F3] dark:bg-[#201316] border border-[#FDDDE3] dark:border-[#351920] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
                >
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#FECDD3] text-[#E11D48] dark:bg-rose-900/60 dark:text-rose-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#FECDD3]/50 dark:text-rose-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#F43F5E]" stroke-dasharray="{{ $stats['grading_percent'] ?? 100 }}, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#E11D48] dark:text-rose-300">
                                {{ $stats['grading_percent'] ?? 100 }}%
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ sprintf('%02d', $pendingSubmissionsCount) }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#E11D48] dark:group-hover:text-rose-300 transition-colors">
                            Pending Reviews &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            {{ $pendingSubmissionsCount > 0 ? 'Needs instructor evaluation' : 'All submissions graded' }}
                        </div>
                    </div>
                </a>

                {{-- Card 4: Upcoming Sessions (Mint / Green Pastel) --}}
                <a 
                    href="{{ route('filament.instructor.pages.schedule') }}" 
                    class="bg-[#F0FDF4] dark:bg-[#0f1f17] border border-[#DCFCE7] dark:border-[#1a3324] rounded-2xl p-4 flex flex-col justify-between relative overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group no-underline"
                >
                    <div class="flex items-start justify-between">
                        <div class="w-9 h-9 rounded-full bg-[#BBF7D0] text-[#16A34A] dark:bg-emerald-900/60 dark:text-emerald-300 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <div class="relative w-11 h-11 flex items-center justify-center">
                            <svg class="w-11 h-11 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-[#BBF7D0]/50 dark:text-emerald-900/40" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-[#10B981]" stroke-dasharray="100, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-[10px] font-extrabold text-[#16A34A] dark:text-emerald-300">
                                {{ $upcomingSessionCount }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ sprintf('%02d', $upcomingSessionCount) }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5 group-hover:text-[#16A34A] dark:group-hover:text-emerald-300 transition-colors">
                            Upcoming Sessions &rarr;
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                            Scheduled workshops & webinars
                        </div>
                    </div>
                </a>

            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- 3. QUICK ACTIONS SHORTCUTS BAR                               --}}
        {{-- ============================================================ --}}
        <div class="edtech-card bg-white dark:bg-[#102028] p-3.5 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm">
            <div class="flex items-center justify-between gap-3 overflow-x-auto pb-1 sm:pb-0">
                <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 pl-2 hidden sm:inline-block">
                    Quick Actions:
                </span>
                <div class="flex items-center gap-2.5 flex-1 justify-start sm:justify-end flex-nowrap">
                    @foreach ($quickActions as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-50 dark:bg-slate-800/70 hover:bg-purple-50 dark:hover:bg-purple-950/40 text-slate-700 dark:text-slate-200 hover:text-[#7C3AED] dark:hover:text-purple-300 border border-slate-200/70 dark:border-slate-700/60 transition whitespace-nowrap transform hover:-translate-y-0.5 shadow-2xs"
                        >
                            <x-filament::icon :icon="$action['icon']" class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                            <span>{{ $action['label'] }}</span>
                            @if (!empty($action['badge']))
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-rose-500 text-white">
                                    {{ $action['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- 4. CORE 2-COLUMN DASHBOARD (CLASSROOMS + AGENDA & QUEUE)     --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- LEFT COLUMN: 8 Cols (Classrooms & Active Cohorts Cards) --}}
            <div id="classrooms-section" class="lg:col-span-8 space-y-6">
                
                {{-- Classrooms Section Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#233842] pb-3 flex-wrap gap-2">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                My Classrooms & Cohorts
                            </h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                Assigned active courses, student rosters, and curriculum deliverables.
                            </p>
                        </div>
                        <a 
                            href="{{ route('filament.instructor.pages.student-results') }}" 
                            class="text-xs font-extrabold text-[#7C3AED] dark:text-purple-400 hover:text-purple-700 flex items-center gap-1"
                        >
                            <span>View All Grades</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        @forelse ($courses as $course)
                            <div class="p-4 rounded-xl border border-slate-200/80 dark:border-[#233842] bg-gradient-to-b from-slate-50/50 to-white dark:from-slate-800/40 dark:to-slate-850 hover:shadow-md transition-all space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="space-y-0.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-50 text-[#7C3AED] dark:bg-purple-950/60 dark:text-purple-300">
                                            {{ $course['code'] }}
                                        </span>
                                        <h4 class="font-extrabold text-slate-900 dark:text-white text-sm line-clamp-1">
                                            {{ $course['title'] }}
                                        </h4>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $course['is_active'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $course['is_active'] ? 'Active' : 'Archived' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                                    <span class="flex items-center gap-1 font-semibold">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        {{ $course['students'] }} {{ Str::plural('Student', $course['students']) }}
                                    </span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ $course['intake'] }}
                                    </span>
                                </div>

                                <div class="pt-2 border-t border-slate-100 dark:border-[#233842] flex items-center justify-between gap-2">
                                    {{-- Student Avatars Stack --}}
                                    <div class="flex -space-x-2 overflow-hidden">
                                        @foreach ($course['student_list'] as $st)
                                            @if (!empty($st['profile_photo_path']))
                                                <img class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-slate-800 object-cover" src="{{ asset('storage/' . $st['profile_photo_path']) }}" alt="{{ $st['name'] }}">
                                            @else
                                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full ring-2 ring-white dark:ring-slate-800 bg-[#7C3AED] text-[10px] font-bold text-white">
                                                    {{ Str::upper(substr($st['name'], 0, 1)) }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <a 
                                            href="{{ \App\Filament\Instructor\Resources\AssignmentResource\AssignmentResource::getUrl() }}" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-[#7C3AED] hover:bg-purple-50 dark:hover:bg-slate-700 transition"
                                            title="Assignments"
                                        >
                                            <x-filament::icon icon="heroicon-o-document-text" class="w-4 h-4" />
                                        </a>
                                        <a 
                                            href="{{ route('filament.instructor.pages.student-results') }}" 
                                            class="px-2.5 py-1 rounded-full text-[11px] font-bold text-[#7C3AED] dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 transition"
                                        >
                                            Roster &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 py-8 text-center text-slate-400 dark:text-slate-500">
                                <p class="text-sm font-bold">No courses currently assigned to you.</p>
                                <p class="text-xs mt-1">Courses assigned by admin will appear here automatically.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pending Grading Queue Feed Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#233842] pb-3 flex-wrap gap-2">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <span>Submissions Requiring Review</span>
                                @if ($pendingSubmissionsCount > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">
                                        {{ $pendingSubmissionsCount }}
                                    </span>
                                @endif
                            </h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                Newly turned in student assignments and assessments waiting for evaluation.
                            </p>
                        </div>

                        <a 
                            href="{{ \App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource::getUrl() }}" 
                            class="text-xs font-extrabold text-[#7C3AED] dark:text-purple-400 hover:text-purple-700 flex items-center gap-1"
                        >
                            <span>Open Full Queue</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-[#233842]">
                        @forelse ($recentSubmissions as $submission)
                            <div class="py-3 flex items-center justify-between gap-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition rounded-xl px-2">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if (!empty($submission['student_photo']))
                                        <img src="{{ $submission['student_photo'] }}" alt="{{ $submission['student_name'] }}" class="w-9 h-9 rounded-full object-cover shadow-2xs" />
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs flex items-center justify-center shadow-2xs">
                                            {{ $submission['initials'] }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h5 class="font-extrabold text-xs text-slate-900 dark:text-white truncate">
                                                {{ $submission['student_name'] }}
                                            </h5>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold {{ $submission['badge_color'] }}">
                                                {{ $submission['type'] }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                            {{ $submission['title'] }} &bull; <span class="text-slate-400">{{ $submission['course'] }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <span class="text-[11px] text-slate-400 hidden sm:inline-block">
                                        {{ $submission['submitted_at'] }}
                                    </span>
                                    <a 
                                        href="{{ $submission['url'] }}" 
                                        class="px-3.5 py-1.5 rounded-full text-xs font-extrabold text-white bg-[#7C3AED] hover:bg-[#6D28D9] dark:bg-purple-600 dark:hover:bg-purple-500 shadow-2xs transition transform hover:-translate-y-0.5"
                                    >
                                        Grade &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center">
                                    <x-heroicon-o-check-circle class="w-6 h-6" />
                                </div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">All submissions are graded!</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">New submissions turned in by students will show up here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: 4 Cols (Interactive Calendar & Today Schedule) --}}
            <div class="lg:col-span-4 space-y-6">
                
                {{-- Interactive Session Calendar Widget Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#233842]">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-calendar-days class="w-4 h-4 text-[#7C3AED] dark:text-purple-400" />
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">
                                {{ $calendarMonthName }}
                            </h2>
                        </div>

                        <div class="flex items-center gap-1">
                            <button 
                                wire:click="previousMonth" 
                                class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                title="Previous Month"
                            >
                                <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                            </button>
                            <button 
                                wire:click="nextMonth" 
                                class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                title="Next Month"
                            >
                                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>

                    {{-- Calendar Day Headers --}}
                    <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                    </div>

                    {{-- Calendar Weeks Grid --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-xs">
                        @foreach ($calendarWeeks as $week)
                            @foreach ($week as $day)
                                <button
                                    type="button"
                                    x-on:click="selectDay('{{ $day['date_full'] }}')"
                                    class="h-8 rounded-lg relative flex flex-col items-center justify-center transition font-semibold {{ $day['in_month'] ? 'text-slate-800 dark:text-slate-200' : 'text-slate-300 dark:text-slate-600' }} {{ $day['is_today'] ? 'bg-[#7C3AED] text-white font-black shadow-2xs' : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                                    :class="selectedDate === '{{ $day['date_full'] }}' ? 'ring-2 ring-[#7C3AED] bg-purple-50 dark:bg-purple-950/60 font-black' : ''"
                                >
                                    <span>{{ $day['date'] }}</span>
                                    @if (!empty($day['sessions']))
                                        <span class="absolute bottom-1 w-1.5 h-1.5 rounded-full {{ $day['is_today'] ? 'bg-white' : 'bg-[#7C3AED]' }}"></span>
                                    @endif
                                </button>
                            @endforeach
                        @endforeach
                    </div>

                    {{-- Expandable Selected Date Drawer --}}
                    <template x-if="selectedDate && events[selectedDate]">
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-[#233842] space-y-2">
                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">
                                Sessions on <strong x-text="selectedDate"></strong>:
                            </span>
                            <template x-for="item in events[selectedDate]" :key="item.title + item.start_time">
                                <div class="p-2.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200/50 dark:border-purple-800/40 text-left space-y-0.5">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="font-bold text-xs text-purple-950 dark:text-purple-200" x-text="item.title"></span>
                                        <span class="text-[10px] font-black text-[#7C3AED] dark:text-purple-300" x-text="item.start_time"></span>
                                    </div>
                                    <p class="text-[11px] text-purple-700 dark:text-purple-400" x-text="item.course_title"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Upcoming Sessions Widget Card --}}
                <div class="edtech-card bg-white dark:bg-[#102028] rounded-2xl p-5 border border-slate-100 dark:border-[#233842] shadow-sm space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#233842]">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100">
                            Upcoming Live Classes
                        </h2>
                        <a 
                            href="{{ route('filament.instructor.pages.schedule') }}" 
                            class="text-[11px] font-extrabold text-[#7C3AED] dark:text-purple-400 hover:text-purple-700"
                        >
                            Schedule &rarr;
                        </a>
                    </div>

                    <div class="space-y-2">
                        @forelse ($upcomingSessions as $session)
                            <div class="p-3 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/60 dark:bg-slate-800/40 hover:bg-purple-50/40 dark:hover:bg-slate-800 transition space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-black text-xs text-slate-900 dark:text-white truncate">
                                        {{ $session['title'] }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $session['is_today'] ? 'bg-[#7C3AED] text-white font-black' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                        {{ $session['date'] }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                    <span class="truncate">{{ $session['course'] }}</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $session['time'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-slate-400 dark:text-slate-500 text-xs">
                                <p>No upcoming live sessions.</p>
                                <a href="{{ route('filament.instructor.pages.schedule') }}" class="text-[#7C3AED] dark:text-purple-400 font-extrabold mt-1 inline-block">
                                    + Schedule Session
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
