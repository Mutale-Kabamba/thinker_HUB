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
    <header class="sticky top-0 z-40 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-3 py-2 sm:px-6 shadow-lg">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-2 sm:gap-3">
            <!-- Top/Left: Back Navigation & Material Meta & Mobile Controls -->
            <div class="flex items-center justify-between gap-2 min-w-0">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <a
                        href="{{ url()->previous() ?: route('filament.student.pages.materials') }}"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition shadow-sm flex-shrink-0"
                        title="Back to Materials"
                    >
                        <i class="fa-solid fa-arrow-left text-sm"></i>
                    </a>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h1 class="text-xs sm:text-base font-bold text-white truncate max-w-[200px] sm:max-w-md" title="{{ $material->title }}">
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
                        <span x-text="currentPage">1</span> / <span x-text="totalPages">1</span>
                    </span>
                    <a
                        :href="pdfUrl"
                        download
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white text-xs transition"
                        title="Download PDF"
                    >
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
            </div>

            <!-- Middle: Active Reading Tracker & Timer Badge -->
            <div class="flex items-center gap-2 sm:gap-3 justify-between md:justify-center flex-wrap">
                <!-- Reading Timer Badge -->
                <div class="flex items-center gap-2 bg-slate-950 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-xl border border-slate-800 shadow-inner">
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
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-xl text-[10px] sm:text-xs font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-700/60 shadow-sm animate-pulse">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                            <span>Points Awarded (+5 XP / +2 TC)</span>
                        </span>
                    </template>

                    <template x-if="!pointsClaimed">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 sm:px-2.5 sm:py-1.5 rounded-xl text-[10px] sm:text-[11px] font-semibold bg-amber-950/60 text-amber-300 border border-amber-800/60">
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
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white text-xs font-semibold transition"
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
    <main class="flex-1 w-full max-w-5xl mx-auto p-4 sm:p-6 flex flex-col items-center">
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
            <a :href="pdfUrl" download class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-700 text-white text-xs font-semibold hover:bg-rose-600 transition">
                <i class="fa-solid fa-download"></i> Download File Instead
            </a>
        </div>

        <!-- Rendered Canvas Container -->
        <div
            id="pdf-container"
            x-ref="pdfContainer"
            class="w-full flex flex-col items-center gap-6"
            x-show="!isLoading && !errorMessage"
        >
            <!-- Canvas pages inserted dynamically by Alpine PDF.js engine -->
        </div>
    </main>

    <!-- Floating Gamification Toast Alert -->
    <div
        x-show="rewardMessage"
        x-cloak
        x-transition
        class="fixed bottom-6 right-6 z-50 p-4 rounded-2xl bg-emerald-900/95 border border-emerald-600 shadow-2xl text-emerald-100 flex items-center gap-3 backdrop-blur-md max-w-md"
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
            currentPage: 1,
            totalPages: 0,
            scale: 1.25,
            isLoading: true,
            loadingStatus: 'Initializing PDF engine...',
            errorMessage: '',
            rewardMessage: '',

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
                if (typeof window.pdfjsLib !== 'undefined') {
                    this.renderPdf();
                    return;
                }

                let attempts = 0;
                const checkPdfjs = setInterval(() => {
                    attempts++;
                    if (typeof window.pdfjsLib !== 'undefined') {
                        clearInterval(checkPdfjs);
                        this.renderPdf();
                    } else if (attempts > 50) {
                        clearInterval(checkPdfjs);
                        this.renderFallbackEmbed('PDF engine loading timed out. Switched to native browser viewer.');
                    }
                }, 100);
            },

            async renderPdf() {
                try {
                    this.loadingStatus = 'Fetching PDF document...';

                    // Configure worker with cross-origin blob fallback
                    try {
                        const workerBlob = new Blob(
                            [`importScripts('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js');`],
                            { type: 'application/javascript' }
                        );
                        window.pdfjsLib.GlobalWorkerOptions.workerSrc = URL.createObjectURL(workerBlob);
                    } catch (workerErr) {
                        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                    }

                    // Fetch as ArrayBuffer to bypass CORS/Range/Session cookie transport issues
                    const response = await fetch(this.pdfUrl, {
                        headers: {
                            'Accept': 'application/pdf,application/octet-stream,*/*',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`Server returned HTTP ${response.status}`);
                    }

                    const arrayBuffer = await response.arrayBuffer();

                    this.loadingStatus = 'Rendering PDF pages...';
                    const loadingTask = window.pdfjsLib.getDocument({
                        data: new Uint8Array(arrayBuffer),
                        cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                        cMapPacked: true,
                        isEvalSupported: false
                    });

                    this.pdfDoc = await loadingTask.promise;
                    this.totalPages = this.pdfDoc.numPages;
                    this.isLoading = false;
                    this.errorMessage = '';

                    const container = this.$refs.pdfContainer;
                    container.innerHTML = '';

                    // Calculate fit-to-screen scale on mobile
                    const containerWidth = Math.min(container.clientWidth || 800, window.innerWidth - 32);
                    const firstPage = await this.pdfDoc.getPage(1);
                    const unscaledViewport = firstPage.getViewport({ scale: 1.0 });
                    if (containerWidth < unscaledViewport.width * 1.2) {
                        this.scale = Math.max(0.6, (containerWidth / unscaledViewport.width) * 0.96);
                    }

                    // Render each page sequentially
                    for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                        await this.renderPage(pageNum);
                    }
                } catch (error) {
                    console.warn('PDF.js canvas rendering error, switching to native browser embed:', error);
                    this.renderFallbackEmbed();
                }
            },

            renderFallbackEmbed(customMessage = null) {
                this.isLoading = false;
                this.errorMessage = '';
                const container = this.$refs.pdfContainer;
                if (!container) return;

                container.innerHTML = `
                    <div class="w-full flex flex-col items-center space-y-4">
                        <div class="w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-800 bg-slate-900 min-h-[75vh]">
                            <object data="${this.pdfUrl}" type="application/pdf" class="w-full h-[75vh] block rounded-2xl">
                                <iframe src="${this.pdfUrl}" class="w-full h-[75vh] border-0 rounded-2xl">
                                    <div class="p-8 text-center text-slate-400">
                                        <p class="mb-4">Your browser does not support inline PDF viewing.</p>
                                        <a href="${this.pdfUrl}" download class="px-4 py-2 rounded-xl bg-teal-600 text-white font-bold">
                                            Download Document
                                        </a>
                                    </div>
                                </iframe>
                            </object>
                        </div>
                    </div>
                `;
            },

            async renderPage(pageNum) {
                if (!this.pdfDoc) return;

                const page = await this.pdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: this.scale });

                // Page Wrapper with shadow and page label
                const pageWrapper = document.createElement('div');
                pageWrapper.className = 'relative bg-white rounded-xl shadow-2xl overflow-hidden mb-6 border border-slate-800';
                pageWrapper.id = 'pdf-page-' + pageNum;

                const canvas = document.createElement('canvas');
                canvas.className = 'w-full h-auto block';
                const context = canvas.getContext('2d');

                // Retina Display scaling (crisp text)
                const outputScale = window.devicePixelRatio || 1;
                canvas.width = Math.floor(viewport.width * outputScale);
                canvas.height = Math.floor(viewport.height * outputScale);
                canvas.style.width = Math.floor(viewport.width) + 'px';
                canvas.style.maxHeight = 'none';

                const transform = outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null;

                const renderContext = {
                    canvasContext: context,
                    transform: transform,
                    viewport: viewport
                };

                pageWrapper.appendChild(canvas);
                this.$refs.pdfContainer.appendChild(pageWrapper);

                await page.render(renderContext).promise;
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
                    this.scale = (containerWidth / unscaled.width) * 0.96;
                    await this.reRenderAllPages();
                }
            },

            async reRenderAllPages() {
                if (!this.pdfDoc) return;
                const container = this.$refs.pdfContainer;
                container.innerHTML = '';
                for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                    await this.renderPage(pageNum);
                }
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
