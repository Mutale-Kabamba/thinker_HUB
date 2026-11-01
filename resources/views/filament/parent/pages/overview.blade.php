<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- ============================================================ --}}
        {{-- 1. HERO WELCOME & METRICS ROW                                --}}
        {{-- ============================================================ --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white p-6 sm:p-8 shadow-xl border border-indigo-700/50">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-12 -bottom-12 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 backdrop-blur-md border border-white/20 text-indigo-100">
                        <x-heroicon-o-shield-check class="w-4 h-4 text-emerald-400" />
                        <span>Protected Parents Hub</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Child Safety Mode Active</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        Welcome back, {{ auth()->user()->name }}
                    </h1>

                    <p class="text-sm sm:text-base text-indigo-100/90 leading-relaxed font-normal">
                        Track, guide, and celebrate your kids' educational journey in real-time. All kids courses maintain strict privacy, keeping your children safe from the public community.
                    </p>

                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <a 
                            href="{{ route('filament.parent.pages.manage-kids') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-white text-indigo-900 hover:bg-indigo-50 shadow-md hover:shadow-lg transition-all duration-150 transform hover:-translate-y-0.5"
                        >
                            <x-heroicon-o-user-plus class="w-4 h-4 text-indigo-600" />
                            <span>Manage My Kids</span>
                        </a>

                        <a 
                            href="{{ route('filament.parent.pages.kids-activities') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-indigo-700/60 hover:bg-indigo-600/60 text-white border border-white/20 backdrop-blur-md transition-all duration-150"
                        >
                            <x-heroicon-o-chart-bar-square class="w-4 h-4 text-indigo-200" />
                            <span>Detailed Activities</span>
                        </a>

                        <a 
                            href="{{ route('filament.parent.pages.course-parents') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-indigo-700/60 hover:bg-indigo-600/60 text-white border border-white/20 backdrop-blur-md transition-all duration-150"
                        >
                            <x-heroicon-o-user-group class="w-4 h-4 text-purple-200" />
                            <span>Course Parents Circle</span>
                        </a>
                    </div>
                </div>

                {{-- Metric cards cluster --}}
                <div class="grid grid-cols-2 gap-3 w-full lg:w-auto shrink-0">
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3.5 text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-1.5 text-xs text-indigo-200 font-semibold mb-1">
                            <x-heroicon-o-users class="w-4 h-4" />
                            <span>Kids Connected</span>
                        </div>
                        <div class="text-2xl font-black text-white">{{ $stats['total_kids'] }}</div>
                        <div class="text-[11px] text-indigo-200/80">Active in Portal</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3.5 text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-1.5 text-xs text-indigo-200 font-semibold mb-1">
                            <x-heroicon-o-academic-cap class="w-4 h-4" />
                            <span>Active Courses</span>
                        </div>
                        <div class="text-2xl font-black text-white">{{ $stats['active_courses'] }}</div>
                        <div class="text-[11px] text-indigo-200/80">Enrolled Classes</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3.5 text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-1.5 text-xs text-indigo-200 font-semibold mb-1">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            <span>Quizzes Passed</span>
                        </div>
                        <div class="text-2xl font-black text-emerald-400">{{ $stats['quizzes_passed'] }}</div>
                        <div class="text-[11px] text-indigo-200/80">Completed tests</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3.5 text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-1.5 text-xs text-indigo-200 font-semibold mb-1">
                            <x-heroicon-o-sparkles class="w-4 h-4" />
                            <span>Combined XP</span>
                        </div>
                        <div class="text-2xl font-black text-amber-300">{{ number_format($stats['total_xp']) }}</div>
                        <div class="text-[11px] text-indigo-200/80">Points earned</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- 2. KIDS QUICK-SWITCHER & PROFILE CARDS                       --}}
        {{-- ============================================================ --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-face-smile class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        <span>My Kids & Learning Profiles</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Click a child to focus their specific progress, or manage enrollment.</p>
                </div>

                {{-- Filter pill buttons --}}
                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl self-start sm:self-auto border border-slate-200 dark:border-slate-700">
                    <button 
                        wire:click="selectKid(null)"
                        type="button" 
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $selectedKidId === null ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-indigo-600' }}"
                    >
                        All Kids
                    </button>
                    @foreach ($kidsList as $k)
                        <button 
                            wire:click="selectKid({{ $k['id'] }})"
                            type="button" 
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $selectedKidId === $k['id'] ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-indigo-600' }}"
                        >
                            {{ $k['name'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if (empty($kidsList))
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <x-heroicon-o-user-plus class="w-8 h-8" />
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">No Children Added Yet</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Add your child or connect their existing student account to start monitoring their classes, progress, and tests.
                        </p>
                    </div>
                    <div>
                        <a 
                            href="{{ route('filament.parent.pages.manage-kids') }}" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-all"
                        >
                            <x-heroicon-o-plus-circle class="w-5 h-5" />
                            <span>Add Your First Child</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($kidsList as $kid)
                        <div 
                            wire:key="kid-card-{{ $kid['id'] }}"
                            class="bg-white dark:bg-slate-800 rounded-2xl p-5 border transition-all duration-200 {{ $selectedKidId === $kid['id'] ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-md' : 'border-slate-200 dark:border-slate-700/80 hover:border-indigo-300 dark:hover:border-indigo-700 shadow-xs hover:shadow-sm' }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full overflow-hidden bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-lg border-2 border-white dark:border-slate-700 shadow-xs shrink-0">
                                        @if ($kid['avatar'])
                                            <img src="{{ $kid['avatar'] }}" alt="{{ $kid['name'] }}" class="w-full h-full object-cover">
                                        @else
                                            <span>{{ strtoupper(substr($kid['name'], 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 dark:text-white text-base leading-tight">
                                            {{ $kid['name'] }}
                                        </h3>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                {{ $kid['relationship'] }}
                                            </span>
                                            <span>•</span>
                                            <span>{{ $kid['track'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <div class="inline-flex items-center gap-1 text-xs font-black text-amber-500 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                                        <x-heroicon-o-sparkles class="w-3.5 h-3.5 text-amber-500" />
                                        <span>{{ number_format($kid['xp']) }} XP</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Enrolled courses badge pills --}}
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 space-y-2">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    Enrolled Courses ({{ count($kid['courses']) }})
                                </div>
                                <div class="space-y-1.5">
                                    @forelse ($kid['courses'] as $course)
                                        <div class="flex items-center justify-between text-xs bg-slate-50 dark:bg-slate-700/50 px-2.5 py-1.5 rounded-lg border border-slate-100 dark:border-slate-700">
                                            <div class="flex items-center gap-1.5 truncate">
                                                @if ($course['is_for_kids'])
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Kids Course"></span>
                                                @else
                                                    <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
                                                @endif
                                                <span class="font-medium text-slate-700 dark:text-slate-200 truncate">{{ $course['title'] }}</span>
                                            </div>
                                            @if ($course['is_for_kids'])
                                                <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-sm shrink-0">
                                                    Kids Track
                                                </span>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 italic">Not enrolled in any course yet.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $kid['tasks_completed'] }} evaluations completed
                                </span>
                                <a 
                                    href="{{ route('filament.parent.pages.kids-activities', ['kid' => $kid['id']]) }}" 
                                    class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1"
                                >
                                    <span>View Progress</span>
                                    <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- 3. TWO-COLUMN: RECENT ACTIVITY STREAM & UPCOMING SCHEDULE    --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column (8 cols): Recent Kids Activity Feed --}}
            <div class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-clock class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            <span>Recent Learning Activities</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Real-time evaluations, quizzes, and project milestones across your children.</p>
                    </div>

                    <a 
                        href="{{ route('filament.parent.pages.kids-activities') }}" 
                        class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                    >
                        <span>View All ({{ count($recentActivities) }})</span>
                        <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                    </a>
                </div>

                @if (empty($recentActivities))
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 text-center space-y-2">
                        <x-heroicon-o-clipboard-document-list class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto" />
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">No Recent Activities Yet</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                            As your kids complete quizzes, submit assignments, and earn badges, you'll see every event here in real-time.
                        </p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($recentActivities as $act)
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600 transition-all shadow-xs flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    {{-- Type Icon Badge --}}
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $act['type'] === 'quiz' ? 'bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-300' : ($act['type'] === 'assignment' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-300') }}">
                                        @if ($act['type'] === 'quiz')
                                            <x-heroicon-o-academic-cap class="w-5 h-5" />
                                        @elseif ($act['type'] === 'assignment')
                                            <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                                        @else
                                            <x-heroicon-o-sparkles class="w-5 h-5" />
                                        @endif
                                    </div>

                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white">
                                                {{ $act['title'] }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                                {{ $act['kid_name'] }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $act['course'] }} • <span class="font-medium text-slate-400">{{ $act['time_ago'] }}</span>
                                        </p>

                                        @if ($act['feedback'])
                                            <div class="mt-1 text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-750 p-2 rounded-lg border border-slate-100 dark:border-slate-700">
                                                <span class="font-bold text-indigo-600 dark:text-indigo-400">Teacher Feedback:</span> {{ $act['feedback'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black {{ $act['passed'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300' }}">
                                        {{ $act['score_label'] }}
                                    </span>
                                    <div class="text-[10px] font-bold mt-1 {{ $act['passed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                        {{ $act['status_label'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right Column (4 cols): Upcoming Kids Classes & Quick Actions --}}
            <div class="lg:col-span-4 space-y-5">
                {{-- Upcoming Schedule Box --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <x-heroicon-o-calendar-days class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            <span>Upcoming Sessions</span>
                        </h3>
                    </div>

                    @if (empty($upcomingSessions))
                        <div class="text-center py-6 space-y-2">
                            <x-heroicon-o-calendar class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto" />
                            <p class="text-xs text-slate-500 dark:text-slate-400">No scheduled sessions in the next few days.</p>
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($upcomingSessions as $session)
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-indigo-700 dark:text-indigo-300 truncate">{{ $session['title'] }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $session['type'] }}</span>
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-300">{{ $session['course'] }}</div>
                                    <div class="text-[11px] text-slate-400 flex items-center justify-between pt-1">
                                        <span>{{ $session['date'] }}</span>
                                        <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $session['time_ago'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Safety & Privacy Guarantee Callout --}}
                <div class="bg-gradient-to-br from-emerald-900/10 to-teal-900/10 dark:from-emerald-950/40 dark:to-teal-950/40 border border-emerald-300 dark:border-emerald-800/80 rounded-2xl p-5 space-y-2.5">
                    <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                        <x-heroicon-o-shield-check class="w-5 h-5 text-emerald-600" />
                        <span>COPPA & Child Safety Compliant</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Courses marked as <strong>For Kids</strong> automatically isolate enrolled children from adult community members and external strangers. Your kids cannot be searched or messaged in the public student community.
                    </p>
                    <div class="pt-1">
                        <a 
                            href="{{ route('filament.parent.pages.course-parents') }}" 
                            class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline flex items-center gap-1"
                        >
                            <span>Connect with fellow parents in same course</span>
                            <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
