<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Offline Library — think.er HUB</title>
    @include('partials.pwa-register')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased">
    <div
        x-data="{
            online: navigator.onLine,
            courses: [],
            pendingCount: 0,
            syncing: false,
            syncStatus: '',
            activeCourse: null,
            activeLesson: null,
            async init() {
                window.addEventListener('online', () => { this.online = true; this.syncNow(); });
                window.addEventListener('offline', () => { this.online = false; });
                window.addEventListener('thinker-offline-updated', () => this.refresh());
                window.addEventListener('thinker-offline-synced', (e) => {
                    const count = e.detail?.synced || 0;
                    if (count > 0) this.syncStatus = 'Synced ' + count + ' offline update(s).';
                });
                await this.refresh();
            },
            async refresh() {
                if (!window.ThinkerOffline) return;
                this.courses = await window.ThinkerOffline.listCourses();
                const outbox = await window.ThinkerOffline.listOutbox();
                this.pendingCount = outbox.length;
                if (this.activeCourse) {
                    const updated = this.courses.find(c => c.id === this.activeCourse.id);
                    if (updated) {
                        this.activeCourse = updated;
                        if (this.activeLesson) {
                            this.activeLesson = updated.lessons.find(l => l.id === this.activeLesson.id) || updated.lessons[0] || null;
                        }
                    }
                }
            },
            openCourse(course) {
                this.activeCourse = course;
                this.activeLesson = (course.lessons || []).find(l => !l.is_completed) || (course.lessons || [])[0] || null;
            },
            async removeCourse(courseId) {
                await window.ThinkerOffline.removeCourse(courseId);
                if (this.activeCourse && this.activeCourse.id === courseId) {
                    this.activeCourse = null;
                    this.activeLesson = null;
                }
                await this.refresh();
            },
            async markLessonComplete(lesson) {
                if (!lesson || !this.activeCourse) return;
                lesson.is_completed = true;
                await window.ThinkerOffline.saveCourseState(this.activeCourse);
                await window.ThinkerOffline.queueAction('lesson_complete', { lesson_id: lesson.id });
                this.syncStatus = this.online ? 'Progress synced.' : 'Saved offline — will sync automatically when back online.';
                await this.refresh();
            },
            async saveVideoPosition(lesson, seconds) {
                if (!lesson || !this.activeCourse) return;
                lesson.last_watched_second = Math.floor(seconds || 0);
                await window.ThinkerOffline.saveCourseState(this.activeCourse);
                await window.ThinkerOffline.queueAction('video_progress', { lesson_id: lesson.id, seconds: lesson.last_watched_second });
            },
            async syncNow() {
                if (!this.online || !window.ThinkerOffline) return;
                this.syncing = true;
                try {
                    const res = await window.ThinkerOffline.syncOutbox('/offline/sync', document.querySelector('meta[name=csrf-token]')?.content);
                    this.syncStatus = res.synced > 0 ? ('Synced ' + res.synced + ' item(s) to server.') : 'Everything is up to date.';
                } catch (e) {
                    this.syncStatus = 'Sync paused — will retry when connection stabilizes.';
                } finally {
                    this.syncing = false;
                    await this.refresh();
                }
            }
        }"
        class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 space-y-6"
    >
        {{-- Top Bar --}}
        <header class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900 px-5 py-4">
            <div class="flex items-center gap-3">
                <a href="/learn/overview" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:bg-slate-700">
                    &larr; Dashboard
                </a>
                <div>
                    <h1 class="text-base font-bold text-white sm:text-lg">Offline Learning Library</h1>
                    <p class="text-xs text-slate-400">Study saved courses anywhere — progress syncs automatically when online.</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                    :class="online ? 'bg-emerald-950/70 text-emerald-300 border border-emerald-800' : 'bg-amber-950/70 text-amber-300 border border-amber-800'"
                >
                    <span class="h-2 w-2 rounded-full" :class="online ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                    <span x-text="online ? 'Online' : 'Offline Mode'"></span>
                </span>

                <button
                    type="button"
                    x-show="pendingCount > 0"
                    x-on:click="syncNow()"
                    :disabled="!online || syncing"
                    class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-500 disabled:opacity-50"
                >
                    <span x-text="syncing ? 'Syncing...' : ('Sync Now (' + pendingCount + ')')"></span>
                </button>
            </div>
        </header>

        <template x-if="syncStatus">
            <div class="rounded-xl border border-teal-800/60 bg-teal-950/50 px-4 py-2.5 text-xs font-medium text-teal-200" x-text="syncStatus"></div>
        </template>

        {{-- Course List View --}}
        <template x-if="!activeCourse">
            <div>
                <template x-if="courses.length === 0">
                    <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/60 p-10 text-center space-y-3">
                        <p class="text-sm font-semibold text-white">No courses saved for offline study yet.</p>
                        <p class="text-xs text-slate-400">Go to your Student Dashboard and click <strong>Save Offline</strong> on any enrolled course while connected to Wi-Fi or mobile data.</p>
                        <a href="/learn/overview" class="inline-flex rounded-lg bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-500">
                            Go to Student Dashboard
                        </a>
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" x-show="courses.length > 0">
                    <template x-for="course in courses" :key="course.id">
                        <div class="flex flex-col justify-between rounded-2xl border border-slate-800 bg-slate-900 p-5 space-y-4">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded bg-teal-950 px-2 py-0.5 text-[11px] font-bold text-teal-300" x-text="course.code || 'COURSE'"></span>
                                    <span class="text-[11px] text-slate-400" x-text="(course.lessons || []).length + ' lessons'"></span>
                                </div>
                                <h2 class="mt-2 text-base font-bold text-white" x-text="course.title"></h2>
                                <p class="mt-1 text-xs text-slate-400" x-text="'Completed: ' + (course.lessons || []).filter(l => l.is_completed).length + ' / ' + (course.lessons || []).length"></p>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-800">
                                <button
                                    type="button"
                                    x-on:click="openCourse(course)"
                                    class="flex-1 rounded-lg bg-teal-600 px-3 py-2 text-xs font-bold text-white hover:bg-teal-500"
                                >
                                    Open Offline
                                </button>
                                <button
                                    type="button"
                                    x-on:click="removeCourse(course.id)"
                                    class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-slate-800"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Offline Course Reader / Player View --}}
        <template x-if="activeCourse">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                {{-- Lesson Sidebar --}}
                <aside class="lg:col-span-1 rounded-2xl border border-slate-800 bg-slate-900 p-4 space-y-3">
                    <button type="button" x-on:click="activeCourse = null; activeLesson = null" class="text-xs font-semibold text-teal-400 hover:underline">
                        &larr; All Saved Courses
                    </button>
                    <h2 class="text-sm font-bold text-white" x-text="activeCourse.title"></h2>
                    <div class="space-y-1.5">
                        <template x-for="lesson in (activeCourse.lessons || [])" :key="lesson.id">
                            <button
                                type="button"
                                x-on:click="activeLesson = lesson"
                                class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-left text-xs transition"
                                :class="activeLesson && activeLesson.id === lesson.id ? 'bg-teal-600/25 text-white border border-teal-600/50' : 'bg-slate-950/60 text-slate-300 hover:bg-slate-800'"
                            >
                                <span class="truncate pr-2" x-text="lesson.title"></span>
                                <span x-show="lesson.is_completed" class="shrink-0 text-emerald-400 font-bold">Done</span>
                            </button>
                        </template>
                    </div>
                </aside>

                {{-- Lesson Content Area --}}
                <main class="lg:col-span-3 rounded-2xl border border-slate-800 bg-slate-900 p-6 space-y-5">
                    <template x-if="activeLesson">
                        <div class="space-y-5">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-3">
                                <div>
                                    <span class="rounded bg-slate-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-300" x-text="activeLesson.type"></span>
                                    <h3 class="mt-1 text-xl font-bold text-white" x-text="activeLesson.title"></h3>
                                </div>
                                <button
                                    type="button"
                                    x-on:click="markLessonComplete(activeLesson)"
                                    class="rounded-lg px-4 py-2 text-xs font-bold text-white transition"
                                    :class="activeLesson.is_completed ? 'bg-emerald-700' : 'bg-teal-600 hover:bg-teal-500'"
                                    x-text="activeLesson.is_completed ? 'Completed' : 'Mark Complete'"
                                ></button>
                            </div>

                            {{-- Cached Direct Video --}}
                            <template x-if="activeLesson.video_url">
                                <div class="aspect-video overflow-hidden rounded-xl bg-black border border-slate-800">
                                    <video
                                        controls
                                        playsinline
                                        :src="activeLesson.video_url"
                                        x-on:loadedmetadata="$el.currentTime = activeLesson.last_watched_second || 0"
                                        x-on:pause="saveVideoPosition(activeLesson, $el.currentTime)"
                                        x-on:ended="markLessonComplete(activeLesson)"
                                        class="h-full w-full object-contain"
                                    ></video>
                                </div>
                            </template>

                            <template x-if="!activeLesson.video_url && activeLesson.external_video_url">
                                <div class="rounded-xl border border-amber-800/60 bg-amber-950/40 p-4 text-xs text-amber-200">
                                    This lesson uses an external video host (e.g., YouTube/Vimeo) which requires an internet connection. Downloadable notes and reading materials remain available below.
                                </div>
                            </template>

                            {{-- Reading Content --}}
                            <template x-if="activeLesson.reading_body">
                                <div class="prose prose-invert max-w-none text-sm leading-relaxed" x-html="activeLesson.reading_body"></div>
                            </template>

                            {{-- Cached SCORM Module --}}
                            <template x-if="activeLesson.scorm_launch_url">
                                <div class="aspect-video overflow-hidden rounded-xl border border-slate-800 bg-white">
                                    <iframe :src="activeLesson.scorm_launch_url" class="h-full w-full border-0"></iframe>
                                </div>
                            </template>

                            {{-- Cached Downloadable Resources --}}
                            <template x-if="(activeLesson.resources || []).length > 0">
                                <div class="space-y-2 border-t border-slate-800 pt-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Saved Offline Resources</h4>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <template x-for="res in activeLesson.resources" :key="res.index">
                                            <a
                                                :href="res.url"
                                                target="_blank"
                                                class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2 text-xs text-slate-200 hover:bg-slate-800"
                                            >
                                                <span class="truncate font-medium" x-text="res.name"></span>
                                                <span class="ml-2 font-bold text-teal-400">Open</span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </main>
            </div>
        </template>
    </div>
</body>
</html>
