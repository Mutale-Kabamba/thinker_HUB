<div>
    @if($quiz)
        <div class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-xl font-bold text-white">{{ $quiz->title }}</h3>
                    @if($quiz->description)
                        <p class="text-sm text-slate-400 mt-1">{{ $quiz->description }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block">Required Pass Score</span>
                    <span class="text-sm font-semibold text-emerald-400">{{ $quiz->pass_percentage ?? 70 }}%</span>
                </div>
            </div>

            @if($submitted)
                <div class="p-6 rounded-xl border {{ $hasPassed ? 'bg-emerald-950/40 border-emerald-500/50' : 'bg-rose-950/40 border-rose-500/50' }} text-center space-y-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $hasPassed ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                        @if($hasPassed)
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </div>

                    <div>
                        <h4 class="text-xl font-bold {{ $hasPassed ? 'text-emerald-300' : 'text-rose-300' }}">
                            {{ $hasPassed ? 'Quiz Passed!' : 'Quiz Attempt Failed' }}
                        </h4>
                        <p class="text-slate-300 text-sm mt-1">
                            Your Score: <strong class="text-lg {{ $hasPassed ? 'text-emerald-400' : 'text-rose-400' }}">{{ $scorePercentage }}%</strong>
                            (Passing: {{ $quiz->pass_percentage ?? 70 }}%)
                        </p>
                    </div>

                    @if(! $hasPassed)
                        <div class="pt-2">
                            <button 
                                wire:click="retake"
                                class="px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-medium rounded-lg shadow transition text-sm inline-flex items-center space-x-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Retake Quiz</span>
                            </button>
                        </div>
                    @else
                        <p class="text-xs text-emerald-400 font-medium">Progress saved. The next lesson is now unlocked!</p>
                    @endif
                </div>
            @else
                <form wire:submit.prevent="submitQuiz" class="space-y-6">
                    @forelse($quiz->questions as $index => $question)
                        <div class="bg-slate-950/60 p-5 rounded-xl border border-slate-800 space-y-3">
                            <div class="flex items-start justify-between">
                                <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Question {{ $index + 1 }}</span>
                                <span class="text-xs text-slate-500">{{ $question->points ?? 1 }} pt{{ ($question->points ?? 1) > 1 ? 's' : '' }}</span>
                            </div>
                            <p class="text-slate-200 font-medium text-base">{{ $question->question }}</p>

                            @if($question->isMultipleChoice())
                                <div class="space-y-2 pt-2">
                                    @foreach($question->options as $option)
                                        <label class="flex items-center space-x-3 p-3 rounded-lg border border-slate-800 hover:bg-slate-800/50 cursor-pointer transition {{ ($answers[$question->id] ?? null) == $option->id ? 'bg-indigo-950/40 border-indigo-500/60' : '' }}">
                                            <input 
                                                type="radio" 
                                                name="q_{{ $question->id }}" 
                                                value="{{ $option->id }}"
                                                wire:model="answers.{{ $question->id }}"
                                                class="text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700"
                                            />
                                            <span class="text-sm text-slate-300">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <textarea 
                                    wire:model="answers.{{ $question->id }}"
                                    rows="3"
                                    placeholder="Type your response here..."
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-sm text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-500"
                                ></textarea>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-400 text-center py-6">No questions have been configured for this quiz yet.</p>
                    @endforelse

                    @if($quiz->questions->isNotEmpty())
                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit" 
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 font-medium rounded-lg text-white shadow-lg transition flex items-center space-x-2"
                            >
                                <span>Submit Quiz</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    @else
        <p class="text-slate-400">Quiz not found.</p>
    @endif
</div>
