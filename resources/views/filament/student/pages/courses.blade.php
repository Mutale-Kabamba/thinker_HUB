<x-filament-panels::page>
    <style>
        .hub-review-input {
            background: var(--hub-surface);
            color: var(--hub-ink);
            border: 1px solid var(--hub-border);
        }

        .hub-review-input::placeholder {
            color: var(--hub-muted);
            opacity: 0.9;
        }

        .hub-review-text {
            color: var(--hub-ink);
        }
    </style>

    <div class="hub-shell">
        <section class="hub-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:0.8rem;flex-wrap:wrap;">
                <div>
                    <p class="hub-eyebrow">Course Catalog</p>
                    <h2 class="hub-title">Available Courses</h2>
                </div>
                <span class="hub-chip hub-chip-primary">Enrolled: {{ $enrolledCount }}/2</span>
            </div>
            <p class="hub-copy">Pick up to two active courses and manage enrollment from this panel.</p>
        </section>

        <div class="hub-grid hub-grid-2">
            @forelse ($courses as $course)
                <article class="hub-card">
                    <div style="display:flex;justify-content:space-between;gap:0.7rem;align-items:flex-start;">
                        <div>
                            <p class="hub-eyebrow">{{ $course['code'] }}</p>
                            <h3 class="hub-title" style="margin-top:0.25rem;">{{ $course['title'] }}</h3>
                        </div>
                        @if (! $course['is_active'])
                            <span class="hub-chip hub-chip-gray">Inactive</span>
                        @elseif ($course['enrolled'])
                            <span class="hub-chip hub-chip-green">Enrolled</span>
                        @elseif (! $course['is_open_enrollment'])
                            <span class="hub-chip hub-chip-gray">Locked</span>
                        @else
                            <span class="hub-chip hub-chip-amber">Open</span>
                        @endif
                    </div>

                    <p class="hub-copy">{{ $course['summary'] }}</p>

                    <details style="margin-top:0.65rem;">
                        <summary style="cursor:pointer;color:#0f766e;font-weight:700;font-size:0.82rem;">View full description</summary>
                        <p class="hub-copy" style="margin-top:0.5rem;">{{ $course['description'] }}</p>
                    </details>

                    <div style="margin-top:0.9rem;border-top:1px solid var(--hub-border);padding-top:0.8rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.6rem;flex-wrap:wrap;">
                            <p class="hub-eyebrow" style="margin:0;">Course Rating</p>
                            <p style="margin:0;font-size:0.76rem;color:var(--hub-muted);font-weight:600;">
                                {{ $course['avg_rating'] > 0 ? number_format($course['avg_rating'], 1) . '/5' : 'No rating yet' }}
                                ({{ $course['ratings_count'] }} {{ \Illuminate\Support\Str::plural('review', $course['ratings_count']) }})
                            </p>
                        </div>

                        @if ($course['enrolled'])
                            <div style="margin-top:0.45rem;display:flex;gap:0.3rem;align-items:center;flex-wrap:wrap;">
                                @for ($star = 1; $star <= 5; $star++)
                                    <button
                                        type="button"
                                        wire:click="setRating({{ $course['id'] }}, {{ $star }})"
                                        style="background:none;border:none;cursor:pointer;font-size:1.1rem;line-height:1;padding:0;"
                                        title="Rate {{ $star }} star{{ $star > 1 ? 's' : '' }}"
                                    >
                                        @if ((int) ($ratingInputs[$course['id']] ?? 0) >= $star)
                                            <span aria-hidden="true" style="color:#f59e0b;">★</span>
                                        @else
                                            <span aria-hidden="true" style="color:#94a3b8;">☆</span>
                                        @endif
                                    </button>
                                @endfor
                                <span style="font-size:0.74rem;color:var(--hub-muted);margin-left:0.3rem;">
                                    Your rating: {{ (int) ($ratingInputs[$course['id']] ?? 0) > 0 ? ($ratingInputs[$course['id']] . '/5') : 'Not set' }}
                                </span>
                            </div>

                            <textarea
                                wire:model.defer="reviewInputs.{{ $course['id'] }}"
                                rows="3"
                                placeholder="Write your review (optional)..."
                                class="hub-review-input"
                                style="margin-top:0.55rem;width:100%;border-radius:10px;padding:0.6rem 0.75rem;font-size:0.8rem;"
                            ></textarea>

                            <button
                                type="button"
                                wire:click="saveRating({{ $course['id'] }})"
                                class="hub-btn hub-btn-primary"
                                style="margin-top:0.55rem;"
                            >
                                Save Review
                            </button>
                        @else
                            <p class="hub-copy" style="margin-top:0.5rem;">Enroll in this course to add your rating and review.</p>
                        @endif

                        @if (!empty($course['reviews']))
                            <div style="margin-top:0.7rem;display:flex;flex-direction:column;gap:0.45rem;">
                                @foreach ($course['reviews'] as $review)
                                    <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.55rem 0.65rem;background:var(--hub-surface);">
                                        <div style="display:flex;justify-content:space-between;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                                            <p style="margin:0;font-size:0.76rem;font-weight:700;color:var(--hub-ink);">{{ $review['user_name'] }}</p>
                                            <div style="display:flex;align-items:center;gap:0.35rem;">
                                                <span style="font-size:0.72rem;color:var(--hub-muted);">{{ $review['created_at'] }}</span>
                                                <span style="font-size:0.72rem;font-weight:700;color:#b45309;">{{ $review['rating'] }}/5</span>
                                            </div>
                                        </div>
                                        @if (!empty($review['review']))
                                            <p class="hub-review-text" style="margin:0.3rem 0 0;font-size:0.78rem;white-space:pre-line;">{{ $review['review'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div style="margin-top:0.9rem;display:flex;gap:0.55rem;flex-wrap:wrap;">
                        @if (! $course['is_active'])
                            <button type="button" disabled class="hub-btn hub-btn-muted" style="opacity:0.6;cursor:not-allowed;">Unavailable</button>
                        @elseif ($course['enrolled'])
                            <button type="button" wire:click="unenroll({{ $course['id'] }})" class="hub-btn hub-btn-danger">Unenroll</button>
                        @elseif (! $course['can_enroll'])
                            <button type="button" disabled class="hub-btn hub-btn-muted" style="opacity:0.6;cursor:not-allowed;">Locked</button>
                        @else
                            <button type="button" wire:click="enroll({{ $course['id'] }})" class="hub-btn hub-btn-primary">Enroll Now</button>
                        @endif
                    </div>
                </article>
            @empty
                <section class="hub-card">
                    <p class="hub-copy">No courses available.</p>
                </section>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
