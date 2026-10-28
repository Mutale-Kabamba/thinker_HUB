<div
    class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-purple-500 selection:text-white"
    x-data="sessionViewerTracker({
        isVideo: @js($session->isVideo()),
        isRead: @js($session->isRead()),
        youtubeId: @js($youtubeId),
        embedUrl: @js($embedUrl),
        fileUrl: @js($fileUrl),
        readingContent: @js($session->reading_content),
        attendanceMarked: @js($attendanceMarked),
        targetSeconds: @js($requiredSeconds)
    })"
>
    <!-- PDF.js Scripts for PDF reading -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <!-- Top Control Bar (Fixed Header) -->
    <header class="sticky top-0 z-40 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-3 py-2.5 sm:px-6 shadow-lg safe-top">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Left: Back Nav & Session Meta -->
            <div class="flex items-center gap-3 min-w-0">
                <a
                    href="{{ url()->previous() ?: route('filament.student.pages.schedule') }}"
                    class="inline-flex items-center justify-center w-10 h-10 min-w-[40px] min-h-[40px] rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition shadow-sm flex-shrink-0 active:scale-95"
                    title="Back to Schedule"
                >
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>

                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xs sm:text-base font-bold text-white truncate max-w-xs sm:max-w-md" title="{{ $session->title }}">
                            {{ $session->title ?: 'Course Session' }}
                        </h1>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $session->isVideo() ? 'bg-purple-950 text-purple-300 border border-purple-800' : 'bg-teal-950 text-teal-300 border border-teal-800' }}">
                            {{ $session->isVideo() ? 'Video Guide' : 'Reading Material' }}
                        </span>
                    </div>
                    @if ($session->course)
                        <p class="text-[10px] sm:text-[11px] text-slate-400 truncate">
                            Course: <span class="text-slate-300 font-medium">{{ $session->course->title }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Middle/Right: Progress & Attendance Status -->
            <div class="flex items-center gap-2 sm:gap-4 flex-wrap">
                <!-- Active Engagement Timer Badge -->
                <div class="flex items-center gap-2 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800 shadow-inner">
                    <div class="flex items-center gap-1.5">
                        <span
                            class="w-2.5 h-2.5 rounded-full"
                            :class="attendanceMarked ? 'bg-emerald-400' : (isActive ? 'bg-purple-400 animate-ping' : 'bg-amber-400')"
                        ></span>
                        <span class="text-[11px] font-medium text-slate-400" x-text="attendanceMarked ? 'Completed' : (isActive ? 'Actively Engaged' : 'Paused')"></span>
                    </div>

                    <div class="text-xs font-mono font-bold text-purple-300">
                        <span x-text="formatTime(activeSeconds)">00:00</span>
                        <span class="text-slate-500 font-normal">/ <span x-text="formatTime(targetSeconds)">15:00</span></span>
                    </div>
                </div>

                <!-- Register Status Badge -->
                <div>
                    <template x-if="attendanceMarked">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-sm">
                            <i class="fa-solid fa-clipboard-check text-emerald-400"></i>
                            <span>Marked Present on Register</span>
                        </span>
                    </template>
                    <template x-if="!attendanceMarked">
                        <button
                            type="button"
                            @click="attemptManualClaim()"
                            :disabled="activeSeconds < targetSeconds || isSubmitting"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition shadow-sm"
                            :class="activeSeconds >= targetSeconds ? 'bg-purple-600 hover:bg-purple-500 text-white cursor-pointer active:scale-95' : 'bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700'"
                        >
                            <i class="fa-solid fa-check-circle" :class="isSubmitting ? 'fa-spin' : ''"></i>
                            <span x-text="activeSeconds >= targetSeconds ? (isSubmitting ? 'Recording...' : 'Mark as Done') : 'Complete to Mark Attendance'"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 flex flex-col items-center">
        @if ($session->isVideo())
            {{-- VIDEO PLAYER VIEW --}}
            <div class="w-full max-w-4xl bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl p-4 sm:p-6 space-y-4">
                <div class="relative w-full aspect-video rounded-xl bg-black overflow-hidden border border-slate-800 shadow-inner">
                    @if ($youtubeId)
                        <iframe
                            id="session-youtube-iframe"
                            x-ref="youtubeIframe"
                            src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&enablejsapi=1&rel=0"
                            class="w-full h-full border-0 block"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            @load="initYouTube()"
                        ></iframe>
                    @elseif ($embedUrl)
                        <iframe
                            id="session-embed-iframe"
                            x-ref="embedIframe"
                            src="{{ $embedUrl }}"
                            class="w-full h-full border-0 block"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                            allowfullscreen
                            @load="handlePlay()"
                        ></iframe>
                    @elseif ($fileUrl)
                        <video
                            x-ref="videoElement"
                            src="{{ $fileUrl }}"
                            controls
                            autoplay
                            class="w-full h-full object-contain bg-black"
                            @play="handlePlay()"
                            @pause="handlePause()"
                            @ended="handleEnded()"
                            @timeupdate="handleTimeUpdate()"
                            @loadedmetadata="handleMetadata()"
                        ></video>
                    @else
                        <div class="flex flex-col items-center justify-center h-full p-8 text-center text-slate-400">
                            <i class="fa-solid fa-film text-4xl text-slate-600 mb-3"></i>
                            <p class="text-sm font-semibold">No video stream or file available for this session.</p>
                        </div>
                    @endif
                </div>

                {{-- Video Session Details --}}
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                    <h3 class="text-sm font-bold text-slate-200 mb-1">Session Guide & Overview</h3>
                    <p class="text-xs text-slate-400 whitespace-pre-line">{{ $session->notes ?: 'Watch the full video guide to complete your session and automatically mark your attendance on the register.' }}</p>
                </div>
            </div>

        @elseif ($session->isRead())
            {{-- READING MATERIAL VIEW --}}
            <div class="w-full max-w-4xl space-y-4">
                @if ($fileUrl)
                    {{-- PDF Viewer Canvas --}}
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-2xl flex flex-col items-center">
                        {{-- Controls bar --}}
                        <div class="w-full flex items-center justify-between mb-3 pb-3 border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="prevPage()"
                                    :disabled="currentPage <= 1"
                                    class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 disabled:opacity-40 text-xs font-bold hover:bg-slate-700 transition"
                                >
                                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                                </button>
                                <span class="text-xs font-mono text-slate-300">
                                    Page <span x-text="currentPage">1</span> of <span x-text="totalPages">1</span>
                                </span>
                                <button
                                    type="button"
                                    @click="nextPage()"
                                    :disabled="currentPage >= totalPages"
                                    class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 disabled:opacity-40 text-xs font-bold hover:bg-slate-700 transition"
                                >
                                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                                </button>
                            </div>

                            <a
                                href="{{ $downloadUrl ?: $fileUrl }}"
                                class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold inline-flex items-center gap-1.5 transition no-underline"
                            >
                                <i class="fa-solid fa-download"></i>
                                <span>Download File</span>
                            </a>
                        </div>

                        {{-- Canvas Container --}}
                        <div class="w-full overflow-x-auto flex justify-center bg-slate-950 p-2 rounded-xl border border-slate-800">
                            <canvas id="pdf-canvas" class="max-w-full shadow-lg rounded"></canvas>
                        </div>
                    </div>
                @endif

                @if ($session->reading_content)
                    {{-- Direct Written Reading Content --}}
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
                        <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                            <h2 class="text-base font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-purple-400"></i>
                                <span>Reading Content</span>
                            </h2>
                            <span class="text-xs text-slate-400 font-mono">
                                Required: {{ $session->required_duration_minutes ?? 15 }} mins
                            </span>
                        </div>
                        <div class="prose prose-invert max-w-none text-slate-200 text-sm leading-relaxed whitespace-pre-line font-normal">
                            {{ $session->reading_content }}
                        </div>
                    </div>
                @endif

                @if (! $fileUrl && ! $session->reading_content)
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-400">
                        <i class="fa-solid fa-file-circle-exclamation text-4xl text-slate-600 mb-3"></i>
                        <p class="text-sm font-semibold">No reading material or written article has been attached to this session yet.</p>
                    </div>
                @endif
            </div>
        @endif
    </main>

    <!-- Interactive Tracker Alpine Script -->
    <script>
        function sessionViewerTracker(config) {
            return {
                isVideo: config.isVideo,
                isRead: config.isRead,
                youtubeId: config.youtubeId,
                fileUrl: config.fileUrl,
                attendanceMarked: config.attendanceMarked,
                targetSeconds: config.targetSeconds || 900,
                activeSeconds: 0,
                isActive: false,
                isSubmitting: false,
                ytPlayer: null,
                pdfDoc: null,
                currentPage: 1,
                totalPages: 1,
                timerInterval: null,

                init() {
                    const self = this;

                    // Tab visibility tracking
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            self.isActive = false;
                        } else if (!self.isVideo) {
                            self.isActive = true;
                        }
                    });

                    // For reading sessions, automatically engage when window is focused
                    if (this.isRead && !this.attendanceMarked) {
                        this.isActive = !document.hidden;
                    }

                    // Master interval to increment active seconds
                    this.timerInterval = setInterval(() => {
                        if (self.isActive && !self.attendanceMarked) {
                            self.activeSeconds++;

                            // Auto-trigger completion once target is reached
                            if (self.activeSeconds >= self.targetSeconds) {
                                self.triggerCompletion();
                            }
                        }
                    }, 1000);

                    // Load PDF if available
                    if (this.isRead && this.fileUrl && (this.fileUrl.includes('.pdf') || true)) {
                        this.loadPdf();
                    }
                },

                initYouTube() {
                    const self = this;
                    if (!window.YT || !window.YT.Player) {
                        const tag = document.createElement('script');
                        tag.src = "https://www.youtube.com/iframe_api";
                        const firstScriptTag = document.getElementsByTagName('script')[0];
                        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

                        window.onYouTubeIframeAPIReady = () => {
                            self.createYouTubePlayer();
                        };
                    } else {
                        self.createYouTubePlayer();
                    }
                },

                createYouTubePlayer() {
                    const self = this;
                    try {
                        this.ytPlayer = new YT.Player('session-youtube-iframe', {
                            events: {
                                'onStateChange': (event) => {
                                    // 1 = playing, 2 = paused, 0 = ended
                                    if (event.data === 1) {
                                        self.isActive = !document.hidden;
                                    } else {
                                        self.isActive = false;
                                    }

                                    if (event.data === 0) {
                                        self.triggerCompletion();
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        console.warn('YouTube Player API error:', e);
                    }
                },

                handlePlay() {
                    if (!document.hidden) {
                        this.isActive = true;
                    }
                },

                handlePause() {
                    this.isActive = false;
                },

                handleEnded() {
                    this.isActive = false;
                    this.triggerCompletion();
                },

                handleTimeUpdate() {},
                handleMetadata() {},

                async loadPdf() {
                    const self = this;
                    try {
                        if (typeof pdfjsLib === 'undefined') return;
                        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                        const loadingTask = pdfjsLib.getDocument(this.fileUrl);
                        this.pdfDoc = await loadingTask.promise;
                        this.totalPages = this.pdfDoc.numPages;
                        this.renderPdfPage(this.currentPage);
                    } catch (e) {
                        console.warn('PDF render error (might be doc/ppt):', e);
                    }
                },

                async renderPdfPage(num) {
                    if (!this.pdfDoc) return;
                    const page = await this.pdfDoc.getPage(num);
                    const canvas = document.getElementById('pdf-canvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.5 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                },

                prevPage() {
                    if (this.currentPage <= 1) return;
                    this.currentPage--;
                    this.renderPdfPage(this.currentPage);
                },

                nextPage() {
                    if (this.currentPage >= this.totalPages) return;
                    this.currentPage++;
                    this.renderPdfPage(this.currentPage);
                },

                formatTime(sec) {
                    const m = Math.floor(sec / 60);
                    const s = Math.floor(sec % 60);
                    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                },

                attemptManualClaim() {
                    if (this.activeSeconds >= this.targetSeconds) {
                        this.triggerCompletion();
                    }
                },

                triggerCompletion() {
                    if (this.attendanceMarked || this.isSubmitting) return;
                    this.isSubmitting = true;

                    const self = this;
                    if (this.isVideo) {
                        let duration = 0;
                        if (this.ytPlayer && typeof this.ytPlayer.getDuration === 'function') {
                            duration = this.ytPlayer.getDuration();
                        } else if (this.$refs.videoElement) {
                            duration = this.$refs.videoElement.duration || 0;
                        }

                        $wire.completeVideoSession({
                            actualSecondsWatched: self.activeSeconds,
                            duration: duration
                        }).then((res) => {
                            self.isSubmitting = false;
                            if (res && res.status === 'success') {
                                self.attendanceMarked = true;
                            }
                        }).catch(() => {
                            self.isSubmitting = false;
                        });
                    } else {
                        $wire.completeReadingSession({
                            activeSeconds: self.activeSeconds
                        }).then((res) => {
                            self.isSubmitting = false;
                            if (res && res.status === 'success') {
                                self.attendanceMarked = true;
                            }
                        }).catch(() => {
                            self.isSubmitting = false;
                        });
                    }
                }
            };
        }
    </script>
</div>
