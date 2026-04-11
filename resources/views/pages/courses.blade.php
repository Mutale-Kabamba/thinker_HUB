<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Courses | think.er HUB',
        'description' => 'Explore practical courses in MS Office, design, social media, data analysis, and digital literacy built for real-world outcomes.',
        'keywords' => 'courses, ms office, graphic design, data analysis, social media ai',
        'type' => 'website',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    <header class="sticky top-0 z-50 bg-[#0a2d27] py-4 shadow-lg">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-white shrink-0">
                <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
            </a>

            <nav class="hidden md:flex items-center gap-10 text-[13px] font-semibold uppercase tracking-wider text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-yellow-400 transition-colors">Home</a>
                <a href="{{ route('landing.courses') }}" class="text-yellow-400">Courses</a>
                <a href="{{ route('landing.instructors') }}" class="hover:text-yellow-400 transition-colors">Instructors</a>
                <a href="{{ route('landing.contact') }}" class="hover:text-yellow-400 transition-colors">Contact</a>
            </nav>

            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('login') }}" class="text-sm font-bold text-white hover:text-yellow-400">Login</a>
                <a href="{{ route('enroll') }}" class="rounded-full bg-yellow-400 px-6 py-2.5 text-sm font-bold text-[#0a2d27] hover:bg-white transition-all">Enroll Now</a>
            </div>

            <button class="md:hidden text-white text-2xl" @click="mobileMenu = !mobileMenu">
                <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
            </button>
        </div>

        <div class="md:hidden bg-[#0a2d27] border-t border-white/10" x-show="mobileMenu" x-transition>
            <nav class="flex flex-col p-6 gap-4 text-white font-semibold">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('landing.courses') }}" class="text-yellow-400">Courses</a>
                <a href="{{ route('landing.instructors') }}">Instructors</a>
                <a href="{{ route('landing.contact') }}">Contact</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-16 lg:py-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Learning Catalog</p>
                <h1 class="mt-4 text-4xl font-black text-white sm:text-5xl">Explore Our Courses</h1>
                <p class="mx-auto mt-5 max-w-2xl text-slate-300">Choose the track and course that match your goals, then enroll and start learning right away.</p>
            </div>
        </section>

        <section class="py-20 lg:py-24">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                @php
                    $courseDetails = [];

                    foreach ($courses as $course) {
                        $courseDetails[$course->id] = [
                            'title' => $course->title,
                            'overview' => $course->overview,
                            'timeline' => $course->timeline,
                            'fees' => $course->fees,
                            'requirements' => $course->requirements,
                            'level_progression' => $course->level_progression,
                            'key_outcome' => $course->key_outcome,
                        ];
                    }
                @endphp
                <div
                    x-data="{
                        selectedCourseId: null,
                        details: @js($courseDetails),
                        parseStructured(value) {
                            if (value === null || value === undefined) {
                                return value;
                            }

                            if (typeof value !== 'string') {
                                return value;
                            }

                            const trimmed = value.trim();

                            if (!trimmed) {
                                return '';
                            }

                            if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
                                try {
                                    return JSON.parse(trimmed);
                                } catch (error) {
                                    return value;
                                }
                            }

                            return value;
                        },
                        lines(value) {
                            const parsed = this.parseStructured(value);

                            if (Array.isArray(parsed)) {
                                return parsed
                                    .map((line) => {
                                        if (typeof line === 'string') {
                                            return line.trim();
                                        }

                                        if (line && typeof line === 'object') {
                                            const text = [line.level, line.amount, line.duration, line.details]
                                                .filter(Boolean)
                                                .join(' - ');

                                            return text.trim();
                                        }

                                        return '';
                                    })
                                    .filter(Boolean);
                            }

                            if (typeof parsed !== 'string') {
                                return [];
                            }

                            return parsed.split('\\n').map(line => line.trim()).filter(Boolean);
                        },
                        feeLabel(key) {
                            if (key === 'one_on_one') {
                                return 'One-on-One';
                            }

                            if (key === 'group') {
                                return 'Group';
                            }

                            return String(key)
                                .replace(/_/g, ' ')
                                .replace(/\b\w/g, char => char.toUpperCase());
                        },
                        feeBadge(key) {
                            if (key === 'one_on_one') {
                                return '1:1 Focus';
                            }

                            if (key === 'group') {
                                return 'Best Value';
                            }

                            return '';
                        },
                        normalizeFeeSectionKey(key) {
                            const normalized = String(key || '')
                                .trim()
                                .toLowerCase()
                                .replace(/[-\s]+/g, '_');

                            if (['one_on_one', 'one2one', 'one_to_one', 'private', 'private_class'].includes(normalized)) {
                                return 'one_on_one';
                            }

                            if (['group', 'group_class', 'group_classes', 'class_group'].includes(normalized)) {
                                return 'group';
                            }

                            return normalized || 'fees';
                        },
                        detectFeeMode(value) {
                            const text = String(value || '').toLowerCase();

                            if (/one\s*[-:]?\s*on\s*[-:]?\s*one|1\s*[:x]\s*1|private/.test(text)) {
                                return 'one_on_one';
                            }

                            if (/group/.test(text)) {
                                return 'group';
                            }

                            return 'fees';
                        },
                        extractFeeModeAndRemainder(value) {
                            const mode = this.normalizeFeeSectionKey(this.detectFeeMode(value));
                            const remainder = String(value || '')
                                .replace(/^(one\s*[-\s:]?\s*on\s*[-\s:]?\s*one|1\s*[:x]\s*1|private|group)\s*(?:[:\-|]\s*)?/i, '')
                                .trim();

                            return {
                                mode,
                                remainder: remainder || String(value || '').trim(),
                            };
                        },
                        cleanFeeToken(value) {
                            return String(value || '')
                                .replace(/\b(one\s*[-:]?\s*on\s*[-:]?\s*one|1\s*[:x]\s*1|private|group)\b\s*[:\-]?\s*/ig, '')
                                .replace(/\s{2,}/g, ' ')
                                .trim();
                        },
                        cleanLevelText(value) {
                            return this.cleanFeeToken(String(value || '').replace(/^level\s*[:\-]\s*/i, ''));
                        },
                        parseFeeRows(rows) {
                            return (rows || [])
                                .flatMap((entry) => {
                                    if (entry && typeof entry === 'object') {
                                        const mode = this.normalizeFeeSectionKey(entry.mode || entry.type || this.detectFeeMode(`${entry.level || ''} ${entry.amount || ''}`));
                                        return [{
                                            level: this.cleanLevelText(entry.level || ''),
                                            amount: this.cleanFeeToken(entry.amount || ''),
                                            duration: String(entry.duration || '').trim(),
                                            mode,
                                        }];
                                    }

                                    const line = String(entry || '').trim();

                                    if (!line) {
                                        return [];
                                    }

                                    const extracted = this.extractFeeModeAndRemainder(line);
                                    const normalizedLine = extracted.remainder;
                                    const mode = extracted.mode;
                                    const compactLine = normalizedLine.replace(/\s+/g, ' ').trim();
                                    const levelPattern = /(Beginner|Intermediate|Advanced)\s*[:\-]\s*([^()]+?)\s*(?:\(([^)]+)\))?(?=\s*(?:Beginner|Intermediate|Advanced)\s*[:\-]|$)/gi;
                                    const multiRows = [];
                                    let levelMatch;

                                    while ((levelMatch = levelPattern.exec(compactLine)) !== null) {
                                        multiRows.push({
                                            level: this.cleanLevelText(levelMatch[1]),
                                            amount: this.cleanFeeToken(levelMatch[2]),
                                            duration: (levelMatch[3] || '').trim(),
                                            mode,
                                        });
                                    }

                                    if (multiRows.length) {
                                        return multiRows;
                                    }

                                    const simpleMatch = normalizedLine.match(/^([^:()|]+?)\s*:\s*([^()]+?)\s*(?:\(([^)]+)\))?$/);

                                    if (simpleMatch) {
                                        return [{
                                            level: this.cleanLevelText(simpleMatch[1]),
                                            amount: this.cleanFeeToken(simpleMatch[2]),
                                            duration: (simpleMatch[3] || '').trim(),
                                            mode,
                                        }];
                                    }

                                    const hyphenMatch = normalizedLine.match(/^(.+?)\s+-\s+([^()]+?)\s*(?:\(([^)]+)\))?$/);

                                    if (hyphenMatch) {
                                        return [{
                                            level: this.cleanLevelText(hyphenMatch[1]),
                                            amount: this.cleanFeeToken(hyphenMatch[2]),
                                            duration: (hyphenMatch[3] || '').trim(),
                                            mode,
                                        }];
                                    }

                                    const parts = normalizedLine.split('|').map(part => part.trim()).filter(Boolean);

                                    if (parts.length >= 2) {
                                        return [{
                                            level: this.cleanLevelText(parts[0]),
                                            amount: this.cleanFeeToken(parts[1]),
                                            duration: parts[2] || '',
                                            mode,
                                        }];
                                    }

                                    return [{
                                        level: this.cleanLevelText(normalizedLine),
                                        amount: '',
                                        duration: '',
                                        mode,
                                    }];
                                })
                                .filter(row => row && (row.level || row.amount || row.duration));
                        },
                        feeTableSections(value) {
                            const parsed = this.parseStructured(value);

                            if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                                return Object.entries(parsed)
                                    .map(([key, rows]) => ({
                                        key: this.normalizeFeeSectionKey(key),
                                        label: this.feeLabel(this.normalizeFeeSectionKey(key)),
                                        badge: this.feeBadge(this.normalizeFeeSectionKey(key)),
                                        rows: this.parseFeeRows(Array.isArray(rows) ? rows : this.lines(rows)),
                                    }))
                                    .filter(section => section.rows.length);
                            }

                            const rows = this.parseFeeRows(Array.isArray(parsed) ? parsed : this.lines(parsed));

                            if (!rows.length) {
                                return [];
                            }

                            const groupedRows = {
                                one_on_one: rows.filter(row => row.mode === 'one_on_one'),
                                group: rows.filter(row => row.mode === 'group'),
                                fees: rows.filter(row => !['one_on_one', 'group'].includes(row.mode)),
                            };

                            return [
                                { key: 'one_on_one', label: this.feeLabel('one_on_one'), badge: this.feeBadge('one_on_one'), rows: groupedRows.one_on_one },
                                { key: 'group', label: this.feeLabel('group'), badge: this.feeBadge('group'), rows: groupedRows.group },
                                { key: 'fees', label: this.feeLabel('fees'), badge: '', rows: groupedRows.fees },
                            ].filter(section => section.rows.length);
                        },
                        levelProgressions(value) {
                            const parsed = this.parseStructured(value);

                            if (Array.isArray(parsed)) {
                                return parsed
                                    .map((entry) => {
                                        if (typeof entry === 'string') {
                                            const parts = entry.split(':');

                                            if (parts.length > 1) {
                                                return {
                                                    level: parts.shift().trim(),
                                                    details: parts.join(':').trim(),
                                                };
                                            }

                                            return { level: entry.trim(), details: '' };
                                        }

                                        if (!entry || typeof entry !== 'object') {
                                            return null;
                                        }

                                        return {
                                            level: String(entry.level || '').trim(),
                                            details: String(entry.details || '').trim(),
                                        };
                                    })
                                    .filter(item => item && (item.level || item.details));
                            }

                            return this.lines(parsed)
                                .map((line) => {
                                    const parts = line.split(':');

                                    if (parts.length > 1) {
                                        return {
                                            level: parts.shift().trim(),
                                            details: parts.join(':').trim(),
                                        };
                                    }

                                    return { level: line, details: '' };
                                })
                                .filter(item => item.level || item.details);
                        },
                        levelProgressionCards(value) {
                            const entries = this.levelProgressions(value);
                            const levels = ['Beginner', 'Intermediate', 'Advanced'];
                            const sourceText = entries
                                .map(item => [item.level, item.details].filter(Boolean).join(': '))
                                .join('\n');

                            return levels.map((level, index) => {
                                const direct = entries.find(item => String(item.level || '').toLowerCase().includes(level.toLowerCase()));

                                if (direct && direct.details) {
                                    return {
                                        level,
                                        details: direct.details,
                                    };
                                }

                                const nextLevel = levels[index + 1] || null;
                                const boundedPattern = nextLevel
                                    ? new RegExp(`${level}\\s*[:\\-]\\s*([\\s\\S]*?)(?=${nextLevel}\\s*[:\\-]|$)`, 'i')
                                    : new RegExp(`${level}\\s*[:\\-]\\s*([\\s\\S]*?)$`, 'i');
                                const boundedMatch = sourceText.match(boundedPattern);

                                if (boundedMatch && boundedMatch[1] && boundedMatch[1].trim()) {
                                    return {
                                        level,
                                        details: boundedMatch[1].trim(),
                                    };
                                }

                                if (entries.length === 1 && index === 0) {
                                    const fallback = [entries[0].level, entries[0].details].filter(Boolean).join(': ').trim();

                                    if (fallback) {
                                        return {
                                            level,
                                            details: fallback,
                                        };
                                    }
                                }

                                return {
                                    level,
                                    details: 'Details coming soon.',
                                };
                            });
                        }
                    }"
                    class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3"
                >
                    @forelse ($courses as $course)
                        <article class="group bg-white rounded-[2rem] p-4 shadow-sm hover:shadow-xl transition-all border border-slate-100">
                            <div class="relative h-52 overflow-hidden rounded-[1.5rem]">
                                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="Course image">
                                <div class="absolute top-4 left-4 bg-yellow-400 text-[#0a2d27] text-[11px] font-bold px-4 py-1.5 rounded-full shadow-lg">ACTIVE</div>
                            </div>
                            <div class="px-3 py-6">
                                <p class="text-xs font-semibold uppercase tracking-wider text-teal-600">{{ $course->code }}</p>
                                <h3 class="mt-2 text-xl font-bold text-slate-900 group-hover:text-teal-600 transition-colors leading-snug">{{ $course->title }}</h3>
                                <div class="mt-8 flex items-center justify-between border-t border-slate-50 pt-5 text-slate-500 font-medium text-xs">
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-clock text-teal-600"></i> Self paced</span>
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-user text-teal-600"></i> {{ $course->enrollments_count ?? 0 }} Students</span>
                                </div>
                                <button
                                    type="button"
                                    @click="selectedCourseId = {{ $course->id }}"
                                    class="mt-4 inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-teal-500 hover:text-teal-600"
                                >
                                    View Details
                                </button>
                                <a
                                    href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                                    class="mt-2 inline-flex items-center justify-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#11443c]"
                                >
                                    Open Course Page
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full py-24 text-center border-2 border-dashed border-slate-200 rounded-[3rem] bg-slate-50/50">
                            <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto shadow-sm mb-4">
                                <i class="fa-solid fa-book-open text-teal-600 text-2xl"></i>
                            </div>
                            <p class="text-slate-500 font-medium">No active courses available yet.</p>
                        </div>
                    @endforelse

                    <div
                        x-show="selectedCourseId"
                        x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4"
                        style="display: none;"
                    >
                        <div class="relative max-h-[88vh] w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl" @click.outside="selectedCourseId = null">
                            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-teal-600">Course Track Document</p>
                                    <h3 class="mt-1 text-xl font-bold text-slate-900" x-text="details[selectedCourseId]?.title"></h3>
                                </div>
                                <button type="button" @click="selectedCourseId = null" class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:text-slate-800">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="max-h-[64vh] overflow-y-auto px-5 py-4 space-y-4 text-sm">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <h4 class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">Overview</h4>
                                        <p class="mt-2 text-slate-700 leading-relaxed" x-text="details[selectedCourseId]?.overview || 'No overview added yet.'"></p>
                                    </section>
                                    <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <h4 class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">Timeline</h4>
                                        <p class="mt-2 font-semibold text-slate-800" x-text="details[selectedCourseId]?.timeline || 'Not specified yet.'"></p>
                                    </section>
                                </div>

                                <section class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                    <h4 class="text-[11px] font-bold uppercase tracking-[0.08em] text-emerald-700">Fees</h4>
                                    <div class="mt-3 space-y-3">
                                        <template x-for="section in feeTableSections(details[selectedCourseId]?.fees)" :key="section.key">
                                            <article class="rounded-2xl border p-3" :class="section.key === 'group' ? 'border-emerald-200 bg-emerald-50/40' : 'border-indigo-200 bg-indigo-50/45'">
                                                <div class="flex items-center justify-between gap-2">
                                                    <h5 class="text-xs font-black uppercase tracking-[0.08em]" :class="section.key === 'group' ? 'text-emerald-800' : 'text-indigo-800'" x-text="section.label"></h5>
                                                    <span
                                                        x-show="section.badge"
                                                        class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em]"
                                                        :class="section.key === 'group' ? 'border border-emerald-300 text-emerald-700' : 'border border-indigo-300 text-indigo-700'"
                                                        x-text="section.badge"
                                                    ></span>
                                                </div>

                                                <div class="mt-2 overflow-hidden rounded-xl bg-white" :class="section.key === 'group' ? 'border border-emerald-200' : 'border border-indigo-200'">
                                                    <table class="w-full text-xs text-slate-700">
                                                        <thead class="text-[10px] font-bold uppercase tracking-[0.08em]" :class="section.key === 'group' ? 'bg-emerald-100/70 text-emerald-800' : 'bg-indigo-100/70 text-indigo-800'">
                                                            <tr>
                                                                <th class="px-3 py-2 text-left">Level</th>
                                                                <th class="px-3 py-2 text-left">Amount</th>
                                                                <th class="px-3 py-2 text-left">Duration</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <template x-for="row in section.rows" :key="`${section.key}-${row.level}-${row.amount}-${row.duration}`">
                                                                <tr :class="section.key === 'group' ? 'border-t border-emerald-100' : 'border-t border-indigo-100'">
                                                                    <td class="px-3 py-2 font-semibold text-slate-800" x-text="row.level || '-' "></td>
                                                                    <td class="px-3 py-2 font-bold" :class="section.key === 'group' ? 'text-emerald-700' : 'text-indigo-700'" x-text="row.amount || '-' "></td>
                                                                    <td class="px-3 py-2 text-slate-600" x-text="row.duration || '-' "></td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </article>
                                        </template>
                                    </div>
                                    <p x-show="!feeTableSections(details[selectedCourseId]?.fees).length" class="mt-2 text-slate-600">No fee details added yet.</p>
                                </section>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <h4 class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">Requirements</h4>
                                        <template x-if="lines(details[selectedCourseId]?.requirements).length">
                                            <ul class="mt-2 list-disc space-y-1.5 pl-5 text-slate-700">
                                                <template x-for="line in lines(details[selectedCourseId]?.requirements)" :key="line">
                                                    <li x-text="line"></li>
                                                </template>
                                            </ul>
                                        </template>
                                        <p x-show="!lines(details[selectedCourseId]?.requirements).length" class="mt-2 text-slate-600">No requirements added yet.</p>
                                    </section>

                                    <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <h4 class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">Key Outcome</h4>
                                        <p class="mt-2 text-slate-700 leading-relaxed" x-text="details[selectedCourseId]?.key_outcome || 'No key outcome added yet.'"></p>
                                    </section>
                                </div>

                                <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <h4 class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">Levels & Progression</h4>
                                    <template x-if="levelProgressionCards(details[selectedCourseId]?.level_progression).length">
                                        <div class="mt-3 space-y-2.5">
                                            <template x-for="item in levelProgressionCards(details[selectedCourseId]?.level_progression)" :key="`${item.level}-${item.details}`">
                                                <article class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                    <h5 class="text-base font-black text-slate-800" x-text="item.level || 'Level'"></h5>
                                                    <p class="mt-1 text-sm leading-relaxed text-slate-600" x-text="item.details || 'Details coming soon.'"></p>
                                                </article>
                                            </template>
                                        </div>
                                    </template>
                                    <p x-show="!levelProgressionCards(details[selectedCourseId]?.level_progression).length" class="mt-2 text-slate-600">No progression details added yet.</p>
                                </section>
                            </div>

                            <div class="flex justify-end border-t border-slate-200 px-5 py-3">
                                <button type="button" @click="selectedCourseId = null" class="rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                                    Close Document
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 lg:px-8 pb-24">
            <div class="rounded-[2.5rem] lg:rounded-[4rem] bg-[#0a2d27] p-8 lg:p-16 text-center lg:text-left relative overflow-hidden">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
                    <div class="max-w-xl">
                        <h2 class="text-3xl lg:text-4xl font-black leading-tight text-white">Join today to start your journey into a better future.</h2>
                        <p class="mt-4 text-slate-400">Get access to unlimited resources and expert guidance.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                        <a href="{{ route('register') }}" class="rounded-full bg-yellow-400 px-8 py-4 font-bold text-[#0a2d27] hover:bg-white transition-all text-center">ENROLL NOW</a>
                        <a href="{{ route('landing.courses') }}" class="rounded-full border border-white/20 px-8 py-4 font-bold text-white hover:bg-white/10 transition-all text-center">Courses</a>
                    </div>
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-400/5 rounded-full -mr-20 -mt-20"></div>
            </div>
        </section>
    </main>

    <footer class="bg-white border-t border-slate-200 py-12 lg:py-16">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center lg:text-left">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr]">
                    <div>
                        <div class="flex items-center justify-center gap-3 lg:justify-start">
                            <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
                        </div>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500">
                            Thinker Hub empowers learners with practical, career-focused courses designed to turn knowledge into measurable results.
                        </p>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-sm text-slate-500 lg:justify-start">
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-[#11443c]">Login</a>
                        </div>
                    </div>

                    <div class="hidden lg:block">
                        <h3 class="text-sm font-bold text-slate-900">Menu</h3>
                        <ul class="mt-4 space-y-2.5 text-sm text-slate-500">
                            <li><a href="{{ route('home') }}" class="transition hover:text-[#0a2d27]">Home</a></li>
                            <li><a href="{{ route('landing.courses') }}" class="transition hover:text-[#0a2d27]">Courses</a></li>
                            <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Instructors</a></li>
                            <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Contacts</h3>
                        <div class="mt-4 space-y-2.5 text-sm text-slate-500">
                            <div class="relative" x-data="{ phoneMenu: false }">
                                <span class="font-semibold text-slate-700">Phone:</span>
                                <button type="button" @click="phoneMenu = !phoneMenu" class="ml-1 text-[#0a2d27] underline-offset-2 hover:underline">+260772640546</button>
                                <div x-show="phoneMenu" x-transition @click.outside="phoneMenu = false" class="absolute left-0 z-20 mt-2 w-44 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg" style="display: none;">
                                    <a href="tel:+260772640546" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-phone text-teal-600"></i>Call</a>
                                    <a href="https://wa.me/260772640546" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><i class="fa-brands fa-whatsapp text-green-600"></i>WhatsApp</a>
                                </div>
                            </div>
                            <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinker.learn@gmail.com" class="text-[#0a2d27] underline-offset-2 hover:underline">thinker.learn@gmail.com</a></p>
                            <p><span class="font-semibold text-slate-700">Address:</span> 10A Off Natwange Street, Airpot, Livingstone Zambia</p>
                        </div>
                        <div class="mt-4 flex items-center justify-center gap-4 text-slate-500 lg:justify-start">
                            <a href="#" class="transition hover:text-[#0a2d27]" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#" class="transition hover:text-[#0a2d27]" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                            <a href="#" class="transition hover:text-[#0a2d27]" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        </div>
                    </div>
            </div>

            <div class="mt-8 border-t border-slate-200 pt-5">
                <div class="flex flex-col items-center gap-4 text-center text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-left">
                    <p>© {{ now()->year }} Thinker Hub. All rights reserved.</p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('landing.contact') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</a>
                        <a href="{{ route('landing.contact') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</a>
                        <a href="{{ route('landing.contact') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
