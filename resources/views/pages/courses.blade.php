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
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

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
                            <p class="text-slate-500 font-medium">No active courses available yet.</p>
                        </div>
                    @endforelse

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
