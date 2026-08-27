<div
    class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-teal-500 selection:text-white"
    x-data="pdfMaterialTracker({
        pdfUrl: @js($fileUrl),
        pointsClaimed: @js($pointsEarned),
        targetSeconds: 180
    })"
>
    <!-- PDF.js Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <!-- Top Reading Control Bar (Fixed Header) -->
    <header class="sticky top-0 z-40 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-3 py-2.5 sm:px-6 shadow-lg safe-top">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3">
            <!-- Top/Left: Back Navigation & Material Meta & Mobile Controls -->
            <div class="flex items-center justify-between gap-2 min-w-0">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <a
                        href="{{ url()->previous() ?: route('filament.student.pages.materials') }}"
                        class="inline-flex items-center justify-center w-10 h-10 min-w-[40px] min-h-[40px] rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition shadow-sm flex-shrink-0 active:scale-95"
                        title="Back to Materials"
                    >
                        <i class="fa-solid fa-arrow-left text-sm"></i>
                    </a>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h1 class="text-xs sm:text-base font-bold text-white truncate max-w-[180px] xs:max-w-[240px] sm:max-w-md" title="{{ $material->title }}">
                                {{ $material->title }}
                            </h1>
                            @if ($material->category)
                                <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-teal-950 text-teal-300 border border-teal-800 shrink-0">
                                    {{ $material->category }}
                                </span>
                            @endif
                        </div>
                        @if ($material->course)
                            <p class="text-[10px] sm:text-[11px] text-slate-400 truncate">
                                Course: <span class="text-slate-300 font-medium">{{ $material->course->title }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Mobile Page Count & Download -->
                <div class="flex items-center gap-2 shrink-0 md:hidden">
                    <span class="text-[11px] text-slate-400 font-mono" x-show="totalPages > 0">
                        <span x-text="currentPage">1</span>/<span x-text="totalPages">1</span>
                    </span>
                    <a
                        :href="pdfUrl"
                        download
                        class="inline-flex items-center justify-center w-10 h-10 min-w-[40px] min-h-[40px] rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white text-sm transition active:scale-95"
                        title="Download PDF"
                    >
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
            </div>

            <!-- Middle: Active Reading Tracker & Timer Badge -->
            <div class="flex items-center gap-2 sm:gap-3 justify-between md:justify-center flex-wrap">
                <!-- Reading Timer Badge -->
                <div class="flex items-center gap-2 bg-slate-950 px-2.5 py-1.5 sm:px-3 sm:py-1.5 rounded-xl border border-slate-800 shadow-inner">
                    <div class="flex items-center gap-1.5">
                        <span
                            class="w-2 h-2 rounded-full"
                            :class="pointsClaimed ? 'bg-emerald-400' : (isTabActive ? 'bg-teal-400 animate-ping' : 'bg-amber-400')"
                        ></span>
                        <span class="text-[10px] sm:text-[11px] font-medium text-slate-400" x-text="isTabActive ? 'Active Reading' : 'Paused'"></span>
                    </div>

                    <div class="text-[11px] sm:text-xs font-mono font-bold text-teal-300">
                        <span x-text="formatTime(activeSeconds)">00:00</span>
                        <span class="text-slate-500 font-normal">/ 03:00</span>
                    </div>
                </div>

                <!-- Reward Status Chip -->
                <div>
                    <template x-if="pointsClaimed">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[10px] sm:text-xs font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-700/60 shadow-sm animate-pulse">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                            <span>Points Awarded (+5 XP / +2 TC)</span>
                        </span>
                    </template>

                    <template x-if="!pointsClaimed">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[10px] sm:text-[11px] font-semibold bg-amber-950/60 text-amber-300 border border-amber-800/60">
                            <i class="fa-solid fa-coins text-amber-400"></i>
                            <span>Read 3 min for +5 XP / +2 TC</span>
                        </span>
                    </template>
                </div>
            </div>

            <!-- Desktop Right: Zoom, Page Controls & Actions -->
            <div class="hidden md:flex items-center gap-2 justify-end">
                <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 border border-slate-700 text-xs">
                    <button type="button" @click="zoomOut()" class="px-2 py-1 hover:bg-slate-700 rounded text-slate-300" title="Zoom Out">
                        <i class="fa-solid fa-minus text-[10px]"></i>
                    </button>
                    <span class="px-1 text-[11px] font-mono font-medium text-slate-300" x-text="Math.round(scale * 100) + '%'">100%</span>
                    <button type="button" @click="zoomIn()" class="px-2 py-1 hover:bg-slate-700 rounded text-slate-300" title="Zoom In">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                    </button>
                    <button type="button" @click="fitWidth()" class="px-2 py-1 hover:bg-slate-700 rounded text-slate-300" title="Fit to width">
                        <i class="fa-solid fa-arrows-left-right text-[10px]"></i>
                    </button>
                </div>

                <span class="text-xs text-slate-400 font-mono" x-show="totalPages > 0">
                    <span x-text="currentPage">1</span> / <span x-text="totalPages">1</span>
                </span>

                <a
                    :href="pdfUrl"
                    download
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white text-xs font-semibold transition"
                    title="Download PDF"
                >
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Download</span>
                </a>
            </div>
        </div>

        <!-- Real-time Progress Line -->
        <div class="w-full bg-slate-800 h-1 mt-2.5 rounded-full overflow-hidden">
            <div
                class="h-full transition-all duration-300 ease-out"
                :class="progressPercent >= 100 ? 'bg-emerald-400' : 'bg-gradient-to-r from-teal-500 to-emerald-400'"
                :style="`width: ${progressPercent}%`"
            ></div>
        </div>
    </header>

    <!-- Reading Container (Scrollable HTML5 Canvas) -->
    <main class="flex-1 w-full max-w-5xl mx-auto p-3 sm:p-6 flex flex-col items-center pb-24 md:pb-8">
        <!-- Loading State -->
        <div x-show="isLoading" class="flex flex-col items-center justify-center py-20 text-center">
            <i class="fa-solid fa-circle-notch fa-spin text-4xl text-teal-400 mb-3"></i>
            <p class="text-sm font-semibold text-slate-200" x-text="loadingStatus">Loading document...</p>
            <p class="text-xs text-slate-400 mt-1">Rendering high-resolution pages directly on canvas</p>
        </div>

        <!-- Error State -->
        <div x-show="errorMessage" x-cloak class="p-6 max-w-md text-center bg-rose-950/60 border border-rose-800 rounded-2xl my-12">
            <i class="fa-solid fa-triangle-exclamation text-3xl text-rose-400 mb-2"></i>
            <p class="text-sm font-bold text-rose-200" x-text="errorMessage"></p>
            <a :href="pdfUrl" download class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-700 text-white text-xs font-semibold hover:bg-rose-600 transition">
                <i class="fa-solid fa-download"></i> Download File Instead
            </a>
        </div>

        <!-- Rendered Canvas Container -->
        <div
            id="pdf-container"
            x-ref="pdfContainer"
            class="w-full flex flex-col items-center gap-4 sm:gap-6"
            x-show="!isLoading && !errorMessage"
        >
            <!-- Canvas pages inserted dynamically by Alpine PDF.js engine -->
        </div>
    </main>

    <!-- Mobile Floating Bottom Control Bar -->
    <div class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 md:hidden bg-slate-900/90 backdrop-blur-md border border-slate-700/80 px-4 py-2 rounded-full shadow-2xl flex items-center gap-3 text-xs safe-bottom">
        <button type="button" @click="zoomOut()" class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-800 text-slate-200 hover:text-white active:scale-95" title="Zoom Out">
            <i class="fa-solid fa-minus text-xs"></i>
        </button>
        <button type="button" @click="fitWidth()" class="px-3 py-1.5 rounded-full bg-slate-800 text-slate-200 hover:text-white font-mono text-[11px] active:scale-95" title="Fit width">
            <span x-text="Math.round(scale * 100) + '%'">100%</span>
        </button>
        <button type="button" @click="zoomIn()" class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-800 text-slate-200 hover:text-white active:scale-95" title="Zoom In">
            <i class="fa-solid fa-plus text-xs"></i>
        </button>
    </div>

    <!-- Floating Gamification Toast Alert -->
    <div
        x-show="rewardMessage"
        x-cloak
        x-transition
        class="fixed bottom-20 md:bottom-6 right-4 sm:right-6 z-50 p-4 rounded-2xl bg-emerald-900/95 border border-emerald-600 shadow-2xl text-emerald-100 flex items-center gap-3 backdrop-blur-md max-w-sm sm:max-w-md safe-bottom"
    >
        <div class="w-10 h-10 rounded-xl bg-emerald-800 flex items-center justify-center text-emerald-300 flex-shrink-0">
            <i class="fa-solid fa-trophy text-lg"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-white uppercase tracking-wider">Reading Goal Completed!</p>
            <p class="text-xs text-emerald-200 mt-0.5" x-text="rewardMessage"></p>
        </div>
        <button type="button" @click="rewardMessage = ''" class="text-emerald-400 hover:text-white p-1">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<script>
    function pdfMaterialTracker(config) {
        return {
            pdfUrl: config.pdfUrl,
            pointsClaimed: config.pointsClaimed || false,
            targetSeconds: config.targetSeconds || 180,
            activeSeconds: 0,
            progressPercent: 0,
            isTabActive: true,
            timer: null,
            pdfDoc: null,
            blobUrl: null,
            currentPage: 1,
            totalPages: 0,
            scale: 1.25,
            isLoading: true,
            loadingStatus: 'Initializing PDF engine...',
            errorMessage: '',
            rewardMessage: '',
            pageObserver: null,

            init() {
                if (!this.pdfUrl) {
                    this.errorMessage = 'No PDF file URL found for this material.';
                    this.isLoading = false;
                    return;
                }

                // If already claimed, set progress to 100%
                if (this.pointsClaimed) {
                    this.activeSeconds = this.targetSeconds;
                    this.progressPercent = 100;
                }

                this.setupTabVisibilityListeners();
                this.loadPdfEngine();
                this.startReadingTimer();
            },

            setupTabVisibilityListeners() {
                // Pause timer immediately if student switches tab, minimizes, or window loses focus
                document.addEventListener('visibilitychange', () => {
                    this.isTabActive = !document.hidden;
                });

                window.addEventListener('blur', () => {
                    this.isTabActive = false;
                });

                window.addEventListener('focus', () => {
                    this.isTabActive = !document.hidden;
                });
            },

            startReadingTimer() {
                if (this.timer) return;

                this.timer = setInterval(() => {
                    // Only count active reading time when tab is focused and visible
                    if (!this.pointsClaimed && this.isTabActive && !document.hidden) {
                        this.activeSeconds += 1;
                        this.progressPercent = Math.min(100, Math.round((this.activeSeconds / this.targetSeconds) * 100));

                        // Check 3-minute milestone (180 seconds)
                        if (this.activeSeconds >= this.targetSeconds) {
                            this.claimReadingPoints();
                        }
                    }
                }, 1000);
            },

            async claimReadingPoints() {
                if (this.pointsClaimed) return;

                this.pointsClaimed = true;
                this.progressPercent = 100;

                try {
                    const result = await this.$wire.awardReadingPoints({
                        activeSeconds: this.activeSeconds
                    });

                    if (result && result.status === 'success') {
                        this.rewardMessage = result.message || '+5 XP and +2 Thinker Coins awarded for active reading!';
                    } else if (result && result.status === 'already_claimed') {
                        this.rewardMessage = 'Points already claimed for this material.';
                    } else if (result && result.message) {
                        this.rewardMessage = result.message;
                    }
                } catch (err) {
                    console.error('Error claiming reading points:', err);
                }
            },

            loadPdfEngine() {
                const startRender = () => {
                    if (typeof window.pdfjsLib !== 'undefined') {
                        try {
                            window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                        } catch (e) {
                            // Ignore worker setting error
                        }
                        this.renderPdf();
                    } else {
                        this.renderFallbackEmbed('PDF.js library could not be loaded.');
                    }
                };

                if (typeof window.pdfjsLib !== 'undefined') {
                    startRender();
                    return;
                }

                let attempts = 0;
                const checkPdfjs = setInterval(() => {
                    attempts++;
                    if (typeof window.pdfjsLib !== 'undefined') {
                        clearInterval(checkPdfjs);
                        startRender();
                    } else if (attempts > 40) {
                        clearInterval(checkPdfjs);
                        this.renderFallbackEmbed('PDF engine loading timed out.');
                    }
                }, 100);
            },

            async renderPdf() {
                try {
                    this.loadingStatus = 'Fetching PDF document...';

                    // Fetch file with same-origin credentials
                    const response = await fetch(this.pdfUrl, {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/pdf,application/octet-stream,*/*',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`Server returned HTTP ${response.status}`);
                    }

                    const arrayBuffer = await response.arrayBuffer();

                    // Generate local object Blob URL for fallback/download
                    try {
                        const blob = new Blob([arrayBuffer], { type: 'application/pdf' });
                        this.blobUrl = URL.createObjectURL(blob);
                    } catch (e) {}

                    this.loadingStatus = 'Rendering PDF pages...';
                    const loadingTask = window.pdfjsLib.getDocument({
                        data: new Uint8Array(arrayBuffer),
                        cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                        cMapPacked: true
                    });

                    this.pdfDoc = await loadingTask.promise;
                    this.totalPages = this.pdfDoc.numPages;
                    this.isLoading = false;
                    this.errorMessage = '';

                    const container = this.$refs.pdfContainer;
                    if (!container) return;
                    container.innerHTML = '';

                    // Calculate initial fit-to-screen scale
                    const containerWidth = Math.min(container.clientWidth || 800, window.innerWidth - 32);
                    const firstPage = await this.pdfDoc.getPage(1);
                    const unscaledViewport = firstPage.getViewport({ scale: 1.0 });
                    if (containerWidth < unscaledViewport.width * 1.25) {
                        this.scale = Math.max(0.6, (containerWidth / unscaledViewport.width) * 0.95);
                    }

                    // Render all pages sequentially
                    for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                        await this.renderPage(pageNum);
                    }

                    this.setupPageObserver();
                } catch (error) {
                    console.warn('PDF.js rendering failed, switching to native embed:', error);
                    await this.renderFallbackEmbed();
                }
            },

            async renderFallbackEmbed(customMessage = null) {
                this.isLoading = false;
                this.errorMessage = '';
                const container = this.$refs.pdfContainer;
                if (!container) return;

                let targetUrl = this.blobUrl;
                if (!targetUrl) {
                    try {
                        const resp = await fetch(this.pdfUrl, { credentials: 'same-origin' });
                        if (resp.ok) {
                            const blob = await resp.blob();
                            this.blobUrl = URL.createObjectURL(blob);
                            targetUrl = this.blobUrl;
                        }
                    } catch (e) {
                        targetUrl = this.pdfUrl;
                    }
                }
                targetUrl = targetUrl || this.pdfUrl;

                container.innerHTML = `
                    <div class="w-full flex flex-col items-center space-y-4">
                        <div class="w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-800 bg-slate-900 min-h-[78vh]">
                            <iframe src="${targetUrl}#toolbar=1" class="w-full h-[78vh] border-0 rounded-2xl bg-white" title="Document Viewer">
                                <div class="p-8 text-center text-slate-400">
                                    <p class="mb-4">Your browser does not support inline PDF viewing.</p>
                                    <a href="${targetUrl}" download class="px-4 py-2 rounded-xl bg-teal-600 text-white font-bold">
                                        Download Document
                                    </a>
                                </div>
                            </iframe>
                        </div>
                    </div>
                `;
            },

            async renderPage(pageNum) {
                if (!this.pdfDoc) return;

                try {
                    const page = await this.pdfDoc.getPage(pageNum);
                    const outputScale = window.devicePixelRatio || 1;

                    // Compute standard viewport for CSS size and scaled viewport for canvas pixels
                    const viewport = page.getViewport({ scale: this.scale });
                    const scaledViewport = page.getViewport({ scale: this.scale * outputScale });

                    // Page Wrapper
                    const pageWrapper = document.createElement('div');
                    pageWrapper.className = 'relative bg-white rounded-xl shadow-2xl overflow-hidden mb-6 border border-slate-800 flex justify-center pdf-page-container';
                    pageWrapper.id = 'pdf-page-' + pageNum;
                    pageWrapper.setAttribute('data-page-number', pageNum);

                    const canvas = document.createElement('canvas');
                    canvas.className = 'block';
                    const context = canvas.getContext('2d');

                    canvas.width = Math.floor(scaledViewport.width);
                    canvas.height = Math.floor(scaledViewport.height);
                    canvas.style.width = Math.floor(viewport.width) + 'px';
                    canvas.style.height = Math.floor(viewport.height) + 'px';
                    canvas.style.maxWidth = '100%';

                    const renderContext = {
                        canvasContext: context,
                        viewport: scaledViewport
                    };

                    pageWrapper.appendChild(canvas);
                    this.$refs.pdfContainer.appendChild(pageWrapper);

                    await page.render(renderContext).promise;
                } catch (err) {
                    console.error(`Failed to render page ${pageNum}:`, err);
                }
            },

            setupPageObserver() {
                if (this.pageObserver) {
                    this.pageObserver.disconnect();
                }

                this.pageObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const pageNum = parseInt(entry.target.getAttribute('data-page-number'));
                            if (pageNum) {
                                this.currentPage = pageNum;
                            }
                        }
                    });
                }, {
                    root: null,
                    threshold: 0.4
                });

                document.querySelectorAll('.pdf-page-container').forEach((el) => {
                    this.pageObserver.observe(el);
                });
            },

            async zoomIn() {
                this.scale = Math.min(3.0, this.scale + 0.2);
                await this.reRenderAllPages();
            },

            async zoomOut() {
                this.scale = Math.max(0.6, this.scale - 0.2);
                await this.reRenderAllPages();
            },

            async fitWidth() {
                const containerWidth = Math.min(this.$refs.pdfContainer.clientWidth || 800, window.innerWidth - 32);
                if (this.pdfDoc) {
                    const firstPage = await this.pdfDoc.getPage(1);
                    const unscaled = firstPage.getViewport({ scale: 1.0 });
                    this.scale = Math.max(0.6, (containerWidth / unscaled.width) * 0.95);
                    await this.reRenderAllPages();
                }
            },

            async reRenderAllPages() {
                if (!this.pdfDoc) return;
                const container = this.$refs.pdfContainer;
                if (!container) return;
                container.innerHTML = '';
                for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                    await this.renderPage(pageNum);
                }
                this.setupPageObserver();
            },

            formatTime(seconds) {
                if (!seconds || isNaN(seconds)) return '00:00';
                const total = Math.floor(seconds);
                const mins = Math.floor(total / 60);
                const secs = total % 60;
                return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            }
        };
    }
</script>
