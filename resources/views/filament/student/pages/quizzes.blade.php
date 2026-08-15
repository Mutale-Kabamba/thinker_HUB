<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card" style="padding:0.75rem 1rem;overflow:hidden;">
            <p class="hub-eyebrow">Quiz Centre</p>
            <h2 class="hub-title" style="font-size:1.05rem;">Quizzes</h2>
            <p class="hub-copy" style="margin-top:0.2rem;word-wrap:break-word;">View available quizzes, track your progress, and test your knowledge.</p>
        </section>

        {{-- ======================== DESKTOP TABLE ======================== --}}
        <div class="hub-card hub-desktop-only" style="padding:0;overflow:hidden;">
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%;">
                <table style="width:100%;border-collapse:collapse;font-size:0.82rem;min-width:600px;">
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
                            <tr style="border-bottom:1px solid var(--hub-border);transition:all 0.15s;" onmouseover="this.style.background='var(--hub-surface)'" onmouseout="this.style.background='transparent'">
                                <td style="padding:0.55rem 0.75rem;">
                                    <p style="margin:0;font-weight:600;color:var(--hub-ink);">{{ $quiz['title'] }}</p>
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
                                        <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-btn hub-btn-sm" style="background:var(--hub-surface);border:1px solid var(--hub-border);color:var(--hub-ink);font-size:0.74rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;white-space:nowrap;">
                                            <x-heroicon-s-eye style="width:0.75rem;height:0.75rem;" />
                                            View Results
                                        </a>
                                    @elseif ($quiz['status'] === 'in_progress')
                                        <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-btn hub-btn-sm hub-btn-primary" style="font-size:0.74rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;white-space:nowrap;">
                                            <x-heroicon-s-play style="width:0.75rem;height:0.75rem;" />
                                            Continue
                                        </a>
                                    @elseif ($quiz['status'] === 'scheduled')
                                        <span class="hub-chip hub-chip-gray" style="font-size:0.72rem;">Upcoming</span>
                                    @else
                                        <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-btn hub-btn-sm hub-btn-primary" style="font-size:0.74rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;white-space:nowrap;">
                                            <x-heroicon-s-pencil-square style="width:0.75rem;height:0.75rem;" />
                                            Take Quiz
                                        </a>
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
        </div>

        {{-- ======================== MOBILE & TABLET RESPONSIVE CARDS ======================== --}}
        <div class="hub-mobile-only hub-quiz-listing">
            @forelse ($quizzes as $quiz)
                <div class="hub-mobile-card" style="padding:0.75rem 0.85rem;background:var(--hub-card);border:1px solid var(--hub-border);border-radius:0.6rem;margin-bottom:0.5rem;box-sizing:border-box;max-width:100%;overflow:hidden;">
                    {{-- Header: Title + Status Chip --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;">
                        <div style="flex:1;min-width:0;">
                            <h3 style="margin:0;font-size:0.9rem;font-weight:700;color:var(--hub-ink);line-height:1.3;word-break:break-word;overflow-wrap:break-word;">
                                {{ $quiz['title'] }}
                            </h3>
                            <div style="margin-top:0.2rem;display:flex;align-items:center;gap:0.35rem;flex-wrap:wrap;">
                                <span class="hub-chip hub-chip-gray" style="font-size:0.62rem;padding:0.05rem 0.3rem;line-height:1.2;">
                                    {{ $quiz['course'] }}
                                </span>
                            </div>
                        </div>
                        <span class="hub-chip {{ $quiz['status'] === 'completed' ? ($quiz['passed'] ? 'hub-chip-green' : 'hub-chip-red') : ($quiz['status'] === 'in_progress' ? 'hub-chip-blue' : ($quiz['status'] === 'scheduled' ? 'hub-chip-purple' : 'hub-chip-amber')) }}" style="font-size:0.62rem;padding:0.1rem 0.35rem;flex-shrink:0;white-space:nowrap;">
                            {{ $quiz['status_label'] }}
                        </span>
                    </div>

                    {{-- Metrics Strip (Qs, Time, Score) --}}
                    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:0.35rem;margin-top:0.55rem;background:var(--hub-surface);border:1px solid var(--hub-border);border-radius:0.45rem;padding:0.35rem 0.45rem;text-align:center;">
                        <div style="min-width:0;">
                            <span style="font-size:0.62rem;color:var(--hub-muted);display:block;">Questions</span>
                            <span style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);">{{ $quiz['question_count'] }}</span>
                        </div>
                        <div style="min-width:0;border-left:1px solid var(--hub-border);border-right:1px solid var(--hub-border);">
                            <span style="font-size:0.62rem;color:var(--hub-muted);display:block;">Time Limit</span>
                            <span style="font-size:0.78rem;font-weight:700;color:var(--hub-ink);">{{ $quiz['time_limit'] ? $quiz['time_limit'] . 'm' : 'None' }}</span>
                        </div>
                        <div style="min-width:0;">
                            <span style="font-size:0.62rem;color:var(--hub-muted);display:block;">Score</span>
                            <span style="font-size:0.78rem;font-weight:700;color:{{ $quiz['score'] !== null ? ($quiz['passed'] ? '#16a34a' : '#dc2626') : 'var(--hub-muted)' }};">
                                {{ $quiz['score'] !== null ? $quiz['score'] . '%' : '-' }}
                            </span>
                        </div>
                    </div>

                    @if (!empty($quiz['description']))
                        <p style="margin:0.45rem 0 0;font-size:0.74rem;color:var(--hub-muted);line-height:1.4;word-break:break-word;overflow-wrap:break-word;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $quiz['description'] }}
                        </p>
                    @endif

                    {{-- Action Button --}}
                    <div style="margin-top:0.6rem;">
                        @if ($quiz['status'] === 'completed')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-action-btn" style="width:100%;box-sizing:border-box;background:var(--hub-surface);color:var(--hub-ink);border:1px solid var(--hub-border);text-decoration:none;display:flex;align-items:center;justify-content:center;gap:0.35rem;font-size:0.76rem;font-weight:600;padding:0.45rem 0.5rem;min-height:38px;border-radius:0.45rem;white-space:nowrap;">
                                <x-heroicon-s-eye style="width:0.85rem;height:0.85rem;color:var(--hub-muted);" />
                                <span>View Results</span>
                            </a>
                        @elseif ($quiz['status'] === 'in_progress')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-action-btn" style="width:100%;box-sizing:border-box;background:#0284c7;color:#fff;border:1px solid #0284c7;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:0.35rem;font-size:0.76rem;font-weight:700;padding:0.45rem 0.5rem;min-height:38px;border-radius:0.45rem;white-space:nowrap;">
                                <x-heroicon-s-play style="width:0.85rem;height:0.85rem;color:#fff;" />
                                <span>Continue Quiz</span>
                            </a>
                        @elseif ($quiz['status'] === 'scheduled')
                            <div style="width:100%;box-sizing:border-box;background:var(--hub-surface);color:var(--hub-muted);border:1px solid var(--hub-border);display:flex;align-items:center;justify-content:center;font-size:0.74rem;font-weight:600;padding:0.45rem 0.5rem;min-height:38px;border-radius:0.45rem;cursor:default;">
                                <span>Upcoming Quiz</span>
                            </div>
                        @else
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}" class="hub-action-btn" style="width:100%;box-sizing:border-box;background:#0f766e;color:#fff;border:1px solid #0f766e;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:0.35rem;font-size:0.76rem;font-weight:700;padding:0.45rem 0.5rem;min-height:38px;border-radius:0.45rem;white-space:nowrap;box-shadow:0 1px 2px rgba(15,118,110,0.2);">
                                <x-heroicon-s-pencil-square style="width:0.85rem;height:0.85rem;color:#fff;" />
                                <span>Take Quiz</span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="hub-mobile-card" style="padding:1.25rem 1rem;text-align:center;">
                    <p class="hub-copy" style="margin:0;">No quizzes available. Enrol in a course to see quizzes.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>

