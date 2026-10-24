{{--
    Course Level & Mode Pre-Checkout Selection Modal
    Included on: courses.blade.php, course.blade.php, filament student courses
    Triggered with JS: window.openCourseOptionModal({ id, title, code, checkoutUrl, options: [...] })
--}}
<style>
    [x-cloak] { display: none !important; }
</style>

<div
    id="course-option-modal"
    class="fixed inset-0 z-[10002] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="course-opt-title"
    x-data="courseOptionModalHandler()"
    x-cloak
    style="display: none;"
    x-show="isOpen"
    @open-course-option-modal.window="openModal($event.detail)"
    @keydown.escape.window="closeModal()"
>
    {{-- Backdrop with blur --}}
    <div
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-300"
        x-show="isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeModal()"
    ></div>

    {{-- Modal Sheet --}}
    <div
        @click.stop
        class="relative w-full max-w-lg bg-white rounded-2xl overflow-hidden border border-slate-200/80 shadow-2xl transition-all duration-300 z-10 my-auto"
        x-show="isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-98"
    >
        {{-- Top Gradient Header --}}
        <div class="bg-[#0a2d27] px-5 py-3.5 text-white relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-teal-600/20 blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between gap-3 relative z-10">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-yellow-400/20 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-yellow-300 border border-yellow-400/30">
                            Select Plan
                        </span>
                        <span class="text-[11px] font-mono text-teal-300" x-text="courseCode"></span>
                    </div>
                    <h3 id="course-opt-title" class="mt-1 text-base sm:text-lg font-black text-white leading-snug truncate" x-text="courseTitle"></h3>
                </div>
                <button
                    type="button"
                    @click="closeModal()"
                    class="h-7 w-7 rounded-full bg-white/10 text-white/80 hover:text-white hover:bg-white/20 flex items-center justify-center transition shrink-0 cursor-pointer"
                    aria-label="Close modal"
                >
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        {{-- Mode Filter Tabs (visible when both Group and 1:1 exist) --}}
        <div class="px-5 py-2.5 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between gap-2" x-show="availableModes.length > 1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Mode:</p>
            <div class="inline-flex rounded-lg bg-slate-200/60 p-0.5 border border-slate-200/80">
                <button
                    type="button"
                    @click="activeFilter = 'all'"
                    class="px-2.5 py-0.5 text-[11px] font-bold rounded-md transition cursor-pointer"
                    :class="activeFilter === 'all' ? 'bg-white text-[#0a2d27] shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                >
                    All (<span x-text="options.length"></span>)
                </button>
                <template x-for="mode in availableModes" :key="mode">
                    <button
                        type="button"
                        @click="activeFilter = mode"
                        class="px-2.5 py-0.5 text-[11px] font-bold rounded-md transition capitalize cursor-pointer"
                        :class="activeFilter === mode ? 'bg-white text-[#0a2d27] shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                        x-text="mode === 'one_on_one' ? '1:1 Private' : (mode === 'self_paced' ? 'Self-Paced' : 'Group Class')"
                    >
                    </button>
                </template>
            </div>
        </div>

        {{-- Options Cards List --}}
        <div class="p-4 sm:p-5 max-h-[50vh] overflow-y-auto space-y-2.5">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.id || idx">
                <div
                    @click="selectOption(opt)"
                    class="group relative rounded-xl border p-3 sm:py-3 sm:px-3.5 transition-all duration-150 cursor-pointer flex items-center justify-between gap-3"
                    :class="selectedOption && selectedOption.id === opt.id
                        ? 'border-teal-600 bg-teal-50/60 ring-1 ring-teal-600/30 shadow-2xs'
                        : 'border-slate-200 bg-white hover:border-teal-300 hover:bg-slate-50/40'"
                >
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        {{-- Radio Indicator --}}
                        <div class="shrink-0">
                            <div
                                class="h-4 w-4 rounded-full border flex items-center justify-center transition-colors"
                                :class="selectedOption && selectedOption.id === opt.id
                                    ? 'border-teal-600 bg-teal-600 text-white'
                                    : 'border-slate-300 bg-white group-hover:border-teal-400'"
                            >
                                <i class="fa-solid fa-check text-[8px]" x-show="selectedOption && selectedOption.id === opt.id"></i>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="opt.level"></span>
                                <span
                                    class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-semibold"
                                    :class="opt.category === 'one_on_one'
                                        ? 'bg-purple-50 text-purple-700 border border-purple-200'
                                        : (opt.category === 'self_paced'
                                            ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                            : 'bg-emerald-50 text-emerald-700 border border-emerald-200')"
                                    x-text="opt.mode_label"
                                >
                                </span>
                                <template x-if="opt.duration">
                                    <span class="inline-flex items-center gap-1 text-[10px] text-slate-400 font-medium">
                                        <i class="fa-regular fa-clock text-slate-400 text-[9px]"></i>
                                        <span x-text="opt.duration"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Price Column --}}
                    <div class="text-right shrink-0">
                        <span class="text-sm sm:text-base font-extrabold text-[#0a2d27] block tabular-nums" x-text="opt.formatted_amount"></span>
                        <span
                            class="text-[9px] font-semibold px-1.5 py-0.2 rounded inline-block"
                            :class="selectedOption && selectedOption.id === opt.id
                                ? 'text-teal-700 font-bold'
                                : 'text-slate-400'"
                            x-text="selectedOption && selectedOption.id === opt.id ? 'Selected' : (opt.mode_badge || '')"
                        >
                        </span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer CTA --}}
        <div class="bg-slate-50/80 px-5 py-3 border-t border-slate-100 flex items-center justify-between gap-3">
            <div class="text-xs min-w-0 flex-1">
                <template x-if="selectedOption">
                    <div class="truncate">
                        <span class="text-slate-400 text-[11px]">Selected:</span>
                        <strong class="text-slate-800 text-xs ml-1" x-text="selectedOption.level"></strong>
                        <span class="mx-1 text-slate-300">&bull;</span>
                        <strong class="text-teal-700 text-xs tabular-nums" x-text="selectedOption.formatted_amount"></strong>
                    </div>
                </template>
            </div>

            <button
                type="button"
                @click="proceedToCheckout()"
                :disabled="!selectedOption"
                class="inline-flex items-center justify-center gap-1.5 rounded-full bg-yellow-400 px-5 py-2 text-xs font-bold text-[#0a2d27] transition hover:bg-yellow-300 active:scale-[.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer shrink-0"
            >
                <span>Proceed to Checkout</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </button>
        </div>
    </div>
