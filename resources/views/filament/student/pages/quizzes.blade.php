<x-filament-panels::page>
    <div class="space-y-5 font-sans">
        {{-- Header Quyl Hero Banner --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-[#7C3AED] dark:bg-purple-900/30 dark:text-purple-300 border border-purple-100 dark:border-purple-800">
                    Knowledge Verification
                </span>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    Quiz Centre
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Test your knowledge, reinforce core course topics, and unlock achievement XP.
                </p>
            </div>
        </div>

        {{-- Desktop Table Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden hidden md:block">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-3 px-4">Quiz</th>
                        <th class="py-3 px-3 text-center">Questions</th>
                        <th class="py-3 px-3 text-center">Time Limit</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Score</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    @forelse ($quizzes as $quiz)
                        @php
                            $statusPill = match ($quiz['status']) {
                                'completed' => ($quiz['passed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200' : 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200'),
                                'retake_allowed' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 border-sky-200',
                                'in_progress' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200',
                                'scheduled' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200',
                                default => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-800 dark:text-slate-100">{{ $quiz['title'] }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $quiz['course'] }}</p>
                            </td>
                            <td class="py-3.5 px-3 text-center text-slate-500 dark:text-slate-400">
                                {{ $quiz['question_count'] }}
                            </td>
                            <td class="py-3.5 px-3 text-center text-slate-500 dark:text-slate-400">
                                {{ $quiz['time_limit'] ? $quiz['time_limit'] . 'm' : 'No limit' }}
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusPill }}">
                                    {{ $quiz['status_label'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-center font-extrabold {{ $quiz['score'] !== null ? ($quiz['passed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400') : 'text-slate-400' }}">
                                {{ $quiz['score'] !== null ? $quiz['score'] . '%' : '-' }}
                                @if(!empty($quiz['is_retake']))
                                    <span class="text-[10px] text-purple-600 dark:text-purple-400 block font-normal">2nd Try</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if ($quiz['status'] === 'retake_allowed')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-xs transition-colors">
                                        Retake (2nd Try)
                                    </a>
                                @elseif ($quiz['status'] === 'completed')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 transition-colors">
                                        View Results
                                    </a>
                                @elseif ($quiz['status'] === 'in_progress')
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-xs font-bold text-white bg-[#7C3AED] hover:bg-[#6D28D9] shadow-xs transition-colors">
                                        Continue
                                    </a>
                                @elseif ($quiz['status'] === 'scheduled')
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">Upcoming</span>
                                @else
                                    <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                                       class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-xs font-bold text-white bg-[#7C3AED] hover:bg-[#6D28D9] shadow-xs transition-colors">
                                        Take Quiz &rarr;
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No quizzes available. Enroll in a course to see quizzes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden space-y-3">
            @forelse ($quizzes as $quiz)
                @php
                    $statusPill = match ($quiz['status']) {
                        'completed' => ($quiz['passed'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200' : 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200'),
                        'retake_allowed' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 border-sky-200',
                        'in_progress' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200',
                        'scheduled' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200',
                        default => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">{{ $quiz['title'] }}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $quiz['course'] }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusPill }} flex-shrink-0">
                            {{ $quiz['status_label'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl p-2.5 text-center text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Questions</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $quiz['question_count'] }}</span>
                        </div>
                        <div class="border-x border-slate-200 dark:border-slate-700">
                            <span class="text-[10px] text-slate-400 block font-semibold">Time</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $quiz['time_limit'] ? $quiz['time_limit'] . 'm' : 'None' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">Score</span>
                            <span class="font-bold {{ $quiz['score'] !== null ? ($quiz['passed'] ? 'text-emerald-600' : 'text-rose-600') : 'text-slate-400' }}">
                                {{ $quiz['score'] !== null ? $quiz['score'] . '%' : '-' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        @if ($quiz['status'] === 'retake_allowed')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               class="w-full flex items-center justify-center py-2 rounded-full text-xs font-bold text-white bg-sky-600 shadow-xs">
                                Retake Quiz (2nd Try)
                            </a>
                        @elseif ($quiz['status'] === 'completed')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               class="w-full flex items-center justify-center py-2 rounded-full text-xs font-bold text-slate-700 bg-slate-100 dark:bg-slate-800 dark:text-slate-300">
                                View Results
                            </a>
                        @elseif ($quiz['status'] === 'in_progress')
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               class="w-full flex items-center justify-center py-2 rounded-full text-xs font-bold text-white bg-[#7C3AED] shadow-xs">
                                Continue Quiz
                            </a>
                        @elseif ($quiz['status'] === 'scheduled')
                            <div class="w-full flex items-center justify-center py-2 rounded-full text-xs font-bold text-slate-400 bg-slate-100 dark:bg-slate-800">
                                Upcoming Quiz
                            </div>
                        @else
                            <a href="{{ route('filament.student.pages.take-quiz', ['quiz' => $quiz['id']]) }}"
                               class="w-full flex items-center justify-center py-2 rounded-full text-xs font-bold text-white bg-[#7C3AED] shadow-xs">
                                Take Quiz &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 text-center border border-slate-100 dark:border-slate-800">
                    <p class="text-xs text-slate-400">No quizzes available.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
