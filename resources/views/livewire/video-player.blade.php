<div
    class="w-full max-w-5xl mx-auto rounded-2xl bg-slate-900 border border-slate-800 p-4 sm:p-6 shadow-2xl text-white my-4"
    x-data="universalVideoTracker({
        youtubeId: @js($youtubeId),
        fileUrl: @js($fileUrl),
        pointsClaimed: @js($pointsEarned),
        initialDuration: @js($initialDuration),
        baseXp: @js($baseXp),
        baseCoins: @js($baseCoins)
    })"
>
    <!-- Video Header & Gamification Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="javascript:history.back()" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition" title="Back">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></svg>
                    <span>{{ $title }}</span>
                </h3>
            </div>
            @if ($courseTitle)
                <p class="text-xs text-slate-400 mt-1 ml-10">
                    Course: <span class="text-slate-300 font-medium">{{ $courseTitle }}</span>
                </p>
            @endif
        </div>

        <!-- Claim / Reward Badge -->
        <div class="flex items-center gap-2">
            <template x-if="pointsClaimed">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-sm animate-pulse">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>Points Claimed (+{{ $baseXp }} XP / +{{ $baseCoins }} TC)</span>
                </span>
            </template>

            <template x-if="!pointsClaimed">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/></svg>
                    <span>Watch 85% for +{{ $baseXp }} XP / +{{ $baseCoins }} TC</span>
                </span>
            </template>
        </div>
    </div>

    <!-- Video Embed Container (16:9 Responsive) -->
    <div class="relative w-full overflow-hidden rounded-xl bg-black aspect-video shadow-2xl border border-slate-800">
        @if ($youtubeId)
            <iframe
                id="youtube-iframe-{{ md5($youtubeId . ($title ?? '')) }}"
                x-ref="youtubeIframe"
                src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&enablejsapi=1&rel=0"
                class="w-full h-full border-0 block"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                @load="onIframeLoaded()"
            ></iframe>
        @elseif ($fileUrl)
            <video
                x-ref="videoElement"
                src="{{ $fileUrl }}"
                controls
                autoplay
                class="w-full h-full object-contain bg-black"
                @play="handleFilePlay()"
                @pause="handleFilePause()"
                @ended="handleFileEnded()"
                @timeupdate="handleFileTimeUpdate()"
                @loadedmetadata="handleFileMetadata()"
            ></video>
        @else
            <div class="flex flex-col items-center justify-center h-full p-6 text-center text-slate-400">
                <svg class="w-12 h-12 text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <p class="text-sm font-medium">No video source provided.</p>
            </div>
        @endif
    </div>

    <!-- Real-time Watch Progress Tracking -->
    @if ($youtubeId || $fileUrl)
        <div class="mt-4 bg-slate-950/60 rounded-xl p-4 border border-slate-800/80">
            <div class="flex items-center justify-between text-xs mb-2">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-300">Watch Progress:</span>
                    <span class="font-bold text-teal-400" x-text="progressPercent + '%'">0%</span>
                    <span
                        class="text-[11px] px-2 py-0.5 rounded text-slate-300 font-medium"
                        :class="isPlaying ? 'bg-teal-900/60 text-teal-300 border border-teal-700/50' : 'bg-slate-800 text-slate-400'"
                        x-text="isPlaying ? '▶ Actively Playing' : '⏸ Paused'"
                    ></span>
                </div>

                <div class="text-slate-400 text-[11px] font-mono">
                    <span x-text="formatTime(actualSecondsWatched)">00:00</span> /
                    <span x-text="formatTime(duration)">00:00</span>
                    <span class="text-slate-500">(Target: 85%)</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-slate-700 rounded-full h-2.5 overflow-hidden relative">
                <!-- 85% Target Line Marker -->
                <div
                    class="absolute top-0 bottom-0 w-0.5 bg-amber-400 z-10"
                    style="left: 85%;"
                    title="85% Reward Threshold"
                ></div>

                <!-- Active Watched Bar -->
                <div
                    class="h-full rounded-full transition-all duration-300 ease-out"
                    :class="progressPercent >= 85 ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-cyan-500 to-blue-500'"
                    :style="'width: ' + Math.min(100, progressPercent) + '%;'"
                ></div>
            </div>

            <!-- Anti-Scrubbing Safeguard Info -->
            <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-400 flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Anti-Scrubbing Active: Progress counts only while video is actively playing.</span>
                </div>

                <template x-if="pointsClaimed">
                    <div class="text-emerald-400 font-semibold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span>+{{ $baseXp }} XP & +{{ $baseCoins }} TC Awarded to profile</span>
                    </div>
                </template>
                <template x-if="!pointsClaimed && progressPercent >= 85">
                    <button
                        type="button"
                        @click="claimPoints()"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold px-3 py-1 rounded-md transition"
                    >Claim +{{ $baseXp }} XP / +{{ $baseCoins }} TC Now</button>
                </template>
            </div>
        </div>
    @endif

    <!-- Alert / Claim Feedback -->
    <div
        x-show="claimMessage"
        x-cloak
        x-transition
        class="mt-3 p-3 rounded-xl text-xs flex items-center justify-between"
        :class="claimSuccess ? 'bg-emerald-950/80 text-emerald-200 border border-emerald-700' : 'bg-amber-950/80 text-amber-200 border border-amber-700'"
    >
        <div class="flex items-center gap-2">
            <span x-text="claimMessage"></span>
        </div>
        <button type="button" @click="claimMessage = ''" class="text-slate-400 hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>

