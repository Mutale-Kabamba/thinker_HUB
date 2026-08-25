<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 0.65rem; width: 100%; max-width: 100%;">
        {{-- Header Bento Banner --}}
        <div class="bento-card bento-mint" style="padding: 0.85rem 1.15rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.8rem; flex-wrap: wrap;">
                <div>
                    <span class="bento-pill bento-pill-mint" style="font-size: 0.65rem; margin-bottom: 0.25rem;">Course Catalog &amp; Enrollment</span>
                    <h2 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: inherit; letter-spacing: -0.02em;">
                        Available Courses
                    </h2>
                    <p style="margin: 0.2rem 0 0 0; font-size: 0.76rem; opacity: 0.9;">
                        Pick up to two active courses to enroll in and manage your curriculum on thinker HUB.
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span class="bento-pill bento-pill-mint" style="font-size: 0.72rem;">
                        🎓 Enrolled: {{ $enrolledCount }}/2
                    </span>
                </div>
            </div>
        </div>

        {{-- Multi-Column Compact Course Bento Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 0.65rem;">
            @forelse ($courses as $course)
                <article class="bento-card" style="padding: 0.9rem 1rem; display: flex; flex-direction: column; justify-content: space-between; gap: 0.75rem;">
                    <div>
                        {{-- Top Code & Status Row --}}
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                            <span class="bento-pill bento-pill-ice" style="font-family: monospace; font-weight: 700; font-size: 0.7rem;">
                                {{ $course['code'] }}
                            </span>
                            
                            @if (! $course['is_active'])
                                <span class="bento-pill bento-pill-neutral">Inactive</span>
                            @elseif ($course['enrolled'])
                                <span class="bento-pill bento-pill-mint">✓ Enrolled</span>
                            @elseif (! $course['is_open_enrollment'])
                                <span class="bento-pill bento-pill-neutral">🔒 Locked</span>
                            @else
                                <span class="bento-pill bento-pill-amber">● Open</span>
                            @endif
                        </div>

                        {{-- Course Title & Summary --}}
                        <h3 style="margin: 0.45rem 0 0 0; font-size: 0.95rem; font-weight: 800; color: var(--hub-ink); line-height: 1.3;">
                            {{ $course['title'] }}
                        </h3>

                        @if ($course['is_ongoing'] && ! empty($course['intake_name']))
                            <div style="margin-top: 0.25rem;">
                                <span class="bento-pill bento-pill-ice" style="font-size: 0.68rem; font-weight: 700;">
                                    📌 Intake: {{ $course['intake_name'] }}
                                </span>
                            </div>
                        @endif

                        <p style="margin: 0.25rem 0 0 0; font-size: 0.75rem; color: var(--hub-muted); line-height: 1.35;">
                            {{ $course['summary'] }}
                        </p>

                        @if (!empty($course['description']))
                            <details style="margin-top: 0.45rem;">
                                <summary style="cursor: pointer; color: #0d9488; font-weight: 700; font-size: 0.72rem;">View details</summary>
                                <p style="margin: 0.35rem 0 0 0; font-size: 0.72rem; color: var(--hub-muted); line-height: 1.35;">
                                    {{ $course['description'] }}
                                </p>
                            </details>
                        @endif
                    </div>

                    {{-- Course Stats / Rating Summary Strip --}}
                    <div style="border-top: 1px solid var(--hub-border); padding-top: 0.65rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="font-size: 0.72rem; font-weight: 700; color: #b45309; display: inline-flex; align-items: center; gap: 0.25rem;">
                            ⭐ {{ $course['avg_rating'] > 0 ? number_format($course['avg_rating'], 1) : 'New' }}
                            <span style="color: var(--hub-muted); font-weight: 500; font-size: 0.7rem;">
                                ({{ $course['ratings_count'] }} {{ \Illuminate\Support\Str::plural('review', $course['ratings_count']) }})
                            </span>
                        </span>

                        @if ($course['enrolled'])
                            <span class="bento-pill bento-pill-mint" style="font-size: 0.65rem;">
                                Active Student
                            </span>
                        @endif
                    </div>

                    {{-- Actions Footer --}}
                    <div style="border-top: 1px solid var(--hub-border); padding-top: 0.65rem; display: flex; justify-content: space-between; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                        @if (! $course['is_active'])
                            <button type="button" disabled style="padding: 0.35rem 0.65rem; font-size: 0.72rem; font-weight: 700; border-radius: 0.4rem; border: 1px solid var(--hub-border); background: var(--hub-surface-soft); color: var(--hub-muted); opacity: 0.6; cursor: not-allowed;">
                                Unavailable
                            </button>
                        @elseif ($course['enrolled'])
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                @if ($course['certificate_claimed'])
                                    <a href="{{ url('/learn/certificates') }}" class="bento-pill bento-pill-mint" style="text-decoration: none;">
                                        🎓 View Certificate
                                    </a>
                                @elseif ($course['certificate_eligible'])
                                    <button type="button" wire:click="claimCertificate({{ $course['id'] }})" class="bento-pill bento-pill-mint" style="cursor: pointer; border: none;">
                                        🎓 Claim Certificate
                                    </button>
                                @endif
                                <button type="button" wire:click="unenroll({{ $course['id'] }})"
                                        style="padding: 0.32rem 0.65rem; background: #fee2e2; color: #991b1b; font-size: 0.72rem; font-weight: 700; border-radius: 0.4rem; border: 1px solid #fca5a5; cursor: pointer;">
                                    Unenroll
                                </button>
                            </div>
                        @elseif (! $course['can_enroll'])
                            <button type="button" disabled style="padding: 0.35rem 0.65rem; font-size: 0.72rem; font-weight: 700; border-radius: 0.4rem; border: 1px solid var(--hub-border); background: var(--hub-surface-soft); color: var(--hub-muted); opacity: 0.6; cursor: not-allowed;">
                                🔒 Locked
                            </button>
                        @elseif ($course['is_payable'])
                            <a href="{{ $course['checkout_url'] }}"
                               style="display: inline-flex; align-items: center; text-decoration: none; padding: 0.35rem 0.85rem; background: #0d9488; color: #ffffff; font-size: 0.74rem; font-weight: 700; border-radius: 0.4rem; border: 1px solid #0f766e;">
                                Enroll &amp; Pay &rarr;
                            </a>
                        @else
                            <button type="button" wire:click="enroll({{ $course['id'] }})"
                                    style="padding: 0.35rem 0.85rem; background: #0d9488; color: #ffffff; font-size: 0.74rem; font-weight: 700; border-radius: 0.4rem; border: 1px solid #0f766e; cursor: pointer;">
                                Enroll Free &rarr;
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="bento-card" style="grid-column: 1 / -1; padding: 2rem; text-align: center;">
                    <p style="margin: 0; font-size: 0.85rem; color: var(--hub-muted);">No courses currently available in the catalog.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
