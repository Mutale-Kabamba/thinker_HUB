<x-filament-panels::page>
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
                        @else
                            <span class="hub-chip hub-chip-amber">Open</span>
                        @endif
                    </div>

                    <p class="hub-copy">{{ $course['summary'] }}</p>

                    {{-- Instructor(s) --}}
                    @if (count($course['instructors'] ?? []))
                        <div style="margin-top:0.75rem;border-top:1px solid var(--hub-border);padding-top:0.65rem;">
                            @foreach ($course['instructors'] as $instructor)
                                <div style="display:flex;align-items:flex-start;gap:0.6rem;{{ !$loop->first ? 'margin-top:0.6rem;' : '' }}">
                                    {{-- Avatar --}}
                                    <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.82rem;flex-shrink:0;">
                                        {{ strtoupper(substr($instructor['name'], 0, 1)) }}
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <p style="margin:0;font-weight:700;font-size:0.82rem;color:var(--hub-ink);">{{ $instructor['name'] }}</p>
                                        @if ($instructor['occupation'])
                                            <p style="margin:0.1rem 0 0;font-size:0.72rem;color:var(--hub-muted);">{{ $instructor['occupation'] }}</p>
                                        @endif
                                        @if ($instructor['proficiency'])
                                            <span style="display:inline-block;margin-top:0.25rem;font-size:0.68rem;font-weight:600;padding:0.1rem 0.45rem;border-radius:999px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">{{ $instructor['proficiency'] }}</span>
                                        @endif
                                        {{-- Social icons --}}
                                        @if ($instructor['whatsapp'] || $instructor['linkedin_url'] || $instructor['facebook_url'])
                                            <div style="display:flex;gap:0.45rem;margin-top:0.35rem;">
                                                @if ($instructor['whatsapp'])
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $instructor['whatsapp']) }}" target="_blank" rel="noopener noreferrer" title="WhatsApp" style="color:#25D366;text-decoration:none;display:inline-flex;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                                    </a>
                                                @endif
                                                @if ($instructor['linkedin_url'])
                                                    <a href="{{ $instructor['linkedin_url'] }}" target="_blank" rel="noopener noreferrer" title="LinkedIn" style="color:#0A66C2;text-decoration:none;display:inline-flex;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                                    </a>
                                                @endif
                                                @if ($instructor['facebook_url'])
                                                    <a href="{{ $instructor['facebook_url'] }}" target="_blank" rel="noopener noreferrer" title="Facebook" style="color:#1877F2;text-decoration:none;display:inline-flex;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <details style="margin-top:0.65rem;">
                        <summary style="cursor:pointer;color:#0f766e;font-weight:700;font-size:0.82rem;">View full description</summary>
                        <p class="hub-copy" style="margin-top:0.5rem;">{{ $course['description'] }}</p>
                    </details>

                    <div style="margin-top:0.9rem;display:flex;gap:0.55rem;flex-wrap:wrap;">
                        @if (! $course['is_active'])
                            <button type="button" disabled class="hub-btn hub-btn-muted" style="opacity:0.6;cursor:not-allowed;">Unavailable</button>
                        @elseif ($course['enrolled'])
                            <button type="button" wire:click="unenroll({{ $course['id'] }})" class="hub-btn hub-btn-danger">Unenroll</button>
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
