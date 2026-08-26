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
<body class="hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-16 lg:py-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Curated Courses</p>
                <h1 class="mt-4 text-4xl font-black text-white sm:text-5xl">Explore Our Courses</h1>
                <p class="mx-auto mt-5 max-w-2xl text-slate-300">Find tutor-created courses, choose what fits your goals, and register to upskill with confidence.</p>
            </div>
        </section>

        <section class="py-20 lg:py-24">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3"
                >
                    @php
                        $courseImages = [
                            'images/courses/computer.png',
                            'images/courses/office.png',
                            'images/courses/design.png',
                            'images/courses/data.png',
                            'images/courses/media_ai.png',
                        ];

                        $courseImageKeywords = [
                            'images/courses/office.png' => ['office', 'excel', 'word', 'powerpoint'],
                            'images/courses/design.png' => ['design', 'graphics', 'ui', 'ux', 'canva', 'photoshop'],
                            'images/courses/data.png' => ['data', 'analytics', 'analysis', 'sql', 'power bi', 'tableau'],
                            'images/courses/media_ai.png' => ['social', 'media', 'marketing', 'content', 'ai'],
                            'images/courses/computer.png' => ['computer', 'digital', 'ict', 'literacy', 'fundamentals'],
                        ];

                        $resolveCourseImage = static function ($course) use ($courseImages, $courseImageKeywords): string {
                            $searchText = strtolower(trim((string) ($course->title.' '.$course->code)));

                            foreach ($courseImageKeywords as $imagePath => $keywords) {
                                foreach ($keywords as $keyword) {
                                    if (str_contains($searchText, $keyword)) {
                                        return $imagePath;
                                    }
                                }
                            }

                            return $courseImages[abs(crc32((string) $course->id)) % count($courseImages)];
                        };
                    @endphp

                    @forelse ($courses as $course)
                        @php
                            $courseImage = $resolveCourseImage($course);
                        @endphp
                        <article class="group bg-white rounded-[2rem] p-4 shadow-sm hover:shadow-xl transition-all border border-slate-100">
                            <div class="relative h-56 overflow-hidden rounded-[1.5rem]">
                                <img src="{{ asset($courseImage) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $course->title }} image">
                                <div class="absolute top-4 left-4 bg-yellow-400 text-[#0a2d27] text-[11px] font-bold px-4 py-1.5 rounded-full shadow-lg">BEST SELLER</div>
                            </div>
                            <div class="px-3 py-6">
                                @php
                                    $avgRating = (float) ($course->average_rating ?? ($course->ratings_avg_rating ?? 0));
                                    $ratingCount = (int) ($course->review_count ?? ($course->ratings_count ?? 0));
                                    $studentsCount = (int) ($course->enrollments_count ?? 0);
                                    $isOpenEnrollment = $course->is_open_enrollment !== false;
                                    $fullTitle = (string) $course->title;
                                    $displayTitle = \Illuminate\Support\Str::limit($fullTitle, 72);
                                    if ($studentsCount === 0) {
                                        $studentsCount = (int) ($course->selected_participants_count ?? 0);
                                    }
                                @endphp
                                <div class="flex items-center gap-1 mb-3">
                                    <x-rating-stars :rating="$avgRating" :count="$ratingCount" size="xs" />
                                </div>
                                <div class="min-h-[6.25rem]">
                                    <h3
                                        class="text-xl font-bold text-slate-900 group-hover:text-teal-600 transition-colors leading-snug"
                                        title="{{ $fullTitle }}"
                                    >
                                        {{ $displayTitle }}
                                    </h3>
                                    @if ($course->isOngoing())
                                        @php $activeIntake = $course->activeIntake; @endphp
                                        <div class="mt-2">
                                            @if ($activeIntake)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-semibold text-teal-700 border border-teal-200">
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
                                <div class="mt-3 space-y-1 text-xs text-slate-600">
                                    <p><span class="font-semibold text-slate-800">Course By:</span> {{ $course->course_owner_label }}</p>
                                    <p><span class="font-semibold text-slate-800">Instructor:</span> {{ $course->instructor_label }}</p>
                                    @if ($course->isOngoing() && $course->activeIntake && $course->activeIntake->next_intake_start_date)
                                        <p><span class="font-semibold text-teal-800">Next Intake:</span> {{ $course->activeIntake->formattedNextIntake() }}</p>
                                    @endif
                                </div>
                                <div class="mt-8 flex items-center justify-between border-t border-slate-50 pt-5 text-slate-500 font-medium text-xs">
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-clock text-teal-600"></i> {{ ($course->isOngoing() && $course->activeIntake ? $course->activeIntake->formattedDateRange() : null) ?: ($course->timeline ?: 'Self paced') }}</span>
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-user text-teal-600"></i> {{ $studentsCount }} Students</span>
                                </div>
                                <div class="mt-4 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        @if ($isOpenEnrollment)
                                            @php
                                                $feeOptions = $course->getFeeOptions();
                                                $hasMultipleOptions = count($feeOptions) > 1;
                                                $courseModalData = [
                                                    'id' => $course->id,
                                                    'title' => $course->title,
                                                    'code' => $course->code,
                                                    'checkoutUrl' => route('checkout.show', $course),
                                                    'options' => $feeOptions,
                                                ];
                                            @endphp
                                            @if ($hasMultipleOptions)
                                                <button
                                                    type="button"
                                                    onclick="window.openCourseOptionModal(@js($courseModalData))"
                                                    @click.prevent="window.openCourseOptionModal(@js($courseModalData))"
                                                    class="inline-flex items-center justify-center rounded-full bg-yellow-400 px-3.5 py-1.5 text-xs font-bold text-[#0a2d27] transition hover:bg-yellow-300 shadow-sm cursor-pointer"
                                                >
                                                    Enroll &amp; Pay
                                                </button>
                                            @else
                                                <a
                                                    href="{{ route('checkout.show', $course) }}"
                                                    class="inline-flex items-center justify-center rounded-full bg-yellow-400 px-3.5 py-1.5 text-xs font-bold text-[#0a2d27] transition hover:bg-yellow-300 shadow-sm"
                                                >
                                                    Enroll &amp; Pay
                                                </a>
                                            @endif
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400 cursor-not-allowed border border-slate-200"
                                            >
                                                <i class="fa-solid fa-lock text-[10px] text-amber-500"></i> Locked
                                            </span>
                                        @endif
                                        <a
                                            href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                                            class="inline-flex items-center justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
                                        >
                                            Details
                                        </a>
                                    </div>

                                    @if ($isOpenEnrollment)
                                        <span
                                            title="Open to enroll"
                                            aria-label="Open to enroll"
                                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                                        >
                                            <i class="fa-solid fa-lock-open text-sm"></i>
                                        </span>
                                    @else
                                        <span
                                            title="Locked for selected students"
                                            aria-label="Locked for selected students"
                                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600"
                                        >
                                            <i class="fa-solid fa-lock text-sm"></i>
                                        </span>
                                    @endif
                                </div>
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

                </div>
            </div>
        </section>

        {{-- Student Reviews & User Ratings Section --}}
        <section class="pb-20 lg:pb-24 border-t border-slate-100 pt-12">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="mb-10 text-center md:text-left">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Learner Experiences</span>
                    <h2 class="text-3xl font-black text-slate-900 mt-2 sm:text-4xl">Student Reviews &amp; Ratings</h2>
                    <p class="mt-2 text-slate-600 text-sm">Explore verified reviews and rating distributions from students across the platform.</p>
                </div>

                <livewire:reviews.review-list target-type="platform" target-title="thinker_HUB Community" />
            </div>
        </section>

        <livewire:reviews.submit-review-modal />

        <section class="max-w-6xl mx-auto px-6 lg:px-8 pb-24">
            <div class="rounded-[2.5rem] lg:rounded-[4rem] bg-[#0a2d27] p-8 lg:p-16 text-center lg:text-left relative overflow-hidden">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
                    <div class="max-w-xl">
                        <h2 class="text-3xl lg:text-4xl font-black leading-tight text-white">Learn new skills or launch the course you want to teach.</h2>
                        <p class="mt-4 text-slate-400">Choose a curated course to upskill, or apply as a tutor to publish and manage your own course.</p>
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
                            think.er HUB is where tutors create and manage courses, and learners register to upskill with practical outcomes.
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
                            <li><a href="{{ route('hub.index') }}" class="transition hover:text-[#0a2d27]">Knowledge Hub</a></li>
                            <li><a href="{{ route('landing.instructors') }}" class="transition hover:text-[#0a2d27]">Network</a></li>
                            <li><a href="{{ route('landing.contact') }}" class="transition hover:text-[#0a2d27]">Contact</a></li>
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="transition hover:text-[#0a2d27]">Login</a></li>
                            @endauth
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Contacts</h3>
                        <div class="mt-4 space-y-2.5 text-sm text-slate-500">
                            <p><span class="font-semibold text-slate-700">Phone:</span> <button type="button" @click="$dispatch('open-contact')" class="ml-1 text-[#0a2d27] font-medium underline-offset-2 hover:underline cursor-pointer">+260772640546</button></p>
                            <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinkerhub@oristudiozm.com" class="text-[#0a2d27] underline-offset-2 hover:underline">thinkerhub@oristudiozm.com</a></p>
                            <p><span class="font-semibold text-slate-700">Address:</span> 10A Off Natwange Street, Airpot, Livingstone Zambia</p>
                        </div>
                    </div>
            </div>

            <div class="mt-8 border-t border-slate-200 pt-5">
                <div class="flex flex-col items-center gap-4 text-center text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-left">
                    <p>© {{ now()->year }} Thinker Hub. All rights reserved.</p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('landing.privacy') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</a>
                        <a href="{{ route('landing.cookies') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</a>
                        <a href="{{ route('landing.terms') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @include('partials.course-selection-modal')
    @include('partials.legal-modals')
</body>
</html>
