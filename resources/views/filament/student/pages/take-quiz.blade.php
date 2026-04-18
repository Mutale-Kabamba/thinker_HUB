<x-filament-panels::page>
    <div class="hub-shell">
        @if ($submitted && !empty($results))
            {{-- =================== RESULTS VIEW =================== --}}
            <section class="hub-card" style="text-align:center;padding:2rem 1.5rem;">
                <div style="margin-bottom:1rem;">
                    @if ($results['passed'])
                        <div style="width:64px;height:64px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.75rem;">
                            <svg style="width:32px;height:32px;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h2 style="font-size:1.5rem;font-weight:800;color:#15803d;margin:0;">Passed!</h2>
                    @else
                        <div style="width:64px;height:64px;border-radius:50%;background:#fef2f2;display:inline-flex;align-items:center;justify-content:center;margin-bottom:0.75rem;">
                            <svg style="width:32px;height:32px;color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h2 style="font-size:1.5rem;font-weight:800;color:#dc2626;margin:0;">Not Passed</h2>
                    @endif
                </div>

                <p style="font-size:0.9rem;color:var(--hub-muted);margin:0 0 1rem;">{{ $quiz['course'] }}</p>

                <div style="display:flex;justify-content:center;gap:2rem;flex-wrap:wrap;margin-bottom:1rem;">
                    <div>
                        <p style="font-size:2rem;font-weight:800;color:var(--hub-ink);margin:0;">{{ $results['percentage'] }}%</p>
                        <p style="font-size:0.75rem;color:var(--hub-muted);margin:0;">Score</p>
                    </div>
                    <div>
                        <p style="font-size:2rem;font-weight:800;color:var(--hub-ink);margin:0;">{{ $results['score'] }}/{{ $results['total'] }}</p>
                        <p style="font-size:0.75rem;color:var(--hub-muted);margin:0;">Points</p>
                    </div>
                </div>

                <p style="font-size:0.78rem;color:var(--hub-muted);margin:0;">Pass mark: {{ $quiz['pass_percentage'] }}% &middot; Completed: {{ $results['completed_at'] }}</p>
            </section>

            @if (!empty($quiz['show_results']) && !empty($questions))
                <section class="hub-card" style="padding:1rem 1.25rem;">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--hub-ink);margin:0 0 1rem;">Review Answers</h3>
                    @foreach ($questions as $index => $question)
                        <div style="padding:1rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--hub-border);' : '' }}">
                            <div style="display:flex;gap:0.5rem;align-items:flex-start;">
                                <span style="background:var(--hub-surface);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:var(--hub-ink);flex-shrink:0;">{{ $index + 1 }}</span>
                                <div style="flex:1;min-width:0;">
                                    <p style="font-weight:600;color:var(--hub-ink);margin:0;font-size:0.88rem;">{{ $question['question'] }}</p>
                                    <span class="hub-chip" style="font-size:0.65rem;margin-top:0.25rem;{{ $question['type'] === 'multiple_choice' ? 'background:#dbeafe;color:#1e40af;' : ($question['type'] === 'theory' ? 'background:#fef3c7;color:#92400e;' : 'background:#ede9fe;color:#5b21b6;') }}">{{ ucfirst(str_replace('_', ' ', $question['type'])) }} &middot; {{ $question['points'] }} pts</span>

                                    @if ($question['type'] === 'multiple_choice' && !empty($question['options']))
                                        <div style="margin-top:0.5rem;display:flex;flex-direction:column;gap:0.35rem;">
                                            @foreach ($question['options'] as $option)
                                                @php
                                                    $isSelected = ($question['user_answer']['option_id'] ?? null) == $option['id'];
                                                    $isCorrectOption = !empty($option['is_correct']);
                                                    $bg = '';
                                                    if ($isCorrectOption) $bg = 'background:#dcfce7;border-color:#86efac;';
                                                    elseif ($isSelected && !$isCorrectOption) $bg = 'background:#fef2f2;border-color:#fca5a5;';
                                                @endphp
                                                <div style="padding:0.4rem 0.6rem;border:1px solid var(--hub-border);border-radius:8px;font-size:0.82rem;{{ $bg }}display:flex;align-items:center;gap:0.4rem;">
                                                    @if ($isCorrectOption)
                                                        <svg style="width:16px;height:16px;color:#16a34a;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @elseif ($isSelected)
                                                        <svg style="width:16px;height:16px;color:#dc2626;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @else
                                                        <span style="width:16px;height:16px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0;display:block;"></span>
                                                    @endif
                                                    <span style="color:var(--hub-ink);">{{ $option['text'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif (in_array($question['type'], ['theory', 'practical']))
                                        <div style="margin-top:0.5rem;padding:0.5rem 0.75rem;background:var(--hub-surface);border-radius:8px;font-size:0.82rem;">
                                            <p style="margin:0;font-size:0.72rem;font-weight:600;color:var(--hub-muted);text-transform:uppercase;">Your Answer:</p>
                                            <p style="margin:0.2rem 0 0;color:var(--hub-ink);white-space:pre-wrap;">{{ $question['user_answer']['text'] ?? 'No answer provided' }}</p>
                                        </div>
                                    @endif

                                    @if (!empty($question['explanation']))
                                        <div style="margin-top:0.5rem;padding:0.5rem 0.75rem;background:#f0f9ff;border-left:3px solid #0ea5e9;border-radius:0 8px 8px 0;font-size:0.8rem;">
                                            <p style="margin:0;font-weight:600;color:#0369a1;font-size:0.72rem;">Explanation</p>
                                            <p style="margin:0.15rem 0 0;color:#334155;">{{ $question['explanation'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif

            <div style="text-align:center;padding:1rem 0;">
                <a href="{{ route('filament.student.pages.quizzes') }}" class="hub-btn hub-btn-primary" style="font-size:0.85rem;text-decoration:none;">Back to Quizzes</a>
            </div>

        @elseif (!empty($questions))
            {{-- =================== QUIZ FORM =================== --}}
            <section class="hub-card" style="padding:0.75rem 1rem;">
                <p class="hub-eyebrow">{{ $quiz['course'] }}</p>
                <h2 class="hub-title" style="font-size:1.1rem;">{{ $quiz['title'] }}</h2>
                @if (!empty($quiz['description']))
                    <p class="hub-copy" style="margin-top:0.2rem;">{{ $quiz['description'] }}</p>
                @endif
                <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:0.5rem;">
                    <span style="font-size:0.75rem;color:var(--hub-muted);">{{ count($questions) }} Questions</span>
                    @if ($quiz['time_limit'])
                        <span style="font-size:0.75rem;color:var(--hub-muted);">⏱ {{ $quiz['time_limit'] }} minutes</span>
                    @endif
                    <span style="font-size:0.75rem;color:var(--hub-muted);">Pass: {{ $quiz['pass_percentage'] }}%</span>
                </div>
            </section>

            <div x-data="{
                currentQuestion: 0,
                totalQuestions: {{ count($questions) }},
                @if ($quiz['time_limit'])
                    timeLimit: {{ $quiz['time_limit'] * 60 }},
                    timeRemaining: {{ $quiz['time_limit'] * 60 }},
                    timerInterval: null,
                    startTimer() {
                        this.timerInterval = setInterval(() => {
                            this.timeRemaining--;
                            if (this.timeRemaining <= 0) {
                                clearInterval(this.timerInterval);
                                $wire.submitQuiz();
                            }
                        }, 1000);
                    },
                    formatTime(seconds) {
                        const m = Math.floor(seconds / 60);
                        const s = seconds % 60;
                        return m.toString().padStart(2, '0') + ':' + s.toString().padStart(2, '0');
                    },
                @endif
            }" @if ($quiz['time_limit']) x-init="startTimer()" @endif>

                {{-- Timer Bar --}}
                @if ($quiz['time_limit'])
                    <div class="hub-quiz-timer-bar" style="position:sticky;top:0;z-index:50;background:white;padding:0.5rem 1rem;border-bottom:1px solid var(--hub-border);display:flex;justify-content:space-between;align-items:center;border-radius:12px;margin-bottom:0.5rem;">
                        <span style="font-size:0.8rem;font-weight:600;color:var(--hub-ink);">Question <span x-text="currentQuestion + 1"></span> of {{ count($questions) }}</span>
                        <span style="font-size:0.85rem;font-weight:700;padding:0.25rem 0.75rem;border-radius:20px;" :style="timeRemaining <= 60 ? 'background:#fef2f2;color:#dc2626;' : 'background:#f0fdf4;color:#15803d;'" x-text="formatTime(timeRemaining)"></span>
                    </div>
                @endif

                {{-- Progress Bar --}}
                <div style="background:var(--hub-border);border-radius:99px;height:6px;overflow:hidden;margin-bottom:0.75rem;">
                    <div style="height:100%;border-radius:99px;background:var(--hub-primary);transition:width 0.3s ease;" :style="'width:' + (((currentQuestion + 1) / totalQuestions) * 100) + '%'"></div>
                </div>

                {{-- Questions --}}
                @foreach ($questions as $index => $question)
                    <div x-show="currentQuestion === {{ $index }}" x-cloak class="hub-card hub-quiz-question-card" style="padding:1.25rem;">
                        <div style="display:flex;gap:0.5rem;align-items:flex-start;margin-bottom:1rem;">
                            <span style="background:var(--hub-primary);color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">{{ $index + 1 }}</span>
                            <div style="flex:1;">
                                <p style="font-weight:700;color:var(--hub-ink);margin:0;font-size:0.95rem;line-height:1.4;">{{ $question['question'] }}</p>
                                <span class="hub-chip" style="font-size:0.65rem;margin-top:0.35rem;{{ $question['type'] === 'multiple_choice' ? 'background:#dbeafe;color:#1e40af;' : ($question['type'] === 'theory' ? 'background:#fef3c7;color:#92400e;' : 'background:#ede9fe;color:#5b21b6;') }}">{{ ucfirst(str_replace('_', ' ', $question['type'])) }} &middot; {{ $question['points'] }} {{ $question['points'] === 1 ? 'point' : 'points' }}</span>
                            </div>
                        </div>

                        @if ($question['type'] === 'multiple_choice')
                            <div style="display:flex;flex-direction:column;gap:0.5rem;">
                                @foreach ($question['options'] as $option)
                                    <label class="hub-quiz-option" style="display:flex;align-items:center;gap:0.6rem;padding:0.65rem 0.85rem;border:2px solid var(--hub-border);border-radius:10px;cursor:pointer;transition:all 0.15s;font-size:0.88rem;" :style="$wire.answers[{{ $question['id'] }}] == '{{ $option['id'] }}' ? 'border-color:var(--hub-primary);background:#f0fdfa;' : ''" onmouseover="if(!this.querySelector('input').checked)this.style.borderColor='#94a3b8'" onmouseout="if(!this.querySelector('input').checked)this.style.borderColor='var(--hub-border)'">
                                        <input type="radio" name="question_{{ $question['id'] }}" value="{{ $option['id'] }}" wire:model="answers.{{ $question['id'] }}" style="accent-color:var(--hub-primary);width:18px;height:18px;flex-shrink:0;">
                                        <span style="color:var(--hub-ink);">{{ $option['text'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($question['type'] === 'theory')
                            <textarea wire:model="answers.{{ $question['id'] }}" rows="6" placeholder="Write your answer here..." style="width:100%;padding:0.75rem;border:2px solid var(--hub-border);border-radius:10px;font-size:0.88rem;resize:vertical;font-family:inherit;color:var(--hub-ink);transition:border-color 0.15s;" onfocus="this.style.borderColor='var(--hub-primary)'" onblur="this.style.borderColor='var(--hub-border)'"></textarea>
                        @elseif ($question['type'] === 'practical')
                            <div style="margin-bottom:0.5rem;padding:0.5rem 0.75rem;background:#ede9fe;border-radius:8px;font-size:0.8rem;color:#5b21b6;">
                                <strong>Practical Task:</strong> Write your code or solution below.
                            </div>
                            <textarea wire:model="answers.{{ $question['id'] }}" rows="10" placeholder="Write your code or solution here..." style="width:100%;padding:0.75rem;border:2px solid var(--hub-border);border-radius:10px;font-size:0.85rem;resize:vertical;font-family:'Courier New',monospace;color:var(--hub-ink);background:#fafafa;transition:border-color 0.15s;" onfocus="this.style.borderColor='var(--hub-primary)'" onblur="this.style.borderColor='var(--hub-border)'"></textarea>
                        @endif

                        {{-- Navigation --}}
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;flex-wrap:wrap;gap:0.5rem;">
                            <button x-show="currentQuestion > 0" @click="currentQuestion--" type="button" class="hub-btn" style="font-size:0.82rem;padding:0.45rem 1rem;background:var(--hub-surface);color:var(--hub-ink);border:1px solid var(--hub-border);border-radius:8px;">← Previous</button>
                            <span x-show="currentQuestion === 0"></span>

                            @if ($index < count($questions) - 1)
                                <button @click="currentQuestion++" type="button" class="hub-btn hub-btn-primary" style="font-size:0.82rem;padding:0.45rem 1rem;border-radius:8px;">Next →</button>
                            @else
                                <button wire:click="submitQuiz" type="button" class="hub-btn" style="font-size:0.82rem;padding:0.5rem 1.5rem;background:#15803d;color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer;" wire:confirm="Are you sure you want to submit this quiz? You cannot change your answers after submission." wire:loading.attr="disabled">
                                    <span wire:loading.remove>Submit Quiz</span>
                                    <span wire:loading>Submitting...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Question Navigation Dots --}}
                <div class="hub-quiz-nav-dots" style="display:flex;justify-content:center;gap:0.35rem;flex-wrap:wrap;padding:0.75rem 0;">
                    @foreach ($questions as $index => $question)
                        <button @click="currentQuestion = {{ $index }}" type="button" style="width:28px;height:28px;border-radius:50%;border:2px solid var(--hub-border);font-size:0.7rem;font-weight:700;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;justify-content:center;" :style="currentQuestion === {{ $index }} ? 'background:var(--hub-primary);color:white;border-color:var(--hub-primary);' : ($wire.answers[{{ $question['id'] }}] ? 'background:#dcfce7;color:#15803d;border-color:#86efac;' : '')">{{ $index + 1 }}</button>
                    @endforeach
                </div>
            </div>
        @else
            <section class="hub-card" style="text-align:center;padding:2rem;">
                <p class="hub-copy">No quiz available or quiz has already been completed.</p>
                <a href="{{ route('filament.student.pages.quizzes') }}" class="hub-btn hub-btn-primary" style="margin-top:1rem;font-size:0.85rem;text-decoration:none;">Back to Quizzes</a>
            </section>
        @endif
    </div>
</x-filament-panels::page>
