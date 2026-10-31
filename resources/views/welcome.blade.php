<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('partials.seo-meta', [
        'title' => 'think.er HUB | Teach, Manage, and Upskill',
        'description' => 'Tutors can publish and manage curated courses, while learners enroll to upskill through practical training.',
        'keywords' => 'course platform, tutor courses, learner upskilling, course registration, thinker hub',
        'type' => 'website',
    ])
    <link rel="preload" as="image" href="{{ asset('images/hero/office.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body
    class="public-layout hub-public bg-white text-slate-900 font-sans antialiased"
    x-data="{
        mobileMenu: false,
        heroSlideIndex: 0,
        heroSlides: [
            {
                image: '{{ asset('images/hero/office.png') }}',
                alt: 'Microsoft Office workspace',
                label: 'MS Office Suite'
            },
            {
                image: '{{ asset('images/hero/design.png') }}',
                alt: 'Graphic design tools on screen',
                label: 'Creative Design'
            },
            {
                image: '{{ asset('images/hero/media_ai.png') }}',
                alt: 'Social media analytics dashboard',
                label: 'Media & AI'
            },
            {
                image: '{{ asset('images/hero/data.png') }}',
                alt: 'Data analysis dashboard',
                label: 'Data Analysis'
            }
        ],
        startHeroRotation() {
            setInterval(() => {
                this.heroSlideIndex = (this.heroSlideIndex + 1) % this.heroSlides.length;
            }, 3000);
        },
        preloadHeroImages() {
            setTimeout(() => {
                this.heroSlides.forEach((slide, index) => {
                    if (index === 0) {
                        return;
                    }

                    const image = new Image();
                    image.decoding = 'async';
                    image.src = slide.image;
                });
            }, 1200);
        },
        reorderHomepageSections() {
            const valuePropositionSection = document.getElementById('value-proposition-section');
            const publicRegistrationSection = document.getElementById('public-registration-section');

            if (!valuePropositionSection || !publicRegistrationSection) {
                return;
            }

            const parent = valuePropositionSection.parentElement;

            if (!parent || parent !== publicRegistrationSection.parentElement) {
                return;
            }

            parent.insertBefore(publicRegistrationSection, valuePropositionSection);
        }
    }"
    x-init="startHeroRotation(); preloadHeroImages(); reorderHomepageSections()"