<script>
    function universalVideoTracker(config) {
        return {
            youtubeId: config.youtubeId,
            fileUrl: config.fileUrl,
            pointsClaimed: config.pointsClaimed || false,
            actualSecondsWatched: 0,
            duration: config.initialDuration || 0,
            currentTime: 0,
            isPlaying: false,
            progressPercent: 0,
            claimMessage: '',
            claimSuccess: false,
            player: null,
            timer: null,
            pingTimer: null,
            baseXp: config.baseXp || 10,
            baseCoins: config.baseCoins || 3,
            messageHandler: null,

            init() {
                if (this.youtubeId) {
                    this.setupYouTubePostMessage();
                    this.setupYouTubeApi();
                }
            },

            destroy() {
                this.stopWatchTimer();
                if (this.pingTimer) clearInterval(this.pingTimer);
                if (this.messageHandler) {
                    window.removeEventListener('message', this.messageHandler);
                }
            },

            setupYouTubePostMessage() {
                this.messageHandler = (event) => {
                    try {
                        const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
                        if (!data) return;

                        // Handle onStateChange events
                        if (data.event === 'onStateChange' || (data.info && data.info.playerState !== undefined)) {
                            const state = data.info?.playerState !== undefined ? data.info.playerState : data.info;
                            if (state === 1) { // PLAYING
                                this.isPlaying = true;
                                this.startWatchTimer();
                            } else if (state === 2 || state === 0) { // PAUSED or ENDED
                                this.isPlaying = false;
                                this.stopWatchTimer();
                            }
                        }

                        // Handle infoDelivery events
                        if (data.event === 'infoDelivery' && data.info) {
                            if (data.info.duration && data.info.duration > 0) {
                                this.duration = Math.round(data.info.duration);
                            }
                            if (data.info.currentTime !== undefined) {
                                this.currentTime = data.info.currentTime;
                            }
                            if (this.duration > 0) {
                                this.progressPercent = Math.min(100, Math.round((this.actualSecondsWatched / this.duration) * 100));
                            }
                            if (!this.pointsClaimed && this.duration > 0) {
                                const threshold = this.duration * 0.80;
                                if (this.actualSecondsWatched >= threshold && this.currentTime >= threshold) {
                                    this.claimPoints();
                                }
                            }
                        }
                    } catch (err) {}
                };

                window.addEventListener('message', this.messageHandler);
            },

            onIframeLoaded() {
                const iframe = this.$refs.youtubeIframe;
                if (iframe && iframe.contentWindow) {
                    try {
                        iframe.contentWindow.postMessage(JSON.stringify({ event: 'listening' }), '*');
                        iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'addEventListener', args: ['onStateChange'] }), '*');
                    } catch(e) {}
                }

                if (this.pingTimer) clearInterval(this.pingTimer);
                this.pingTimer = setInterval(() => {
                    const frame = this.$refs.youtubeIframe;
                    if (frame && frame.contentWindow) {
                        try {
                            frame.contentWindow.postMessage(JSON.stringify({ event: 'listening' }), '*');
                        } catch(e) {}
                    }
                }, 2000);
            },

            setupYouTubeApi() {
                if (window.YT && window.YT.Player) {
                    this.initPlayer();
                    return;
                }

                if (!window._ytIframeApiLoading) {
                    window._ytIframeApiLoading = true;
                    const tag = document.createElement('script');
                    tag.src = 'https://www.youtube.com/iframe_api';
                    const firstScript = document.getElementsByTagName('script')[0];
                    if (firstScript && firstScript.parentNode) {
                        firstScript.parentNode.insertBefore(tag, firstScript);
                    } else {
                        document.head.appendChild(tag);
                    }
                }

                const checkYT = setInterval(() => {
                    if (window.YT && window.YT.Player) {
                        clearInterval(checkYT);
                        this.initPlayer();
                    }
                }, 200);

                setTimeout(() => clearInterval(checkYT), 6000);
            },

            initPlayer() {
                try {
                    const iframe = this.$refs.youtubeIframe;
                    if (!iframe) return;

                    this.player = new window.YT.Player(iframe, {
                        events: {
                            onReady: (event) => {
                                const dur = event.target.getDuration();
                                if (dur && dur > 0) this.duration = Math.round(dur);
                            },
                            onStateChange: (event) => {
                                if (event.data === 1) { // PLAYING
                                    this.isPlaying = true;
                                    this.startWatchTimer();
                                } else {
                                    this.isPlaying = false;
                                    this.stopWatchTimer();
                                }
                            }
                        }
                    });
                } catch (err) {}
            },

            startWatchTimer() {
                if (this.timer) return;

                this.timer = setInterval(() => {
                    if (!this.isPlaying) return;

                    this.actualSecondsWatched += 1;

                    if (this.player && typeof this.player.getDuration === 'function') {
                        const dur = this.player.getDuration();
                        if (dur && dur > 0) this.duration = Math.round(dur);
                    }

                    if (this.player && typeof this.player.getCurrentTime === 'function') {
                        this.currentTime = this.player.getCurrentTime();
                    }

                    if (this.duration > 0) {
                        this.progressPercent = Math.min(100, Math.round((this.actualSecondsWatched / this.duration) * 100));
                    }

                    if (!this.pointsClaimed && this.duration > 0) {
                        const threshold = this.duration * 0.80;
                        if (this.actualSecondsWatched >= threshold && this.currentTime >= threshold) {
                            this.claimPoints();
                        }
                    }
                }, 1000);
            },

            stopWatchTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },

            handleFilePlay() {
                this.isPlaying = true;
                this.startFileTimer();
            },

            handleFilePause() {
                this.isPlaying = false;
                this.stopFileTimer();
            },

            handleFileEnded() {
                this.isPlaying = false;
                this.stopFileTimer();
            },

            handleFileMetadata() {
                const el = this.$refs.videoElement;
                if (el && el.duration) {
                    this.duration = Math.round(el.duration);
                }
            },

            handleFileTimeUpdate() {
                const el = this.$refs.videoElement;
                if (el) {
                    this.currentTime = el.currentTime;
                    if (!this.duration && el.duration) {
                        this.duration = Math.round(el.duration);
                    }
                }
            },

            startFileTimer() {
                if (this.timer) return;
                this.timer = setInterval(() => {
                    if (!this.isPlaying) return;
                    this.actualSecondsWatched += 1;
                    const el = this.$refs.videoElement;
                    if (el) {
                        this.currentTime = el.currentTime;
                        if (el.duration && (!this.duration || this.duration <= 0)) {
                            this.duration = Math.round(el.duration);
                        }
                    }
                    if (this.duration > 0) {
                        this.progressPercent = Math.min(100, Math.round((this.actualSecondsWatched / this.duration) * 100));
                    }
                    if (!this.pointsClaimed && this.duration > 0) {
                        const threshold = this.duration * 0.80;
                        if (this.actualSecondsWatched >= threshold && this.currentTime >= threshold) {
                            this.claimPoints();
                        }
                    }
                }, 1000);
            },

            stopFileTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },

            async claimPoints() {
                if (this.pointsClaimed) return;
                this.pointsClaimed = true;

                try {
                    const result = await this.$wire.awardVideoCompletionPoints({
                        actualSecondsWatched: this.actualSecondsWatched,
                        duration: this.duration,
                        currentTime: this.currentTime,
                    });

                    if (result && result.status === 'success') {
                        this.claimSuccess = true;
                        this.claimMessage = result.message || `Points Claimed! +${this.baseXp} XP and +${this.baseCoins} Thinker Coins awarded.`;
                    } else if (result && result.status === 'already_claimed') {
                        this.claimSuccess = true;
                        this.claimMessage = 'Points already claimed for this video.';
                    } else if (result && result.message) {
                        this.claimSuccess = false;
                        this.claimMessage = result.message;
                    }
                } catch (error) {
                    console.error('Error claiming video completion points:', error);
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
