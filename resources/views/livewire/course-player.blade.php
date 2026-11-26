<div
    x-data="{
        sidebarOpen: true,
        mobileDrawerOpen: false,
        activeTab: 'overview',
        lessonNote: '',
        noteSaved: false,
        loadNote(lessonId) {
            if (!lessonId) { this.lessonNote = ''; return; }
            try {
                this.lessonNote = localStorage.getItem('thub_lesson_note_' + lessonId) || '';
            } catch (_) { this.lessonNote = ''; }
        },
        saveNote(lessonId) {
            if (!lessonId) return;
            try {
                localStorage.setItem('thub_lesson_note_' + lessonId, this.lessonNote);
                this.noteSaved = true;
                setTimeout(() => { this.noteSaved = false; }, 1800);
            } catch (_) {}
        }
    }"
    x-init="loadNote({{ $activeLesson?->id ?? 'null' }})"
    class="flex min-h-dvh lg:h-screen w-full flex-col bg-white text-slate-900 font-sans antialiased lg:overflow-hidden"
>
    @php
        $orderedLessons = $course->lessons->sortBy('order_column')->values();
        $currentIdx = $activeLesson ? $orderedLessons->search(fn ($l) => $l->id === $activeLesson->id) : false;
        $hasPrev = $currentIdx !== false && $currentIdx > 0;
        $hasNext = $currentIdx !== false && $currentIdx < ($orderedLessons->count() - 1);
        $completedCount = collect($progressMap)->where('is_completed', true)->count();
        $totalLessonCount = $orderedLessons->count();
    @endphp

    {{-- Udemy-Style Dark Top Navigation Bar --}}
    <header class="sticky top-0 z-30 h-14 bg-[#0b1c22] text-white border-b border-slate-800 px-3 sm:px-5 flex items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
            <a
                href="{{ Route::has('portal.dashboard') ? route('portal.dashboard') : route('filament.student.pages.overview') }}"
                class="flex items-center gap-2 shrink-0 hover:opacity-85 transition"
                title="Back to Dashboard"
            >
                <img src="{{ asset('images/logos/white.png') }}" alt="think.er HUB" class="h-5 sm:h-6 w-auto object-contain" onerror="this.style.display='none'" />
            </a>
            <span class="hidden sm:inline-block h-5 w-px bg-slate-700 shrink-0"></span>
            <h1 class="text-xs sm:text-sm font-medium text-slate-100 truncate">{{ $course->title }}</h1>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            @if(Route::has('filament.student.pages.reviews'))
                <a
                    href="{{ route('filament.student.pages.reviews') }}"
                    class="hidden md:inline-flex items-center gap-1.5 text-xs font-medium text-slate-300 hover:text-white transition"
                >
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span>Leave a rating</span>
                </a>
            @endif

            {{-- Progress Indicator --}}
            <div class="flex items-center gap-2 text-xs text-slate-200">
                <div class="relative flex items-center justify-center w-7 h-7 rounded-full border-2 border-teal-500/40 bg-slate-900">
                    <span class="text-[10px] font-bold text-teal-300">{{ $overallProgress }}%</span>
                </div>
                <span class="hidden sm:inline text-xs font-medium text-slate-300">
                    Your progress <span class="text-slate-400">({{ $completedCount }}/{{ $totalLessonCount }})</span>
                </span>
            </div>

            {{-- Mobile Curriculum Drawer Button --}}
            <button
                type="button"
                @click="mobileDrawerOpen = true"
                class="lg:hidden inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800/80 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-slate-700 transition"
            >
                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                <span>Content</span>
            </button>
        </div>
    </header>

    {{-- Main Split Layout: Left Stage + Tabs | Right Collapsible Curriculum Sidebar --}}
    <div class="relative flex flex-1 lg:overflow-hidden">

        {{-- Floating "Course content" reopen button on desktop when sidebar is collapsed --}}
        <button
            type="button"
            x-show="!sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = true"
            style="display: none;"
            class="hidden lg:inline-flex items-center gap-2 fixed right-0 top-20 z-30 bg-[#0b1c22] text-white border border-r-0 border-slate-700 px-3.5 py-2.5 rounded-l-lg shadow-lg text-xs font-semibold hover:bg-slate-800 transition"
        >
            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Course content</span>
        </button>

        {{-- Left Column: Full-Width Stage + Lower Information Tabs --}}
        <main class="flex-1 flex flex-col lg:overflow-y-auto bg-white min-w-0">

            {{-- 1. Top Content Stage (Full-bleed Udemy-style theater) --}}
            <div class="relative w-full border-b border-slate-200 {{ in_array($activeLesson?->type, ['reading', 'resource', 'quiz', 'assignment'], true) ? 'bg-white' : 'bg-[#0f172a]' }}">

                {{-- Floating Previous / Next Lesson Stage Arrows --}}
                @if($hasPrev)
                    <button
                        type="button"
                        wire:click="goToAdjacentLesson('prev')"
                        title="Previous lesson"
                        class="hidden sm:flex items-center justify-center absolute left-0 top-1/2 -translate-y-1/2 z-20 w-9 h-12 bg-teal-600/90 hover:bg-teal-500 text-white rounded-r-md shadow-md transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                @endif

                @if($hasNext)
                    <button
                        type="button"
                        wire:click="goToAdjacentLesson('next')"
                        title="Next lesson"
                        class="hidden sm:flex items-center justify-center absolute right-0 top-1/2 -translate-y-1/2 z-20 w-9 h-12 bg-teal-600/90 hover:bg-teal-500 text-white rounded-l-md shadow-md transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endif

                @if($activeLesson)
                    @if($activeLesson->type === 'video')
                        @php
                            $videoUrl = $activeLesson->video_url;
                            $parsedVideo = \App\Support\VideoEmbedParser::parse($videoUrl);
                            $youtubeId = $parsedVideo['youtube_id'];
                            $embedUrl = $parsedVideo['autoplay_embed_url'] ?? $parsedVideo['embed_url'];
                            $isDirectFile = $parsedVideo['is_direct_file'];
                            $minPct = $activeLesson->min_watch_percentage ?: 85;
                            $resumeTime = $progressMap[$activeLesson->id]['last_watched_second'] ?? 0;
                        @endphp

                        <div class="max-w-5xl mx-auto">
                            @if($youtubeId || ($embedUrl && !$isDirectFile))
                                <div class="aspect-video bg-black">
                                    <iframe
                                        class="w-full h-full"
                                        src="{{ $embedUrl ?: ($youtubeId ? 'https://www.youtube.com/embed/' . $youtubeId . '?autoplay=1&enablejsapi=1&rel=0' : '') }}"
                                        title="{{ $activeLesson->title }}"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2 bg-[#0b1c22] px-4 py-2.5 text-xs text-slate-300 border-t border-slate-800">
                                    <span>Watch at least <strong class="text-white">{{ $minPct }}%</strong> to unlock the next lesson.</span>
                                    <button
                                        wire:click="handleVideoCompletion"
                                        class="px-3.5 py-1.5 bg-teal-600 hover:bg-teal-500 font-semibold rounded-md text-white transition"
                                    >
                                        Complete &amp; Continue &rarr;
                                    </button>
                                </div>
                            @else
                                <div
                                    x-data="{
                                        lastSaved: 0,
                                        hasTriggered: false,
                                        lowBandwidth: false,
                                        hdSrc: @js($videoUrl ?: ''),
                                        sdSrc: @js($activeLesson->low_bandwidth_video_url ?: ''),
                                        toggleBandwidth() {
                                            if (!this.sdSrc) return;
                                            const video = this.$refs.videoElement;
                                            const pos = video ? video.currentTime : 0;
                                            this.lowBandwidth = !this.lowBandwidth;
                                            this.$nextTick(() => {
                                                if (video) {
                                                    video.src = this.lowBandwidth ? this.sdSrc : this.hdSrc;
                                                    video.currentTime = pos;
                                                    const p = video.play();
                                                    if (p !== undefined) p.catch(() => {});
                                                }
                                            });
                                        },
                                        init() {
                                            const video = this.$refs.videoElement;
                                            if (!video) return;
                                            const resumeTime = {{ $resumeTime }};
                                            if (resumeTime > 0) video.currentTime = resumeTime;
                                            this.$nextTick(() => {
                                                const p = video.play();
                                                if (p !== undefined) p.catch(() => {});
                                            });
                                            video.addEventListener('timeupdate', () => {
                                                if (Math.abs(video.currentTime - this.lastSaved) > 5) {
                                                    this.lastSaved = video.currentTime;
                                                    $wire.syncVideoTime(Math.floor(video.currentTime));
                                                }
                                                if (video.duration > 0 && ((video.currentTime / video.duration) * 100) >= {{ $minPct }} && !this.hasTriggered) {
                                                    this.hasTriggered = true;
                                                    $wire.handleVideoCompletion();
                                                }
                                            });
                                            video.addEventListener('ended', () => {
                                                if (!this.hasTriggered) {
                                                    this.hasTriggered = true;
                                                    $wire.handleVideoCompletion();
                                                }
                                            });
                                        }
                                    }"
                                >
                                    <div class="aspect-video bg-black">
                                        <video
                                            x-ref="videoElement"
                                            controls
                                            autoplay
                                            playsinline
                                            preload="metadata"
                                            controlsList="nodownload"
                                            class="w-full h-full object-contain"
                                            src="{{ $videoUrl ?: '' }}"
                                        ></video>
                                    </div>
                                    @if(!empty($activeLesson->low_bandwidth_video_url))
                                        <div class="flex items-center justify-end bg-[#0b1c22] px-4 py-2 border-t border-slate-800">
                                            <button
                                                type="button"
                                                x-on:click="toggleBandwidth()"
                                                class="rounded-md border border-slate-700 bg-slate-900 px-3 py-1 text-xs font-medium text-slate-200 hover:bg-slate-800 transition"
                                                x-text="lowBandwidth ? 'Low-Bandwidth Mode: ON' : 'Switch to Low-Bandwidth Mode'"
                                            ></button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'scorm')
                        <div class="max-w-5xl mx-auto p-3 sm:p-5">
                            @if($activeLesson->scormPackage)
                                @livewire('scorm-player', ['package' => $activeLesson->scormPackage, 'lessonId' => $activeLesson->id], key('scorm-'.$activeLesson->id))
                            @else
                                <div class="py-16 text-center text-slate-400 text-sm">
                                    No SCORM / xAPI package is attached to this lesson yet.
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'reading')
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 sm:py-12 min-h-[380px] flex flex-col justify-between">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-6">{{ $activeLesson->title }}</h2>
                                <div class="prose prose-slate max-w-none leading-relaxed">
                                    {!! $activeLesson->reading_body ?? '<p class="text-slate-500 italic">No content provided for this reading lesson.</p>' !!}
                                </div>
                            </div>
                            <div class="flex justify-end pt-8 mt-8 border-t border-slate-100">
                                <button
                                    wire:click="markReadingComplete"
                                    class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 font-semibold rounded-lg text-white text-sm transition"
                                >
                                    Mark as Read &amp; Continue &rarr;
                                </button>
                            </div>
                        </div>

                    @elseif($activeLesson->type === 'quiz')
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 min-h-[380px]">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-5">{{ $activeLesson->title }}</h2>
                            @if($activeLesson->content_id)
                                @livewire('quiz-player-embedded', ['quizId' => $activeLesson->content_id], key('quiz-'.$activeLesson->id))
                            @else
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center">
                                    <p class="text-slate-500 text-sm">Quiz configuration is missing for this lesson.</p>
                                    <button wire:click="handleQuizPassed" class="mt-4 px-4 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs font-semibold text-white">
                                        Mark as passed (Admin Override)
                                    </button>
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'assignment')
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 min-h-[380px]">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-5">{{ $activeLesson->title }}</h2>
                            @if($activeLesson->content_id)
                                @livewire('assignment-task-embedded', ['assignmentId' => $activeLesson->content_id], key('task-'.$activeLesson->id))
                            @else
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center">
                                    <p class="text-slate-500 text-sm">Assignment configuration is missing for this lesson.</p>
                                    <button wire:click="handleTaskSubmitted" class="mt-4 px-4 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs font-semibold text-white">
                                        Mark as submitted (Admin Override)
                                    </button>
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'resource')
                        @php
                            $packetFiles = is_array($activeLesson->resource_files) && ! empty($activeLesson->resource_files)
                                ? array_values($activeLesson->resource_files)
                                : (is_array($activeLesson->attachments) ? array_values($activeLesson->attachments) : []);
                        @endphp
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 sm:py-12 min-h-[340px] space-y-5">
                            <h2 class="text-2xl font-bold text-slate-900">{{ $activeLesson->title }}</h2>
                            @if($activeLesson->reading_body)
                                <div class="prose prose-slate max-w-none text-sm">{!! $activeLesson->reading_body !!}</div>
                            @endif
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @forelse($packetFiles as $rIdx => $rItem)
                                    @php
                                        $rName = is_array($rItem) ? ($rItem['name'] ?? basename((string) ($rItem['path'] ?? 'Resource'))) : basename((string) $rItem);
                                    @endphp
                                    <a
                                        href="{{ route('lessons.resource.download', ['lesson' => $activeLesson->id, 'index' => $rIdx]) }}"
                                        class="flex items-center justify-between p-3.5 rounded-lg border border-slate-200 bg-slate-50 hover:border-teal-400 transition text-sm text-slate-800"
                                    >
                                        <span class="truncate font-medium">{{ $rName }}</span>
                                        <span class="ml-3 px-2.5 py-1 rounded bg-teal-600 text-white text-xs font-semibold shrink-0">Download</span>
                                    </a>
                                @empty
                                    <p class="text-slate-500 text-sm">No downloadable packets attached yet.</p>
                                @endforelse
                            </div>
                            <div class="flex justify-end pt-3">
                                <button wire:click="handleVideoCompletion" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 font-semibold rounded-lg text-white text-sm transition">
                                    Mark Complete &amp; Continue &rarr;
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="max-w-2xl mx-auto py-20 px-6 text-center">
                        <h3 class="text-lg font-bold text-slate-300">No lessons available in this course yet.</h3>
                        <p class="text-sm text-slate-500 mt-1">Please check back soon once course content is published.</p>
                    </div>
                @endif
            </div>

            {{-- 2. Udemy-Style Lower Course Information Tabs (Overview | Resources | Notes | Offline) --}}
            <div class="max-w-4xl w-full mx-auto px-4 sm:px-8 py-6 flex-1">
                <div class="flex items-center gap-6 border-b border-slate-200 overflow-x-auto text-sm font-semibold">
                    <button
                        type="button"
                        @click="activeTab = 'overview'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'overview' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Overview
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'resources'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'resources' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Resources
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'notes'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'notes' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Notes
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'offline'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'offline' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Offline Access
                    </button>
                </div>

                {{-- Tab: Overview --}}
                <div x-show="activeTab === 'overview'" class="py-6 space-y-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $course->title }}</h2>
                        @if($course->course_by)
                            <p class="text-xs text-slate-500 mt-1">by {{ $course->course_by }}</p>
                        @endif
                    </div>

                    {{-- Key Course Stats Strip (Udemy style) --}}
                    <div class="flex flex-wrap items-center gap-6 sm:gap-10 py-3 border-y border-slate-100 text-sm">
                        <div>
                            <div class="flex items-center gap-1 font-bold text-amber-600">
                                <span>{{ $course->average_rating ? number_format((float) $course->average_rating, 1) : '5.0' }}</span>
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-xs text-slate-500">{{ number_format((int) ($course->ratings_count ?? $course->review_count ?? 0)) }} ratings</p>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">{{ number_format((int) ($course->enrollments_count ?? 0)) }}</div>
                            <p class="text-xs text-slate-500">Students</p>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">{{ $totalLessonCount }} {{ \Illuminate\Support\Str::plural('lesson', $totalLessonCount) }}</div>
                            <p class="text-xs text-slate-500">{{ $course->timeline ?: 'Self-paced' }}</p>
                        </div>
                    </div>

                    @if($course->overview || $course->description)
                        <div class="space-y-2 text-sm text-slate-700 leading-relaxed">
                            <h3 class="font-bold text-slate-900">About this course</h3>
                            <p>{{ $course->overview ?: $course->description }}</p>
                        </div>
                    @endif

                    @if($course->key_outcome)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            <h4 class="font-bold text-slate-900 mb-1">Key Outcome</h4>
                            <p>{{ $course->key_outcome }}</p>
                        </div>
                    @endif
                </div>

                {{-- Tab: Resources --}}
                <div x-show="activeTab === 'resources'" style="display: none;" class="py-6 space-y-3">
                    @php
                        $lessonAttachments = $activeLesson && is_array($activeLesson->attachments) ? array_filter($activeLesson->attachments) : [];
                    @endphp
                    @if(!empty($lessonAttachments))
                        <h3 class="text-sm font-bold text-slate-900">Downloadable Lesson Resources</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($lessonAttachments as $attIndex => $attPath)
                                @if(is_string($attPath) && filled($attPath))
                                    <a
                                        href="{{ route('file.download', ['type' => 'lesson', 'id' => $activeLesson->id, 'index' => $attIndex]) }}"
                                        target="_blank"
                                        class="flex items-center justify-between p-3.5 rounded-lg border border-slate-200 hover:border-teal-500 bg-white transition text-xs text-slate-800"
                                    >
                                        <span class="truncate font-semibold">{{ basename($attPath) }}</span>
                                        <span class="ml-2 px-2.5 py-1 rounded bg-teal-50 text-teal-700 font-bold shrink-0">Download</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No downloadable files attached to this lesson.</p>
                    @endif
                </div>

                {{-- Tab: Personal Study Notes --}}
                <div x-show="activeTab === 'notes'" style="display: none;" class="py-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">My Lesson Notes</h3>
                        <span x-show="noteSaved" class="text-xs font-semibold text-emerald-600">Saved locally</span>
                    </div>
                    <textarea
                        x-model="lessonNote"
                        rows="5"
                        placeholder="Jot down key takeaways, timestamps, or questions for this lesson..."
                        class="w-full rounded-xl border border-slate-300 p-3.5 text-sm text-slate-800 focus:border-teal-500 focus:ring-teal-500"
                    ></textarea>
                    <div class="flex justify-end">
                        <button
                            type="button"
                            @click="saveNote({{ $activeLesson?->id ?? 'null' }})"
                            class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-xs font-semibold text-white transition"
                        >
                            Save Note
                        </button>
                    </div>
                </div>

                {{-- Tab: Offline Access --}}
                <div
                    x-show="activeTab === 'offline'"
                    style="display: none;"
                    x-data="{
                        saving: false,
                        saved: false,
                        async saveCourse() {
                            if (!window.ThinkerOffline || this.saving) return;
                            this.saving = true;
                            try {
                                await window.ThinkerOffline.downloadCourse(@js(route('offline.course.bundle', $course)));
                                this.saved = true;
                            } catch (_) {}
                            finally { this.saving = false; }
                        }
                    }"
                    class="py-6 space-y-4"
                >
                    <p class="text-sm text-slate-600">Save this course to your device so you can read lessons, open resources, and continue studying without internet.</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            @click="saveCourse()"
                            :disabled="saving"
                            class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-xs font-semibold text-white transition"
                            x-text="saving ? 'Saving...' : (saved ? 'Saved for Offline Study' : 'Save Course Offline')"
                        ></button>
                        <a href="{{ route('offline.library') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Open Offline Library
                        </a>
                    </div>
                </div>
            </div>
        </main>

        {{-- Mobile Backdrop for Curriculum Drawer --}}
        <div
            x-show="mobileDrawerOpen"
            x-transition.opacity
            @click="mobileDrawerOpen = false"
            class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs lg:hidden"
            style="display: none;"
        ></div>

        {{-- Right Curriculum Sidebar (Udemy-Style Crisp Light Panel, Collapsible on Desktop & Drawer on Mobile) --}}
        <aside
            :class="[
                mobileDrawerOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0',
                sidebarOpen ? 'lg:w-88 xl:w-96 lg:border-l' : 'lg:w-0 lg:border-l-0 lg:overflow-hidden'
            ]"
            class="fixed inset-y-0 right-0 z-50 w-[86vw] max-w-sm lg:static lg:z-auto border-slate-200 bg-white flex flex-col h-full overflow-hidden shrink-0 transform transition-all duration-200 ease-out"
        >
            {{-- Sidebar Header --}}
            <div class="px-4 py-3.5 border-b border-slate-200 flex items-center justify-between bg-white shrink-0">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-900 border-b-2 border-slate-900 pb-1">Course content</span>
                </div>
                <button
                    type="button"
                    @click="sidebarOpen = false; mobileDrawerOpen = false"
                    class="rounded-md p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition"
                    title="Close panel"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Scrollable Sections & Lessons --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-200">
                @if($course->sections->isNotEmpty())
                    @php $lessonCounter = 0; @endphp
                    @foreach($course->sections as $sectionIndex => $section)
                        @php
                            $secCompleted = $section->lessons->filter(fn ($l) => (bool) ($progressMap[$l->id]['is_completed'] ?? false))->count();
                            $secTotal = $section->lessons->count();
                        @endphp
                        <div x-data="{ open: true }">
                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full flex items-start justify-between gap-2 px-4 py-3.5 text-left bg-slate-50 hover:bg-slate-100/80 transition"
                            >
                                <div class="min-w-0">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">
                                        Section {{ $sectionIndex + 1 }}: {{ $section->title }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $secCompleted }} / {{ $secTotal }}</p>
                                </div>
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-slate-500 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" class="bg-white divide-y divide-slate-100">
                                @foreach($section->lessons as $lesson)
                                    @php
                                        $lessonCounter++;
                                        $p = $progressMap[$lesson->id] ?? null;
                                        $isUnlocked = $p['is_unlocked'] ?? false;
                                        $isCompleted = $p['is_completed'] ?? false;
                                        $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="selectLesson({{ $lesson->id }})"
                                        @click="mobileDrawerOpen = false; loadNote({{ $lesson->id }})"
                                        class="w-full flex items-start gap-3 px-4 py-3 text-left transition {{ $isActive ? 'bg-teal-50/90' : 'hover:bg-slate-50' }} {{ !$isUnlocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    >
                                        <span class="mt-0.5 shrink-0">
                                            @if($isCompleted)
                                                <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-teal-600 text-white">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            @elseif($isUnlocked)
                                                <span class="inline-block w-4 h-4 rounded border-2 border-slate-400"></span>
                                            @else
                                                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium leading-snug {{ $isActive ? 'text-slate-900 font-semibold' : 'text-slate-800' }}">
                                                {{ $lessonCounter }}. {{ $lesson->title }}
                                            </p>
                                            <span class="mt-1 inline-flex items-center gap-1 text-[11px] text-slate-500 uppercase tracking-wide">
                                                {{ $lesson->type }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-white divide-y divide-slate-100">
                        @foreach($orderedLessons as $idx => $lesson)
                            @php
                                $p = $progressMap[$lesson->id] ?? null;
                                $isUnlocked = $p['is_unlocked'] ?? false;
                                $isCompleted = $p['is_completed'] ?? false;
                                $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                            @endphp
                            <button
                                type="button"
                                wire:click="selectLesson({{ $lesson->id }})"
                                @click="mobileDrawerOpen = false; loadNote({{ $lesson->id }})"
                                class="w-full flex items-start gap-3 px-4 py-3 text-left transition {{ $isActive ? 'bg-teal-50/90' : 'hover:bg-slate-50' }} {{ !$isUnlocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                            >
                                <span class="mt-0.5 shrink-0">
                                    @if($isCompleted)
                                        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-teal-600 text-white">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    @elseif($isUnlocked)
                                        <span class="inline-block w-4 h-4 rounded border-2 border-slate-400"></span>
                                    @else
                                        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium leading-snug {{ $isActive ? 'text-slate-900 font-semibold' : 'text-slate-800' }}">
                                        {{ $idx + 1 }}. {{ $lesson->title }}
                                    </p>
                                    <span class="mt-1 inline-flex items-center gap-1 text-[11px] text-slate-500 uppercase tracking-wide">
                                        {{ $lesson->type }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
