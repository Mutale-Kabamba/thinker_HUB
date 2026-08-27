<x-filament-panels::page>
    <div class="hub-shell safe-pb">
        @if ($submitted && !empty($results))
            {{-- =================== RESULTS VIEW =================== --}}
            <section class="hub-card text-center p-6 sm:p-8">
                <div class="mb-4">
                    @if ($results['passed'])
                        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 inline-flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400 m-0">Passed!</h2>
                    @else
                        <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 inline-flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 m-0">Not Passed</h2>
                    @endif
                </div>

                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ $quiz['course'] }}</p>

                @if (!empty($results['is_retake']))
                    <div class="my-2 mx-auto max-w-md p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl text-xs text-blue-700 dark:text-blue-300">
                        <span class="font-bold">⭐ 2nd Attempt (Retake):</span> Recorded marks are capped at the passing mark ({{ $quiz['pass_percentage'] }}%).
                    </div>
                @endif

                <div class="flex justify-center gap-6 sm:gap-8 flex-wrap my-4">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 min-w-[110px]">
                        <p class="text-3xl font-black text-slate-900 dark:text-white m-0">{{ $results['percentage'] }}%</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Score</p>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 min-w-[110px]">
                        <p class="text-3xl font-black text-slate-900 dark:text-white m-0">{{ $results['score'] }}/{{ $results['total'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Points</p>
                    </div>
                </div>

                <p class="text-xs text-slate-400 dark:text-slate-500 m-0">Pass mark: {{ $quiz['pass_percentage'] }}% &middot; Completed: {{ $results['completed_at'] }}</p>
            </section>

            @if (!empty($quiz['show_results']) && !empty($questions))
                <section class="hub-card p-4 sm:p-6 mt-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white m-0 mb-4">Review Answers</h3>
                    @foreach ($questions as $index => $question)
                        <div class="py-4 {{ !$loop->last ? 'border-b border-slate-100 dark:border-slate-800' : '' }}">
                            <div class="flex gap-3 items-start">
                                <span class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">{{ $index + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-900 dark:text-white m-0 text-sm leading-relaxed">{{ $question['question'] }}</p>
                                    <span class="hub-chip mt-1 inline-flex {{ $question['type'] === 'multiple_choice' ? 'hub-chip-blue' : ($question['type'] === 'theory' ? 'hub-chip-amber' : 'hub-chip-primary') }}">{{ ucfirst(str_replace('_', ' ', $question['type'])) }} &middot; {{ $question['points'] }} pts</span>

                                    @if ($question['type'] === 'multiple_choice' && !empty($question['options']))
                                        <div class="mt-3 flex flex-col gap-2">
                                            @foreach ($question['options'] as $option)
                                                @php
                                                    $isSelected = ($question['user_answer']['option_id'] ?? null) == $option['id'];
                                                    $isCorrectOption = !empty($option['is_correct']);
                                                    $optClass = 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900';
                                                    if ($isCorrectOption) $optClass = 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200';
                                                    elseif ($isSelected && !$isCorrectOption) $optClass = 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-700 text-rose-800 dark:text-rose-200';
                                                @endphp
                                                <div class="p-3 rounded-xl border {{ $optClass }} flex items-center gap-2.5 text-xs sm:text-sm">
                                                    @if ($isCorrectOption)
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @elseif ($isSelected)
                                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @else
                                                        <span class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600 shrink-0 inline-block"></span>
                                                    @endif
                                                    <span class="text-slate-800 dark:text-slate-200">{{ $option['text'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif (in_array($question['type'], ['theory', 'practical']))
                                        <div class="mt-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 text-xs sm:text-sm">
                                            <p class="m-0 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Your Answer:</p>
                                            <p class="m-0 mt-1 text-slate-800 dark:text-slate-200 whitespace-pre-wrap">{{ $question['user_answer']['text'] ?? 'No answer provided' }}</p>
                                        </div>
                                    @endif

                                    @if (!empty($question['explanation']))
                                        <div class="mt-3 p-3 bg-sky-50 dark:bg-sky-950/40 border-l-4 border-sky-500 rounded-r-xl text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                                            <p class="m-0 font-bold text-sky-700 dark:text-sky-300 text-xs">Explanation</p>
                                            <p class="m-0 mt-1">{{ $question['explanation'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif

            <div class="text-center py-6">
                <a href="{{ route('filament.student.pages.quizzes') }}" class="btn-primary min-h-[46px] px-8 text-sm">Back to Quizzes</a>
            </div>

        @elseif (!empty($questions))
            {{-- =================== QUIZ FORM =================== --}}
            @if ($isRetake)
                <div class="mb-3 p-3 bg-blue-500/10 border border-blue-500/30 rounded-xl text-xs sm:text-sm text-blue-700 dark:text-blue-300">
                    <strong>⭐ 2nd Attempt / Retake:</strong> You are taking your 2nd attempt for this quiz. (Recorded mark is capped at the passing mark of {{ $quiz['pass_percentage'] }}%).
                </div>
            @endif

            <section class="hub-card p-4 sm:p-5 mb-3">
                <p class="hub-eyebrow">{{ $quiz['course'] }}</p>
                <h2 class="hub-title text-base sm:text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ $quiz['title'] }}</h2>
                @if (!empty($quiz['description']))
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $quiz['description'] }}</p>
                @endif
                <div class="flex gap-4 flex-wrap mt-3 text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <span>{{ count($questions) }} Questions</span>
                    @if ($quiz['time_limit'])
                        <span>⏱ {{ $quiz['time_limit'] }} minutes</span>
                    @endif
                    <span>Pass: {{ $quiz['pass_percentage'] }}%</span>
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

                {{-- Sticky Timer & Question Header Bar --}}
                <div class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-md flex justify-between items-center mb-3">
                    <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Question <span x-text="currentQuestion + 1"></span> of {{ count($questions) }}</span>
                    @if ($quiz['time_limit'])
                        <span class="text-xs sm:text-sm font-bold px-3 py-1 rounded-full font-mono transition" :class="timeRemaining <= 60 ? 'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400 animate-pulse' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'" x-text="formatTime(timeRemaining)"></span>
                    @endif
                </div>

                {{-- Progress Bar --}}
                <div class="bg-slate-200 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden mb-4">
                    <div class="h-full rounded-full bg-teal-600 dark:bg-teal-400 transition-all duration-300 ease-out" :style="'width:' + (((currentQuestion + 1) / totalQuestions) * 100) + '%'"></div>
                </div>

                {{-- Questions --}}
                @foreach ($questions as $index => $question)
                    <div x-show="currentQuestion === {{ $index }}" x-cloak class="hub-card p-4 sm:p-6 mb-4">
                        <div class="flex gap-3 items-start mb-4">
                            <span class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs sm:text-sm font-bold shrink-0 shadow-xs">{{ $index + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-900 dark:text-white m-0 text-sm sm:text-base leading-relaxed">{{ $question['question'] }}</p>
                                <span class="hub-chip mt-1.5 inline-flex {{ $question['type'] === 'multiple_choice' ? 'hub-chip-blue' : ($question['type'] === 'theory' ? 'hub-chip-amber' : 'hub-chip-primary') }}">{{ ucfirst(str_replace('_', ' ', $question['type'])) }} &middot; {{ $question['points'] }} {{ $question['points'] === 1 ? 'point' : 'points' }}</span>
                            </div>
                        </div>

                        @if ($question['type'] === 'multiple_choice')
                            <div class="flex flex-col gap-2.5">
                                @foreach ($question['options'] as $option)
                                    <label
                                        class="flex items-center gap-3 p-3 sm:p-4 rounded-xl border-2 cursor-pointer transition min-h-[48px] bg-white dark:bg-slate-900 active:scale-99"
                                        :class="$wire.answers[{{ $question['id'] }}] == '{{ $option['id'] }}' ? 'border-teal-600 bg-teal-50/50 dark:bg-teal-950/20 dark:border-teal-400 shadow-sm' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                                    >
                                        <input type="radio" name="question_{{ $question['id'] }}" value="{{ $option['id'] }}" wire:model="answers.{{ $question['id'] }}" class="text-teal-600 focus:ring-teal-500 w-5 h-5 shrink-0">
                                        <span class="text-xs sm:text-sm font-medium text-slate-900 dark:text-slate-100 flex-1">{{ $option['text'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($question['type'] === 'theory')
                            <textarea wire:model="answers.{{ $question['id'] }}" rows="6" placeholder="Write your detailed answer here…" class="w-full p-3.5 border-2 border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:border-teal-500 focus:ring-0 transition resize-y"></textarea>
                        @elseif ($question['type'] === 'practical')
                            <div class="mb-2 p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-xl text-xs text-purple-700 dark:text-purple-300 font-medium">
                                <strong>Practical Task:</strong> Write your solution or code below.
                            </div>
                            <textarea wire:model="answers.{{ $question['id'] }}" rows="8" placeholder="Write your code or practical solution here…" class="w-full p-3.5 border-2 border-slate-200 dark:border-slate-800 rounded-xl text-xs sm:text-sm font-mono text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:border-teal-500 focus:ring-0 transition resize-y"></textarea>
                        @endif

                        {{-- Question Actions Navigation Bar --}}
                        <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 gap-3">
                            <button x-show="currentQuestion > 0" @click="currentQuestion--" type="button" class="btn-secondary min-h-[44px] px-5 text-xs sm:text-sm active:scale-95">← Previous</button>
                            <span x-show="currentQuestion === 0"></span>

                            @if ($index < count($questions) - 1)
                                <button @click="currentQuestion++" type="button" class="btn-primary min-h-[44px] px-6 text-xs sm:text-sm active:scale-95">Next →</button>
                            @else
                                <button wire:click="submitQuiz" type="button" class="inline-flex items-center justify-center min-h-[44px] px-6 py-2 rounded-full font-bold text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-md active:scale-95 transition" wire:confirm="Are you sure you want to submit this quiz? You cannot change your answers after submission." wire:loading.attr="disabled">
                                    <span wire:loading.remove>Submit Assessment</span>
                                    <span wire:loading>Submitting…</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Question Navigation Dots (Scrollable / Wrapping with 40px Tap Targets) --}}
                <div class="hub-quiz-nav-dots flex justify-center gap-2 flex-wrap py-3 max-w-full overflow-x-auto touch-scroll-x">
                    @foreach ($questions as $index => $question)
                        <button
                            @click="currentQuestion = {{ $index }}"
                            type="button"
                            class="w-9 h-9 min-w-[36px] min-h-[36px] rounded-xl border-2 font-bold text-xs flex items-center justify-center transition active:scale-95"
                            :class="currentQuestion === {{ $index }} ? 'bg-teal-600 text-white border-teal-600 shadow-sm' : ($wire.answers[{{ $question['id'] }}] ? 'bg-emerald-100 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300')"
                        >
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <section class="hub-card text-center p-8">
                <p class="text-sm text-slate-500 dark:text-slate-400">No quiz available or quiz has already been completed.</p>
                <a href="{{ route('filament.student.pages.quizzes') }}" class="btn-primary min-h-[46px] inline-flex items-center mt-4 px-6 text-sm">Back to Quizzes</a>
            </section>
        @endif
    </div>
</x-filament-panels::page>
