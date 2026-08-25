<x-filament-panels::page>

    <div class="hub-shell">

        <section style="padding:0.35rem 0.5rem 0.6rem;">
            <p class="hub-eyebrow">Learning Resources</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Watch &amp; Learn</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Recorded lessons for your courses plus curated videos from top learning channels. Watch 85% of any video to earn +10 XP and +3 Thinker Coins!</p>
            <div style="height:1px;background:var(--hub-border);margin-top:0.65rem;"></div>
        </section>

        {{-- ==================== SAVED / BOOKMARKED ==================== --}}
        <section style="padding:0.35rem 0.5rem 0.7rem;">
            <div style="display:flex;align-items:center;gap:0.4rem;margin-bottom:0.65rem;">
                <x-heroicon-o-bookmark style="width:1.15rem;height:1.15rem;color:var(--hub-primary);" />
                <h3 class="hub-title" style="font-size:0.95rem;margin:0;">Saved for Later</h3>
            </div>
            <div style="height:1px;background:var(--hub-border);margin:0 0 0.7rem;"></div>

            @if (count($savedItems) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">Nothing saved yet. Tap the bookmark icon on any lesson, video, or material to find it here later.</p>
            @else
                <div class="hub-stack">
                    @foreach ($savedItems as $saved)
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;border:1px solid var(--hub-border);border-radius:10px;padding:0.5rem 0.65rem;">
                            <button
                                type="button"
                                wire:click="{{ $saved['type'] === 'lesson' ? 'openLesson' : 'openGeneralVideo' }}({{ $saved['id'] }})"
                                style="min-width:0;flex:1;text-align:left;background:none;border:none;cursor:pointer;padding:0;"
                            >
                                <span style="font-weight:600;font-size:0.82rem;color:var(--hub-ink);">{{ $saved['title'] }}</span>
                                <span style="font-size:0.7rem;color:var(--hub-muted);margin-left:0.35rem;">{{ $saved['kind'] }} · {{ $saved['meta'] }} · saved {{ $saved['saved_at'] }}</span>
                            </button>
                            <button
                                type="button"
                                wire:click="toggleBookmark('{{ $saved['type'] }}', {{ $saved['id'] }})"
                                title="Remove from saved"
                                style="flex-shrink:0;background:none;border:1px solid var(--hub-border);border-radius:6px;padding:0.25rem 0.5rem;font-size:0.72rem;cursor:pointer;color:#dc2626;font-weight:600;"
                            >Remove</button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ==================== COURSE RECORDED LESSONS ==================== --}}
        <section style="padding:0.35rem 0.5rem 0.7rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.65rem;">
                <h3 class="hub-title" style="font-size:0.95rem;margin:0;">Recorded Lessons</h3>
                @if (count($lessonCategories) > 0)
                    <select wire:model.live="filterLessonCategory" class="hub-input" style="max-width:180px;font-size:0.8rem;padding:0.3rem 0.5rem;">
                        <option value="">All Topics</option>
                        @foreach ($lessonCategories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div style="height:1px;background:var(--hub-border);margin:0 0 0.7rem;"></div>

            @if (count($courseLessons) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">No recorded lessons are available for your courses yet.</p>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:0.85rem;">
                    @foreach ($courseLessons as $lesson)
                        <div style="position:relative;">
                        <button
                            type="button"
                            @if (($lesson['record_type'] ?? 'lesson') === 'video')
                                wire:click="openGeneralVideo({{ $lesson['id'] }})"
                            @else
                                wire:click="openLesson({{ $lesson['id'] }})"
                            @endif
                            style="width:100%;text-align:left;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.75rem;overflow:hidden;cursor:pointer;padding:0;transition:transform .1s,box-shadow .1s;"
                            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,.08)'"
                            onmouseout="this.style.transform='';this.style.boxShadow=''"
                        >
                            <div style="position:relative;aspect-ratio:16/9;background:#0f172a;display:flex;align-items:center;justify-content:center;">
                                @if ($lesson['thumbnail'])
                                    <img src="{{ $lesson['thumbnail'] }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                @endif
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                    <svg width="46" height="46" viewBox="0 0 24 24" fill="white" style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.5));opacity:.95;"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                                @if ($lesson['points_earned'] ?? false)
                                    <span style="position:absolute;top:6px;left:6px;background:rgba(5,150,105,.9);color:#fff;font-size:0.65rem;padding:0.15rem 0.45rem;border-radius:999px;font-weight:600;display:inline-flex;align-items:center;gap:0.2rem;">
                                        <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span>Claimed</span>
                                    </span>
                                @else
                                    <span style="position:absolute;top:6px;left:6px;background:rgba(217,119,6,.9);color:#fff;font-size:0.65rem;padding:0.15rem 0.45rem;border-radius:999px;font-weight:600;">
                                        +10 XP / +3 TC
                                    </span>
                                @endif
                            </div>
                            <div style="padding:0.55rem 0.7rem;">
                                <p style="margin:0;font-weight:600;color:var(--hub-ink);font-size:0.85rem;line-height:1.25;">{{ $lesson['title'] }}</p>
                                @if (! empty($lesson['category']))
                                    <p style="margin:0.15rem 0 0;font-size:0.68rem;color:var(--hub-muted);">{{ $lesson['category'] }}</p>
                                @endif
                                <p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--hub-muted);">{{ $lesson['course'] }}</p>
                            </div>
                        </button>
                        <button
                            type="button"
                            wire:click="toggleBookmark('{{ ($lesson['record_type'] ?? 'lesson') === 'video' ? 'video' : 'lesson' }}', {{ $lesson['id'] }})"
                            title="{{ $lesson['bookmarked'] ? 'Remove from saved' : 'Save for later' }}"
                            style="position:absolute;top:8px;right:8px;z-index:2;display:flex;align-items:center;justify-content:center;width:30px;height:30px;border:none;border-radius:999px;cursor:pointer;background:rgba(15,23,42,.75);color:{{ $lesson['bookmarked'] ? '#fbbf24' : '#fff' }};"
                        >
                            @if ($lesson['bookmarked'])
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.583A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z"/></svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/></svg>
                            @endif
                        </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ==================== GENERAL / CURATED VIDEOS ==================== --}}
        <section style="padding:0.35rem 0.5rem 0.7rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.65rem;">
                <h3 class="hub-title" style="font-size:0.95rem;margin:0;">Explore More</h3>
                @if (count($generalCategories) > 0)
                    <select wire:model.live="filterCategory" class="hub-input" style="max-width:180px;font-size:0.8rem;padding:0.3rem 0.5rem;">
                        <option value="">All Topics</option>
                        @foreach ($generalCategories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div style="height:1px;background:var(--hub-border);margin:0 0 0.7rem;"></div>

            @if (count($generalVideos) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">No curated videos yet. Check back soon.</p>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:0.85rem;">
                    @foreach ($generalVideos as $video)
                        <div style="position:relative;">
                        <button
                            type="button"
                            wire:click="openGeneralVideo({{ $video['id'] }})"
                            style="width:100%;text-align:left;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.75rem;overflow:hidden;cursor:pointer;padding:0;transition:transform .1s,box-shadow .1s;"
                            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,.08)'"
                            onmouseout="this.style.transform='';this.style.boxShadow=''"
                        >
                            <div style="position:relative;aspect-ratio:16/9;background:#0f172a;">
                                @if ($video['thumbnail'])
                                    <img src="{{ $video['thumbnail'] }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                @endif
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                    <svg width="46" height="46" viewBox="0 0 24 24" fill="white" style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.5));opacity:.95;"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                                <span style="position:absolute;top:6px;left:6px;background:rgba(15,23,42,.85);color:#fff;font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:999px;">{{ $video['category'] }}</span>
                                @if ($video['points_earned'] ?? false)
                                    <span style="position:absolute;bottom:6px;left:6px;background:rgba(5,150,105,.9);color:#fff;font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:999px;font-weight:600;display:inline-flex;align-items:center;gap:0.2rem;">
                                        <svg width="11" height="11" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span>Claimed</span>
                                    </span>
                                @else
                                    <span style="position:absolute;bottom:6px;left:6px;background:rgba(217,119,6,.9);color:#fff;font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:999px;font-weight:600;">
                                        +10 XP / +3 TC
                                    </span>
                                @endif
                                @if ($video['processing'] ?? false)
                                    <span style="position:absolute;top:6px;right:6px;background:rgba(217,119,6,.9);color:#fff;font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:999px;display:inline-flex;align-items:center;gap:0.2rem;">
                                        <x-heroicon-o-arrow-path class="animate-spin" style="width:0.75rem;height:0.75rem;" />
                                        <span>Processing…</span>
                                    </span>
                                @elseif ($video['source'] === 'file')
                                    <span style="position:absolute;top:6px;right:6px;background:rgba(15,118,110,.9);color:#fff;font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:999px;display:inline-flex;align-items:center;gap:0.2rem;">
                                        <x-heroicon-o-folder style="width:0.75rem;height:0.75rem;" />
                                        <span>Upload</span>
                                    </span>
                                @endif
                            </div>
                            <div style="padding:0.55rem 0.7rem;">
                                <p style="margin:0;font-weight:600;color:var(--hub-ink);font-size:0.85rem;line-height:1.25;">{{ $video['title'] }}</p>
                                @if ($video['channel'])
                                    <p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--hub-muted);">{{ $video['channel'] }}</p>
                                @endif
                            </div>
                        </button>
                        <button
                            type="button"
                            wire:click="toggleBookmark('video', {{ $video['id'] }})"
                            title="{{ $video['bookmarked'] ? 'Remove from saved' : 'Save for later' }}"
                            style="position:absolute;top:8px;right:8px;z-index:2;display:flex;align-items:center;justify-content:center;width:30px;height:30px;border:none;border-radius:999px;cursor:pointer;background:rgba(15,23,42,.75);color:{{ $video['bookmarked'] ? '#fbbf24' : '#fff' }};"
                        >
                            @if ($video['bookmarked'])
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.583A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z"/></svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/></svg>
                            @endif
                        </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ==================== IN-APP TRACKED PLAYER + COMMENTS MODAL ==================== --}}
        @if ($showPlayer)
            <div
                x-data="lrVideoModalTracker({
                    youtubeId: @js($activeYoutubeId),
                    fileUrl: @js($activeFileUrl),
                    pointsClaimed: @js($activePointsEarned),
                    baseXp: @js($activeXp),
                    baseCoins: @js($activeCoins)
                })"
                @keydown.escape.window="$wire.closePlayer()"
                style="position:fixed;inset:0;z-index:60;background:rgba(0,0,0,.85);backdrop-filter:blur(4px);display:flex;align-items:flex-start;justify-content:center;padding:0.5rem;overflow-y:auto;"
            >
                <div @click.outside="$wire.closePlayer()" style="width:100%;max-width:960px;margin:auto;background:#0f172a;color:#f8fafc;border:1px solid #334155;border-radius:0.85rem;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.7);">
                    <!-- Modal Header -->
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;border-bottom:1px solid #1e293b;background:#0f172a;" class="min-w-0">
                        <div style="min-width:0;flex:1;">
                            <p style="margin:0;font-weight:700;color:#f1f5f9;font-size:0.88rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $playerTitle }}">{{ $playerTitle }}</p>
                        </div>

                        <!-- Points Claim Status Badge -->
                        <div style="display:flex;align-items:center;gap:0.35rem;flex-shrink:0;">
                            <template x-if="pointsClaimed">
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.18rem 0.5rem;border-radius:999px;font-size:0.68rem;font-weight:700;background:rgba(16,185,129,.2);color:#6ee7b7;border:1px solid rgba(16,185,129,.3);white-space:nowrap;">
                                    <svg style="width:0.8rem;height:0.8rem;color:#34d399;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Claimed (+{{ $activeXp }}XP)</span>
                                </span>
                            </template>

                            <template x-if="!pointsClaimed">
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.18rem 0.5rem;border-radius:999px;font-size:0.68rem;font-weight:600;background:rgba(245,158,11,.15);color:#fcd34d;border:1px solid rgba(245,158,11,.3);white-space:nowrap;">
                                    <svg style="width:0.8rem;height:0.8rem;color:#fbbf24;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/></svg>
                                    <span>+{{ $activeXp }} XP / +{{ $activeCoins }} TC</span>
                                </span>
                            </template>

                            <button type="button" wire:click="closePlayer" style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:999px;background:rgba(255,255,255,0.08);border:none;cursor:pointer;color:#cbd5e1;font-size:1.2rem;line-height:1;transition:background .15s;" title="Close">&times;</button>
                        </div>
                    </div>

                    <!-- Video Container -->
                    <div style="aspect-ratio:16/9;background:#000;position:relative;overflow:hidden;">
                        @if ($playerSource === 'youtube')
                            <iframe
                                id="lr-youtube-iframe-{{ $activeVideoId }}"
                                x-ref="youtubeIframe"
                                src="{{ $playerUrl }}&enablejsapi=1"
                                style="width:100%;height:100%;border:0;display:block;"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                @load="onIframeLoaded()"
                            ></iframe>
                        @elseif ($playerSource === 'file')
                            <video
                                x-ref="videoElement"
                                src="{{ $playerUrl }}"
                                controls
                                autoplay
                                style="width:100%;height:100%;background:#000;object-fit:contain;"
                                @play="handleFilePlay()"
                                @pause="handleFilePause()"
                                @ended="handleFileEnded()"
                                @timeupdate="handleFileTimeUpdate()"
                                @loadedmetadata="handleFileMetadata()"
                            ></video>
                        @elseif ($playerSource === 'processing')
                            <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.5rem;background:#0f172a;color:#e2e8f0;">
                                <x-heroicon-o-clock style="width:2rem;height:2rem;color:#f59e0b;" />
                                <p style="margin:0;font-size:0.9rem;font-weight:600;">This video is still being processed.</p>
                                <p style="margin:0;font-size:0.76rem;color:#94a3b8;">Please check back soon — it will play here once it's ready.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Watch Progress Tracker (Compact & Responsive) -->
                    @if ($playerSource === 'youtube' || $playerSource === 'file')
                        <div style="padding:0.5rem 0.85rem;background:#020617;border-bottom:1px solid #1e293b;">
                            <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.72rem;margin-bottom:0.35rem;gap:0.4rem;">
                                <div style="display:flex;align-items:center;gap:0.35rem;min-width:0;">
                                    <span style="font-weight:600;color:#cbd5e1;">Watch:</span>
                                    <span style="font-weight:700;color:#2dd4bf;" x-text="progressPercent + '%'">0%</span>
                                    <span
                                        style="font-size:0.65rem;padding:0.08rem 0.35rem;border-radius:4px;font-weight:600;"
                                        :style="isPlaying ? 'background:rgba(13,148,136,.3);color:#5eead4;border:1px solid rgba(13,148,136,.5);' : 'background:#1e293b;color:#94a3b8;'"
                                        x-text="isPlaying ? 'Playing' : 'Paused'"
                                    ></span>
                                </div>
                                <div style="color:#94a3b8;font-family:monospace;font-size:0.7rem;white-space:nowrap;flex-shrink:0;">
                                    <span x-text="formatTime(actualSecondsWatched)">00:00</span> /
                                    <span x-text="formatTime(duration)">00:00</span>
                                    <span style="color:#64748b;font-size:0.65rem;">(85%)</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div style="width:100%;height:6px;background:#334155;border-radius:999px;overflow:hidden;position:relative;">
                                <div style="position:absolute;top:0;bottom:0;left:85%;width:2px;background:#fbbf24;z-index:2;" title="85% Reward Target"></div>
                                <div
                                    style="height:100%;border-radius:999px;transition:width 0.3s ease-out;"
                                    :style="'width: ' + Math.min(100, progressPercent) + '%; background: ' + (progressPercent >= 85 ? 'linear-gradient(90deg, #10b981, #34d399)' : 'linear-gradient(90deg, #06b6d4, #3b82f6)') + ';'"
                                ></div>
                            </div>

                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.35rem;font-size:0.68rem;color:#94a3b8;gap:0.4rem;">
                                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Counts as you watch</span>
                                <template x-if="pointsClaimed">
                                    <span style="color:#34d399;font-weight:700;white-space:nowrap;flex-shrink:0;">+{{ $activeXp }} XP &amp; +{{ $activeCoins }} TC Awarded</span>
                                </template>
                                <template x-if="!pointsClaimed && progressPercent >= 85">
                                    <button
                                        type="button"
                                        @click="claimPoints()"
                                        style="background:#059669;color:#fff;border:none;padding:0.18rem 0.55rem;border-radius:6px;font-weight:700;cursor:pointer;font-size:0.7rem;white-space:nowrap;flex-shrink:0;"
                                    >Claim +{{ $activeXp }} XP Now</button>
                                </template>
                            </div>
                        </div>
                    @endif

                    <!-- Claim Toast/Banner inside Modal -->
                    <div
                        x-show="claimMessage"
                        x-cloak
                        x-transition
                        style="padding:0.5rem 0.85rem;font-size:0.74rem;display:flex;justify-content:space-between;align-items:center;"
                        :style="claimSuccess ? 'background:rgba(6,78,59,.8);color:#a7f3d0;border-bottom:1px solid #065f46;' : 'background:rgba(120,53,15,.8);color:#fde68a;border-bottom:1px solid #92400e;'"
                    >
                        <span x-text="claimMessage"></span>
                        <button type="button" @click="claimMessage = ''" style="background:none;border:none;color:inherit;cursor:pointer;font-size:1rem;">&times;</button>
                    </div>

                    <!-- Comments Section -->
                    @if ($commentType && $commentId)
                        <div style="padding:0.75rem 0.85rem;max-height:35vh;overflow-y:auto;background:var(--hub-card);">
                            @livewire('comment-section', ['type' => $commentType, 'id' => $commentId], key('cs-'.$commentType.'-'.$commentId))
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    <script>
        function lrVideoModalTracker(config) {
            return {
                youtubeId: config.youtubeId,
                fileUrl: config.fileUrl,
                pointsClaimed: config.pointsClaimed || false,
                actualSecondsWatched: 0,
                duration: 0,
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

                            // Handle infoDelivery events (currentTime and duration)
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

                    // Keep pinging for state updates
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

                    // Timeout after 6s to avoid persistent intervals
                    setTimeout(() => clearInterval(checkYT), 6000);
                },

                initPlayer() {
                    try {
                        const iframeId = 'lr-youtube-iframe-' + @js($activeVideoId);
                        const frameEl = document.getElementById(iframeId);
                        if (!frameEl) return;

                        this.player = new window.YT.Player(frameEl, {
                            events: {
                                onReady: (event) => {
                                    const dur = event.target.getDuration();
                                    if (dur && dur > 0) this.duration = Math.round(dur);
                                },
                                onStateChange: (event) => {
                                    if (event.data === 1) { // Playing
                                        this.isPlaying = true;
                                        this.startWatchTimer();
                                    } else {
                                        this.isPlaying = false;
                                        this.stopWatchTimer();
                                    }
                                }
                            }
                        });
                    } catch (err) {
                        // postMessage listener acts as fallback
                    }
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
</x-filament-panels::page>
