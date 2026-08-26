<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- Header Quyl SaaS Hero Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-[#7C3AED] dark:bg-purple-900/30 dark:text-purple-300 border border-purple-100 dark:border-purple-800">
                    <span>Course Catalog &amp; Enrollment</span>
                </div>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    Available Courses
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Pick up to two active courses to enroll in and manage your curriculum on thinker HUB.
                </p>
            </div>
            <div class="flex items-center">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    🎓 Enrolled: {{ $enrolledCount }}/2
                </span>
            </div>
        </div>

        {{-- Multi-Column Course Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse ($courses as $course)
                <article class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between gap-4 group">
                    <div class="space-y-3">
                        {{-- Top Code & Status Row --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $course['code'] }}
                            </span>
                            
                            @if (! $course['is_active'])
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                            @elseif ($course['enrolled'])
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">✓ Enrolled</span>
                            @elseif (! $course['is_open_enrollment'])
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">🔒 Locked</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">● Open</span>
                            @endif
                        </div>

                        {{-- Course Title & Summary --}}
                        <div>
                            <h3 class="text-base font-extrabold text-slate-800 dark:text-white line-clamp-1 group-hover:text-[#7C3AED] transition-colors">
                                {{ $course['title'] }}
                            </h3>

                            @if ($course['is_ongoing'] && ! empty($course['intake_name']))
                                <div class="mt-1.5">
                                    <span class="inline-flex items-center text-[11px] font-bold px-2 py-0.5 rounded-md bg-purple-50 text-[#7C3AED] dark:bg-purple-900/30 dark:text-purple-300 border border-purple-100 dark:border-purple-800">
                                        📌 Intake: {{ $course['intake_name'] }}
                                    </span>
                                </div>
                            @endif

                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">
                                {{ $course['summary'] }}
                            </p>
                        </div>

                        @if (!empty($course['description']))
                            <details class="text-xs pt-1">
                                <summary class="cursor-pointer font-bold text-[#7C3AED] hover:underline">View details</summary>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    {{ $course['description'] }}
                                </p>
                            </details>
                        @endif
                    </div>

                    <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        {{-- Course Stats / Rating Summary Strip --}}
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                ⭐ {{ $course['avg_rating'] > 0 ? number_format($course['avg_rating'], 1) : 'New' }}
                                <span class="text-slate-400 font-medium">
                                    ({{ $course['ratings_count'] }} {{ \Illuminate\Support\Str::plural('review', $course['ratings_count']) }})
                                </span>
                            </span>

                            @if ($course['enrolled'])
                                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                    Active Student
                                </span>
                            @endif
                        </div>

                        {{-- Actions Footer --}}
                        <div class="flex items-center justify-between gap-2 pt-1">
                            @if (! $course['is_active'])
                                <button type="button" disabled class="px-4 py-2 text-xs font-bold rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 cursor-not-allowed">
                                    Unavailable
                                </button>
                            @elseif ($course['enrolled'])
                                <div class="flex items-center gap-2 w-full justify-between">
                                    @if ($course['certificate_claimed'])
                                        <a href="{{ url('/learn/certificates') }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            🎓 View Certificate
                                        </a>
                                    @elseif ($course['certificate_eligible'])
                                        <button type="button" wire:click="claimCertificate({{ $course['id'] }})" class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 cursor-pointer">
                                            🎓 Claim Certificate
                                        </button>
                                    @endif
                                    <button type="button" wire:click="unenroll({{ $course['id'] }})"
                                            class="px-3.5 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-300 rounded-full border border-rose-200 dark:border-rose-800 cursor-pointer transition-colors ml-auto">
                                        Unenroll
                                    </button>
                                </div>
                            @elseif (! $course['can_enroll'])
                                <button type="button" disabled class="px-4 py-2 text-xs font-bold rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 cursor-not-allowed">
                                    🔒 Locked
                                </button>
                            @elseif ($course['is_payable'])
                                @if ($course['has_multiple_options'] ?? false)
                                    @php
                                        $studentModalData = [
                                            'id' => $course['id'],
                                            'title' => $course['title'],
                                            'code' => $course['code'],
                                            'checkoutUrl' => $course['checkout_url'],
                                            'options' => $course['fee_options'] ?? [],
                                        ];
                                    @endphp
                                    <button type="button"
                                            @click="window.openCourseOptionModal(@js($studentModalData))"
                                            class="inline-flex items-center justify-center px-5 py-2 rounded-full text-xs font-bold text-white bg-[#7C3AED] hover:bg-[#6D28D9] shadow-xs transition-colors cursor-pointer">
                                        Enroll &amp; Pay &rarr;
                                    </button>
                                @else
                                    <a href="{{ $course['checkout_url'] }}"
                                       class="inline-flex items-center justify-center px-5 py-2 rounded-full text-xs font-bold text-white bg-[#7C3AED] hover:bg-[#6D28D9] shadow-xs transition-colors">
                                        Enroll &amp; Pay &rarr;
                                    </a>
                                @endif
                            @else
                                <button type="button" wire:click="enroll({{ $course['id'] }})"
                                        class="inline-flex items-center justify-center px-5 py-2 rounded-full text-xs font-bold text-white bg-[#7C3AED] hover:bg-[#6D28D9] shadow-xs transition-colors cursor-pointer">
                                    Enroll Free &rarr;
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white dark:bg-slate-900 rounded-2xl p-8 text-center border border-slate-100 dark:border-slate-800">
                    <p class="text-xs text-slate-400">No courses currently available in the catalog.</p>
                </div>
            @endforelse
        </div>
    </div>

    @include('partials.course-selection-modal')
</x-filament-panels::page>
