{{--
    Skill Level Progression Roadmap Framework & Guidelines Modal
    Included on: course.blade.php
    Triggered with JS: window.openSkillLevelModal({ levelName, tier, badge, badgeColor, target, focus, milestones, trackType, courseDetails, courseTitle })
--}}
<div
    id="skill-level-progression-modal"
    class="fixed inset-0 z-[10003] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="skill-level-modal-title"
    x-data="skillLevelModalHandler()"
    x-cloak
    style="display: none;"
    x-show="isOpen"
    @open-skill-level-modal.window="openModal($event.detail)"
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
        class="relative w-full max-w-xl bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-2xl transition-all duration-300 z-10 my-auto flex flex-col max-h-[90vh]"
        x-show="isOpen"
        x-transition:enter="ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 scale-95"
    >
        {{-- Top Gradient Header --}}
        <div class="bg-gradient-to-r from-[#0a2d27] via-[#0d3b33] to-[#134e48] px-6 py-5 text-white relative overflow-hidden flex-shrink-0">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-teal-400/20 blur-xl pointer-events-none"></div>
            <div class="absolute -left-6 -top-6 w-24 h-24 rounded-full bg-amber-400/15 blur-lg pointer-events-none"></div>

            <div class="flex items-start justify-between gap-4 relative z-10">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-400/20 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-yellow-300 border border-yellow-400/30">
                            <i class="fa-solid fa-chart-line text-[9px]"></i>
                            Progression Framework
                        </span>
                        <span class="text-xs font-semibold text-teal-200/90" x-text="tier"></span>
                    </div>
                    <h3 id="skill-level-modal-title" class="mt-2 text-xl sm:text-2xl font-black text-white leading-tight" x-text="levelName"></h3>
                    <p class="mt-1 text-xs text-teal-100/80 font-medium" x-text="trackType"></p>
                </div>

                <button
                    type="button"
                    @click="closeModal()"
                    class="h-8 w-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition flex-shrink-0 cursor-pointer"
                    aria-label="Close modal"
                >
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Scrollable Body --}}
        <div class="p-6 overflow-y-auto space-y-5 text-slate-700 text-sm flex-1">
            
            {{-- Course Specific Note (if course has customized progression notes) --}}
            <template x-if="(levelSections && levelSections.length > 0) || courseDetails">
                <div class="rounded-2xl border border-teal-200 bg-teal-50/70 p-4 sm:p-5 space-y-3">
                    <div class="flex items-center gap-2 text-teal-900 font-bold text-xs uppercase tracking-wide">
                        <i class="fa-solid fa-circle-check text-teal-600 text-sm"></i>
                        <span>Course Milestone Outcomes: <span class="font-black text-teal-950" x-text="courseTitle"></span></span>
                    </div>

                    {{-- Structured breakdown for combined levels (e.g. Beginner + Intermediate + Advanced) --}}
                    <template x-if="levelSections && levelSections.length > 0">
                        <div class="space-y-2.5">
                            <template x-for="(sec, sIdx) in levelSections" :key="sIdx">
                                <div class="rounded-xl border border-teal-200/80 bg-white p-3.5 space-y-1 shadow-2xs">
                                    <div class="flex items-center gap-2 font-bold text-teal-900 text-xs">
                                        <span class="w-2 h-2 rounded-full bg-teal-500 flex-shrink-0"></span>
                                        <span x-text="sec.level"></span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed text-justify" x-text="sec.details"></p>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Fallback single text block if levelSections is empty --}}
                    <template x-if="(!levelSections || levelSections.length === 0) && courseDetails">
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed text-justify whitespace-pre-line" x-text="courseDetails"></p>
                    </template>
                </div>
            </template>

            {{-- Target Audience --}}
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <i class="fa-solid fa-users text-teal-600"></i>
                    <span>Target Audience</span>
                </div>
                <p class="text-sm text-slate-800 leading-relaxed text-justify" x-text="target"></p>
            </div>

            {{-- Core Focus & Curriculum --}}
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <i class="fa-solid fa-bullseye text-teal-600"></i>
                    <span>Core Focus &amp; Learning Objectives</span>
                </div>
                <p class="text-sm text-slate-800 leading-relaxed text-justify" x-text="focus"></p>
            </div>

            {{-- Key Milestones & Competencies --}}
            <div class="space-y-2 pt-1">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <i class="fa-solid fa-list-check text-teal-600"></i>
                    <span>Key Competencies &amp; Progression Milestones</span>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2.5">
                    <template x-for="(milestone, idx) in milestones" :key="idx">
                        <div class="flex items-start gap-2.5 text-xs sm:text-sm text-slate-700">
                            <span class="h-5 w-5 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-[10px] flex-shrink-0 mt-0.5" x-text="idx + 1"></span>
                            <span class="leading-snug text-justify" x-text="milestone"></span>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="border-t border-slate-100 bg-slate-50 px-6 py-4 flex items-center justify-between gap-3 flex-shrink-0">
            <span class="text-xs text-slate-400 hidden sm:inline">
                Skill Level Progression Roadmap Framework &amp; Guidelines
            </span>
            <button
                type="button"
                @click="closeModal()"
                class="w-full sm:w-auto px-6 py-2.5 rounded-full bg-[#0a2d27] hover:bg-[#134e48] text-white font-bold text-xs transition cursor-pointer ml-auto"
            >
                Close
            </button>
        </div>
    </div>
</div>

<script>
    function skillLevelModalHandler() {
        return {
            isOpen: false,
            levelName: '',
            tier: '',
            badge: '',
            badgeColor: 'teal',
            target: '',
            focus: '',
            milestones: [],
            trackType: '',
            courseDetails: '',
            courseTitle: '',
            levelSections: [],

            openModal(detail) {
                this.levelName = detail.levelName || 'Skill Level';
                this.tier = detail.tier || '';
                this.badge = detail.badge || '';
                this.badgeColor = detail.badgeColor || 'teal';
                this.target = detail.target || '';
                this.focus = detail.focus || '';
                this.milestones = Array.isArray(detail.milestones) ? detail.milestones : [];
                this.trackType = detail.trackType || '';
                this.courseDetails = detail.courseDetails || '';
                this.courseTitle = detail.courseTitle || '';
                this.levelSections = Array.isArray(detail.levelSections) ? detail.levelSections : [];
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeModal() {
                this.isOpen = false;
                document.body.style.overflow = '';
            }
        };
    }

    window.openSkillLevelModal = function(levelData) {
        window.dispatchEvent(new CustomEvent('open-skill-level-modal', {
            detail: levelData
        }));
    };
</script>
