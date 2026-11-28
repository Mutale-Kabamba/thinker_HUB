<div
    x-data="{
        sidebarOpen: true,
        mobileDrawerOpen: false,
        activeTab: 'overview',
        lessonNote: '',
        noteSaved: false,
        ratingModalOpen: false,
        hoverRating: 0,
        selectedRating: {{ $userRating }},
        reviewText: @js($userReview),
        ratingLabels: {
            0: 'Select Rating',
            1: 'Awful, not what I expected at all',
            2: 'Poor, pretty disappointed',
            3: 'Average, could be better',
            4: 'Good, what I expected',
            5: 'Amazing, above expectations!'
        },
        loadNote(lessonId) {
            if (!lessonId) { this.lessonNote = ''; return; }
            try {
                this.lessonNote = localStorage.getItem('thub_lesson_note_' + lessonId) || '';
            } catch (_) { this.lessonNote = ''; }
        },
        saveNote(lessonId) {
            if (!lessonId) return;
            try {
                localStorage.setItem('thub_lesson_note_' + lessonId, this.lessonNote);
                this.noteSaved = true;
                setTimeout(() => { this.noteSaved = false; }, 1800);
            } catch (_) {}
        }
    }"
    x-init="loadNote({{ $activeLesson?->id ?? 'null' }})"
    @rating-saved.window="setTimeout(() => { ratingModalOpen = false; }, 900)"
    @keydown.escape.window="ratingModalOpen = false"
    class="flex min-h-dvh lg:h-screen w-full flex-col bg-white text-slate-900 font-sans antialiased lg:overflow-hidden"
