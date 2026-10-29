<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Courses | think.er HUB',
        'description' => 'Explore curated courses created by tutors and enroll in the learning path that helps you upskill.',
        'keywords' => 'curated courses, tutor-led learning, upskill, course enrollment, thinker hub',
        'type' => 'website',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="public-layout hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-14 lg:py-18">
            <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center relative z-10">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Curated Courses</p>
                <h1 class="mt-3.5 text-3xl font-black text-white sm:text-5xl tracking-tight">Explore Our Courses</h1>
                <p class="mx-auto mt-3.5 max-w-2xl text-sm sm:text-base text-slate-300 leading-relaxed">Find tutor-created courses, choose what fits your goals, and register to upskill with confidence.</p>
            </div>
        </section>

        <section class="py-14 lg:py-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
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
                            $feeOptions = $course->getFeeOptions();
                            $hasMultipleOptions = count($feeOptions) > 1;
                            $startingFee = $course->getNumericFee();
                            $singleOptionParams = ! empty($feeOptions[0])
                                ? ['course' => $course, 'track' => $feeOptions[0]['level'], 'mode' => $feeOptions[0]['category']]
                                : ['course' => $course];
                            $courseModalData = [
                                'id' => $course->id,
                                'title' => $course->title,
                                'code' => $course->code,
                                'checkoutUrl' => route('checkout.show', $course),
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
                                    <div class="absolute top-2.5 left-2.5 inline-flex items-center gap-1 {{ $badge['classes'] }} text-[10px] font-black px-2.5 py-0.5 rounded-full border shadow-xs">
                                        @if (! empty($badge['icon']))
                                            <i class="{{ $badge['icon'] }} text-[9px]"></i>
                                        @endif
                                        <span>{{ $badge['label'] }}</span>
                                    </div>
                                    @if ($course->code)
                                        <span class="absolute top-2.5 right-2.5 rounded-full bg-slate-950/70 backdrop-blur-xs border border-white/15 px-2.5 py-0.5 text-[10px] font-bold text-white">
                                            {{ $course->code }}
                                        </span>
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
                                <div class="mt-2.5 flex items-center justify-between gap-1.5">
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
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-400 cursor-not-allowed border border-slate-200"
                                            >
                                                <i class="fa-solid fa-lock text-[9px] text-amber-500"></i> Locked
                                            </span>
                                        @endif
                                        <a
                                            href="{{ $courseShowUrl }}"
                                            class="inline-flex items-center justify-center rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-200"
                                        >
                                            Details
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
                            <p class="text-slate-500 font-medium text-sm">No active courses available yet.</p>
                        </div>
                    @endforelse

                </div>
            </div>
        </section>

        {{-- Student Reviews & User Ratings Section --}}
        <section class="pb-16 lg:pb-20 border-t border-slate-200/60 pt-12">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="mb-8 text-center md:text-left">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Learner Experiences</span>
                    <h2 class="text-3xl font-black text-slate-900 mt-2 sm:text-4xl tracking-tight">Student Reviews &amp; Ratings</h2>
                    <p class="mt-2 text-slate-600 text-sm">Explore verified reviews and rating distributions from students across the platform.</p>
                </div>

                <livewire:reviews.review-list target-type="platform" target-title="thinker_HUB Community" />
            </div>
        </section>

        <livewire:reviews.submit-review-modal />

        <section class="max-w-6xl mx-auto px-6 lg:px-8 pb-20">
            <div class="rounded-3xl bg-[#0a2d27] p-8 lg:p-14 text-center lg:text-left relative overflow-hidden shadow-xl">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="max-w-xl">
                        <h2 class="text-2xl lg:text-4xl font-black leading-tight text-white">Learn new skills or launch the course you want to teach.</h2>
                        <p class="mt-3 text-sm sm:text-base text-slate-300">Choose a curated course to upskill, or apply as a tutor to publish and manage your own course.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3.5 w-full lg:w-auto shrink-0">
                        <a href="{{ route('register') }}" class="rounded-full bg-yellow-400 px-7 py-3.5 text-xs sm:text-sm font-bold text-[#0a2d27] hover:bg-amber-300 transition-all text-center">Enroll Now</a>
                        <a href="{{ route('landing.instructors.apply') }}" class="rounded-full border border-white/25 bg-white/5 px-7 py-3.5 text-xs sm:text-sm font-bold text-white hover:bg-white/15 transition-all text-center">Apply as Tutor</a>
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
