<div
    class="w-full rounded-2xl bg-slate-900 border border-slate-800 p-4 sm:p-6 shadow-xl text-white"
    x-data="youtubeVideoTracker({
        videoId: @js($youtubeId),
        pointsClaimed: @js($pointsEarned),
        initialDuration: @js($lesson->duration_seconds ?? 0)
    })"
>
    <!-- Video Header & Gamification Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-play-circle text-teal-400"></i>
                <span>{{ $lesson->title }}</span>
            </h3>
            @if ($lesson->course)
                <p class="text-xs text-slate-400 mt-0.5">
                    Course: <span class="text-slate-300 font-medium">{{ $lesson->course->title }}</span>
                </p>
            @endif
        </div>

        <!-- Claim / Reward Badge -->
        <div class="flex items-center gap-2">
            <template x-if="pointsClaimed">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-sm animate-pulse">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    <span>Points Claimed (+10 XP / +5 TC)</span>
                </span>
            </template>

            <template x-if="!pointsClaimed">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                    <i class="fa-solid fa-coins text-amber-400"></i>
                    <span>Watch 85% for +10 XP / +5 TC</span>
                </span>
            </template>
        </div>
    </div>

    <!-- YouTube Player Embed Container (16:9 Responsive) -->
    <div class="relative w-full overflow-hidden rounded-xl bg-black aspect-video shadow-2xl border border-slate-800">
        @if ($youtubeId)
            <div x-ref="playerContainer" class="w-full h-full">
                <div id="youtube-player-{{ $lesson->id }}" x-ref="youtubePlayer" class="w-full h-full"></div>
            </div>

            <!-- Loading API Overlay -->
            <div
                x-show="isLoadingApi"
                class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/80 backdrop-blur-sm z-10"
            >
                <i class="fa-solid fa-spinner fa-spin text-3xl text-teal-400 mb-2"></i>
                <p class="text-xs text-slate-300">Loading secure video player...</p>
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-full p-6 text-center text-slate-400">
                <i class="fa-solid fa-video-slash text-4xl mb-2 opacity-50"></i>
                <p class="text-sm font-medium">No YouTube video URL provided for this lesson.</p>
            </div>
        @endif
    </div>

    <!-- Real-time Watch Progress Tracking -->
    @if ($youtubeId)
        <div class="mt-4 bg-slate-950/60 rounded-xl p-3.5 border border-slate-800/80">
            <div class="flex items-center justify-between text-xs mb-2">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-300">Watch Progress:</span>
                    <span class="font-bold text-teal-400" x-text="progressPercent + '%'">0%</span>
                    <span
                        class="text-[11px] px-1.5 py-0.5 rounded text-slate-400 bg-slate-800"
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
            <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden relative">
                <!-- 85% Target Line Marker -->
                <div
                    class="absolute top-0 bottom-0 w-0.5 bg-amber-400/70 z-10"
                    style="left: 85%;"
                    title="85% Reward Threshold"
                ></div>

                <!-- Active Watched Bar -->
                <div
                    class="h-full rounded-full transition-all duration-300 ease-out"
                    :class="progressPercent >= 85 ? 'bg-gradient-to-r from-teal-500 to-emerald-400' : 'bg-gradient-to-r from-teal-600 to-teal-400'"
                    :style="`width: ${Math.min(100, progressPercent)}%`"
                ></div>
            </div>

            <!-- Anti-Scrubbing Safeguard Info -->
            <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-400 flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-teal-400 text-xs"></i>
                    <span>Anti-Scrubbing Active: Progress ticks only while actively playing.</span>
                </div>

                <template x-if="pointsClaimed">
                    <div class="text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-award"></i>
                        <span>+10 XP & +5 TC Awarded to your profile</span>
                    </div>
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
        :class="claimSuccess ? 'bg-emerald-950/70 text-emerald-200 border border-emerald-800' : 'bg-amber-950/70 text-amber-200 border border-amber-800'"
    >
        <div class="flex items-center gap-2">
            <i :class="claimSuccess ? 'fa-solid fa-circle-check text-emerald-400' : 'fa-solid fa-triangle-exclamation text-amber-400'"></i>
            <span x-text="claimMessage"></span>
        </div>
        <button type="button" @click="claimMessage = ''" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<script>
    function youtubeVideoTracker(config) {
        return {
            videoId: config.videoId,
            pointsClaimed: config.pointsClaimed || false,
            actualSecondsWatched: 0,
            duration: config.initialDuration || 0,
            currentTime: 0,
            isPlaying: false,
            progressPercent: 0,
            isLoadingApi: true,
            claimMessage: '',
            claimSuccess: false,
            player: null,
            timer: null,

            init() {
                if (!this.videoId) {
                    this.isLoadingApi = false;
                    return;
                }

                this.loadYouTubeIframeAPI();
            },

            loadYouTubeIframeAPI() {
                if (window.YT && window.YT.Player) {
                    this.initPlayer();
                    return;
                }

                if (!window._ytIframeApiLoading) {
                    window._ytIframeApiLoading = true;
                    const tag = document.createElement('script');
                    tag.src = 'https://www.youtube.com/iframe_api';
                    const firstScriptTag = document.getElementsByTagName('script')[0];
                    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                }

                // Poll until window.YT.Player is available
                const checkYT = setInterval(() => {
                    if (window.YT && window.YT.Player) {
                        clearInterval(checkYT);
                        this.initPlayer();
                    }
                }, 100);
            },

            initPlayer() {
                try {
                    const playerContainerId = 'youtube-player-' + @js($lesson->id);
                    this.player = new window.YT.Player(playerContainerId, {
                        videoId: this.videoId,
                        playerVars: {
                            playsinline: 1,
                            rel: 0,
                            modestbranding: 1,
                            enablejsapi: 1,
                        },
                        events: {
                            onReady: (event) => {
                                this.isLoadingApi = false;
                                const dur = event.target.getDuration();
                                if (dur && dur > 0) {
                                    this.duration = Math.round(dur);
                                }
                            },
                            onStateChange: (event) => {
                                this.handleStateChange(event);
                            }
                        }
                    });
                } catch (err) {
                    console.error('Error initializing YouTube Player:', err);
                    this.isLoadingApi = false;
                }
            },

            handleStateChange(event) {
                // YT.PlayerState: PLAYING === 1, PAUSED === 2, ENDED === 0, BUFFERING === 3
                if (event.data === 1) { // PLAYING
                    this.isPlaying = true;
                    this.startWatchTimer();
                } else {
                    this.isPlaying = false;
                    this.stopWatchTimer();
                }
            },

            startWatchTimer() {
                if (this.timer) return;

                this.timer = setInterval(() => {
                    if (!this.isPlaying) return;

                    // Increment actual watched seconds
                    this.actualSecondsWatched += 1;

                    // Read player duration and current playback head
                    if (this.player && typeof this.player.getDuration === 'function') {
                        const dur = this.player.getDuration();
                        if (dur && dur > 0) this.duration = Math.round(dur);
                    }

                    if (this.player && typeof this.player.getCurrentTime === 'function') {
                        this.currentTime = this.player.getCurrentTime();
                    }

                    // Calculate progress percentage
                    if (this.duration > 0) {
                        this.progressPercent = Math.min(100, Math.round((this.actualSecondsWatched / this.duration) * 100));
                    }

                    // Anti-Scrubbing Threshold Check (85%)
                    if (!this.pointsClaimed && this.duration > 0) {
                        const threshold = this.duration * 0.85;
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
                        this.claimMessage = result.message || 'Points Claimed! +10 XP and +5 Thinker Coins awarded.';
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