>
    @php
        $orderedLessons = $course->lessons->sortBy('order_column')->values();
        $currentIdx = $activeLesson ? $orderedLessons->search(fn ($l) => $l->id === $activeLesson->id) : false;
        $hasPrev = $currentIdx !== false && $currentIdx > 0;
        $hasNext = $currentIdx !== false && $currentIdx < ($orderedLessons->count() - 1);
        $completedCount = collect($progressMap)->where('is_completed', true)->count();
        $totalLessonCount = $orderedLessons->count();
    @endphp

    {{-- Udemy-Style Dark Top Navigation Bar --}}
    <header class="sticky top-0 z-30 h-14 bg-[#0b1c22] text-white border-b border-slate-800 px-3 sm:px-5 flex items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
            <a
                href="{{ Route::has('portal.dashboard') ? route('portal.dashboard') : route('filament.student.pages.overview') }}"
                class="flex items-center gap-2 shrink-0 hover:opacity-85 transition"
                title="Back to Dashboard"
            >
                <img src="{{ asset('images/logos/white.png') }}" alt="think.er HUB" class="h-5 sm:h-6 w-auto object-contain" onerror="this.style.display='none'" />
            </a>
            <span class="hidden sm:inline-block h-5 w-px bg-slate-700 shrink-0"></span>
            <h1 class="text-xs sm:text-sm font-medium text-slate-100 truncate">{{ $course->title }}</h1>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <button
                type="button"
                @click="ratingModalOpen = true"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-300 hover:text-white transition"
            >
                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span class="hidden sm:inline" x-text="selectedRating > 0 ? 'Your rating (' + selectedRating + '/5)' : 'Leave a rating'"></span>
            </button>

            {{-- Progress Indicator --}}
            <div class="flex items-center gap-2 text-xs text-slate-200">
                <div class="relative flex items-center justify-center w-7 h-7 rounded-full border-2 border-teal-500/40 bg-slate-900">
                    <span class="text-[10px] font-bold text-teal-300">{{ $overallProgress }}%</span>
                </div>
                <span class="hidden sm:inline text-xs font-medium text-slate-300">
                    Your progress <span class="text-slate-400">({{ $completedCount }}/{{ $totalLessonCount }})</span>
                </span>
            </div>

            {{-- Mobile Curriculum Drawer Button --}}
            <button
                type="button"
                @click="mobileDrawerOpen = true"
                class="lg:hidden inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800/80 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-slate-700 transition"
            >
                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                <span>Content</span>
            </button>
        </div>
    </header>

    {{-- Main Split Layout: Left Stage + Tabs | Right Collapsible Curriculum Sidebar --}}
    <div class="relative flex flex-1 lg:overflow-hidden">

        {{-- Floating "Course content" reopen button on desktop when sidebar is collapsed --}}
        <button
            type="button"
            x-show="!sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = true"
            style="display: none;"
            class="hidden lg:inline-flex items-center gap-2 fixed right-0 top-20 z-30 bg-[#0b1c22] text-white border border-r-0 border-slate-700 px-3.5 py-2.5 rounded-l-lg shadow-lg text-xs font-semibold hover:bg-slate-800 transition"
        >
            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Course content</span>
        </button>

        {{-- Left Column: Full-Width Stage + Lower Information Tabs --}}
        <main class="flex-1 flex flex-col lg:overflow-y-auto bg-white min-w-0">

            {{-- 1. Top Content Stage (Full-bleed Udemy-style theater) --}}
            <div class="relative w-full border-b border-slate-200 {{ in_array($activeLesson?->type, ['reading', 'resource', 'quiz', 'assignment'], true) ? 'bg-white' : 'bg-[#0f172a]' }}">

                {{-- Floating Previous / Next Lesson Stage Arrows --}}
                @if($hasPrev)
                    <button
                        type="button"
                        wire:click="goToAdjacentLesson('prev')"
                        title="Previous lesson"
                        class="hidden sm:flex items-center justify-center absolute left-0 top-1/2 -translate-y-1/2 z-20 w-9 h-12 bg-teal-600/90 hover:bg-teal-500 text-white rounded-r-md shadow-md transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                @endif

                @if($hasNext)
                    <button
                        type="button"
                        wire:click="goToAdjacentLesson('next')"
                        title="Next lesson"
                        class="hidden sm:flex items-center justify-center absolute right-0 top-1/2 -translate-y-1/2 z-20 w-9 h-12 bg-teal-600/90 hover:bg-teal-500 text-white rounded-l-md shadow-md transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endif

                @if($activeLesson)
                    @if($activeLesson->type === 'video')
                        @include('livewire.partials.course-video-player')

                    @elseif($activeLesson->type === 'scorm')
                        <div class="w-full">
                            @if($activeLesson->scormPackage)
                                @livewire('scorm-player', ['package' => $activeLesson->scormPackage, 'lessonId' => $activeLesson->id], key('scorm-'.$activeLesson->id))
                            @else
                                <div class="py-16 text-center text-slate-400 text-sm">
                                    No SCORM / xAPI package is attached to this lesson yet.
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'reading')
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 sm:py-12 min-h-[380px] flex flex-col justify-between">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-6">{{ $activeLesson->title }}</h2>
                                <div class="prose prose-slate max-w-none leading-relaxed">
                                    {!! $activeLesson->reading_body ?? '<p class="text-slate-500 italic">No content provided for this reading lesson.</p>' !!}
                                </div>
                            </div>
                            <div class="flex justify-end pt-8 mt-8 border-t border-slate-100">
                                <button
                                    wire:click="markReadingComplete"
                                    class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 font-semibold rounded-lg text-white text-sm transition"
                                >
                                    Mark as Read &amp; Continue &rarr;
                                </button>
                            </div>
                        </div>

                    @elseif($activeLesson->type === 'quiz')
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 min-h-[380px]">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-5">{{ $activeLesson->title }}</h2>
                            @if($activeLesson->content_id)
                                @livewire('quiz-player-embedded', ['quizId' => $activeLesson->content_id], key('quiz-'.$activeLesson->id))
                            @else
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center">
                                    <p class="text-slate-500 text-sm">Quiz configuration is missing for this lesson.</p>
                                    <button wire:click="handleQuizPassed" class="mt-4 px-4 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs font-semibold text-white">
                                        Mark as passed (Admin Override)
                                    </button>
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'assignment')
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 min-h-[380px]">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-5">{{ $activeLesson->title }}</h2>
                            @if($activeLesson->content_id)
                                @livewire('assignment-task-embedded', ['assignmentId' => $activeLesson->content_id], key('task-'.$activeLesson->id))
                            @else
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center">
                                    <p class="text-slate-500 text-sm">Assignment configuration is missing for this lesson.</p>
                                    <button wire:click="handleTaskSubmitted" class="mt-4 px-4 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs font-semibold text-white">
                                        Mark as submitted (Admin Override)
                                    </button>
                                </div>
                            @endif
                        </div>

                    @elseif($activeLesson->type === 'resource')
                        @php
                            $packetFiles = is_array($activeLesson->resource_files) && ! empty($activeLesson->resource_files)
                                ? array_values($activeLesson->resource_files)
                                : (is_array($activeLesson->attachments) ? array_values($activeLesson->attachments) : []);
                        @endphp
                        <div class="max-w-3xl mx-auto px-5 sm:px-10 py-8 sm:py-12 min-h-[340px] space-y-5">
                            <h2 class="text-2xl font-bold text-slate-900">{{ $activeLesson->title }}</h2>
                            @if($activeLesson->reading_body)
                                <div class="prose prose-slate max-w-none text-sm">{!! $activeLesson->reading_body !!}</div>
                            @endif
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @forelse($packetFiles as $rIdx => $rItem)
                                    @php
                                        $rName = is_array($rItem) ? ($rItem['name'] ?? basename((string) ($rItem['path'] ?? 'Resource'))) : basename((string) $rItem);
                                    @endphp
                                    <a
                                        href="{{ route('lessons.resource.download', ['lesson' => $activeLesson->id, 'index' => $rIdx]) }}"
                                        class="flex items-center justify-between p-3.5 rounded-lg border border-slate-200 bg-slate-50 hover:border-teal-400 transition text-sm text-slate-800"
                                    >
                                        <span class="truncate font-medium">{{ $rName }}</span>
                                        <span class="ml-3 px-2.5 py-1 rounded bg-teal-600 text-white text-xs font-semibold shrink-0">Download</span>
                                    </a>
                                @empty
                                    <p class="text-slate-500 text-sm">No downloadable packets attached yet.</p>
                                @endforelse
                            </div>
                            <div class="flex justify-end pt-3">
                                <button wire:click="handleVideoCompletion" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 font-semibold rounded-lg text-white text-sm transition">
                                    Mark Complete &amp; Continue &rarr;
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="max-w-2xl mx-auto py-16 px-6 text-center space-y-4">
                        <span class="inline-block rounded-full bg-teal-900/60 border border-teal-700/60 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-teal-300">
                            {{ $course->deliveryModeLabel() }} Course
                        </span>
                        <h3 class="text-xl font-bold text-white">{{ $course->title }}</h3>
                        <p class="text-sm text-slate-300 max-w-lg mx-auto">
                            This course is delivered through your Student Portal &mdash; access your live schedule, downloadable materials, assignments, and quizzes below.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-2.5 pt-2">
                            <a href="{{ url('/learn/courses') }}" class="rounded-lg bg-teal-600 hover:bg-teal-500 px-4 py-2 text-xs font-bold text-white transition">Course Materials</a>
                            <a href="{{ url('/learn/schedule') }}" class="rounded-lg border border-slate-700 bg-slate-800/80 hover:bg-slate-700 px-4 py-2 text-xs font-bold text-slate-200 transition">Live Schedule</a>
                            <a href="{{ url('/learn/assignments') }}" class="rounded-lg border border-slate-700 bg-slate-800/80 hover:bg-slate-700 px-4 py-2 text-xs font-bold text-slate-200 transition">Assignments</a>
                            <a href="{{ url('/learn/quizzes') }}" class="rounded-lg border border-slate-700 bg-slate-800/80 hover:bg-slate-700 px-4 py-2 text-xs font-bold text-slate-200 transition">Quizzes</a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. Udemy-Style Lower Course Information Tabs (Search | Overview | Q&A | Notes | Announcements | Reviews | Learning tools) --}}
            <div class="max-w-4xl w-full mx-auto px-4 sm:px-8 py-6 flex-1">
                <div class="flex items-center gap-6 border-b border-slate-200 overflow-x-auto text-sm font-semibold">
                    <button
                        type="button"
                        @click="activeTab = 'search'"
                        title="Search course content"
                        class="pb-3 whitespace-nowrap border-b-2 transition inline-flex items-center"
                        :class="activeTab === 'search' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'overview'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'overview' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Overview
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'qa'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'qa' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Q&amp;A
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'notes'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'notes' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Notes
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'announcements'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'announcements' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Announcements
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'reviews'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'reviews' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Reviews
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'tools'"
                        class="pb-3 whitespace-nowrap border-b-2 transition"
                        :class="activeTab === 'tools' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    >
                        Learning tools
                    </button>
                </div>

                {{-- Tab: Search Lessons --}}
                <div x-show="activeTab === 'search'" style="display: none;" x-data="{ query: '' }" class="py-6 space-y-4">
                    <div class="flex gap-2">
                        <input
                            type="text"
                            x-model="query"
                            placeholder="Search lessons in this course..."
                            class="flex-1 rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-800 focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none"
                        />
                    </div>
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden bg-white">
                        @foreach($orderedLessons as $idx => $lesson)
                            <button
                                type="button"
                                x-show="!query || @js(strtolower($lesson->title)).includes(query.toLowerCase())"
                                wire:click="selectLesson({{ $lesson->id }})"
                                @click="activeTab = 'overview'; loadNote({{ $lesson->id }})"
                                class="w-full flex items-center justify-between px-4 py-3 text-left text-sm hover:bg-slate-50 transition"
                            >
                                <span class="font-medium text-slate-800">{{ $idx + 1 }}. {{ $lesson->title }}</span>
                                <span class="text-[11px] uppercase tracking-wider text-slate-400">{{ $lesson->type }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Tab: Overview --}}
                <div x-show="activeTab === 'overview'" class="py-6 space-y-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $course->title }}</h2>
                        @if($course->course_by)
                            <p class="text-xs text-slate-500 mt-1">by {{ $course->course_by }}</p>
                        @endif
                    </div>

                    {{-- Key Course Stats Strip --}}
                    <div class="flex flex-wrap items-center gap-6 sm:gap-10 py-3 border-y border-slate-100 text-sm">
                        <div>
                            <div class="flex items-center gap-1 font-bold text-amber-600">
                                <span>{{ number_format((float) $averageRating, 1) }}</span>
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-xs text-slate-500">{{ number_format((int) $totalReviews) }} {{ \Illuminate\Support\Str::plural('rating', $totalReviews) }}</p>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">{{ number_format((int) ($course->enrollments_count ?? 0)) }}</div>
                            <p class="text-xs text-slate-500">Students</p>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">{{ $totalLessonCount }} {{ \Illuminate\Support\Str::plural('lesson', $totalLessonCount) }}</div>
                            <p class="text-xs text-slate-500">{{ $course->timeline ?: 'Self-paced' }}</p>
                        </div>
                    </div>

                    @if($course->overview || $course->description)
                        <div class="space-y-2 text-sm text-slate-700 leading-relaxed">
                            <h3 class="font-bold text-slate-900">About this course</h3>
                            <p>{{ $course->overview ?: $course->description }}</p>
                        </div>
                    @endif

                    @if($course->key_outcome)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            <h4 class="font-bold text-slate-900 mb-1">Key Outcome</h4>
                            <p>{{ $course->key_outcome }}</p>
                        </div>
                    @endif

                    @php
                        $lessonAttachments = $activeLesson && is_array($activeLesson->attachments) ? array_filter($activeLesson->attachments) : [];
                    @endphp
                    @if(!empty($lessonAttachments))
                        <div class="space-y-3 pt-2">
                            <h3 class="text-sm font-bold text-slate-900">Lesson Resources</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($lessonAttachments as $attIndex => $attPath)
                                    @if(is_string($attPath) && filled($attPath))
                                        <a
                                            href="{{ route('file.download', ['type' => 'lesson', 'id' => $activeLesson->id, 'index' => $attIndex]) }}"
                                            target="_blank"
                                            class="flex items-center justify-between p-3.5 rounded-lg border border-slate-200 hover:border-teal-500 bg-white transition text-xs text-slate-800"
                                        >
                                            <span class="truncate font-semibold">{{ basename($attPath) }}</span>
                                            <span class="ml-2 px-2.5 py-1 rounded bg-teal-50 text-teal-700 font-bold shrink-0">Download</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Tab: Q&A (Udemy-Style with AI Study Buddy Banner + Course Questions) --}}
                <div
                    x-show="activeTab === 'qa'"
                    style="display: none;"
                    x-data="{ qaSearch: '', qaSort: 'recent', showAskForm: false, upvoted: {} }"
                    class="py-6 space-y-6"
                >
                    {{-- AI Assistant Banner --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-teal-200/80 bg-teal-50/70 px-5 py-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600/10 text-teal-700 shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Get an instant answer from your AI Study Buddy</p>
                                <p class="text-xs text-slate-600 mt-0.5">Our AI uses context from this course to help answer most questions immediately.</p>
                            </div>
                        </div>
                        <a
                            href="{{ Route::has('filament.student.pages.overview') ? route('filament.student.pages.overview') : url('/learn') }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#0A2D27] hover:bg-teal-800 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition shrink-0"
                        >
                            <span>Get an instant answer</span>
                        </a>
                    </div>

                    {{-- Search & Filters --}}
                    <div class="space-y-3">
                        <div class="flex gap-2">
                            <input
                                type="text"
                                x-model="qaSearch"
                                placeholder="Search all course questions"
                                class="flex-1 rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none"
                            />
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-11 rounded-lg bg-[#0A2D27] hover:bg-teal-800 text-white transition"
                                aria-label="Search questions"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </div>

                        <div class="flex flex-wrap items-end gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Sort by:</label>
                                <select x-model="qaSort" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 focus:border-teal-600 outline-none">
                                    <option value="recent">Sort by most recent</option>
                                    <option value="oldest">Sort by oldest</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Questions Heading --}}
                    <div class="flex items-center justify-between pt-1">
                        <h3 class="text-base font-bold text-slate-900">
                            All questions in this course <span class="text-slate-500 font-normal">({{ $questions->count() }})</span>
                        </h3>
                    </div>

                    {{-- Questions List --}}
                    <div class="divide-y divide-slate-200/80">
                        @forelse($questions as $q)
                            @php
                                $qLines = preg_split('/\r\n|\r|\n/', trim((string) $q->body), 2);
                                $qTitle = $qLines[0] ?? 'Question';
                                $qDetail = $qLines[1] ?? '';
                                $authorName = $q->user?->name ?? 'Learner';
                                $initials = collect(explode(' ', trim($authorName)))->filter()->map(fn ($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('') ?: 'L';
                            @endphp
                            <div
                                x-show="!qaSearch || @js(strtolower($q->body . ' ' . $authorName)).includes(qaSearch.toLowerCase())"
                                class="py-4 flex items-start justify-between gap-4"
                            >
                                <div class="flex items-start gap-3.5 min-w-0">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 leading-snug">{{ \Illuminate\Support\Str::limit($qTitle, 110) }}</p>
                                        @if($qDetail !== '')
                                            <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $qDetail }}</p>
                                        @endif
                                        <p class="text-xs text-slate-500 mt-2">
                                            <span class="font-semibold text-teal-700">{{ $authorName }}</span>
                                            &middot;
                                            <span>{{ $q->created_at?->diffForHumans() }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1.5 text-xs text-slate-600 shrink-0">
                                    <button
                                        type="button"
                                        @click="upvoted[{{ $q->id }}] = !upvoted[{{ $q->id }}]"
                                        class="inline-flex items-center gap-1 font-semibold hover:text-teal-700 transition"
                                        :class="upvoted[{{ $q->id }}] ? 'text-teal-700' : 'text-slate-600'"
                                    >
                                        <span x-text="upvoted[{{ $q->id }}] ? 1 : 0"></span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60">
                                <p class="text-sm font-semibold text-slate-700">No questions yet in this course.</p>
                                <p class="text-xs text-slate-500 mt-1">Be the first to ask a question or use the AI Study Buddy for an instant answer.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Ask a New Question Composer --}}
                    <div class="pt-2 space-y-3">
                        <div class="flex flex-wrap items-center gap-4">
                            <a
                                href="{{ Route::has('filament.student.pages.overview') ? route('filament.student.pages.overview') : url('/learn') }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-[#0A2D27] hover:bg-teal-800 px-4 py-2 text-xs font-bold text-white transition"
                            >
                                Get an instant answer
                            </a>
                            <button
                                type="button"
                                @click="showAskForm = !showAskForm"
                                class="text-xs font-bold text-slate-800 hover:text-teal-700 transition"
                            >
                                Ask a new question
                            </button>
                            @if($questionPosted)
                                <span class="text-xs font-semibold text-teal-700">Question posted!</span>
                            @endif
                        </div>

                        <div x-show="showAskForm" x-transition style="display: none;" class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                            <input
                                type="text"
                                wire:model="newQuestionTitle"
                                placeholder="Question title or summary..."
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:border-teal-600 outline-none"
                            />
                            <textarea
                                wire:model="newQuestionBody"
                                rows="3"
                                placeholder="Add details so instructors and classmates can help you..."
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:border-teal-600 outline-none resize-none"
                            ></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showAskForm = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                                <button
                                    type="button"
                                    wire:click="postQuestion"
                                    @click="showAskForm = false"
                                    class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-xs font-bold text-white transition"
                                >
                                    Publish Question
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: Personal Study Notes --}}
                <div x-show="activeTab === 'notes'" style="display: none;" class="py-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">My Lesson Notes</h3>
                        <span x-show="noteSaved" class="text-xs font-semibold text-emerald-600">Saved locally</span>
                    </div>
                    <textarea
                        x-model="lessonNote"
                        rows="5"
                        placeholder="Jot down key takeaways, timestamps, or questions for this lesson..."
                        class="w-full rounded-xl border border-slate-300 p-3.5 text-sm text-slate-800 focus:border-teal-500 focus:ring-teal-500 outline-none"
                    ></textarea>
                    <div class="flex justify-end">
                        <button
                            type="button"
                            @click="saveNote({{ $activeLesson?->id ?? 'null' }})"
                            class="px-4 py-2 rounded-lg bg-[#0A2D27] hover:bg-teal-800 text-xs font-semibold text-white transition"
                        >
                            Save Note
                        </button>
                    </div>
                </div>

                {{-- Tab: Announcements (Udemy-Style Feed from Course Broadcasts) --}}
                <div x-show="activeTab === 'announcements'" style="display: none;" class="py-6 space-y-8">
                    @forelse($announcements as $announcement)
                        @php
                            $senderName = $announcement->sender?->name ?? $course->course_by ?? 'think.er HUB Instructor';
                            $senderInitials = collect(explode(' ', trim($senderName)))->filter()->map(fn ($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('') ?: 'TH';
                            $postedAt = ($announcement->sent_at ?? $announcement->created_at)?->diffForHumans() ?? 'recently';
                        @endphp
                        <article class="space-y-4 pb-6 border-b border-slate-200 last:border-b-0">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0A2D27] text-xs font-bold text-teal-200 shrink-0">
                                    {{ $senderInitials }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-teal-800">{{ $senderName }}</p>
                                    <p class="text-xs text-slate-500">posted an announcement &middot; {{ $postedAt }}</p>
                                </div>
                            </div>

                            <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                {{ $announcement->subject }}
                            </h3>

                            <div class="text-sm text-slate-700 leading-relaxed space-y-3">
                                {!! nl2br(e($announcement->body)) !!}
                            </div>

                            @if($announcement->hasAttachment())
                                <div class="inline-flex items-center gap-2 rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-800">
                                    <span>Attachment: {{ $announcement->attachment_name ?: basename((string) $announcement->attachment_path) }}</span>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="py-10 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60">
                            <h3 class="text-sm font-bold text-slate-800">No announcements posted yet</h3>
                            <p class="text-xs text-slate-500 mt-1">Your instructor&rsquo;s course updates and cohort announcements will appear here.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Tab: Reviews (Udemy-Style Student Feedback + Rating Breakdown + Searchable Reviews) --}}
                <div
                    x-show="activeTab === 'reviews'"
                    style="display: none;"
                    x-data="{ reviewSearch: '', ratingFilter: 'all', helpfulVotes: {} }"
                    class="py-6 space-y-8"
                >
                    {{-- Student Feedback Summary --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-lg font-bold text-slate-900">Student feedback</h3>
                            <button
                                type="button"
                                @click="ratingModalOpen = true"
                                class="rounded-lg border border-teal-600 px-3.5 py-1.5 text-xs font-bold text-teal-700 hover:bg-teal-50 transition"
                            >
                                {{ $userRating > 0 ? 'Edit your rating' : 'Leave a rating' }}
                            </button>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                            {{-- Big Score --}}
                            <div class="text-center sm:w-32 shrink-0">
                                <div class="text-5xl font-extrabold text-amber-600 leading-none">
                                    {{ number_format((float) $averageRating, 1) }}
                                </div>
                                <div class="mt-2 flex items-center justify-center gap-0.5 text-amber-500">
                                    @for($s = 1; $s <= 5; $s++)
                                        <svg class="w-4 h-4 {{ $averageRating >= ($s - 0.3) ? 'fill-current' : 'text-slate-300 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <p class="mt-1 text-xs font-bold text-amber-700">Course Rating</p>
                            </div>

                            {{-- 5-Star Horizontal Progress Bars --}}
                            <div class="flex-1 space-y-2">
                                @foreach($ratingBreakdown as $star => $meta)
                                    <button
                                        type="button"
                                        @click="ratingFilter = (ratingFilter === '{{ $star }}' ? 'all' : '{{ $star }}')"
                                        class="w-full flex items-center gap-3 text-xs group"
                                    >
                                        <div class="flex-1 h-2 rounded-full bg-slate-200 overflow-hidden">
                                            <div class="h-full bg-slate-600 group-hover:bg-teal-600 transition-all" style="width: {{ $meta['percent'] }}%"></div>
                                        </div>
                                        <div class="flex items-center gap-0.5 text-amber-500 shrink-0">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-3.5 h-3.5 {{ $i <= $star ? 'fill-current' : 'text-slate-300 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                        <span class="w-9 text-right font-semibold text-teal-700 underline">{{ $meta['percent'] }}%</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Reviews Search & Filter Bar --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-slate-900">Reviews</h3>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex flex-1 gap-2">
                                <input
                                    type="text"
                                    x-model="reviewSearch"
                                    placeholder="Search reviews"
                                    class="flex-1 rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-teal-600 outline-none"
                                />
                                <button type="button" class="w-10 rounded-lg bg-[#0A2D27] hover:bg-teal-800 text-white inline-flex items-center justify-center transition" aria-label="Search reviews">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </button>
                            </div>
                            <select x-model="ratingFilter" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-600 outline-none">
                                <option value="all">All ratings</option>
                                <option value="5">5 stars</option>
                                <option value="4">4 stars</option>
                                <option value="3">3 stars</option>
                                <option value="2">2 stars</option>
                                <option value="1">1 star</option>
                            </select>
                        </div>

                        {{-- Reviews List --}}
                        <div class="divide-y divide-slate-200/80">
                            @forelse($reviews as $rev)
                                @php
                                    $reviewerName = $rev->user?->name ?? 'Learner';
                                    $revInitials = collect(explode(' ', trim($reviewerName)))->filter()->map(fn ($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('') ?: 'L';
                                @endphp
                                <div
                                    x-show="(ratingFilter === 'all' || ratingFilter === '{{ $rev->rating }}') && (!reviewSearch || @js(strtolower(($rev->review ?? '') . ' ' . $reviewerName)).includes(reviewSearch.toLowerCase()))"
                                    class="py-5 flex items-start gap-3.5"
                                >
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white shrink-0">
                                        {{ $revInitials }}
                                    </div>
                                    <div class="flex-1 min-w-0 space-y-1.5">
                                        <p class="text-sm font-bold text-slate-900">{{ $reviewerName }}</p>
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-0.5 text-amber-500">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <svg class="w-3.5 h-3.5 {{ $s <= $rev->rating ? 'fill-current' : 'text-slate-300 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-slate-500">{{ $rev->created_at?->diffForHumans() }}</span>
                                        </div>
                                        @if(filled($rev->review))
                                            <p class="text-sm text-slate-700 leading-relaxed pt-0.5">{{ $rev->review }}</p>
                                        @endif
                                        <div class="pt-2 flex items-center gap-2 text-xs text-slate-500">
                                            <span>Was this review helpful?</span>
                                            <button
                                                type="button"
                                                @click="helpfulVotes[{{ $rev->id }}] = (helpfulVotes[{{ $rev->id }}] === 'up' ? null : 'up')"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-full border transition"
                                                :class="helpfulVotes[{{ $rev->id }}] === 'up' ? 'border-teal-600 bg-teal-50 text-teal-700' : 'border-slate-300 text-slate-600 hover:border-slate-500'"
                                                aria-label="Helpful"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14z"/></svg>
                                            </button>
                                            <button
                                                type="button"
                                                @click="helpfulVotes[{{ $rev->id }}] = (helpfulVotes[{{ $rev->id }}] === 'down' ? null : 'down')"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-full border transition"
                                                :class="helpfulVotes[{{ $rev->id }}] === 'down' ? 'border-slate-700 bg-slate-100 text-slate-900' : 'border-slate-300 text-slate-600 hover:border-slate-500'"
                                                aria-label="Not helpful"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 15v4a3 3 0 003 3l4-9V2H5.72a2 2 0 00-2 1.7l-1.38 9a2 2 0 002 2.3H10z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-10 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60">
                                    <p class="text-sm font-bold text-slate-800">No reviews yet</p>
                                    <p class="text-xs text-slate-500 mt-1">Be the first to share your experience with this course.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Tab: Learning tools (Udemy-Style Learning Reminders + Offline Access) --}}
                <div
                    x-show="activeTab === 'tools'"
                    style="display: none;"
                    x-data="{
                        courseTitle: @js($course->title),
                        reminders: [],
                        addingReminder: false,
                        newTitle: 'Time to learn!',
                        newTime: '20:00',
                        newFrequency: 'Daily',
                        savingOffline: false,
                        savedOffline: false,
                        init() {
                            try {
                                const stored = JSON.parse(localStorage.getItem('thub_reminders_{{ $course->id }}') || 'null');
                                this.reminders = Array.isArray(stored) && stored.length > 0 ? stored : [{
                                    id: 1,
                                    title: 'Time to learn!',
                                    time: '20:00',
                                    frequency: 'Daily'
                                }];
                            } catch (_) {
                                this.reminders = [{ id: 1, title: 'Time to learn!', time: '20:00', frequency: 'Daily' }];
                            }
                        },
                        persist() {
                            try { localStorage.setItem('thub_reminders_{{ $course->id }}', JSON.stringify(this.reminders)); } catch (_) {}
                        },
                        addReminder() {
                            if (!this.newTitle.trim()) return;
                            this.reminders.push({
                                id: Date.now(),
                                title: this.newTitle.trim(),
                                time: this.newTime || '20:00',
                                frequency: this.newFrequency || 'Daily'
                            });
                            this.persist();
                            this.addingReminder = false;
                            this.newTitle = 'Time to learn!';
                        },
                        removeReminder(id) {
                            this.reminders = this.reminders.filter(r => r.id !== id);
                            this.persist();
                        },
                        formatTime(t) {
                            const [hStr, mStr] = (t || '20:00').split(':');
                            const h = parseInt(hStr || '20', 10);
                            const suffix = h >= 12 ? 'PM' : 'AM';
                            const h12 = (h % 12) || 12;
                            return h12 + ':' + (mStr || '00') + ' ' + suffix;
                        },
                        googleCalendarUrl(r) {
                            const title = encodeURIComponent(r.title + ' — ' + this.courseTitle);
                            const details = encodeURIComponent('Scheduled study reminder from think.er HUB: ' + window.location.href);
                            const recur = r.frequency === 'Weekly' ? '&recur=RRULE:FREQ=WEEKLY' : (r.frequency === 'Weekdays' ? '&recur=RRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR' : '&recur=RRULE:FREQ=DAILY');
                            return 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' + title + '&details=' + details + recur;
                        },
                        async saveCourse() {
                            if (!window.ThinkerOffline || this.savingOffline) return;
                            this.savingOffline = true;
                            try {
                                await window.ThinkerOffline.downloadCourse(@js(route('offline.course.bundle', $course)));
                                this.savedOffline = true;
                            } catch (_) {}
                            finally { this.savingOffline = false; }
                        }
                    }"
                    class="py-6 space-y-8"
                >
                    {{-- Learning Reminders Section --}}
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Learning reminders</h3>
                            <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                                Learning a little each day adds up. Research shows that students who make learning a habit are more likely to reach their goals. Set time aside to learn and get reminders using your learning scheduler.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <template x-for="r in reminders" :key="r.id">
                                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs space-y-2.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-base font-bold text-slate-900" x-text="r.title"></p>
                                        <button
                                            type="button"
                                            @click="removeReminder(r.id)"
                                            class="text-xs text-slate-400 hover:text-rose-600 transition"
                                            title="Remove reminder"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-700">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span x-text="formatTime(r.time)"></span>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span x-text="r.frequency"></span>
                                        </span>
                                    </div>
                                    <div>
                                        <a
                                            :href="googleCalendarUrl(r)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900 underline"
                                        >
                                            Add to Google Calendar
                                        </a>
                                    </div>
                                    <p class="text-xs text-slate-500">Course: {{ $course->title }}</p>
                                </div>
                            </template>
                        </div>

                        <div x-show="addingReminder" x-transition style="display: none;" class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <input type="text" x-model="newTitle" placeholder="Reminder label" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-teal-600" />
                                <input type="time" x-model="newTime" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-teal-600" />
                                <select x-model="newFrequency" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-teal-600">
                                    <option value="Daily">Daily</option>
                                    <option value="Weekdays">Weekdays</option>
                                    <option value="Weekly">Weekly</option>
                                </select>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="addingReminder = false" class="px-3 py-1.5 text-xs font-semibold text-slate-600">Cancel</button>
                                <button type="button" @click="addReminder()" class="px-4 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-xs font-bold text-white">Save Reminder</button>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="addingReminder = !addingReminder"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#0A2D27] hover:bg-teal-800 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition"
                        >
                            <span>+ Add another</span>
                        </button>
                    </div>

                    {{-- Offline Study Tool --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 space-y-3">
                        <h4 class="text-sm font-bold text-slate-900">Offline Study Access</h4>
                        <p class="text-xs text-slate-600">Save this course to your device so you can read lessons, open resources, and continue studying without internet.</p>
                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                @click="saveCourse()"
                                :disabled="savingOffline"
                                class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-xs font-semibold text-white transition"
                                x-text="savingOffline ? 'Saving...' : (savedOffline ? 'Saved for Offline Study' : 'Save Course Offline')"
                            ></button>
                            <a href="{{ route('offline.library') }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                Open Offline Library
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        {{-- Mobile Backdrop for Curriculum Drawer --}}
        <div
            x-show="mobileDrawerOpen"
            x-transition.opacity
            @click="mobileDrawerOpen = false"
            class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs lg:hidden"
            style="display: none;"
        ></div>

        {{-- Right Curriculum Sidebar (Udemy-Style Crisp Light Panel, Collapsible on Desktop & Drawer on Mobile) --}}
        <aside
            :class="[
                mobileDrawerOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0',
                sidebarOpen ? 'lg:w-88 xl:w-96 lg:border-l' : 'lg:w-0 lg:border-l-0 lg:overflow-hidden'
            ]"
            class="fixed inset-y-0 right-0 z-50 w-[86vw] max-w-sm lg:static lg:z-auto border-slate-200 bg-white flex flex-col h-full overflow-hidden shrink-0 transform transition-all duration-200 ease-out"
        >
            {{-- Sidebar Header --}}
            <div class="px-4 py-3.5 border-b border-slate-200 flex items-center justify-between bg-white shrink-0">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-slate-900 border-b-2 border-slate-900 pb-1">Course content</span>
                </div>
                <button
                    type="button"
                    @click="sidebarOpen = false; mobileDrawerOpen = false"
                    class="rounded-md p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition"
                    title="Close panel"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Scrollable Sections & Lessons --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-200">
                @if($course->sections->isNotEmpty())
                    @php $lessonCounter = 0; @endphp
                    @foreach($course->sections as $sectionIndex => $section)
                        @php
                            $secCompleted = $section->lessons->filter(fn ($l) => (bool) ($progressMap[$l->id]['is_completed'] ?? false))->count();
                            $secTotal = $section->lessons->count();
                        @endphp
                        <div x-data="{ open: true }">
                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full flex items-start justify-between gap-2 px-4 py-3.5 text-left bg-slate-50 hover:bg-slate-100/80 transition"
                            >
                                <div class="min-w-0">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">
                                        Section {{ $sectionIndex + 1 }}: {{ $section->title }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $secCompleted }} / {{ $secTotal }}</p>
                                </div>
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-slate-500 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" class="bg-white divide-y divide-slate-100">
                                @foreach($section->lessons as $lesson)
                                    @php
                                        $lessonCounter++;
                                        $p = $progressMap[$lesson->id] ?? null;
                                        $isUnlocked = $p['is_unlocked'] ?? false;
                                        $isCompleted = $p['is_completed'] ?? false;
                                        $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="selectLesson({{ $lesson->id }})"
                                        @click="mobileDrawerOpen = false; loadNote({{ $lesson->id }})"
                                        class="w-full flex items-start gap-3 px-4 py-3 text-left transition {{ $isActive ? 'bg-teal-50/90' : 'hover:bg-slate-50' }} {{ !$isUnlocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    >
                                        <span class="mt-0.5 shrink-0">
                                            @if($isCompleted)
                                                <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-teal-600 text-white">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            @elseif($isUnlocked)
                                                <span class="inline-block w-4 h-4 rounded border-2 border-slate-400"></span>
                                            @else
                                                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium leading-snug {{ $isActive ? 'text-slate-900 font-semibold' : 'text-slate-800' }}">
                                                {{ $lessonCounter }}. {{ $lesson->title }}
                                            </p>
                                            <span class="mt-1 inline-flex items-center gap-1 text-[11px] text-slate-500 uppercase tracking-wide">
                                                {{ $lesson->type }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-white divide-y divide-slate-100">
                        @foreach($orderedLessons as $idx => $lesson)
                            @php
                                $p = $progressMap[$lesson->id] ?? null;
                                $isUnlocked = $p['is_unlocked'] ?? false;
                                $isCompleted = $p['is_completed'] ?? false;
                                $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                            @endphp
                            <button
                                type="button"
                                wire:click="selectLesson({{ $lesson->id }})"
                                @click="mobileDrawerOpen = false; loadNote({{ $lesson->id }})"
                                class="w-full flex items-start gap-3 px-4 py-3 text-left transition {{ $isActive ? 'bg-teal-50/90' : 'hover:bg-slate-50' }} {{ !$isUnlocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                            >
                                <span class="mt-0.5 shrink-0">
                                    @if($isCompleted)
                                        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-teal-600 text-white">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    @elseif($isUnlocked)
                                        <span class="inline-block w-4 h-4 rounded border-2 border-slate-400"></span>
                                    @else
                                        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium leading-snug {{ $isActive ? 'text-slate-900 font-semibold' : 'text-slate-800' }}">
                                        {{ $idx + 1 }}. {{ $lesson->title }}
                                    </p>
                                    <span class="mt-1 inline-flex items-center gap-1 text-[11px] text-slate-500 uppercase tracking-wide">
                                        {{ $lesson->type }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>

    {{-- Udemy-Style Leave a Rating Modal --}}
    <div
        x-show="ratingModalOpen"
        x-transition.opacity.duration.200ms
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/35 backdrop-blur-md"
        @click.self="ratingModalOpen = false"
    >
        <div
            x-show="ratingModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-md rounded-2xl bg-white p-7 sm:p-8 shadow-2xl border border-slate-200/80 text-center"
        >
            {{-- Top-right Close Button --}}
            <button
                type="button"
                @click="ratingModalOpen = false"
                aria-label="Close rating modal"
                class="absolute right-4 top-4 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <h2 class="text-xl font-bold text-slate-900 tracking-tight mt-1">
                How would you rate this course?
            </h2>

            <p
                class="mt-3 text-xs font-bold text-slate-700 min-h-[18px]"
                x-text="ratingLabels[hoverRating || selectedRating] || 'Select Rating'"
            ></p>

            {{-- 5 Interactive Gold Stars --}}
            <div
                class="mt-4 flex items-center justify-center gap-2.5"
                @mouseleave="hoverRating = 0"
            >
                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                    <button
                        type="button"
                        @mouseenter="hoverRating = star"
                        @click="selectedRating = star"
                        :aria-label="'Rate ' + star + ' out of 5 stars'"
                        class="p-0.5 transition-transform duration-150 hover:scale-110 focus:outline-none"
                    >
                        <svg
                            class="w-10 h-10 transition-colors duration-150"
                            :class="(hoverRating || selectedRating) >= star ? 'text-amber-500 fill-amber-400' : 'text-amber-500 fill-none'"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </button>
                </template>
            </div>

            {{-- Optional Review Input & Save Button (shown once a star is selected) --}}
            <div x-show="selectedRating > 0" x-transition class="mt-6 text-left space-y-3">
                <textarea
                    x-model="reviewText"
                    rows="3"
                    placeholder="Tell us about your own personal experience taking this course. Was it a good match for you?"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none resize-none"
                ></textarea>

                <div class="flex items-center justify-between gap-3 pt-1">
                    @if($ratingSaved)
                        <span class="text-xs font-semibold text-teal-700">Rating saved. Thank you!</span>
                    @else
                        <span class="text-[11px] text-slate-400">Optional written feedback</span>
                    @endif
                    <button
                        type="button"
                        @click="$wire.submitRating(selectedRating, reviewText)"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-lg bg-[#0A2D27] hover:bg-teal-800 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitRating">Save and Continue</span>
                        <span wire:loading wire:target="submitRating">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
