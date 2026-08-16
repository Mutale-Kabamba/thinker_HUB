<div class="hub-quiz-review-modal" style="font-family: inherit; color: var(--hub-ink, #0f172a);">
    @php
        $quiz = $attempt->quiz;
        $student = $attempt->user;
        $answers = $attempt->answers->keyBy('quiz_question_id');
        $questions = $quiz ? $quiz->questions()->with('options')->orderBy('sort_order')->orderBy('id')->get() : collect();
    @endphp

    {{-- Header Summary Card --}}
    <div style="background: linear-gradient(135deg, #0f766e 0%, #042f2e 100%); color: #ffffff; padding: 1.25rem; border-radius: 12px; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.85; color: #99f6e4;">
                    Quiz Submission Review
                </span>
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0.2rem 0; color: #ffffff;">
                    {{ $quiz?->title ?? 'Quiz Attempt' }}
                </h3>
                <p style="font-size: 0.8rem; margin: 0; opacity: 0.9; color: #ccfbf1;">
                    {{ $quiz?->course?->title ?? 'Course' }} &middot; Student: <strong style="color: #ffffff;">{{ $student?->name ?? 'Student' }}</strong> ({{ $student?->email }})
                </p>
            </div>

            {{-- Score & Status Badges --}}
            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(4px); padding: 0.5rem 0.85rem; border-radius: 8px; text-align: center; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <div style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700; color: #ccfbf1;">Score</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #ffffff;">
                        {{ $attempt->score ?? 0 }} / {{ $attempt->total_points ?? 0 }}
                    </div>
                </div>

                <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(4px); padding: 0.5rem 0.85rem; border-radius: 8px; text-align: center; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <div style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700; color: #ccfbf1;">Percentage</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: {{ $attempt->passed ? '#86efac' : '#fca5a5' }};">
                        {{ $attempt->percentage !== null ? $attempt->percentage . '%' : '—' }}
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                    <span style="display: inline-block; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 9999px; text-align: center; background: {{ $attempt->passed ? '#10b981' : '#ef4444' }}; color: #ffffff;">
                        {{ $attempt->passed ? '✓ PASSED' : '✗ FAILED' }}
                    </span>
                    @if ($attempt->is_retake)
                        <span style="display: inline-block; font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 9999px; text-align: center; background: #3b82f6; color: #ffffff;" title="Recorded mark is capped at passing score">
                            2nd Attempt (Capped)
                        </span>
                    @else
                        <span style="display: inline-block; font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 9999px; text-align: center; background: rgba(255,255,255,0.2); color: #ffffff;">
                            1st Attempt
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta info footer --}}
        <div style="display: flex; gap: 1rem; margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px solid rgba(255, 255, 255, 0.15); font-size: 0.72rem; color: #ccfbf1; flex-wrap: wrap;">
            <span>Passing Mark: <strong>{{ $quiz?->pass_percentage ?? 50 }}%</strong></span>
            @if ($attempt->is_retake && $attempt->raw_score !== null)
                <span>Raw Score: <strong>{{ $attempt->raw_score }} pts</strong></span>
            @endif
            <span>Started: <strong>{{ $attempt->started_at?->format('M d, Y h:i A') ?? '—' }}</strong></span>
            <span>Completed: <strong>{{ $attempt->completed_at?->format('M d, Y h:i A') ?? 'In progress' }}</strong></span>
        </div>
    </div>

    {{-- Questions Breakdown Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0;">
            Question-by-Question Breakdown ({{ $questions->count() }} Questions)
        </h4>
        <span style="font-size: 0.75rem; color: #64748b;">
            @php
                $correctCount = $answers->where('is_correct', true)->count();
            @endphp
            {{ $correctCount }} / {{ $questions->count() }} Correct Answers
        </span>
    </div>

    {{-- Question Cards List --}}
    @if ($questions->isEmpty())
        <div style="padding: 2rem; text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; color: #64748b; font-size: 0.85rem;">
            No questions found in this quiz.
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach ($questions as $qIndex => $question)
                @php
                    $answer = $answers->get($question->id);
                    $isAnswered = $answer !== null;
                    $isCorrect = $answer?->is_correct;
                    $selectedOptionId = $answer?->quiz_option_id;
                    $correctOption = $question->options->firstWhere('is_correct', true);
                @endphp

                <div style="background: #ffffff; border: 1.5px solid {{ $isCorrect ? '#10b981' : ($isAnswered ? '#ef4444' : '#cbd5e1') }}; border-radius: 10px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    {{-- Question Header Ribbon --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.6rem; height: 1.6rem; border-radius: 6px; background: #0f172a; color: #ffffff; font-size: 0.78rem; font-weight: 800;">
                                {{ $qIndex + 1 }}
                            </span>
                            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b;">
                                {{ ucfirst(str_replace('_', ' ', $question->type ?? 'multiple_choice')) }}
                            </span>
                            <span style="font-size: 0.72rem; background: #f1f5f9; color: #475569; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;">
                                {{ $question->points }} pts
                            </span>
                        </div>

                        <div>
                            @if ($isCorrect === true)
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.74rem; font-weight: 800; padding: 0.2rem 0.55rem; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 9999px;">
                                    ✓ Correct (+{{ $answer?->points_earned ?? $question->points }} pts)
                                </span>
                            @elseif ($isCorrect === false)
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.74rem; font-weight: 800; padding: 0.2rem 0.55rem; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 9999px;">
                                    ✗ Incorrect (0 / {{ $question->points }} pts)
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.74rem; font-weight: 600; padding: 0.2rem 0.55rem; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 9999px;">
                                    Not Answered (0 pts)
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Question Text --}}
                    <p style="font-size: 0.9rem; font-weight: 600; color: #1e293b; line-height: 1.5; margin: 0 0 0.85rem 0;">
                        {{ $question->question }}
                    </p>

                    {{-- Multiple Choice Options List --}}
                    @if ($question->options->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                            @foreach ($question->options as $optIndex => $option)
                                @php
                                    $isSelected = (int) $selectedOptionId === (int) $option->id;
                                    $isOptCorrect = (bool) $option->is_correct;
                                @endphp

                                <div style="
                                    display: flex;
                                    align-items: flex-start;
                                    justify-content: space-between;
                                    gap: 0.6rem;
                                    padding: 0.6rem 0.8rem;
                                    border-radius: 8px;
                                    font-size: 0.84rem;
                                    line-height: 1.4;
                                    @if ($isSelected && $isOptCorrect)
                                        background: #ecfdf5;
                                        border: 1.5px solid #10b981;
                                        color: #065f46;
                                    @elseif ($isSelected && !$isOptCorrect)
                                        background: #fef2f2;
                                        border: 1.5px solid #ef4444;
                                        color: #991b1b;
                                    @elseif ($isOptCorrect)
                                        background: #f0fdf4;
                                        border: 1.5px dashed #22c55e;
                                        color: #166534;
                                    @else
                                        background: #f8fafc;
                                        border: 1px solid #e2e8f0;
                                        color: #334155;
                                    @endif
                                ">
                                    <div style="display: flex; align-items: flex-start; gap: 0.5rem; flex: 1;">
                                        <span style="font-weight: 700; opacity: 0.75; min-width: 18px;">
                                            {{ chr(65 + $optIndex) }}.
                                        </span>
                                        <span style="font-weight: {{ $isSelected || $isOptCorrect ? '700' : '400' }};">
                                            {{ $option->option_text }}
                                        </span>
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0;">
                                        @if ($isSelected && $isOptCorrect)
                                            <span style="font-size: 0.68rem; font-weight: 800; background: #10b981; color: #ffffff; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                                ✓ Selected (Correct)
                                            </span>
                                        @elseif ($isSelected && !$isOptCorrect)
                                            <span style="font-size: 0.68rem; font-weight: 800; background: #ef4444; color: #ffffff; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                                ✗ Selected (Incorrect)
                                            </span>
                                        @elseif ($isOptCorrect)
                                            <span style="font-size: 0.68rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 0.15rem 0.45rem; border-radius: 4px; border: 1px solid #86efac;">
                                                ✓ Correct Answer
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Theory / Practical Free Text Response --}}
                    @if ($question->isTheory() || $question->isPractical() || (!$question->isMultipleChoice() && $question->options->isEmpty()))
                        <div style="margin-top: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.35rem;">
                                Student's Submitted Answer:
                            </div>
                            <div style="font-size: 0.85rem; color: #1e293b; white-space: pre-wrap; line-height: 1.6; font-family: monospace; background: #ffffff; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                                {{ $answer?->text_answer ?: '(No response submitted)' }}
                            </div>
                            @if ($answer?->feedback)
                                <div style="margin-top: 0.4rem; font-size: 0.75rem; color: #0f766e;">
                                    <strong>Instructor Feedback:</strong> {{ $answer->feedback }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Question Explanation --}}
                    @if (filled($question->explanation))
                        <div style="margin-top: 0.75rem; padding: 0.6rem 0.8rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; font-size: 0.78rem; color: #0369a1; line-height: 1.5;">
                            <strong style="color: #0284c7;">💡 Explanation:</strong> {{ $question->explanation }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
