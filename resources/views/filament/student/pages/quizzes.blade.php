<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 0.65rem; width: 100%; max-width: 100%;">
        {{-- Header Bento Banner --}}
        <div class="bento-card bento-ice" style="padding: 0.85rem 1.15rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.8rem; flex-wrap: wrap;">
                <div>
                    <span class="bento-pill bento-pill-ice" style="font-size: 0.65rem; margin-bottom: 0.25rem;">Knowledge Verification</span>
                    <h2 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: inherit; letter-spacing: -0.02em;">
                        Quiz Centre
                    </h2>
                    <p style="margin: 0.2rem 0 0 0; font-size: 0.76rem; opacity: 0.9;">
                        Test your knowledge, reinforce core course topics, and unlock achievement XP.
                    </p>
                </div>
            </div>
        </div>

        {{-- Desktop Table Bento --}}
        <div class="bento-card hub-desktop-only" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                <thead>
                    <tr style="background: var(--hub-surface-soft); border-bottom: 1px solid var(--hub-border); text-align: left;">
                        <th style="padding: 0.55rem 0.75rem; font-weight: 700; color: var(--hub-muted); font-size: 0.7rem; text-transform: uppercase;">Quiz</th>
                        <th style="padding: 0.55rem 0.6rem; font-weight: 700; color: var(--hub-muted); font-size: 0.7rem; text-transform: uppercase; text-align: center;">Questions</th>
                        <th style="padding: 0.55rem 0.6rem; font-weight: 700; color: var(--hub-muted); font-size: 0.7rem; text-transform: uppercase; text-align: center;">Time Limit</th>
                        <th style="padding: 0.55rem 0.6rem; font-weight: 700; color: var(--hub-muted); font-size: 0.7rem; text-transform: uppercase; text-align: center;">Status</th>
                        <th style="padding: 0.55rem 0.6rem; font-weight: 700; color: var(--hub-muted); font-size: 0.7rem; text-transform: uppercase; text-align: center;">Score</th>
                        <th style="padding: 0.55rem 0.75rem; font-weight: 700; color: var(--hub-muted); font-size: 0.7rem; text-transform: uppercase; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quizzes as $quiz)
                        @php
                            $statusPill = match ($quiz['status']) {
                                'completed' => ($quiz['passed'] ? 'bento-pill-mint' : 'bento-pill-coral'),
                                'retake_allowed' => 'bento-pill-ice',
                                'in_progress' => 'bento-pill-ice',
                                'scheduled' => 'bento-pill-neutral',
                                default => 'bento-pill-amber',
                            };
                        @endphp
                        <tr style="border-bottom: 1px solid var(--hub-border);">
                            <td style="padding: 0.55rem 0.75rem;">
                                <p style="margin: 0; font-weight: 700; color: var(--hub-ink);">{{ $quiz['title'] }}</p>
                                <p style="margin: 0.15rem 0 0; font-size: 0.72rem; color: var(--hub-muted);">{{ $quiz['course'] }}</p>
                            </td>
                            <td style="padding: 0.55rem 0.6rem; text-align: center; color: var(--hub-muted);">
                                {{ $quiz['question_count'] }}
                            </td>
                            <td style="padding: 0.55rem 0.6rem; text-align: center; color: var(--hub-muted);">
                                {{ $quiz['time_limit'] ? $quiz['time_limit'] . 'm' : 'No limit' }}
                            </td>
                            <td style="padding: 0.55rem 0.6rem; text-align: center;">
                                <span class="bento-pill {{ $statusPill }}" style="font-size: 0.68rem;">
                                    {{ $quiz['status_label'] }}
                                </span>
                            </td>
                            <td style="padding: 0.55rem 0.6rem; text-align: center; font-weight: 800; color: {{ $quiz['score'] !== null ? ($quiz['passed'] ? '#059669' : '#dc2626') : 'var(--hub-muted)' }};">
                                {{ $quiz['score'] !== null ? $quiz['score'] . '%' : '-' }}
                                @if(!empty($quiz['is_retake']))
                                    <span style="font-size: 0.6rem; color: #0d9488; display: block;">2nd Try</span>
                                @endif
                            </td>
                            <td style="padding: 0.55rem 0.75rem; text-align: right;">
                                @if ($quiz['status'] === 'retake_allowed')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       style="padding: 0.25rem 0.6rem; background: #0284c7; color: #ffffff; font-size: 0.72rem; font-weight: 700; border-radius: 0.35rem; text-decoration: none; border: 1px solid #0369a1; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        Retake (2nd Try)
                                    </a>
                                @elseif ($quiz['status'] === 'completed')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       style="padding: 0.25rem 0.6rem; background: var(--hub-surface-soft); color: var(--hub-ink); font-size: 0.72rem; font-weight: 700; border-radius: 0.35rem; text-decoration: none; border: 1px solid var(--hub-border);">
                                        View Results
                                    </a>
                                @elseif ($quiz['status'] === 'in_progress')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       style="padding: 0.25rem 0.6rem; background: #0d9488; color: #ffffff; font-size: 0.72rem; font-weight: 700; border-radius: 0.35rem; text-decoration: none; border: 1px solid #0f766e;">
                                        Continue
                                    </a>
                                @elseif ($quiz['status'] === 'scheduled')
                                    <span class="bento-pill bento-pill-neutral" style="font-size: 0.68rem;">Upcoming</span>
                                @else
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       style="padding: 0.25rem 0.65rem; background: #0d9488; color: #ffffff; font-size: 0.72rem; font-weight: 700; border-radius: 0.35rem; text-decoration: none; border: 1px solid #0f766e;">
                                        Take Quiz &rarr;
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 1.5rem; text-align: center; color: var(--hub-muted);">
                                No quizzes available. Enroll in a course to see quizzes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Bento --}}
        <div class="hub-mobile-only hub-quiz-listing" style="display: flex; flex-direction: column; gap: 0.5rem;">
            @forelse ($quizzes as $quiz)
                @php
                    $statusPill = match ($quiz['status']) {
                        'completed' => ($quiz['passed'] ? 'bento-pill-mint' : 'bento-pill-coral'),
                        'retake_allowed' => 'bento-pill-ice',
                        'in_progress' => 'bento-pill-ice',
                        'scheduled' => 'bento-pill-neutral',
                        default => 'bento-pill-amber',
                    };
                @endphp
                <div class="bento-card hub-mobile-card" style="padding: 0.75rem 0.85rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.4rem;">
                        <div>
                            <h3 style="margin: 0; font-size: 0.85rem; font-weight: 700; color: var(--hub-ink);">{{ $quiz['title'] }}</h3>
                            <p style="margin: 0.1rem 0 0; font-size: 0.7rem; color: var(--hub-muted);">{{ $quiz['course'] }}</p>
                        </div>
                        <span class="bento-pill {{ $statusPill }}" style="font-size: 0.65rem;">{{ $quiz['status_label'] }}</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.3rem; margin-top: 0.45rem; background: var(--hub-surface-soft); border: 1px solid var(--hub-border); border-radius: 0.4rem; padding: 0.3rem; text-align: center;">
                        <div>
                            <span style="font-size: 0.62rem; color: var(--hub-muted); display: block;">Questions</span>
                            <span style="font-size: 0.76rem; font-weight: 700; color: var(--hub-ink);">{{ $quiz['question_count'] }}</span>
                        </div>
                        <div style="border-left: 1px solid var(--hub-border); border-right: 1px solid var(--hub-border);">
                            <span style="font-size: 0.62rem; color: var(--hub-muted); display: block;">Time</span>
                            <span style="font-size: 0.76rem; font-weight: 700; color: var(--hub-ink);">{{ $quiz['time_limit'] ? $quiz['time_limit'] . 'm' : 'None' }}</span>
                        </div>
                        <div>
                            <span style="font-size: 0.62rem; color: var(--hub-muted); display: block;">Score</span>
                            <span style="font-size: 0.76rem; font-weight: 700; color: {{ $quiz['score'] !== null ? ($quiz['passed'] ? '#059669' : '#dc2626') : 'var(--hub-muted)' }};">
                                {{ $quiz['score'] !== null ? $quiz['score'] . '%' : '-' }}
                            </span>
                        </div>
                    </div>

                    <div style="margin-top: 0.5rem;">
                        @if ($quiz['status'] === 'retake_allowed')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               style="width: 100%; box-sizing: border-box; background: #0284c7; color: #ffffff; border: 1px solid #0369a1; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 0.74rem; font-weight: 700; padding: 0.4rem; border-radius: 0.4rem;">
                                Retake Quiz (2nd Try)
                            </a>
                        @elseif ($quiz['status'] === 'completed')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               style="width: 100%; box-sizing: border-box; background: var(--hub-surface-soft); color: var(--hub-ink); border: 1px solid var(--hub-border); text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 0.74rem; font-weight: 700; padding: 0.4rem; border-radius: 0.4rem;">
                                View Results
                            </a>
                        @elseif ($quiz['status'] === 'in_progress')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               style="width: 100%; box-sizing: border-box; background: #0d9488; color: #ffffff; border: 1px solid #0f766e; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 0.74rem; font-weight: 700; padding: 0.4rem; border-radius: 0.4rem;">
                                Continue Quiz
                            </a>
                        @elseif ($quiz['status'] === 'scheduled')
                            <div style="width: 100%; box-sizing: border-box; background: var(--hub-surface-soft); color: var(--hub-muted); border: 1px solid var(--hub-border); display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; padding: 0.4rem; border-radius: 0.4rem;">
                                Upcoming Quiz
                            </div>
                        @else
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               style="width: 100%; box-sizing: border-box; background: #0d9488; color: #ffffff; border: 1px solid #0f766e; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 0.74rem; font-weight: 700; padding: 0.4rem; border-radius: 0.4rem;">
                                Take Quiz &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bento-card" style="padding: 1.5rem; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; color: var(--hub-muted);">No quizzes available.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
