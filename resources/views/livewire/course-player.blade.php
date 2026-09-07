<div class="flex h-screen w-full flex-col bg-slate-950 text-slate-100 font-sans antialiased overflow-hidden">
    <!-- Top Navigation Header -->
    <header class="h-16 border-b border-slate-800 bg-slate-900 px-6 flex items-center justify-between z-10 shrink-0">
        <div class="flex items-center space-x-4 truncate">
            <a href="{{ Route::has('portal.dashboard') ? route('portal.dashboard') : route('filament.student.pages.overview') }}" class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-slate-800" title="Back to Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h1 class="font-semibold text-slate-100 truncate text-base lg:text-lg">{{ $course->title }}</h1>
        </div>

        <!-- Overall Course Progress Gauge -->
        <div class="flex items-center space-x-4">
            <div class="hidden sm:flex flex-col items-end">
                <span class="text-xs text-slate-400">Your Progress</span>
                <span class="text-sm font-semibold text-emerald-400">{{ $overallProgress }}%</span>
            </div>
            <div class="w-32 bg-slate-800 h-2 rounded-full overflow-hidden">
                <div class="bg-emerald-500 h-full transition-all duration-300" style="width: {{ $overallProgress }}%"></div>
            </div>
        </div>
    </header>

    <!-- Main Learning Layout Grid -->
    <div class="flex flex-1 overflow-hidden">
        
        <!-- Left Content Area -->
        <main class="flex-1 flex flex-col overflow-y-auto bg-slate-900/50 p-6 lg:p-10 justify-between">
            <div class="max-w-4xl w-full mx-auto space-y-6">
                @if($activeLesson)
                    <!-- Title & Type Meta -->
                    <div class="border-b border-slate-800 pb-4">
                        <span class="inline-block px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider rounded bg-indigo-900/50 text-indigo-400 mb-2">
                            {{ strtoupper($activeLesson->type) }}
                        </span>
                        <h2 class="text-2xl font-bold text-white">{{ $activeLesson->title }}</h2>
                    </div>

                    <!-- DYNAMIC CONTENT DISPATCHER -->
                    @if($activeLesson->type === 'video')
                        @php
                            $videoUrl = $activeLesson->video_url;
                            $youtubeId = $activeLesson->youtube_id;
                            $minPct = $activeLesson->min_watch_percentage ?: 90;
                            $resumeTime = $progressMap[$activeLesson->id]['last_watched_second'] ?? 0;
                        @endphp

                        @if($youtubeId)
                            <div 
                                x-data="{
                                    lastSaved: 0,
                                    percentWatched: 0,
                                    timer: null,
                                    init() {
                                        // Periodic check or manual trigger for completion if using external iframe
                                    }
                                }"
                                class="space-y-4"
                            >
                                <div class="relative aspect-video rounded-xl overflow-hidden bg-black shadow-2xl border border-slate-800">
                                    <iframe 
                                        class="w-full h-full"
                                        src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&enablejsapi=1" 
                                        title="{{ $activeLesson->title }}"
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen
                                    ></iframe>
                                </div>
                                <div class="flex justify-between items-center bg-slate-900/80 p-4 rounded-xl border border-slate-800 text-sm">
                                    <span class="text-slate-400 text-xs sm:text-sm">
                                        Watch at least <strong class="text-white">{{ $minPct }}%</strong> of the video to unlock the next lesson.
                                    </span>
                                    <button 
                                        wire:click="handleVideoCompletion"
                                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 font-medium rounded-lg text-white shadow transition text-xs sm:text-sm flex items-center space-x-2"
                                    >
                                        <span>Complete Video & Next</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div 
                                x-data="{
                                    player: null,
                                    lastSaved: 0,
                                    hasTriggered: false,
                                    init() {
                                        const video = this.$refs.videoElement;
                                        if (!video) return;
                                        const resumeTime = {{ $resumeTime }};
                                        if (resumeTime > 0) video.currentTime = resumeTime;

                                        video.addEventListener('timeupdate', () => {
                                            if (Math.abs(video.currentTime - this.lastSaved) > 5) {
                                                this.lastSaved = video.currentTime;
                                                $wire.syncVideoTime(Math.floor(video.currentTime));
                                            }
                                            if (video.duration > 0) {
                                                const percent = (video.currentTime / video.duration) * 100;
                                                if (percent >= {{ $minPct }} && !this.hasTriggered) {
                                                    this.hasTriggered = true;
                                                    $wire.handleVideoCompletion();
                                                }
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
                                class="relative aspect-video rounded-xl overflow-hidden bg-black shadow-2xl border border-slate-800"
                            >
                                <video 
                                    x-ref="videoElement" 
                                    controls 
                                    controlsList="nodownload" 
                                    class="w-full h-full object-contain"
                                    src="{{ $videoUrl ?: '' }}"
                                ></video>
                            </div>
                        @endif

                    @elseif($activeLesson->type === 'reading')
                        <div class="prose prose-invert max-w-none bg-slate-900 p-8 rounded-xl border border-slate-800 shadow">
                            {!! $activeLesson->reading_body ?? '<p class="text-slate-400 italic">No content provided for this reading lesson.</p>' !!}
                        </div>
                        <div class="flex justify-end pt-4">
                            <button 
                                wire:click="markReadingComplete" 
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 font-medium rounded-lg text-white shadow-lg transition flex items-center space-x-2"
                            >
                                <span>Mark as Read & Continue</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>

                    @elseif($activeLesson->type === 'quiz')
                        <div class="bg-slate-900 p-8 rounded-xl border border-slate-800 shadow">
                            @if($activeLesson->content_id)
                                @livewire('quiz-player-embedded', ['quizId' => $activeLesson->content_id], key('quiz-'.$activeLesson->id))
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-400">Quiz configuration is missing for this lesson.</p>
                                    <button wire:click="handleQuizPassed" class="mt-4 px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-xs text-slate-300">
                                        Mark as passed (Admin Override)
                                    </button>
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'assignment')
                        <div class="bg-slate-900 p-8 rounded-xl border border-slate-800 shadow">
                            @if($activeLesson->content_id)
                                @livewire('assignment-task-embedded', ['assignmentId' => $activeLesson->content_id], key('task-'.$activeLesson->id))
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-400">Assignment configuration is missing for this lesson.</p>
                                    <button wire:click="handleTaskSubmitted" class="mt-4 px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-xs text-slate-300">
                                        Mark as submitted (Admin Override)
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="bg-slate-900 p-12 rounded-xl border border-slate-800 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <h3 class="text-lg font-semibold text-slate-300">No lessons available in this course yet.</h3>
                        <p class="text-sm text-slate-500 mt-1">Please check back soon once course content is published.</p>
                    </div>
                @endif
            </div>

            <!-- Footer Bar -->
            @if($activeLesson)
                <div class="max-w-4xl w-full mx-auto pt-8 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                    <span>Course: {{ $course->title }}</span>
                    <span>Lesson ID: #{{ $activeLesson->id }}</span>
                </div>
            @endif
        </main>

        <!-- Right Curriculum Sidebar (Udemy Style) -->
        <aside class="w-80 lg:w-96 border-l border-slate-800 bg-slate-900 flex flex-col h-full overflow-hidden shrink-0">
            <div class="p-4 border-b border-slate-800 bg-slate-900 font-semibold text-sm text-slate-200 flex items-center justify-between">
                <span>Course Content</span>
                <span class="text-xs text-slate-500 font-normal">
                    {{ $course->lessons->count() }} lessons
                </span>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-slate-800">
                @if($course->sections->isNotEmpty())
                    @foreach($course->sections as $sectionIndex => $section)
                        <div x-data="{ open: true }" class="border-b border-slate-800/50">
                            <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left bg-slate-900/80 hover:bg-slate-800/50 transition">
                                <div>
                                    <span class="text-xs uppercase tracking-wider text-slate-400 font-bold">Section {{ $sectionIndex + 1 }}</span>
                                    <h4 class="text-sm font-semibold text-slate-100 leading-snug">{{ $section->title }}</h4>
                                </div>
                                <svg class="w-4 h-4 transform transition-transform text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" class="bg-slate-950/40">
                                @foreach($section->lessons as $lesson)
                                    @php
                                        $p = $progressMap[$lesson->id] ?? null;
                                        $isUnlocked = $p['is_unlocked'] ?? false;
                                        $isCompleted = $p['is_completed'] ?? false;
                                        $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                                    @endphp

                                    <div 
                                        wire:click="selectLesson({{ $lesson->id }})"
                                        class="flex items-center px-4 py-3 space-x-3 transition cursor-pointer 
                                        {{ $isActive ? 'bg-indigo-950/40 border-l-4 border-indigo-500' : 'hover:bg-slate-800/30' }}
                                        {{ !$isUnlocked ? 'opacity-45 cursor-not-allowed' : '' }}"
                                    >
                                        <!-- Status Icon Indicator -->
                                        <div class="shrink-0">
                                            @if($isCompleted)
                                                <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            @elseif($isUnlocked)
                                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                                            @else
                                                <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                            @endif
                                        </div>

                                        <!-- Lesson Label & Format -->
                                        <div class="flex-1 truncate">
                                            <p class="text-xs font-medium {{ $isActive ? 'text-indigo-300 font-semibold' : 'text-slate-300' }} truncate">
                                                {{ $lesson->title }}
                                            </p>
                                            <span class="text-[10px] text-slate-500 uppercase tracking-wider">
                                                {{ $lesson->type }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Flat lesson list when course sections are not yet created --}}
                    <div class="bg-slate-950/40">
                        @foreach($course->lessons as $lesson)
                            @php
                                $p = $progressMap[$lesson->id] ?? null;
                                $isUnlocked = $p['is_unlocked'] ?? false;
                                $isCompleted = $p['is_completed'] ?? false;
                                $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                            @endphp

                            <div 
                                wire:click="selectLesson({{ $lesson->id }})"
                                class="flex items-center px-4 py-3 space-x-3 transition cursor-pointer 
                                {{ $isActive ? 'bg-indigo-950/40 border-l-4 border-indigo-500' : 'hover:bg-slate-800/30' }}
                                {{ !$isUnlocked ? 'opacity-45 cursor-not-allowed' : '' }}"
                            >
                                <div class="shrink-0">
                                    @if($isCompleted)
                                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @elseif($isUnlocked)
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                                    @else
                                        <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 truncate">
                                    <p class="text-xs font-medium {{ $isActive ? 'text-indigo-300 font-semibold' : 'text-slate-300' }} truncate">
                                        {{ $lesson->title }}
                                    </p>
                                    <span class="text-[10px] text-slate-500 uppercase tracking-wider">
                                        {{ $lesson->type }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
