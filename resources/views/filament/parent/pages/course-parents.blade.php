<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- ============================================================ --}}
        {{-- HEADER WITH PRIVACY NOTICE & SEARCH                          --}}
        {{-- ============================================================ --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                        <x-heroicon-o-user-group class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
                        <span>Course Parents Circle</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Connect with fellow parents whose children share the exact same course. Coordinate study groups, homework support, and class milestones.
                    </p>
                </div>

                {{-- Search Bar --}}
                <div class="relative w-full md:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Search parent or child name..." 
                        class="w-full text-xs pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                    @if ($search !== '')
                        <button 
                            wire:click="$set('search', '')" 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>

            {{-- Privacy Notice --}}
            <div class="flex items-center gap-2.5 text-xs text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 p-3 rounded-xl border border-indigo-200 dark:border-indigo-800/80">
                <x-heroicon-o-lock-closed class="w-5 h-5 shrink-0 text-indigo-600 dark:text-indigo-400" />
                <span>
                    <strong>Strict Course-Cohort Isolation:</strong> You can only view parents whose children are actively enrolled in the exact same course as your children. Other parents across the platform cannot see you, and children's profiles remain completely hidden from the public community.
                </span>
            </div>

            {{-- Course Filter Chips --}}
            @if (!empty($courses))
                <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 border-t border-slate-100 dark:border-slate-700/60">
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider shrink-0 mr-1">
                        Filter By Course:
                    </span>
                    <button 
                        wire:click="selectCourse(null)" 
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ is_null($selectedCourseId) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300' }}"
                    >
                        <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5" />
                        <span>All My Kids' Courses ({{ count($courses) }})</span>
                    </button>

                    @foreach ($courses as $c)
                        <button 
                            wire:click="selectCourse({{ $c['id'] }})" 
                            type="button"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $selectedCourseId === $c['id'] ? 'bg-indigo-600 text-white shadow-xs ring-2 ring-indigo-300 dark:ring-indigo-900' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300' }}"
                        >
                            <span>{{ $c['title'] }}</span>
                            @if ($c['is_for_kids'])
                                <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">Kids Track</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- PEER PARENTS GRID                                            --}}
        {{-- ============================================================ --}}
        @if (empty($peerParents))
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-12 border border-slate-200 dark:border-slate-700 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400">
                    <x-heroicon-o-user-plus class="w-8 h-8" />
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">No Fellow Parents in this Cohort Yet</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @if ($search !== '' || $selectedCourseId)
                            No parents match the current filters. Try changing or clearing your search term.
                        @else
                            You are the pioneering parent in this course! As other families join the same course with their children, you will be able to connect with them here.
                        @endif
                    </p>
                </div>
                @if ($search !== '' || $selectedCourseId)
                    <div class="pt-2">
                        <button 
                            wire:click="$set('search', ''); $set('selectedCourseId', null)" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-all"
                        >
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                            <span>View All Course Parents</span>
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($peerParents as $parent)
                    <div wire:key="peer-parent-{{ $parent['id'] }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-between shadow-xs hover:border-slate-300 dark:hover:border-slate-600 transition-all">
                        <div class="space-y-4">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-13 h-13 rounded-full overflow-hidden bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-lg shrink-0 shadow-xs">
                                        @if ($parent['avatar'])
                                            <img src="{{ $parent['avatar'] }}" alt="{{ $parent['name'] }}" class="w-full h-full object-cover">
                                        @else
                                            <span>{{ strtoupper(substr($parent['name'], 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                            {{ $parent['name'] }}
                                        </h3>
                                        <div class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 mt-0.5">
                                            <x-heroicon-o-face-smile class="w-3.5 h-3.5" />
                                            <span>{{ $parent['children_summary'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                    Since {{ $parent['joined_date'] }}
                                </span>
                            </div>

                            {{-- Shared Courses --}}
                            <div class="space-y-1.5 pt-2">
                                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                    Shared Courses
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($parent['shared_courses'] as $courseTitle)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 dark:bg-slate-750 text-slate-700 dark:text-slate-300 border border-slate-100 dark:border-slate-700">
                                            <x-heroicon-o-academic-cap class="w-3 h-3 text-indigo-500" />
                                            <span>{{ $courseTitle }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <a 
                                    href="{{ route('filament.parent.pages.parents-chat', ['direct' => $parent['id']]) }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all"
                                    title="Open Parents Forum Direct Message"
                                >
                                    <x-heroicon-o-chat-bubble-left-right class="w-3.5 h-3.5" />
                                    <span>Community Chat</span>
                                </a>

                                @if (!empty($parent['email']))
                                    <a 
                                        href="mailto:{{ $parent['email'] }}?subject=Thinker%20HUB%20Course%20Parents%20Circle" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 transition-all"
                                    >
                                        <x-heroicon-o-envelope class="w-3.5 h-3.5" />
                                        <span>Email</span>
                                    </a>
                                @endif

                                @if (!empty($parent['whatsapp']))
                                    <a 
                                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $parent['whatsapp']) }}" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-all"
                                    >
                                        <x-heroicon-o-chat-bubble-oval-left-ellipsis class="w-3.5 h-3.5" />
                                        <span>WhatsApp</span>
                                    </a>
                                @endif
                            </div>

                            <div class="text-[11px] font-bold text-slate-400 ml-auto flex items-center gap-1">
                                <x-heroicon-o-check-badge class="w-4 h-4 text-indigo-500" />
                                <span>Verified Parent</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
