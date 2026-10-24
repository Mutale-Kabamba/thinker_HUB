<div class="space-y-4 text-sm text-slate-700 dark:text-slate-300 font-sans">
    <div class="rounded-xl border border-teal-500/30 bg-teal-50/50 dark:bg-teal-950/30 p-4">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0 font-bold">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white text-base">Course Build Structure Guide</h4>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    Follow this standardized 8-step structure when creating and structuring new courses on Thinker Hub.
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        {{-- Step 1 --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-teal-600 dark:text-teal-400 text-xs flex items-center justify-center font-bold">1</span>
                <span>Course Identity & Code</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                Provide a clean, engaging course title and a unique catalog code (e.g. <strong class="text-teal-600 dark:text-teal-400 font-mono">Full-Stack Web Development</strong>, Code: <strong class="text-teal-600 dark:text-teal-400 font-mono">FSW-201</strong>).
            </p>
        </div>

        {{-- Step 2 --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-teal-600 dark:text-teal-400 text-xs flex items-center justify-center font-bold">2</span>
                <span>Description & High-Level Overview</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                Include a comprehensive description of the syllabus alongside a high-level executive summary displayed on course catalog cards.
            </p>
        </div>

        {{-- Step 3 --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-teal-600 dark:text-teal-400 text-xs flex items-center justify-center font-bold">3</span>
                <span>Timeline & Pacing</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                Specify duration and estimated student time commitment (e.g. <em class="text-slate-800 dark:text-slate-200">8 Weeks (approx. 4-5 hours per week)</em>).
            </p>
        </div>

        {{-- Step 4 --}}
        <div class="p-3.5 rounded-xl border border-teal-500/30 dark:border-teal-500/20 bg-teal-500/5 dark:bg-teal-950/20 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-teal-500 text-white text-xs flex items-center justify-center font-bold">4</span>
                <span>Standardized Fees Structure</span>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-700 dark:text-teal-300 ml-auto">Important</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                Use one entry per line formatted as: <code class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 font-mono text-xs text-teal-700 dark:text-teal-300">Category + Level + Amount + Duration</code>
            </p>
            <div class="mt-2 pl-8 space-y-1 font-mono text-xs text-slate-700 dark:text-slate-300">
                <div class="p-2 rounded-lg bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800">
                    <span class="text-slate-400">Example 1:</span> One-On-One + Beginner + K450 + 6 Weeks<br>
                    <span class="text-slate-400">Example 2:</span> Group + Intermediate + K300 + 8 Weeks
                </div>
            </div>
        </div>

        {{-- Step 5 --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-teal-600 dark:text-teal-400 text-xs flex items-center justify-center font-bold">5</span>
                <span>Requirements & Prerequisites</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                List each prerequisite on a new line (e.g. hardware specs, basic computer literacy, or prior programming language experience).
            </p>
        </div>

        {{-- Step 6 --}}
        <div class="p-3.5 rounded-xl border border-teal-500/30 dark:border-teal-500/20 bg-teal-500/5 dark:bg-teal-950/20 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-teal-500 text-white text-xs flex items-center justify-center font-bold">6</span>
                <span>Level Progression</span>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-700 dark:text-teal-300 ml-auto">Important</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                Use one entry per line formatted as: <code class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 font-mono text-xs text-teal-700 dark:text-teal-300">Level + Description</code>
            </p>
            <div class="mt-2 pl-8 space-y-1 font-mono text-xs text-slate-700 dark:text-slate-300">
                <div class="p-2 rounded-lg bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800">
                    <span class="text-slate-400">Beginner +</span> Foundations, core syntax, and development environment setup<br>
                    <span class="text-slate-400">Intermediate +</span> Hands-on project implementation, APIs, and database modeling<br>
                    <span class="text-slate-400">Advanced +</span> Production deployment, optimization, and capstone project
                </div>
            </div>
        </div>

        {{-- Step 7 --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-teal-600 dark:text-teal-400 text-xs flex items-center justify-center font-bold">7</span>
                <span>Key Learning Outcomes</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                Clearly state what skills, tools, and portfolios learners will have mastered by the end of the course.
            </p>
        </div>

        {{-- Step 8 --}}
        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm">
                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-teal-600 dark:text-teal-400 text-xs flex items-center justify-center font-bold">8</span>
                <span>Roadmap & Detailed Curriculum</span>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 pl-8">
                Prepare a week-by-week roadmap or syllabus document (PDF/Word/ZIP) to upload for administrative review.
            </p>
        </div>
    </div>
</div>
