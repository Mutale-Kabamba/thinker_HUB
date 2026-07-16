<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'think.er HUB | Instructor & Student Management Platform',
        'description' => 'Manage instructors, students, schedules, and course resources in one platform. Public users can also register for available courses.',
        'keywords' => 'instructor management platform, student management system, course registration, course operations, thinker hub',
        'type' => 'website',
    ])
    <link rel="preload" as="image" href="{{ asset('images/hero/office.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body
    class="bg-white text-slate-900 font-sans antialiased"
    x-data="{
        mobileMenu: false,
        videoModal: false,
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

    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-32">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="grid items-center gap-16 lg:grid-cols-2">
                    <div class="text-center lg:text-left order-2 lg:order-1">
                        <h1 class="text-4xl font-extrabold leading-[1.1] text-white sm:text-5xl lg:text-6xl">
                            Manage Learning Operations.<br class="hidden lg:block"> Keep Teams Aligned.
                        </h1>
                        <p class="mt-8 text-lg text-slate-300 leading-relaxed max-w-lg mx-auto lg:mx-0">
                            think.er HUB is built for instructors and admins to manage students, schedules, resources, and progress from one place, while still allowing new users to register for available courses.
                        </p>
                        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-6">
                            <a href="{{ route('register') }}" class="w-full sm:w-auto rounded-full bg-yellow-400 px-10 py-4 font-bold text-[#0a2d27] hover:translate-y-[-2px] transition-all shadow-lg shadow-yellow-400/20">Register for a Course</a>
                            <button type="button" @click="videoModal = true" class="flex items-center gap-3 text-white font-semibold group">
                                <span class="flex items-center justify-center w-12 h-12 rounded-full border border-white/30 group-hover:bg-white group-hover:text-[#0a2d27] transition-all">
                                    <i class="fa-solid fa-play ml-1"></i>
                                </span>
                                See How It Works
                            </button>
                        </div>
                        
                        <div class="mt-16 grid grid-cols-3 gap-3 sm:gap-4 border-t border-white/10 pt-10 max-w-md mx-auto lg:mx-0">
                            <div class="text-center lg:text-left"><p class="text-xl sm:text-2xl font-bold text-white">{{ ($stats['tutors'] ?? 0) < 10 ? ($stats['tutors'] ?? 0) : '10+' }}</p><p class="text-[10px] sm:text-xs uppercase tracking-tighter text-slate-400">Tutors</p></div>
                            <div class="text-center lg:text-left"><p class="text-xl sm:text-2xl font-bold text-white">{{ ($stats['students'] ?? 0) < 10 ? ($stats['students'] ?? 0) : '10+' }}</p><p class="text-[10px] sm:text-xs uppercase tracking-tighter text-slate-400">Students</p></div>
                            <div class="text-center lg:text-left"><p class="text-xl sm:text-2xl font-bold text-white">{{ ($stats['courses'] ?? 0) < 10 ? ($stats['courses'] ?? 0) : '10+' }}</p><p class="text-[10px] sm:text-xs uppercase tracking-tighter text-slate-400">Courses</p></div>
                        </div>
                    </div>

                    <div class="order-1 lg:order-2 flex justify-center relative">
                        <div class="relative w-64 h-64 sm:w-80 sm:h-80 lg:w-[420px] lg:h-[420px]">
                            <div class="absolute inset-0 rounded-full border-[15px] border-yellow-400/10 scale-110"></div>
                            <img
                                :src="heroSlides[heroSlideIndex].image"
                                :alt="heroSlides[heroSlideIndex].alt"
                                width="840"
                                height="840"
                                loading="eager"
                                fetchpriority="high"
                                decoding="async"
                                class="rounded-full w-full h-full object-cover border-4 border-white/10 absolute inset-0 z-10"
                            >
                            <div class="absolute top-4 left-1/2 -translate-x-1/2 z-20 rounded-full bg-black/45 px-4 py-1.5 text-[11px] font-bold uppercase tracking-wide text-white backdrop-blur-sm" x-text="heroSlides[heroSlideIndex].label"></div>
                            <div class="absolute -bottom-2 -right-4 bg-white p-4 rounded-2xl shadow-2xl z-20 hidden sm:flex items-center gap-3">
                                <div class="bg-green-100 p-2 rounded-lg text-green-600"><i class="fa-solid fa-certificate"></i></div>
                                <div><p class="text-xs font-bold text-[#0a2d27]">Operations Ready</p><p class="text-[10px] text-slate-500">Instructor & Student Panels</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="value-proposition-section" class="py-20 lg:py-24 bg-[#f8fcf9]">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="max-w-3xl text-center mx-auto">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Value Proposition</span>
                    <h2 class="mt-3 text-3xl font-black text-slate-900 sm:text-4xl">Why Teams Choose think.er HUB</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">From instructor workflows to student onboarding, the platform keeps operations organized while making course registration easy for new users.</p>
                </div>

                <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-600"><i class="fa-solid fa-users-gear"></i></div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Centralized Team Management</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Manage instructors, students, and role-based access from one consistent admin flow.</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-calendar-check"></i></div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Structured Daily Operations</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Coordinate classes, sessions, assignments, and assessments without scattered tools.</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-chart-line"></i></div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Clear Progress Visibility</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Track learner activity and outcomes so instructors can intervene earlier and better.</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700"><i class="fa-solid fa-folder-tree"></i></div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Resource Control</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Publish and manage materials, updates, and communication in one platform.</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid fa-user-plus"></i></div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Simple Course Registration</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Public users can register for available courses and enter the right workflow quickly.</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i class="fa-solid fa-shield-halved"></i></div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Controlled Access by Role</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Guide each user into admin, instructor, or student views with the permissions they need.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="public-registration-section" class="py-24 lg:py-32">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6 text-center md:text-left">
                    <div class="max-w-xl">
                        <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Public Registration</span>
                        <h2 class="text-3xl font-black text-slate-900 mt-3 sm:text-4xl">Courses Open for Registration</h2>
                    </div>
                    <div class="flex justify-center md:justify-end">
                        <a href="{{ route('landing.courses') }}" class="inline-flex items-center gap-2 text-sm font-bold text-teal-700 transition hover:text-[#0a2d27]">
                            Browse All Courses
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </a>
                    </div>
                </div>

                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3"
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
                                    $avgRating = round((float) ($course->ratings_avg_rating ?? 0), 1);
                                    $ratingCount = (int) ($course->ratings_count ?? 0);
                                    $studentsCount = (int) ($course->enrollments_count ?? 0);
                                    $isOpenEnrollment = $course->is_open_enrollment !== false;
                                    $fullTitle = (string) $course->title;
                                    $displayTitle = \Illuminate\Support\Str::limit($fullTitle, 72);
                                    if ($studentsCount === 0) {
                                        $studentsCount = (int) ($course->selected_participants_count ?? 0);
                                    }
                                @endphp
                                <div class="flex items-center gap-1 text-[10px] mb-3">
                                    @for ($star = 1; $star <= 5; $star++)
                                        @if ($star <= floor($avgRating))
                                            <i class="fa-solid fa-star text-yellow-500"></i>
                                        @elseif ($star - $avgRating < 1 && $star - $avgRating > 0)
                                            <i class="fa-solid fa-star-half-stroke text-yellow-500"></i>
                                        @else
                                            <i class="fa-regular fa-star text-slate-300"></i>
                                        @endif
                                    @endfor
                                    <span class="text-slate-400 font-semibold ml-2">
                                        @if ($ratingCount > 0)
                                            {{ $avgRating }} ({{ $ratingCount }} {{ Str::plural('review', $ratingCount) }})
                                        @else
                                            No reviews yet
                                        @endif
                                    </span>
                                </div>
                                <div class="min-h-[6.25rem]">
                                    <h3
                                        class="text-xl font-bold text-slate-900 group-hover:text-teal-600 transition-colors leading-snug"
                                        title="{{ $fullTitle }}"
                                    >
                                        {{ $displayTitle }}
                                    </h3>
                                </div>
                                <div class="mt-8 flex items-center justify-between border-t border-slate-50 pt-5 text-slate-500 font-medium text-xs">
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-clock text-teal-600"></i> {{ $course->timeline ?: 'Self paced' }}</span>
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-user text-teal-600"></i> {{ $studentsCount }} Students</span>
                                </div>
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <a
                                        href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title ?: $course->code)]) }}"
                                        class="inline-flex items-center justify-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#11443c]"
                                    >
                                        Open Course Page
                                    </a>

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
                            <p class="text-slate-500 font-medium">New courses arriving shortly.</p>
                        </div>
                    @endforelse

                </div>
            </div>
        </section>

        <section class="pb-20 lg:pb-24">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-8 lg:p-12 shadow-sm">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Platform Focus</span>
                    <h2 class="mt-3 text-3xl font-black text-slate-900 sm:text-4xl">Built for Academic Operations</h2>
                    <p class="mt-4 max-w-3xl text-slate-600 leading-relaxed">This is a management-first platform that helps teaching teams coordinate daily work, monitor progress, and keep students supported from onboarding to completion.</p>

                    <div class="mt-8 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h3 class="text-lg font-bold text-slate-900">Instructor Control</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-relaxed">Manage courses, assignments, schedules, assessments, and materials with clear visibility across your students.</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5">
                            <h3 class="text-lg font-bold text-slate-900">Student Onboarding</h3>
                            <p class="mt-2 text-sm text-slate-700 leading-relaxed">Keep public registration simple so new learners can join available courses and move into the right panel quickly.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-20 lg:pb-24">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6 text-center md:text-left">
                    <div class="max-w-xl">
                        <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Real Feedback</span>
                        <h2 class="text-3xl font-black text-slate-900 mt-3 sm:text-4xl">Student Reviews</h2>
                        <p class="mt-3 text-slate-600">Reviews shared by learners actively using the system.</p>
                    </div>
                </div>

                @if (($reviews ?? collect())->isNotEmpty())
                    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($reviews as $review)
                            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-bold text-slate-900">{{ $review->user?->name ?? 'Student' }}</p>
                                    <div class="flex items-center gap-0.5 text-xs">
                                        @for ($star = 1; $star <= 5; $star++)
                                            @if ($star <= (int) $review->rating)
                                                <i class="fa-solid fa-star text-yellow-500"></i>
                                            @else
                                                <i class="fa-regular fa-star text-slate-300"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                <p class="mt-1 text-xs font-semibold text-teal-700">{{ $review->course?->title ?? 'Course' }}</p>
                                <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $review->review }}</p>
                                <p class="mt-3 text-xs text-slate-400">{{ $review->created_at?->diffForHumans() }}</p>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 p-10 text-center">
                        <i class="fa-regular fa-star text-slate-300 text-3xl"></i>
                        <p class="mt-3 text-slate-500 font-medium">No reviews yet. Enrolled students can add reviews from their dashboard.</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 lg:px-8 pb-24">
            <div class="rounded-[2.5rem] lg:rounded-[4rem] bg-[#0a2d27] p-8 lg:p-16 text-center lg:text-left relative overflow-hidden">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
                    <div class="max-w-xl">
                        <h2 class="text-3xl lg:text-4xl font-black leading-tight text-white">Run your training operations in one place.</h2>
                        <p class="mt-4 text-slate-400">Support instructors, manage students, and keep course activity organized while still accepting new course registrations.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                        <a href="{{ route('register') }}" class="rounded-full bg-yellow-400 px-8 py-4 font-bold text-[#0a2d27] hover:bg-white transition-all text-center">CREATE ACCOUNT</a>
                        <a href="{{ route('landing.courses') }}" class="rounded-full border border-white/20 px-8 py-4 font-bold text-white hover:bg-white/10 transition-all text-center">Courses</a>
                    </div>
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-400/5 rounded-full -mr-20 -mt-20"></div>
            </div>
        </section>

        <div
            x-show="videoModal"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
            style="display: none;"
        >
            <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl" @click.outside="videoModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h3 class="text-sm font-bold text-slate-900">Thinker Hub Intro Video</h3>
                    <button type="button" @click="videoModal = false" class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="aspect-video w-full bg-black">
                    <template x-if="videoModal">
                        <iframe
                            class="h-full w-full"
                            src="https://www.youtube.com/embed/p6QZvXzwLdw?autoplay=1&rel=0"
                            title="Thinker Hub video"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </template>
                </div>
                <div class="flex items-center justify-end border-t border-slate-200 px-4 py-3">
                    <a
                        href="https://www.youtube.com/watch?v=p6QZvXzwLdw"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-lg bg-[#0a2d27] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#11443c]"
                    >
                        Visit on YouTube
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-12 lg:py-16">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center lg:text-left">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr]">
                    <div>
                        <div class="flex items-center justify-center gap-3 lg:justify-start">
                            <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
                        </div>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500">
                            Thinker Hub helps instructors and admins manage students, teaching workflows, and course operations in one platform, with public course registration for new learners.
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
                        <a href="{{ route('landing.privacy') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</a>
                        <a href="{{ route('landing.cookies') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</a>
                        <a href="{{ route('landing.terms') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>