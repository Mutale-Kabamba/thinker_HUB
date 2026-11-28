@php
    $videoUrl = $activeLesson->video_url;
    $parsedVideo = \App\Support\VideoEmbedParser::parse($videoUrl);
    $youtubeId = $parsedVideo['youtube_id'];
    $embedUrl = $parsedVideo['autoplay_embed_url'] ?? $parsedVideo['embed_url'];
    $isDirectFile = $parsedVideo['is_direct_file'];
    $minPct = $activeLesson->min_watch_percentage ?: 85;
    $resumeTime = $progressMap[$activeLesson->id]['last_watched_second'] ?? 0;
@endphp

<div class="w-full bg-black">
    @if($youtubeId || ($embedUrl && !$isDirectFile))
        <div x-ref="embedStage" class="w-full bg-black">
            <div class="w-full aspect-video max-h-[75vh] bg-black mx-auto">
                <iframe
                    class="w-full h-full"
                    src="{{ $embedUrl ?: ($youtubeId ? 'https://www.youtube.com/embed/' . $youtubeId . '?autoplay=1&enablejsapi=1&rel=0' : '') }}"
                    title="{{ $activeLesson->title }}"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                    allowfullscreen
                ></iframe>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 bg-[#0b1c22] px-4 py-2 text-xs text-slate-300 border-t border-slate-800">
                <div class="flex items-center gap-3">
                    <span>Watch at least <strong class="text-white">{{ $minPct }}%</strong> to unlock the next lesson.</span>
                    <button
                        type="button"
                        @click="activeTab = 'notes'"
                        class="hidden sm:inline-flex items-center gap-1 rounded border border-slate-700 bg-slate-800/70 px-2.5 py-1 text-[11px] font-medium text-slate-200 hover:bg-slate-700 transition"
                    >
                        <span>Quick Note</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="sidebarOpen = !sidebarOpen"
                        title="Toggle Expanded Theater View"
                        class="hidden lg:inline-flex items-center gap-1 rounded border border-slate-700 bg-slate-800/70 px-2.5 py-1 text-[11px] font-medium text-slate-200 hover:bg-slate-700 transition"
                        x-text="sidebarOpen ? 'Expand View' : 'Show Sidebar'"
                    ></button>
                    <button
                        wire:click="handleVideoCompletion"
                        class="px-3.5 py-1.5 bg-teal-600 hover:bg-teal-500 font-semibold rounded-md text-white transition"
                    >
                        Complete &amp; Continue &rarr;
                    </button>
                </div>
            </div>
        </div>
    @else
        <div
            x-data="{
                playing: false,
                muted: false,
                volume: 1,
                currentTime: 0,
                duration: 0,
                playbackRate: 1,
                speedMenuOpen: false,
                settingsMenuOpen: false,
                autoplayNext: true,
                lastSaved: 0,
                hasTriggered: false,
                lowBandwidth: false,
                hdSrc: @js($videoUrl ?: ''),
                sdSrc: @js($activeLesson->low_bandwidth_video_url ?: ''),
                speeds: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2],
                formatClock(sec) {
                    const s = Math.max(0, Math.floor(sec || 0));
                    const m = Math.floor(s / 60);
                    const r = s % 60;
                    return m + ':' + (r < 10 ? '0' : '') + r;
                },
                progressPercent() {
                    return this.duration > 0 ? Math.min(100, (this.currentTime / this.duration) * 100) : 0;
                },
                togglePlay() {
                    const v = this.$refs.videoElement;
                    if (!v) return;
                    if (v.paused) {
                        const p = v.play();
                        if (p !== undefined) p.catch(() => {});
                    } else {
                        v.pause();
                    }
                },
                seekBy(delta) {
                    const v = this.$refs.videoElement;
                    if (!v) return;
                    v.currentTime = Math.max(0, Math.min(v.duration || 0, v.currentTime + delta));
                },
                scrubTo(event) {
                    const v = this.$refs.videoElement;
                    if (!v || !this.duration) return;
                    const rect = event.currentTarget.getBoundingClientRect();
                    const ratio = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
                    v.currentTime = ratio * this.duration;
                },
                setSpeed(rate) {
                    const v = this.$refs.videoElement;
                    this.playbackRate = rate;
                    if (v) v.playbackRate = rate;
                    this.speedMenuOpen = false;
                },
                toggleMute() {
                    const v = this.$refs.videoElement;
                    if (!v) return;
                    v.muted = !v.muted;
                    this.muted = v.muted;
                },
                setVolume(val) {
                    const v = this.$refs.videoElement;
                    this.volume = parseFloat(val);
                    if (v) {
                        v.volume = this.volume;
                        v.muted = this.volume === 0;
                        this.muted = v.muted;
                    }
                },
                toggleFullscreen() {
                    const box = this.$refs.videoStage;
                    if (!box) return;
                    if (!document.fullscreenElement) {
                        box.requestFullscreen?.().catch(() => {});
                    } else {
                        document.exitFullscreen?.().catch(() => {});
                    }
                },
                addTimestampNote() {
                    const stamp = '[' + this.formatClock(this.currentTime) + '] ';
                    activeTab = 'notes';
                    if (!lessonNote.includes(stamp)) {
                        lessonNote = (lessonNote ? lessonNote + '\n' : '') + stamp;
                    }
                },
                toggleBandwidth() {
                    if (!this.sdSrc) return;
                    const v = this.$refs.videoElement;
                    const pos = v ? v.currentTime : 0;
                    this.lowBandwidth = !this.lowBandwidth;
                    this.$nextTick(() => {
                        if (v) {
                            v.src = this.lowBandwidth ? this.sdSrc : this.hdSrc;
                            v.currentTime = pos;
                            v.playbackRate = this.playbackRate;
                            const p = v.play();
                            if (p !== undefined) p.catch(() => {});
                        }
                    });
                },
                init() {
                    const v = this.$refs.videoElement;
                    if (!v) return;
                    const resumeTime = {{ $resumeTime }};
                    if (resumeTime > 0) v.currentTime = resumeTime;

                    v.addEventListener('play', () => { this.playing = true; });
                    v.addEventListener('pause', () => { this.playing = false; });
                    v.addEventListener('loadedmetadata', () => { this.duration = v.duration || 0; });
                    v.addEventListener('timeupdate', () => {
                        this.currentTime = v.currentTime || 0;
                        if (!this.duration && v.duration) this.duration = v.duration;
                        if (Math.abs(v.currentTime - this.lastSaved) > 5) {
                            this.lastSaved = v.currentTime;
                            $wire.syncVideoTime(Math.floor(v.currentTime));
                        }
                        if (v.duration > 0 && ((v.currentTime / v.duration) * 100) >= {{ $minPct }} && !this.hasTriggered) {
                            this.hasTriggered = true;
                            $wire.handleVideoCompletion();
                        }
                    });
                    v.addEventListener('ended', () => {
                        this.playing = false;
                        if (!this.hasTriggered) {
                            this.hasTriggered = true;
                            $wire.handleVideoCompletion();
                        } else if (this.autoplayNext) {
                            $wire.goToAdjacentLesson('next');
                        }
                    });

                    this.$nextTick(() => {
                        const p = v.play();
                        if (p !== undefined) p.catch(() => {});
                    });
                }
            }"
            x-ref="videoStage"
            @keydown.window="
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes($event.target.tagName)) return;
                if ($event.code === 'Space') { $event.preventDefault(); togglePlay(); }
                if ($event.key === 'ArrowLeft') { seekBy(-5); }
                if ($event.key === 'ArrowRight') { seekBy(5); }
                if ($event.key === 'm' || $event.key === 'M') { toggleMute(); }
                if ($event.key === 'f' || $event.key === 'F') { toggleFullscreen(); }
            "
            class="relative w-full bg-black select-none group"
        >
            {{-- Video Viewport --}}
            <div class="relative w-full aspect-video max-h-[76vh] bg-black mx-auto flex items-center justify-center" @click="togglePlay()">
                <video
                    x-ref="videoElement"
                    autoplay
                    playsinline
                    preload="metadata"
                    controlsList="nodownload"
                    class="w-full h-full object-contain"
                    src="{{ $videoUrl ?: '' }}"
                ></video>

                {{-- Center Play Overlay When Paused --}}
                <button
                    type="button"
                    x-show="!playing"
                    x-transition.opacity
                    @click.stop="togglePlay()"
                    aria-label="Play video"
                    class="absolute flex h-16 w-16 items-center justify-center rounded-full bg-[#0A2D27]/90 text-white shadow-xl ring-2 ring-teal-400/50 hover:scale-105 transition"
                >
                    <svg class="w-7 h-7 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"></polygon></svg>
                </button>
            </div>

            {{-- Custom Bottom Control Deck --}}
            <div class="w-full bg-[#0b1c22]/95 border-t border-slate-800" @click.outside="speedMenuOpen = false; settingsMenuOpen = false">
                {{-- Full-Width Interactive Progress Scrub Bar --}}
                <div
                    @click="scrubTo($event)"
                    class="relative h-1.5 hover:h-2 w-full cursor-pointer bg-slate-700/80 transition-all"
                    title="Seek"
                >
                    <div class="h-full bg-teal-400 transition-all duration-100" :style="'width: ' + progressPercent() + '%'"></div>
                </div>

                {{-- Quick Controls Row --}}
                <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2 text-white">
                    {{-- Left Controls: Play, -5s, Speed Pill, +5s, Clock, Timestamp Note --}}
                    <div class="flex items-center gap-2 sm:gap-3">
                        <button type="button" @click="togglePlay()" class="p-1 text-slate-100 hover:text-teal-300 transition" :title="playing ? 'Pause (Space)' : 'Play (Space)'">
                            <svg x-show="!playing" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"></polygon></svg>
                            <svg x-show="playing" style="display:none;" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        </button>

                        <button type="button" @click="seekBy(-5)" class="px-1.5 py-0.5 text-[11px] font-bold text-slate-200 hover:text-teal-300 transition" title="Rewind 5s (Left Arrow)">
                            -5s
                        </button>

                        {{-- Speed Quick Menu --}}
                        <div class="relative">
                            <button
                                type="button"
                                @click="speedMenuOpen = !speedMenuOpen; settingsMenuOpen = false"
                                class="rounded bg-white px-2 py-0.5 text-[11px] font-extrabold text-slate-900 hover:bg-teal-100 transition min-w-[38px] text-center"
                                title="Playback speed"
                                x-text="playbackRate + 'x'"
                            ></button>
                            <div
                                x-show="speedMenuOpen"
                                x-transition
                                style="display: none;"
                                class="absolute bottom-9 left-0 z-40 w-24 rounded-lg border border-slate-700 bg-[#0b1c22] py-1 shadow-xl"
                            >
                                <template x-for="rate in speeds" :key="rate">
                                    <button
                                        type="button"
                                        @click="setSpeed(rate)"
                                        class="w-full px-3 py-1.5 text-left text-xs font-semibold hover:bg-slate-800 flex items-center justify-between"
                                        :class="playbackRate === rate ? 'text-teal-400' : 'text-slate-200'"
                                    >
                                        <span x-text="rate + 'x'"></span>
                                        <span x-show="playbackRate === rate">&bull;</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <button type="button" @click="seekBy(5)" class="px-1.5 py-0.5 text-[11px] font-bold text-slate-200 hover:text-teal-300 transition" title="Forward 5s (Right Arrow)">
                            +5s
                        </button>

                        <span class="text-xs font-semibold tabular-nums text-slate-200">
                            <span x-text="formatClock(currentTime)">0:00</span>
                            <span class="text-slate-500 mx-0.5">/</span>
                            <span x-text="formatClock(duration)">0:00</span>
                        </span>

                        <button
                            type="button"
                            @click="addTimestampNote()"
                            title="Add note at current timestamp"
                            class="hidden sm:inline-flex items-center gap-1 rounded px-2 py-1 text-[11px] font-semibold text-slate-300 hover:bg-slate-800 hover:text-teal-300 transition"
                        >
                            <span>+ Note</span>
                        </button>
                    </div>

                    {{-- Right Controls: Volume, Notes, Settings Quick Menu, Fullscreen, Expand Theater --}}
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="toggleMute()" class="p-1 text-slate-200 hover:text-teal-300 transition" title="Mute / Unmute (M)">
                                <svg x-show="!muted && volume > 0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                                <svg x-show="muted || volume === 0" style="display:none;" class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
                            </button>
                            <input
                                type="range"
                                min="0"
                                max="1"
                                step="0.05"
                                :value="muted ? 0 : volume"
                                @input="setVolume($event.target.value)"
                                class="hidden md:block w-16 accent-teal-400 cursor-pointer h-1"
                                aria-label="Volume"
                            />
                        </div>

                        {{-- Settings Quick Menu --}}
                        <div class="relative">
                            <button
                                type="button"
                                @click="settingsMenuOpen = !settingsMenuOpen; speedMenuOpen = false"
                                class="p-1 text-slate-200 hover:text-teal-300 transition"
                                title="Playback Settings"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.6.85 1 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            </button>
                            <div
                                x-show="settingsMenuOpen"
                                x-transition
                                style="display: none;"
                                class="absolute bottom-9 right-0 z-40 w-56 rounded-lg border border-slate-700 bg-[#0b1c22] p-2.5 shadow-xl text-xs space-y-2"
                            >
                                <label class="flex items-center justify-between gap-2 cursor-pointer py-1">
                                    <span class="text-slate-200 font-medium">Autoplay next lesson</span>
                                    <input type="checkbox" x-model="autoplayNext" class="rounded accent-teal-500" />
                                </label>
                                <template x-if="sdSrc">
                                    <button
                                        type="button"
                                        @click="toggleBandwidth()"
                                        class="w-full flex items-center justify-between rounded bg-slate-800/80 px-2.5 py-1.5 text-slate-200 hover:bg-slate-700 transition"
                                    >
                                        <span>Low-Bandwidth Mode</span>
                                        <span class="font-bold" :class="lowBandwidth ? 'text-teal-400' : 'text-slate-400'" x-text="lowBandwidth ? 'ON' : 'OFF'"></span>
                                    </button>
                                </template>
                                <div class="border-t border-slate-800 pt-2 text-[10px] text-slate-400 leading-relaxed">
                                    Shortcuts: Space (Play), Left/Right (±5s), M (Mute), F (Fullscreen)
                                </div>
                            </div>
                        </div>

                        <button type="button" @click="toggleFullscreen()" class="p-1 text-slate-200 hover:text-teal-300 transition" title="Fullscreen (F)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                        </button>

                        <button
                            type="button"
                            @click="sidebarOpen = !sidebarOpen"
                            class="hidden lg:inline-flex p-1 text-slate-200 hover:text-teal-300 transition"
                            :title="sidebarOpen ? 'Expand theater view' : 'Show course content sidebar'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="7 8 3 12 7 16"></polyline><polyline points="17 8 21 12 17 16"></polyline><line x1="3" y1="12" x2="21" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
