<x-filament-panels::page>
    <div class="space-y-6 font-sans">
        {{-- ============================================================ --}}
        {{-- HEADER WITH ACTIONS                                          --}}
        {{-- ============================================================ --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <div class="space-y-1">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                    <x-heroicon-o-user-group class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
                    <span>Manage My Kids</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Add new child accounts, link registered students, and manage their course enrollments under your supervision.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button 
                    wire:click="openAddKidModal" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow-md transition-all duration-150"
                >
                    <x-heroicon-o-user-plus class="w-4 h-4" />
                    <span>Create Child Account</span>
                </button>

                <button 
                    wire:click="openLinkKidModal" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 transition-all duration-150"
                >
                    <x-heroicon-o-link class="w-4 h-4" />
                    <span>Link Existing Student</span>
                </button>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- KIDS CARDS GRID                                              --}}
        {{-- ============================================================ --}}
        @if (empty($kids))
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-12 border border-slate-200 dark:border-slate-700 text-center space-y-4">
                <div class="w-20 h-20 mx-auto rounded-full bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <x-heroicon-o-face-smile class="w-10 h-10" />
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">No Children Connected Yet</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Create an account for your child or link their existing Thinker HUB student login to start managing their learning path.
                    </p>
                </div>
                <div class="pt-2 flex justify-center gap-3">
                    <button 
                        wire:click="openAddKidModal" 
                        type="button" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-all"
                    >
                        <x-heroicon-o-plus class="w-4 h-4" />
                        <span>Create Child Account</span>
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($kids as $kid)
                    <div wire:key="manage-kid-{{ $kid['id'] }}" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-between shadow-xs hover:shadow-md transition-all">
                        <div class="space-y-4">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-14 h-14 rounded-full overflow-hidden bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-xl border-2 border-indigo-200 dark:border-indigo-800 shrink-0 shadow-xs">
                                        @if ($kid['avatar'])
                                            <img src="{{ $kid['avatar'] }}" alt="{{ $kid['name'] }}" class="w-full h-full object-cover">
                                        @else
                                            <span>{{ strtoupper(substr($kid['name'], 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                            {{ $kid['name'] }}
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[180px]">
                                            {{ $kid['email'] }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                {{ $kid['relationship'] }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                                {{ $kid['track'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0 text-right">
                                    <div class="text-xs font-black text-amber-500 bg-amber-50 dark:bg-amber-950/40 px-2 py-1 rounded-full border border-amber-200 dark:border-amber-800">
                                        {{ number_format($kid['xp']) }} XP
                                    </div>
                                </div>
                            </div>

                            {{-- Unique Login Code Display & Copy --}}
                            @if (!empty($kid['unique_code']))
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/60 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kid Login Code:</span>
                                        <code class="px-2 py-0.5 rounded-md font-mono font-bold text-xs bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 shadow-2xs">
                                            {{ $kid['unique_code'] }}
                                        </code>
                                    </div>
                                    <button 
                                        type="button" 
                                        x-data="{ copied: false }" 
                                        x-on:click="navigator.clipboard.writeText('{{ $kid['unique_code'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors"
                                        title="Copy code for your child"
                                    >
                                        <span x-show="!copied" class="flex items-center gap-1">
                                            <x-heroicon-o-clipboard-document class="w-3.5 h-3.5" />
                                            <span>Copy</span>
                                        </span>
                                        <span x-show="copied" class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold" style="display: none;">
                                            <x-heroicon-o-check class="w-3.5 h-3.5" />
                                            <span>Copied!</span>
                                        </span>
                                    </button>
                                </div>
                            @endif

                            {{-- School, Grade & Age Badges --}}
                            @if ($kid['age'] || $kid['school'] || $kid['grade'])
                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-300">
                                    @if ($kid['age'])
                                        <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700/80 px-2 py-0.5 rounded-md">
                                            <x-heroicon-o-calendar class="w-3 h-3 text-slate-400" />
                                            <span>Age {{ $kid['age'] }}</span>
                                        </span>
                                    @endif
                                    @if ($kid['grade'])
                                        <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700/80 px-2 py-0.5 rounded-md">
                                            <x-heroicon-o-academic-cap class="w-3 h-3 text-slate-400" />
                                            <span>Grade: {{ $kid['grade'] }}</span>
                                        </span>
                                    @endif
                                    @if ($kid['school'])
                                        <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700/80 px-2 py-0.5 rounded-md truncate max-w-[200px]" title="{{ $kid['school'] }}">
                                            <x-heroicon-o-building-library class="w-3 h-3 text-slate-400 shrink-0" />
                                            <span class="truncate">{{ $kid['school'] }}</span>
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Enrolled Courses List --}}
                            <div class="space-y-2 pt-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Courses ({{ $kid['courses_count'] }})
                                    </span>
                                    <button 
                                        wire:click="openEnrollKidModal({{ $kid['id'] }})" 
                                        type="button" 
                                        class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                                    >
                                        <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                        <span>Enroll in Course</span>
                                    </button>
                                </div>

                                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                    @forelse ($kid['courses'] as $c)
                                        <div class="flex items-center justify-between text-xs bg-slate-50 dark:bg-slate-750 px-3 py-2 rounded-xl border border-slate-100 dark:border-slate-700">
                                            <div class="space-y-0.5 truncate">
                                                <div class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $c['title'] }}</div>
                                                @if ($c['intake'])
                                                    <div class="text-[10px] text-slate-400">Class: {{ $c['intake'] }}</div>
                                                @endif
                                            </div>
                                            @if ($c['is_for_kids'])
                                                <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800 shrink-0">
                                                    Kids Track
                                                </span>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-xs text-slate-400 italic p-3 bg-slate-50 dark:bg-slate-750 rounded-xl text-center">
                                            Not enrolled in any courses yet.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            @if ($kid['notes'])
                                <div class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-750 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">Notes:</span> {{ $kid['notes'] }}
                                </div>
                            @endif
                        </div>

                        {{-- Footer Action Buttons --}}
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <button 
                                    wire:click="switchToKid({{ $kid['id'] }})" 
                                    type="button" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 transition-all"
                                    title="Switch into {{ $kid['name'] }}'s student portal view"
                                >
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-3.5 h-3.5" />
                                    <span>Switch Account</span>
                                </button>

                                <button 
                                    wire:click="openEditKidModal({{ $kid['id'] }})" 
                                    type="button" 
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition-all"
                                >
                                    <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                                    <span>Edit</span>
                                </button>

                                <button 
                                    wire:confirm="Are you sure you want to unlink {{ $kid['name'] }}? Their student account and coursework will remain intact."
                                    wire:click="unlinkKid({{ $kid['id'] }})" 
                                    type="button" 
                                    class="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-all"
                                >
                                    <x-heroicon-o-link-slash class="w-3.5 h-3.5" />
                                    <span>Unlink</span>
                                </button>
                            </div>

                            <a 
                                href="{{ route('filament.parent.pages.kids-activities', ['kid' => $kid['id']]) }}" 
                                class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline"
                            >
                                <span>Activity Log</span>
                                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL: ADD CHILD ACCOUNT                                     --}}
        {{-- ============================================================ --}}
        @if ($showAddKidModal)
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <x-heroicon-o-user-plus class="w-5 h-5" />
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create Child Account</h3>
                        </div>
                        <button wire:click="$set('showAddKidModal', false)" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form wire:submit="createKid" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Child's Full Name *</label>
                            <input wire:model="newKidName" type="text" placeholder="e.g. Liam Zulu" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                            @error('newKidName') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Child Email / Login ID *</label>
                                <input wire:model="newKidEmail" type="email" placeholder="liam@gmail.com" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                @error('newKidEmail') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Set Child Password *</label>
                                <input wire:model="newKidPassword" type="password" placeholder="Min 6 characters" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                                @error('newKidPassword') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Age</label>
                                <input wire:model="newKidAge" type="number" min="3" max="18" placeholder="e.g. 10" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                @error('newKidAge') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Grade</label>
                                <input wire:model="newKidGrade" type="text" placeholder="e.g. Grade 5" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                @error('newKidGrade') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">School</label>
                                <input wire:model="newKidSchool" type="text" placeholder="e.g. Rhodes Park" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                @error('newKidSchool') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Your Relationship *</label>
                                <select wire:model="newKidRelationship" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Parent">Parent</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Learning Track *</label>
                                <select wire:model="newKidTrack" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Gender (Optional)</label>
                                <select wire:model="newKidGender" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                    <option value="">Prefer not to say</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Initial Course Enrollment (Optional)</label>
                            <select wire:model="newKidCourseId" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                <option value="">Select a course to enroll now (or enroll later)</option>
                                @foreach ($availableKidsCourses as $course)
                                    <option value="{{ $course['id'] }}">
                                        {{ $course['title'] }} {{ $course['is_for_kids'] ? '[Kids Course]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Parent Notes (Optional)</label>
                            <textarea wire:model="newKidNotes" rows="2" placeholder="e.g. Learning goals, special interests, school grade" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5"></textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                            <button wire:click="$set('showAddKidModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                                Create & Connect Child
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL: LINK EXISTING STUDENT                                 --}}
        {{-- ============================================================ --}}
        @if ($showLinkKidModal)
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <x-heroicon-o-link class="w-5 h-5" />
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Link Existing Student</h3>
                        </div>
                        <button wire:click="$set('showLinkKidModal', false)" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form wire:submit="linkKid" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Child's Unique Login Code or Email Address *</label>
                            <input wire:model="linkKidEmail" type="text" placeholder="e.g. KID-4892 or student@example.com" class="w-full text-xs font-mono uppercase rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5" required>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                Enter the Unique Login Code (e.g. KID-4892) provided by administration, or the child's login email.
                            </p>
                            @error('linkKidEmail') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Your Relationship *</label>
                            <select wire:model="linkKidRelationship" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Parent">Parent</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes (Optional)</label>
                            <textarea wire:model="linkKidNotes" rows="2" placeholder="e.g. Enrolled through school partnership" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5"></textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                            <button wire:click="$set('showLinkKidModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                                Verify & Connect
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL: ENROLL CHILD IN COURSE                                --}}
        {{-- ============================================================ --}}
        @if ($showEnrollKidModal)
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <x-heroicon-o-academic-cap class="w-5 h-5" />
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Enroll Child in Course</h3>
                        </div>
                        <button wire:click="$set('showEnrollKidModal', false)" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form wire:submit="enrollKid" class="space-y-4">
                        <div class="rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 p-3 text-xs text-emerald-900 dark:text-emerald-200">
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-information-circle class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                <p class="leading-relaxed">
                                    Enrolling your child in an additional course requires course fee payment. You will be directed to the secure checkout with this child pre-selected.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Course *</label>
                            <select wire:model.live="enrollCourseId" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5" required>
                                <option value="">Choose a Course...</option>
                                @foreach ($availableKidsCourses as $course)
                                    <option value="{{ $course['id'] }}">
                                        {{ $course['title'] }} {{ $course['is_for_kids'] ? '[Kids Course]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @php
                            $selectedCourseData = collect($availableKidsCourses)->firstWhere('id', (int) $enrollCourseId);
                        @endphp

                        @if ($selectedCourseData && !empty($selectedCourseData['intakes']))
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Class / Intake (Optional)</label>
                                <select wire:model="enrollIntakeId" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                    <option value="">Default Class Cohort</option>
                                    @foreach ($selectedCourseData['intakes'] as $intake)
                                        <option value="{{ $intake['id'] }}">{{ $intake['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                            <button wire:click="$set('showEnrollKidModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">
                                Proceed to Payment & Enrollment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL: EDIT CHILD DETAILS                                    --}}
        {{-- ============================================================ --}}
        @if ($showEditKidModal)
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <x-heroicon-o-pencil class="w-5 h-5" />
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Child Details</h3>
                        </div>
                        <button wire:click="$set('showEditKidModal', false)" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <form wire:submit="updateKid" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Child's Name *</label>
                            <input wire:model="editKidName" type="text" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5" required>
                            @error('editKidName') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Unique Login Code *</label>
                            <input wire:model="editKidUniqueCode" type="text" class="w-full text-xs font-mono uppercase font-bold rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5" required>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                Kids cannot change this code. Only linked parents or administrators can modify it.
                            </p>
                            @error('editKidUniqueCode') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Age</label>
                                <input wire:model="editKidAge" type="number" min="3" max="18" placeholder="e.g. 10" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                @error('editKidAge') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Grade</label>
                                <input wire:model="editKidGrade" type="text" placeholder="e.g. Grade 5" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                @error('editKidGrade') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">School</label>
                                <input wire:model="editKidSchool" type="text" placeholder="e.g. Rhodes Park" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                @error('editKidSchool') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Relationship *</label>
                                <select wire:model="editKidRelationship" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Aunt">Aunt</option>
                                    <option value="Uncle">Uncle</option>
                                    <option value="Elder Sister">Elder Sister</option>
                                    <option value="Elder Brother">Elder Brother</option>
                                    <option value="Grandmother">Grandmother</option>
                                    <option value="Grandfather">Grandfather</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Parent">Parent</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Track *</label>
                                <select wire:model="editKidTrack" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Reset Password (Optional)</label>
                            <input wire:model="editKidPassword" type="password" placeholder="Leave blank to keep existing password" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                            <textarea wire:model="editKidNotes" rows="2" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white p-2.5"></textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                            <button wire:click="$set('showEditKidModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