>

    @include('partials.app-preloader')
    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden pt-12 pb-16 lg:pt-18 lg:pb-24">
            <div class="hub-glow-orb pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="hub-glow-orb pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-yellow-400/10 blur-3xl" style="animation-delay: -3.5s;"></div>

            <div class="mx-auto max-w-6xl px-6 lg:px-8 relative z-10">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <div class="text-center lg:text-left order-2 lg:order-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Practical Digital Upskilling Platform</p>
                        <h1 class="mt-4 text-4xl font-extrabold leading-[1.12] tracking-tight text-white sm:text-5xl lg:text-[3.4rem]">
                            Teach What You Know.<br class="hidden lg:block"> Learn What You Need.
                        </h1>
                        <p class="mt-5 text-base sm:text-lg text-slate-300 leading-relaxed max-w-lg mx-auto lg:mx-0">
                            think.er HUB connects tutors and learners in one place: tutors can register courses and manage their learners, while anyone ready to upskill can register for a course of interest.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5">
                            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-yellow-400 px-7 py-3.5 text-sm font-bold text-[#0a2d27] shadow-md transition duration-200 ease-out hover:-translate-y-0.5 hover:bg-amber-300 focus:outline-none focus:ring-4 focus:ring-yellow-300/60">
                                <span>Register for a Course</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                            <a href="{{ route('landing.instructors.apply') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-white/25 bg-white/10 px-7 py-3.5 text-sm font-bold text-white backdrop-blur-xs transition duration-200 ease-out hover:bg-white/20 focus:outline-none focus:ring-4 focus:ring-white/20 text-center">
                                <i class="fa-solid fa-chalkboard-user text-xs text-teal-300"></i>
                                <span>Instructor Registration</span>
                            </a>
                        </div>

                        @php
                            $formatStatInFives = static function (mixed $value): string {
                                $count = max(0, (int) $value);
                                return $count < 5 ? (string) $count : ((int) (floor($count / 5) * 5)) . '+';
                            };
                        @endphp
                        <div class="mt-10 grid grid-cols-4 gap-2 sm:gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-xs max-w-lg mx-auto lg:mx-0">
                            <div class="text-center lg:text-left">
                                <p class="text-xl sm:text-2xl font-extrabold text-white tabular-nums">{{ $formatStatInFives($stats['tutors'] ?? 0) }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tutors</p>
                            </div>
                            <div class="text-center lg:text-left border-l border-white/10 pl-2 sm:pl-4">
                                <p class="text-xl sm:text-2xl font-extrabold text-white tabular-nums">{{ $formatStatInFives($stats['students'] ?? 0) }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400">Students</p>
                            </div>
                            <div class="text-center lg:text-left border-l border-white/10 pl-2 sm:pl-4">
                                <p class="text-xl sm:text-2xl font-extrabold text-white tabular-nums">{{ $formatStatInFives($stats['courses'] ?? 0) }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400">Courses</p>
                            </div>
                            <div class="text-center lg:text-left border-l border-white/10 pl-2 sm:pl-4">
                                @php
                                    $heroAvgRating = (float) ($stats['avg_rating'] ?? ($ratingStats['avgRating'] ?? 5.0));
                                    if ($heroAvgRating <= 0) {
                                        $heroAvgRating = 5.0;
                                    }
                                @endphp
                                <p class="text-xl sm:text-2xl font-extrabold text-amber-300 tabular-nums">{{ number_format($heroAvgRating, 1) }} ★</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400">Rating</p>
                            </div>
                        </div>
                    </div>

                    <div class="order-1 lg:order-2 flex flex-col items-center justify-center relative">
                        <div class="relative w-64 h-64 sm:w-80 sm:h-80 lg:w-[390px] lg:h-[390px]">
                            <div class="hub-hero-ring absolute inset-0 rounded-full border-[12px] border-yellow-400/15 scale-105"></div>
                            <img
                                :src="heroSlides[heroSlideIndex].image"
                                :alt="heroSlides[heroSlideIndex].alt"
                                width="840"
                                height="840"
                                loading="eager"
                                fetchpriority="high"
                                decoding="async"
                                class="rounded-full w-full h-full object-cover border-4 border-white/15 shadow-2xl absolute inset-0 z-10 transition-all duration-500"
                            >
                            <div class="absolute top-4 left-1/2 -translate-x-1/2 z-20 rounded-full bg-slate-950/60 border border-white/15 px-3.5 py-1 text-[11px] font-bold uppercase tracking-wider text-white backdrop-blur-md transition-all duration-300" x-text="heroSlides[heroSlideIndex].label"></div>
                            <div class="hub-float-badge absolute -bottom-2 -right-3 bg-white px-4 py-3 rounded-2xl border border-slate-200/90 shadow-lg z-20 hidden sm:flex items-center gap-3">
                                <div class="bg-emerald-50 border border-emerald-200/80 h-9 w-9 flex items-center justify-center rounded-xl text-emerald-600"><i class="fa-solid fa-certificate"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-[#0a2d27]">Creator + Learner Ready</p>
                                    <p class="text-[10px] font-medium text-slate-500">Teach, Manage, Upskill</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-2 z-20">
                            <template x-for="(slide, idx) in heroSlides" :key="idx">
                                <button
                                    type="button"
                                    @click="heroSlideIndex = idx"
                                    :aria-label="'Show slide ' + slide.label"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                    :class="heroSlideIndex === idx ? 'w-6 bg-yellow-400' : 'w-2 bg-white/30 hover:bg-white/50'"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="value-proposition-section" class="py-16 lg:py-20 bg-[#f8fcf9]">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="max-w-3xl text-center mx-auto">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Value Proposition</span>
                    <h2 class="mt-2.5 text-3xl font-black text-slate-900 sm:text-4xl tracking-tight">Why Teams Choose think.er HUB</h2>
                    <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">From tutor-led course delivery to learner enrollment, the platform makes it simple to teach, manage, and upskill.</p>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <article class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 hover:-translate-y-0.5 transition-all">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 border border-teal-100 text-teal-600"><i class="fa-solid fa-users-gear"></i></div>
                        <h3 class="mt-3.5 text-base font-bold text-slate-900">Tutor-Led Course Management</h3>
                        <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-slate-600">Tutors can register courses, manage learners, and run delivery from one focused workspace.</p>
                    </article>

                    <article class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 hover:-translate-y-0.5 transition-all">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600"><i class="fa-solid fa-calendar-check"></i></div>
                        <h3 class="mt-3.5 text-base font-bold text-slate-900">Structured Course Delivery</h3>
                        <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-slate-600">Coordinate sessions, assignments, and assessments without scattered tools.</p>
                    </article>

                    <article class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 hover:-translate-y-0.5 transition-all">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 border border-amber-100 text-amber-600"><i class="fa-solid fa-chart-line"></i></div>
                        <h3 class="mt-3.5 text-base font-bold text-slate-900">Clear Progress Visibility</h3>
                        <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-slate-600">Track learner activity and outcomes so instructors can intervene earlier and better.</p>
                    </article>

                    <article class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 hover:-translate-y-0.5 transition-all">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 border border-cyan-100 text-cyan-700"><i class="fa-solid fa-folder-tree"></i></div>
                        <h3 class="mt-3.5 text-base font-bold text-slate-900">Resource Control</h3>
                        <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-slate-600">Publish and manage materials, updates, and communication in one platform.</p>
                    </article>

                    <article class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 hover:-translate-y-0.5 transition-all">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600"><i class="fa-solid fa-user-plus"></i></div>
                        <h3 class="mt-3.5 text-base font-bold text-slate-900">Curated Course Enrollment</h3>
                        <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-slate-600">Learners can discover curated courses and register quickly based on their goals.</p>
                    </article>

                    <article class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 hover:-translate-y-0.5 transition-all">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 border border-rose-100 text-rose-600"><i class="fa-solid fa-user-shield"></i></div>
                        <h3 class="mt-3.5 text-base font-bold text-slate-900">Controlled Access by Role</h3>
                        <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-slate-600">Guide each user into admin, instructor, or student views with the permissions they need.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="public-registration-section" class="py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4 text-center md:text-left">
                    <div class="max-w-xl">
                        <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Open Enrollment</span>
                        <h2 class="text-3xl font-black text-slate-900 mt-2 sm:text-4xl tracking-tight">Courses Open for Registration</h2>
                    </div>
                    <div class="flex justify-center md:justify-end">
                        <a href="{{ route('landing.courses') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-teal-800 shadow-2xs transition hover:border-teal-300 hover:bg-teal-50/50">
                            <span>Browse All Courses</span>
                            <i class="fa-solid fa-arrow-right-long text-[11px]"></i>
                        </a>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $maxOpenStudents = (int) collect($courses)
                            ->filter(fn ($c) => $c->is_open_enrollment !== false)
                            ->max(fn ($c) => (int) ($c->enrollments_count ?: ($c->selected_participants_count ?? 0)));
                        $newestCourseId = (int) (collect($courses)
                            ->filter(fn ($c) => $c->is_open_enrollment !== false)
                            ->sortByDesc(fn ($c) => [$c->created_at?->timestamp ?? 0, (int) $c->id])
                            ->first()?->id ?? 0);
                    @endphp
                    @forelse ($courses as $course)
                        @php
                            $courseImage = $course->course_image_url;
                            $avgRating = (float) ($course->average_rating ?? ($course->ratings_avg_rating ?? 0));
                            $ratingCount = (int) ($course->review_count ?? ($course->ratings_count ?? 0));
                            $studentsCount = (int) ($course->enrollments_count ?? 0);
                            $isOpenEnrollment = $course->is_open_enrollment !== false;
                            $fullTitle = (string) $course->title;
                            $displayTitle = \Illuminate\Support\Str::limit($fullTitle, 65);
                            if ($studentsCount === 0) {
                                $studentsCount = (int) ($course->selected_participants_count ?? 0);
                            }
                            $badge = $course->getCatalogBadge($maxOpenStudents, $newestCourseId);
                            $promoBadge = $course->getPromoBadge();
                            $feeOptions = $course->getFeeOptions();
                            $hasMultipleOptions = count($feeOptions) > 1;
                            $startingFee = $course->getNumericFee();
                            $singleOptionParams = ! empty($feeOptions[0])
                                ? ['course' => $course, 'track' => $feeOptions[0]['level'], 'mode' => $feeOptions[0]['category']]
                                : ['course' => $course];
                            if ($promoBadge) {
                                $singleOptionParams['promo'] = $promoBadge['code'];
                            }
                            $courseModalData = [
                                'id' => $course->id,
                                'title' => $course->title,
                                'code' => $course->code,
                                'checkoutUrl' => route('checkout.show', $promoBadge ? ['course' => $course->id, 'promo' => $promoBadge['code']] : $course),
                                'options' => $feeOptions,
                            ];
                            $courseShowUrl = route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]);
                        @endphp
                        <article class="group bg-white rounded-2xl p-3.5 border border-slate-200/90 hover:-translate-y-1 transition-all flex flex-col justify-between">
                            <div>
                                <div class="relative h-42 sm:h-44 overflow-hidden rounded-xl bg-slate-100">
                                    <a href="{{ $courseShowUrl }}" class="block w-full h-full">
                                        <img src="{{ $courseImage }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $course->title }} image">
                                    </a>
                                    <div class="absolute top-2.5 left-2.5 flex flex-wrap items-center gap-1.5 max-w-[76%]">
                                        <div class="inline-flex items-center gap-1 {{ $badge['classes'] }} text-[10px] font-black px-2.5 py-0.5 rounded-full border shadow-xs">
                                            @if (! empty($badge['icon']))
                                                <i class="{{ $badge['icon'] }} text-[9px]"></i>
                                            @endif
                                            <span>{{ $badge['label'] }}</span>
                                        </div>
                                        @if ($promoBadge)
                                            <a href="{{ $courseShowUrl }}?promo={{ urlencode($promoBadge['code']) }}" class="inline-flex items-center gap-1 {{ $promoBadge['classes'] }} text-[10px] font-black px-2.5 py-0.5 rounded-full border" style="background-color: #e11d48; color: #ffffff; border-color: #be123c;" title="Use promo code {{ $promoBadge['code'] }} for {{ $promoBadge['discount'] }}">
                                                <i class="{{ $promoBadge['icon'] }} text-[9px]" style="color: #fde047;"></i>
                                                <span>{{ $promoBadge['label'] }}</span>
                                            </a>
                                        @endif
                                    </div>
                                    @if ($course->code)
                                        <span class="absolute top-2.5 right-2.5 rounded-full bg-slate-950/70 backdrop-blur-xs border border-white/15 px-2.5 py-0.5 text-[10px] font-bold text-white">
                                            {{ $course->code }}
                                        </span>
                                    @endif
                                    @if ($promoBadge)
                                        <a href="{{ $courseShowUrl }}?promo={{ urlencode($promoBadge['code']) }}" class="hub-promo-strip absolute bottom-2 left-2.5 right-2.5 flex items-center justify-between gap-2 rounded-lg px-2.5 py-1 text-[10px] text-white border" style="background-color: #0a2d27; color: #ffffff; border-color: #11443c;">
                                            <span class="font-bold flex items-center gap-1 truncate" style="color: #fde047;">
                                                <i class="fa-solid fa-bolt text-[9px]"></i> {{ $promoBadge['discount'] }}
                                            </span>
                                            <span class="font-mono font-extrabold tracking-wider px-1.5 py-0.5 rounded text-[9px] shrink-0" style="background-color: #11443c; color: #fde047;">
                                                CODE: {{ $promoBadge['code'] }}
                                            </span>
                                        </a>
                                    @endif
                                </div>
                                <div class="px-1 pt-3 pb-1">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <x-rating-stars :rating="$avgRating" :count="$ratingCount" size="xs" />
                                        @if ($isOpenEnrollment && $startingFee > 0)
                                            <span class="inline-flex items-center rounded-md bg-teal-50 border border-teal-200/70 px-2 py-0.5 text-[10px] font-extrabold text-teal-800 tabular-nums">
                                                {{ $hasMultipleOptions ? 'From ' : '' }}K{{ number_format($startingFee, 0) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <h3
                                            class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-teal-700 transition-colors leading-snug line-clamp-2"
                                            title="{{ $fullTitle }}"
                                        >
                                            <a href="{{ $courseShowUrl }}" class="hover:text-teal-700 transition-colors">
                                                {{ $displayTitle }}
                                            </a>
                                        </h3>
                                        @if ($course->isOngoing())
                                            @php $activeIntake = $course->activeIntake; @endphp
                                            <div class="mt-1.5">
                                                @if ($activeIntake)
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-semibold text-teal-700 border border-teal-200/80">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Intake: {{ $activeIntake->name }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600 border border-slate-200">
                                                        Ongoing (Intakes)
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2.5 space-y-1 text-[11px] text-slate-600">
                                        <p class="truncate"><span class="font-semibold text-slate-800">Course By:</span> {{ $course->course_owner_label }}</p>
                                        <p class="truncate">
                                            <span class="font-semibold text-slate-800">Instructor:</span>
                                            @if ($course->instructors->isNotEmpty())
                                                @foreach ($course->instructors as $inst)
                                                    <a href="{{ route('landing.instructors.show', ['instructor' => $inst->id, 'slug' => \Illuminate\Support\Str::slug($inst->name ?: (string) $inst->id)]) }}" class="text-teal-700 hover:text-[#0a2d27] font-semibold hover:underline underline-offset-2 transition">{{ $inst->name }}</a>@if (! $loop->last)<span> / </span>@endif
                                                @endforeach
                                            @else
                                                {{ $course->instructor_label }}
                                            @endif
                                        </p>
                                        @if ($course->isOngoing() && $course->activeIntake && $course->activeIntake->next_intake_start_date)
                                            <p class="truncate"><span class="font-semibold text-teal-800">Next Intake:</span> {{ $course->activeIntake->formattedNextIntake() }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5 text-slate-500 font-medium text-[11px]">
                                    <span class="flex items-center gap-1.5 truncate"><i class="fa-regular fa-clock text-teal-600"></i> {{ ($course->isOngoing() && $course->activeIntake ? $course->activeIntake->formattedDateRange() : null) ?: ($course->timeline ?: 'Self paced') }}</span>
                                    <span class="flex items-center gap-1.5 shrink-0"><i class="fa-regular fa-user text-teal-600"></i> {{ $studentsCount }} {{ \Illuminate\Support\Str::plural('Student', $studentsCount) }}</span>
                                </div>
                                <div class="mt-2.5 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5">
                                        @if ($isOpenEnrollment)
                                            @if ($hasMultipleOptions)
                                                <button
                                                    type="button"
                                                    onclick="window.openCourseOptionModal(@js($courseModalData))"
                                                    @click.prevent="window.openCourseOptionModal(@js($courseModalData))"
                                                    class="inline-flex items-center justify-center rounded-full bg-yellow-400 px-3.5 py-1.5 text-[11px] font-bold text-[#0a2d27] transition hover:bg-yellow-300 cursor-pointer"
                                                >
                                                    Enroll &amp; Pay
                                                </button>
                                            @else
                                                <a
                                                    href="{{ route('checkout.show', $singleOptionParams) }}"
                                                    class="inline-flex items-center justify-center rounded-full bg-yellow-400 px-3.5 py-1.5 text-[11px] font-bold text-[#0a2d27] transition hover:bg-yellow-300"
                                                >
                                                    Enroll &amp; Pay
                                                </a>
                                            @endif
                                        @endif
                                        <a
                                            href="{{ $courseShowUrl }}"
                                            class="inline-flex items-center justify-center rounded-full {{ $isOpenEnrollment ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-[#0a2d27] text-white hover:bg-[#11443c]' }} px-3.5 py-1.5 text-[11px] font-bold transition"
                                        >
                                            {{ $isOpenEnrollment ? 'Details' : 'Open Course →' }}
                                        </a>
                                    </div>

                                    @if ($isOpenEnrollment)
                                        <span
                                            title="Open to enroll"
                                            aria-label="Open to enroll"
                                            class="inline-flex h-6.5 w-6.5 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200/60"
                                        >
                                            <i class="fa-solid fa-lock-open text-[10px]"></i>
                                        </span>
                                    @else
                                        <span
                                            title="Locked for selected students"
                                            aria-label="Locked for selected students"
                                            class="inline-flex h-6.5 w-6.5 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600 border border-amber-200/60"
                                        >
                                            <i class="fa-solid fa-lock text-[10px]"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full py-20 text-center border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50">
                            <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto border border-slate-200 mb-3">
                                <i class="fa-solid fa-book-open text-teal-600 text-xl"></i>
                            </div>
                            <p class="text-slate-500 font-medium text-sm">New courses arriving shortly.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="pb-16 lg:pb-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="rounded-3xl border border-slate-200/90 bg-white p-7 lg:p-10 shadow-2xs">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Platform Focus</span>
                    <h2 class="mt-2.5 text-2xl font-black text-slate-900 sm:text-3xl tracking-tight">Built for Course Creators and Learners</h2>
                    <p class="mt-2.5 max-w-3xl text-slate-600 text-sm sm:text-base leading-relaxed">This platform supports both sides of growth: tutors can create and manage courses, and learners can enroll to build practical skills that matter.</p>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200/90 bg-slate-50/80 p-5">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-100/80 text-teal-700 text-xs"><i class="fa-solid fa-sliders"></i></span>
                                <h3 class="text-base font-bold text-slate-900">Instructor Control</h3>
                            </div>
                            <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">Register courses, manage learners, and run schedules, assignments, and assessments in one place.</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-200/90 bg-emerald-50/50 p-5">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 text-xs"><i class="fa-solid fa-graduation-cap"></i></span>
                                <h3 class="text-base font-bold text-slate-900">Student Onboarding</h3>
                            </div>
                            <p class="mt-2 text-xs sm:text-sm text-slate-700 leading-relaxed">Make upskilling easy so learners can register for courses they care about and start quickly.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Real Reviews Section --}}
        <section class="py-16 lg:py-20 bg-[#FDFBF7] border-t border-slate-200/60">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Real Feedback</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-800 tracking-tight mt-2">Student Reviews &amp; Ratings</h2>
                </div>

                <livewire:reviews.review-list target-type="platform" target-title="thinker_HUB Community" />
            </div>
        </section>

        <livewire:reviews.submit-review-modal />

        <section class="max-w-6xl mx-auto px-6 lg:px-8 py-16 lg:py-20">
            <div class="rounded-3xl bg-[#0a2d27] p-8 lg:p-14 text-center lg:text-left relative overflow-hidden shadow-xl">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="max-w-xl">
                        <h2 class="text-2xl lg:text-4xl font-black leading-tight text-white">Teach your craft or upskill for your next step.</h2>
                        <p class="mt-3 text-sm sm:text-base text-slate-300">If you are a tutor, register your course and manage your learners. If you are a learner, register for a course that matches your goals.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3.5 w-full lg:w-auto shrink-0">
                        <a href="{{ route('register') }}" class="rounded-full bg-yellow-400 px-7 py-3.5 text-xs sm:text-sm font-bold text-[#0a2d27] hover:bg-amber-300 transition-all text-center">Create Account</a>
                        <a href="{{ route('landing.courses') }}" class="rounded-full border border-white/25 bg-white/5 px-7 py-3.5 text-xs sm:text-sm font-bold text-white hover:bg-white/15 transition-all text-center">Explore Courses</a>
                    </div>
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-400/5 rounded-full -mr-20 -mt-20 pointer-events-none"></div>
            </div>
        </section>

    </main>

    @include('partials.public-footer')

    @include('partials.course-selection-modal')
    @include('partials.legal-modals')
</body>
</html>