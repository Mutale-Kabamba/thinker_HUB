{{--
    Course Level & Mode Pre-Checkout Selection Modal
    Included on: courses.blade.php, course.blade.php, filament student courses
    Triggered with JS: window.openCourseOptionModal({ id, title, code, checkoutUrl, options: [...] })
--}}

<div
    id="course-option-modal"
    class="fixed inset-0 z-[150] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="course-opt-title"
    x-data="courseOptionModalHandler()"
    x-cloak
    x-show="isOpen"
    @open-course-option-modal.window="openModal($event.detail)"
    @keydown.escape.window="closeModal()"
>
    {{-- Backdrop with blur --}}
    <div
        class="fixed inset-0 bg-[#0a2d27]/70 backdrop-blur-sm transition-opacity duration-300"
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
        class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-teal-100 transition-all duration-300 z-10 my-8"
        x-show="isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 scale-95"
    >
        {{-- Top Gradient Header --}}
        <div class="bg-[#0a2d27] px-6 py-5 text-white relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-teal-600/20 blur-xl pointer-events-none"></div>
            <div class="flex items-start justify-between gap-4 relative z-10">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-yellow-400/20 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-yellow-300 border border-yellow-400/30">
                            Select Option
                        </span>
                        <span class="text-xs font-mono text-teal-300" x-text="courseCode"></span>
                    </div>
                    <h3 id="course-opt-title" class="mt-1 text-lg sm:text-xl font-black text-white leading-snug truncate" x-text="courseTitle"></h3>
                    <p class="mt-1 text-xs text-slate-300">Choose your preferred learning track and delivery mode before checkout.</p>
                </div>
                <button
                    type="button"
                    @click="closeModal()"
                    class="h-8 w-8 rounded-full bg-white/10 text-white/80 hover:text-white hover:bg-white/20 flex items-center justify-center transition shrink-0 cursor-pointer"
                    aria-label="Close modal"
                >
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Mode Filter Tabs (visible when both Group and 1:1 exist) --}}
        <div class="px-6 pt-4 pb-2 border-b border-slate-100 flex items-center justify-between gap-2" x-show="availableModes.length > 1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Filter Delivery Mode:</p>
            <div class="inline-flex rounded-xl bg-slate-100 p-1">
                <button
                    type="button"
                    @click="activeFilter = 'all'"
                    class="px-3 py-1 text-xs font-bold rounded-lg transition"
                    :class="activeFilter === 'all' ? 'bg-white text-[#0a2d27] shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                >
                    All (<span x-text="options.length"></span>)
                </button>
                <template x-for="mode in availableModes" :key="mode">
                    <button
                        type="button"
                        @click="activeFilter = mode"
                        class="px-3 py-1 text-xs font-bold rounded-lg transition capitalize"
                        :class="activeFilter === mode ? 'bg-white text-[#0a2d27] shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                        x-text="mode === 'one_on_one' ? '1:1 Private' : 'Group Class'"
                    >
                    </button>
                </template>
            </div>
        </div>

        {{-- Options Cards List --}}
        <div class="p-6 max-h-[52vh] overflow-y-auto space-y-3">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.id || idx">
                <div
                    @click="selectOption(opt)"
                    class="group relative rounded-2xl border-2 p-4 transition-all duration-200 cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                    :class="selectedOption && selectedOption.id === opt.id
                        ? 'border-teal-600 bg-teal-50/70 shadow-md ring-2 ring-teal-600/20'
                        : 'border-slate-200 bg-white hover:border-teal-300 hover:bg-slate-50/50 shadow-xs'"
                >
                    <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        {{-- Radio / Checkbox Indicator --}}
                        <div class="pt-0.5 shrink-0">
                            <div
                                class="h-5 w-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                :class="selectedOption && selectedOption.id === opt.id
                                    ? 'border-teal-600 bg-teal-600 text-white'
                                    : 'border-slate-300 bg-white group-hover:border-teal-400'"
                            >
                                <i class="fa-solid fa-check text-[10px]" x-show="selectedOption && selectedOption.id === opt.id"></i>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-sm font-black text-slate-900" x-text="opt.level + ' Level'"></span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold"
                                    :class="opt.category === 'one_on_one'
                                        ? 'bg-purple-100 text-purple-800 border border-purple-200'
                                        : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                    x-text="opt.mode_label"
                                >
                                </span>
                                <template x-if="opt.duration">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500">
                                        <i class="fa-regular fa-clock text-teal-600 text-[10px]"></i>
                                        <span x-text="opt.duration"></span>
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed" x-text="opt.highlight"></p>
                        </div>
                    </div>

                    {{-- Price & Badge Column --}}
                    <div class="sm:text-right shrink-0 flex sm:flex-col items-center sm:items-end justify-between border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                        <span class="text-base font-black text-[#0a2d27]" x-text="opt.formatted_amount"></span>
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded-full mt-1"
                            :class="selectedOption && selectedOption.id === opt.id
                                ? 'bg-teal-600 text-white'
                                : 'bg-slate-100 text-slate-600'"
                            x-text="selectedOption && selectedOption.id === opt.id ? 'Selected' : opt.mode_badge"
                        >
                        </span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer CTA --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-600 text-center sm:text-left">
                <template x-if="selectedOption">
                    <div>
                        <span class="text-slate-400">Selected Plan:</span>
                        <strong class="text-slate-800 ml-1" x-text="selectedOption.level + ' (' + selectedOption.mode_label + ')'"></strong>
                        <span class="mx-1 text-slate-300">&bull;</span>
                        <strong class="text-teal-700" x-text="selectedOption.formatted_amount"></strong>
                    </div>
                </template>
            </div>

            <button
                type="button"
                @click="proceedToCheckout()"
                :disabled="!selectedOption"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-yellow-400 px-6 py-3 text-xs font-extrabold text-[#0a2d27] shadow-md shadow-yellow-400/20 transition hover:bg-yellow-300 active:scale-[.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
                <span>Proceed to Checkout</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
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
            const modes = new Set(this.options.map(o => o.category || o.mode || 'group'));
            return Array.from(modes);
        },

        get filteredOptions() {
            if (this.activeFilter === 'all') {
                return this.options;
            }
            return this.options.filter(o => (o.category || o.mode) === this.activeFilter);
        },

        openModal(data) {
            this.courseId = data.id;
            this.courseTitle = data.title || 'Course Enrollment';
            this.courseCode = data.code || '';
            this.checkoutBaseUrl = data.checkoutUrl || ('/courses/' + data.id + '/checkout');
            this.options = Array.isArray(data.options) ? data.options : [];
            this.activeFilter = 'all';

            // Auto-select preferred or first option
            if (this.options.length > 0) {
                const defaultTrack = data.defaultTrack ? String(data.defaultTrack).toLowerCase() : 'beginner';
                const defaultMode = data.defaultMode ? String(data.defaultMode).toLowerCase() : null;

                let match = null;
                if (defaultMode) {
                    match = this.options.find(o => (o.category || o.mode) === defaultMode && o.level.toLowerCase() === defaultTrack);
                }
                if (!match) {
                    match = this.options.find(o => o.level.toLowerCase() === defaultTrack);
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

            const targetUrl = new URL(this.checkoutBaseUrl, window.location.origin);
            targetUrl.searchParams.set('track', this.selectedOption.level);
            targetUrl.searchParams.set('mode', this.selectedOption.category || this.selectedOption.mode || 'group');

            window.location.href = targetUrl.toString();
        }
    };
}

// Global JavaScript trigger helper
window.openCourseOptionModal = function(courseData) {
    window.dispatchEvent(new CustomEvent('open-course-option-modal', {
        detail: courseData
    }));
};
</script>
