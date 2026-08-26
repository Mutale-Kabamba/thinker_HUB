<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- Search Input Hero Header --}}
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <div class="space-y-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800">
                    Workspace Search
                </span>
                <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Find Anything Fast
                </h1>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Instant lookup across your courses, enrolled students, scheduled sessions, and assignments.
                </p>
            </div>

            <div class="relative max-w-2xl pt-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none pt-2">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-slate-400" />
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="query" 
                    placeholder="Type keywords (course title, student name, session date)…" 
                    class="w-full pl-11 pr-4 py-3 text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition shadow-inner"
                />
            </div>
        </div>

        @if (trim($query) !== '')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Courses Results --}}
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-book-open class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                            <span>My Courses</span>
                        </h3>
                        <span class="text-xs font-bold text-slate-400">{{ count($results['courses']) }} found</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse ($results['courses'] as $item)
                            <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-teal-50/40 dark:hover:bg-slate-800 transition">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-xs text-slate-900 dark:text-white">
                                        {{ $item['code'] }} &bull; {{ $item['title'] }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $item['is_active'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $item['is_active'] ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-400 font-medium">No courses found matching "{{ $query }}"</p>
                        @endforelse
                    </div>
                </div>

                {{-- Students Results --}}
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-user-group class="w-4 h-4 text-sky-600 dark:text-sky-400" />
                            <span>Enrolled Students</span>
                        </h3>
                        <span class="text-xs font-bold text-slate-400">{{ count($results['students']) }} found</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse ($results['students'] as $item)
                            <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-sky-50/40 dark:hover:bg-slate-800 transition">
                                <p class="font-bold text-xs text-slate-900 dark:text-white">{{ $item['name'] }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $item['email'] }}</p>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-400 font-medium">No students found matching "{{ $query }}"</p>
                        @endforelse
                    </div>
                </div>

                {{-- Scheduled Sessions Results --}}
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4 md:col-span-2 lg:col-span-1">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-calendar-days class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            <span>Live Sessions</span>
                        </h3>
                        <span class="text-xs font-bold text-slate-400">{{ count($results['sessions']) }} found</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse ($results['sessions'] as $item)
                            <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-purple-50/40 dark:hover:bg-slate-800 transition space-y-1">
                                <p class="font-bold text-xs text-slate-900 dark:text-white">{{ $item['title'] }}</p>
                                <p class="text-[11px] text-slate-400">{{ $item['course'] }} &bull; {{ $item['date'] }}</p>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-400 font-medium">No sessions found matching "{{ $query }}"</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
