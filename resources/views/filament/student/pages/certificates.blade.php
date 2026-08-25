<x-filament-panels::page>
    <div class="space-y-5 font-sans">
        {{-- Header Quyl Banner --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    Achievements &amp; Credentials
                </span>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    My Certificates
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Certificates you have earned for completing courses. Each one carries a public verification link.
                </p>
            </div>
        </div>

        @if (count($certificates) === 0)
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-10 text-center border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 text-[#7C3AED] dark:bg-purple-900/30 dark:text-purple-300 mx-auto flex items-center justify-center">
                    <x-heroicon-o-academic-cap class="w-7 h-7" />
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">No certificates yet</h3>
                <p class="text-xs text-slate-400 max-w-md mx-auto">
                    Enroll in a course and pass all of its quizzes — a "Claim Certificate" button will then appear on the Courses page.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($certificates as $certificate)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300 flex items-center justify-center flex-shrink-0">
                                    <x-heroicon-o-academic-cap class="w-5 h-5" />
                                </span>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white truncate">
                                    {{ $certificate['course_title'] }}
                                </h3>
                            </div>
                            <p class="text-xs text-slate-400">
                                {{ $certificate['course_code'] }} · Issued {{ $certificate['issued_at'] }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Verification code:
                                <code class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $certificate['verification_code'] }}</code>
                            </p>
                            <p class="text-[11px] text-slate-400 truncate">
                                Verify at:
                                <a href="{{ $certificate['verification_url'] }}" target="_blank" rel="noopener" class="text-[#7C3AED] hover:underline">{{ $certificate['verification_url'] }}</a>
                            </p>
                        </div>

                        <a href="{{ $certificate['download_url'] }}" target="_blank" rel="noopener" 
                           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold text-white bg-[#7C3AED] hover:bg-[#6D28D9] shadow-xs transition-colors flex-shrink-0">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            <span>Download PDF</span>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
