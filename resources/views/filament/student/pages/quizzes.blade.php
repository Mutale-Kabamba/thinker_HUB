<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;overflow:hidden;">
            <p class="hub-eyebrow">Quiz Centre</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Quizzes</h2>
            <p class="hub-copy" style="margin-top:0.2rem;word-wrap:break-word;">View available quizzes, track your progress, and test your knowledge.</p>
        </section>

        {{-- ======================== DESKTOP TABLE ======================== --}}
        <div class="hub-card hub-desktop-only" style="padding:0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                <thead>
                    <tr style="background:var(--hub-surface);border-bottom:2px solid var(--hub-border);">
                        <th style="padding:0.6rem 0.75rem;text-align:left;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Quiz</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Questions</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Time Limit</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Status</th>
                        <th style="padding:0.6rem 0.5rem;text-align:center;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Score</th>
                        <th style="padding:0.6rem 0.75rem;text-align:right;font-weight:700;color:var(--hub-ink);font-size:0.74rem;text-transform:uppercase;letter-spacing:0.04em;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quizzes as $quiz)
                        <tr style="border-bottom:1px solid var(--hub-border);{{ $quiz['status'] === 'completed' ? 'background:#f8fafc;opacity:0.75;' : '' }}transition:all 0.15s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background='{{ $quiz['status'] === 'completed' ? '#f8fafc' : '' }}'">
                            <td style="padding:0.55rem 0.75rem;">
                                <p style="margin:0;font-weight:600;color:{{ $quiz['status'] === 'completed' ? '#64748b' : 'var(--hub-ink)' }};">{{ $quiz['title'] }}</p>
                                <p style="margin:0.15rem 0 0;font-size:0.74rem;color:var(--hub-muted);">{{ $quiz['course'] }}</p>
                            </td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;color:var(--hub-muted);">{{ $quiz['question_count'] }}</td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;color:var(--hub-muted);">{{ $quiz['time_limit'] ? $quiz['time_limit'] . ' min' : 'No limit' }}</td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;">
                                <span class="hub-chip {{ $quiz['status'] === 'completed' ? ($quiz['passed'] ? 'hub-chip-green' : 'hub-chip-red') : ($quiz['status'] === 'in_progress' ? 'hub-chip-blue' : ($quiz['status'] === 'scheduled' ? 'hub-chip-purple' : 'hub-chip-amber')) }}" style="font-size:0.7rem;">{{ $quiz['status_label'] }}</span>
                            </td>
                            <td style="padding:0.55rem 0.5rem;text-align:center;font-weight:700;color:{{ $quiz['score'] !== null ? ($quiz['passed'] ? '#15803d' : '#dc2626') : 'var(--hub-muted)' }};">{{ $quiz['score'] !== null ? $quiz['score'] . '%' : '-' }}</td>
                            <td style="padding:0.55rem 0.75rem;text-align:right;">
                                @if ($quiz['status'] === 'completed')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:0.28rem 0.65rem;font-size:0.74rem;cursor:pointer;color:#334155;font-weight:600;transition:all 0.15s;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;" onmouseover="this.style.background='#f1f5f9';this.style.borderColor='#94a3b8';" onmouseout="this.style.background='#ffffff';this.style.borderColor='#cbd5e1';">
                                        <svg style="width:13px;height:13px;color:#64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View Results
                                    </a>
                                @elseif ($quiz['status'] === 'in_progress')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" style="background:#0284c7;border:1px solid #0284c7;border-radius:6px;padding:0.28rem 0.75rem;font-size:0.74rem;cursor:pointer;color:#fff;font-weight:600;transition:background 0.15s;text-decoration:none;" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'">Continue</a>
                                @elseif ($quiz['status'] === 'scheduled')
                                    <span style="background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:6px;padding:0.28rem 0.65rem;font-size:0.72rem;color:var(--hub-muted);font-weight:600;display:inline-block;cursor:default;">Upcoming</span>
                                @else
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" style="background:#0d9488;border:1px solid #0d9488;border-radius:6px;padding:0.28rem 0.75rem;font-size:0.74rem;cursor:pointer;color:#fff;font-weight:600;box-shadow:0 1px 2px rgba(13,148,136,0.2);transition:background 0.15s;text-decoration:none;" onmouseover="this.style.background='#0f766e'" onmouseout="this.style.background='#0d9488'">Take Quiz</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:1.5rem;text-align:center;">
                                <p class="hub-copy">No quizzes available. Enrol in a course to see quizzes.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ======================== MOBILE CARDS ======================== --}}
        <div class="hub-mobile-only hub-quiz-listing">
            @forelse ($quizzes as $quiz)
                <div class="hub-mobile-card" style="{{ $quiz['status'] === 'completed' ? 'background:#f8fafc;border-color:#e2e8f0;opacity:0.85;' : '' }}">
                    {{-- Header: Title + Status --}}
                    <div class="hub-mobile-card-row">
                        <div style="flex:1;min-width:0;overflow:hidden;">
                            <p style="margin:0;font-weight:700;color:{{ $quiz['status'] === 'completed' ? '#64748b' : 'var(--hub-ink)' }};font-size:0.86rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $quiz['title'] }}</p>
                            <p style="margin:0.1rem 0 0;font-size:0.73rem;color:var(--hub-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $quiz['course'] }}</p>
                        </div>
                        <span class="hub-chip {{ $quiz['status'] === 'completed' ? ($quiz['passed'] ? 'hub-chip-green' : 'hub-chip-red') : ($quiz['status'] === 'in_progress' ? 'hub-chip-blue' : ($quiz['status'] === 'scheduled' ? 'hub-chip-purple' : 'hub-chip-amber')) }}" style="font-size:0.65rem;flex-shrink:0;margin-left:0.35rem;">{{ $quiz['status_label'] }}</span>
                    </div>

                    {{-- Meta row --}}
                    <div class="hub-mobile-card-meta" style="margin-top:0.4rem;">
                        <span style="color:var(--hub-muted);font-size:0.76rem;"><strong>Qs:</strong> {{ $quiz['question_count'] }}</span>
                        <span style="color:var(--hub-muted);font-size:0.76rem;"><strong>Time:</strong> {{ $quiz['time_limit'] ? $quiz['time_limit'] . 'm' : '∞' }}</span>
                        <span style="font-weight:700;font-size:0.76rem;color:{{ $quiz['score'] !== null ? ($quiz['passed'] ? '#15803d' : '#dc2626') : 'var(--hub-muted)' }};"><strong>Score:</strong> {{ $quiz['score'] !== null ? $quiz['score'] . '%' : '-' }}</span>
                    </div>

                    @if (!empty($quiz['description']))
                        <p style="margin:0.3rem 0 0;font-size:0.76rem;color:var(--hub-muted);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $quiz['description'] }}</p>
                    @endif

                    {{-- Action --}}
                    <div class="hub-mobile-card-actions" style="margin-top:0.55rem;">
                        @if ($quiz['status'] === 'completed')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-action-btn" style="background:#ffffff;color:#334155;border-color:#cbd5e1;text-decoration:none;flex:1;text-align:center;display:inline-flex;align-items:center;justify-content:center;gap:0.3rem;">
                                <svg style="width:13px;height:13px;color:#64748b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View Results
                            </a>
                        @elseif ($quiz['status'] === 'in_progress')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-action-btn" style="color:#0284c7;border-color:#0284c7;text-decoration:none;flex:1;text-align:center;">Continue</a>
                        @elseif ($quiz['status'] === 'scheduled')
                            <span class="hub-action-btn" style="color:var(--hub-muted);border-color:var(--hub-border);background:var(--hub-surface);flex:1;text-align:center;cursor:default;">Upcoming</span>
                        @else
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-action-btn" style="background:#0d9488;color:#fff;border-color:#0d9488;text-decoration:none;flex:1;text-align:center;">Take Quiz</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="hub-mobile-card">
                    <p class="hub-copy" style="text-align:center;">No quizzes available. Enrol in a course to see quizzes.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
