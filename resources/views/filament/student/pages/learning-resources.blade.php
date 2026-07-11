<x-filament-panels::page>

    <div class="hub-shell">

        <section class="hub-card" style="padding:0.75rem 1rem;">
            <p class="hub-eyebrow">Learning Resources</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Watch &amp; Learn</h2>
            <p class="hub-copy" style="margin-top:0.2rem;">Recorded lessons for your courses plus curated videos from top learning channels. Everything plays right here in the app.</p>
        </section>

        {{-- ==================== COURSE RECORDED LESSONS ==================== --}}
        <section class="hub-card" style="padding:0.85rem 1rem;">
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

            @if (count($courseLessons) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">No recorded lessons are available for your courses yet.</p>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:0.85rem;">
                    @foreach ($courseLessons as $lesson)
                        <button
                            type="button"
                            @if (($lesson['record_type'] ?? 'lesson') === 'video')
                                wire:click="openGeneralVideo({{ $lesson['id'] }})"
                            @else
                                wire:click="openLesson({{ $lesson['id'] }})"
                            @endif
                            style="text-align:left;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.75rem;overflow:hidden;cursor:pointer;padding:0;transition:transform .1s,box-shadow .1s;"
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
                            </div>
                            <div style="padding:0.55rem 0.7rem;">
                                <p style="margin:0;font-weight:600;color:var(--hub-ink);font-size:0.85rem;line-height:1.25;">{{ $lesson['title'] }}</p>
                                @if (! empty($lesson['category']))
                                    <p style="margin:0.15rem 0 0;font-size:0.68rem;color:var(--hub-muted);">{{ $lesson['category'] }}</p>
                                @endif
                                <p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--hub-muted);">{{ $lesson['course'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ==================== GENERAL / CURATED VIDEOS ==================== --}}
        <section class="hub-card" style="padding:0.85rem 1rem;">
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

            @if (count($generalVideos) === 0)
                <p class="hub-copy" style="color:var(--hub-muted);">No curated videos yet. Check back soon.</p>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:0.85rem;">
                    @foreach ($generalVideos as $video)
                        <button
                            type="button"
                            wire:click="openGeneralVideo({{ $video['id'] }})"
                            style="text-align:left;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.75rem;overflow:hidden;cursor:pointer;padding:0;transition:transform .1s,box-shadow .1s;"
                            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,.08)'"
                            onmouseout="this.style.transform='';this.style.boxShadow=''"
                        >
                            <div style="position:relative;aspect-ratio:16/9;background:#0f172a;">
                                <img src="{{ $video['thumbnail'] }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                    <svg width="46" height="46" viewBox="0 0 24 24" fill="white" style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.5));opacity:.95;"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                                <span style="position:absolute;top:6px;left:6px;background:rgba(15,23,42,.85);color:#fff;font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:999px;">{{ $video['category'] }}</span>
                            </div>
                            <div style="padding:0.55rem 0.7rem;">
                                <p style="margin:0;font-weight:600;color:var(--hub-ink);font-size:0.85rem;line-height:1.25;">{{ $video['title'] }}</p>
                                @if ($video['channel'])
                                    <p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--hub-muted);">{{ $video['channel'] }}</p>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ==================== IN-APP PLAYER + COMMENTS MODAL ==================== --}}
        @if ($showPlayer)
            <div
                x-data
                @keydown.escape.window="$wire.closePlayer()"
                wire:click.self="closePlayer"
                style="position:fixed;inset:0;z-index:60;background:rgba(0,0,0,.8);display:flex;align-items:flex-start;justify-content:center;padding:1rem;overflow-y:auto;"
            >
                <div style="width:100%;max-width:960px;margin:auto;background:var(--hub-card);border-radius:0.9rem;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5);">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;padding:0.6rem 0.9rem;border-bottom:1px solid var(--hub-border);">
                        <p style="margin:0;font-weight:600;color:var(--hub-ink);font-size:0.9rem;">{{ $playerTitle }}</p>
                        <button type="button" wire:click="closePlayer" style="background:none;border:none;cursor:pointer;color:var(--hub-muted);font-size:1.4rem;line-height:1;">&times;</button>
                    </div>

                    <div style="aspect-ratio:16/9;background:#000;">
                        @if ($playerSource === 'youtube')
                            <iframe
                                src="{{ $playerUrl }}"
                                style="width:100%;height:100%;border:0;"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @elseif ($playerSource === 'file')
                            <video src="{{ $playerUrl }}" controls autoplay style="width:100%;height:100%;background:#000;"></video>
                        @endif
                    </div>

                    @if ($commentType && $commentId)
                        <div style="padding:0.85rem 1rem;max-height:45vh;overflow-y:auto;">
                            @livewire('comment-section', ['type' => $commentType, 'id' => $commentId], key('cs-'.$commentType.'-'.$commentId))
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
