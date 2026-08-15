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
                                <div class="mt-3 space-y-1 text-xs text-slate-600">
                                    <p><span class="font-semibold text-slate-800">Course By:</span> {{ $course->course_owner_label }}</p>
                                    <p><span class="font-semibold text-slate-800">Instructor:</span> {{ $course->instructor_label }}</p>
                                </div>
                                <div class="mt-8 flex items-center justify-between border-t border-slate-50 pt-5 text-slate-500 font-medium text-xs">
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-clock text-teal-600"></i> {{ $course->timeline ?: 'Self paced' }}</span>
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-user text-teal-600"></i> {{ $studentsCount }} Students</span>
                                </div>
                                <div class="mt-4 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        @if ($isOpenEnrollment)
                                            <a
                                                href="{{ route('checkout.show', $course) }}"
                                                class="inline-flex items-center justify-center rounded-full bg-yellow-400 px-3.5 py-1.5 text-xs font-bold text-[#0a2d27] transition hover:bg-yellow-300 shadow-sm"
                                            >
                                                Enroll &amp; Pay
                                            </a>
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
        @php
            $writtenReviews = ($reviews ?? collect())->filter(fn ($r) => filled($r->review))->values();
            $writtenReviewsCount = $writtenReviews->count();
            $avgRating = (float) ($ratingStats['avgRating'] ?? 0);
            $totalRatingsCount = (int) ($ratingStats['totalRatingsCount'] ?? 0);
            $starCounts = $ratingStats['starCounts'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        @endphp

        <section class="pb-20 lg:pb-24 border-t border-slate-100 pt-12">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="mb-10 text-center md:text-left">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Learner Experiences</span>
                    <h2 class="text-3xl font-black text-slate-900 mt-2 sm:text-4xl">Student Reviews &amp; Ratings</h2>
                    <p class="mt-2 text-slate-600 text-sm">Explore verified reviews and rating distributions from students across all courses.</p>
                </div>

                {{-- Side-by-Side Minimal Layout: Reviews (Left) | Ratings (Right) in 2:1 Ratio --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-10 items-start">
                    
                    {{-- LEFT COLUMN: REVIEWS SLIDE (2 Parts of 2:1 Ratio -> 8 Columns) --}}
                    <div class="md:col-span-8 md:pr-8 lg:pr-10 pb-8 md:pb-0 border-b md:border-b-0 md:border-r border-slate-200 flex flex-col justify-between min-h-[260px]">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-slate-900">Student Reviews</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $writtenReviewsCount }} {{ Str::plural('review', $writtenReviewsCount) }} from learners</p>
                        </div>

                        @if ($writtenReviewsCount > 0)
                            <div x-data="{
                                    active: 0,
                                    total: {{ $writtenReviewsCount }},
                                    timer: null,
                                    start() {
                                        this.stop();
                                        if (this.total > 1) {
                                            this.timer = setInterval(() => {
                                                this.next();
                                            }, 4000);
                                        }
                                    },
                                    stop() {
                                        if (this.timer) clearInterval(this.timer);
                                    },
                                    next() {
                                        this.active = (this.active + 1) % this.total;
                                    },
                                    prev() {
                                        this.active = (this.active - 1 + this.total) % this.total;
                                    },
                                    goTo(idx) {
                                        this.active = idx;
                                    }
                                 }"
                                 x-init="start()"
                                 @mouseenter="stop()"
                                 @mouseleave="start()"
                                 class="relative flex flex-col justify-between flex-1"
                            >
                                {{-- Slide Content --}}
                                <div class="relative min-h-[140px] flex items-center">
                                    @foreach ($writtenReviews as $idx => $r)
                                        <div x-show="active === {{ $idx }}"
                                             x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-x-2"
                                             x-transition:enter-start="opacity-0 translate-x-2"
                                             x-transition:enter-end="opacity-100 translate-x-0"
                                             x-transition:leave="transition ease-in duration-200 transform opacity-100 translate-x-0"
                                             x-transition:leave-start="opacity-100 translate-x-0"
                                             x-transition:leave-end="opacity-0 -translate-x-2"
                                             class="w-full"
                                             style="{{ $idx === 0 ? '' : 'display: none;' }}"
                                        >
                                            {{-- Profile Card | Name (Rating beside name) --}}
                                            <div class="flex items-center gap-3.5">
                                                @if ($r->user?->profile_photo_path)
                                                    <img src="{{ $r->user->getFilamentAvatarUrl() }}" alt="" class="h-11 w-11 rounded-full object-cover shrink-0 border border-slate-200" onerror="this.style.display='none'">
                                                @else
                                                    <div class="h-11 w-11 rounded-full bg-teal-50 text-teal-800 font-bold flex items-center justify-center text-sm shrink-0 border border-teal-100">
                                                        {{ strtoupper(substr($r->user?->name ?? 'S', 0, 1)) }}
                                                    </div>
                                                @endif

                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center flex-wrap gap-2">
                                                        <h4 class="font-bold text-slate-900 text-sm truncate">{{ $r->user?->name ?? 'Student' }}</h4>

                                                        @if ($r->rating)
                                                            <div class="inline-flex items-center gap-0.5 text-yellow-400 text-xs">
                                                                @for ($star = 1; $star <= 5; $star++)
                                                                    @if ($star <= $r->rating)
                                                                        <i class="fa-solid fa-star text-[11px]"></i>
                                                                    @else
                                                                        <i class="fa-regular fa-star text-slate-300 text-[11px]"></i>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        @if ($r->course?->title)
                                                            <span class="text-[11px] font-semibold text-teal-700 truncate max-w-[200px]">{{ $r->course->title }}</span>
                                                            <span class="text-slate-300">•</span>
                                                        @endif
                                                        <p class="text-[11px] text-slate-400">{{ $r->created_at ? $r->created_at->diffForHumans() : 'Recently' }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Below the name: What they wrote --}}
                                            <p class="mt-3.5 text-sm leading-relaxed text-slate-700">
                                                {{ $r->review }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Slider Controls (Minimal Dots & Subtle Prev/Next) --}}
                                @if ($writtenReviewsCount > 1)
                                    <div class="flex items-center justify-between pt-4 mt-6 border-t border-slate-100">
                                        <div class="flex items-center gap-1.5">
                                            @foreach ($writtenReviews as $idx => $r)
                                                <button type="button"
                                                        @click="goTo({{ $idx }})"
                                                        class="h-1.5 rounded-full transition-all duration-300"
                                                        :class="active === {{ $idx }} ? 'w-4 bg-teal-600' : 'w-1.5 bg-slate-300 hover:bg-slate-400'"
                                                        aria-label="Review {{ $idx + 1 }}">
                                                </button>
                                            @endforeach
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    @click="prev(); start();"
                                                    class="h-7 w-7 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition"
                                                    aria-label="Previous review">
                                                <i class="fa-solid fa-chevron-left text-xs"></i>
                                            </button>
                                            <button type="button"
                                                    @click="next(); start();"
                                                    class="h-7 w-7 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition"
                                                    aria-label="Next review">
                                                <i class="fa-solid fa-chevron-right text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-8 text-slate-500 text-sm">
                                <p class="text-slate-600 font-medium">No written reviews yet.</p>
                                <p class="text-xs text-slate-400 mt-1">Learner reviews will appear here once submitted.</p>
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT COLUMN: RATINGS (1 Part of 2:1 Ratio -> 4 Columns) --}}
                    <div class="md:col-span-4 md:pl-4 lg:pl-6">
                        <div class="text-center md:text-left">
                            <h3 class="text-xl font-bold text-slate-900">User ratings</h3>

                            {{-- Star pill & rating score --}}
                            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-slate-50 border border-slate-200/80 px-3.5 py-1.5">
                                <div class="flex items-center gap-1 text-yellow-400 text-xs">
                                    @for ($star = 1; $star <= 5; $star++)
                                        @if ($star <= floor($avgRating))
                                            <i class="fa-solid fa-star"></i>
                                        @elseif ($star - $avgRating < 1 && $star - $avgRating > 0)
                                            <i class="fa-solid fa-star-half-stroke"></i>
                                        @else
                                            <i class="fa-regular fa-star text-slate-300"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-xs font-semibold text-slate-700">{{ $avgRating > 0 ? $avgRating : '0' }} out of 5</span>
                            </div>

                            <p class="mt-2 text-xs text-slate-500">{{ $totalRatingsCount }} user {{ Str::plural('rating', $totalRatingsCount) }}</p>
                        </div>

                        {{-- Star Distribution Breakdown Bars --}}
                        <div class="mt-6 space-y-2.5 max-w-sm">
                            @for ($s = 5; $s >= 1; $s--)
                                @php
                                    $countForStar = $starCounts[$s] ?? 0;
                                    $pct = $totalRatingsCount > 0 ? round(($countForStar / $totalRatingsCount) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="w-10 font-medium text-slate-600">
                                        {{ $s }} star
                                    </span>
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="w-9 text-right font-medium text-slate-500">{{ $pct }}%</span>
                                </div>
                            @endfor
                        </div>
                    </div>

                </div>
            </div>
        </section>

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

    @include('partials.legal-modals')
</body>
</html>