</div>

<script>
function courseOptionModalHandler() {
    return {
        isOpen: false,
        courseId: null,
        courseTitle: '',
        courseCode: '',
        checkoutBaseUrl: '',
        options: [],
        selectedOption: null,
        activeFilter: 'all',

        get availableModes() {
            if (!this.options || !Array.isArray(this.options)) return [];
            const modes = new Set(this.options.map(o => o.category || o.mode || 'group'));
            return Array.from(modes);
        },

        get filteredOptions() {
            if (!this.options || !Array.isArray(this.options)) return [];
            if (this.activeFilter === 'all') {
                return this.options;
            }
            return this.options.filter(o => (o.category || o.mode) === this.activeFilter);
        },

        openModal(data) {
            if (!data) return;
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    console.error('Invalid course modal data JSON:', e);
                    return;
                }
            }

            this.courseId = data.id || null;
            this.courseTitle = data.title || 'Course Enrollment';
            this.courseCode = data.code || '';
            this.checkoutBaseUrl = data.checkoutUrl || (this.courseId ? `/courses/${this.courseId}/checkout` : '/courses');
            this.options = Array.isArray(data.options) ? data.options : [];
            this.activeFilter = 'all';

            // Auto-select preferred or first option
            if (this.options.length > 0) {
                const defaultTrack = data.defaultTrack ? String(data.defaultTrack).toLowerCase() : null;
                const defaultMode = data.defaultMode ? String(data.defaultMode).toLowerCase() : null;

                let match = null;
                if (defaultMode && defaultTrack) {
                    match = this.options.find(o => (o.category || o.mode) === defaultMode && String(o.level).toLowerCase() === defaultTrack);
                }
                if (!match && defaultTrack) {
                    match = this.options.find(o => String(o.level).toLowerCase() === defaultTrack);
                }
                if (!match && defaultMode) {
                    match = this.options.find(o => (o.category || o.mode) === defaultMode);
                }
                this.selectedOption = match || this.options[0];
            } else {
                this.selectedOption = null;
            }

            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        selectOption(opt) {
            this.selectedOption = opt;
        },

        proceedToCheckout() {
            if (!this.selectedOption) return;

            const level = this.selectedOption.level || 'Beginner';
            const mode = this.selectedOption.category || this.selectedOption.mode || 'group';

            try {
                const targetUrl = new URL(this.checkoutBaseUrl, window.location.origin);
                targetUrl.searchParams.set('track', level);
                targetUrl.searchParams.set('mode', mode);
                window.location.href = targetUrl.toString();
            } catch (err) {
                const sep = this.checkoutBaseUrl.includes('?') ? '&' : '?';
                window.location.href = `${this.checkoutBaseUrl}${sep}track=${encodeURIComponent(level)}&mode=${encodeURIComponent(mode)}`;
            }
        }
    };
}

// Global JavaScript trigger helper
window.openCourseOptionModal = function(courseData) {
    try {
        window.dispatchEvent(new CustomEvent('open-course-option-modal', {
            detail: courseData
        }));
    } catch (err) {
        console.error('Failed to dispatch open-course-option-modal event:', err);
    }
};
</script>
