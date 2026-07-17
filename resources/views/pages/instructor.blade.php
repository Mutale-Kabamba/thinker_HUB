<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => $instructor->name.' | Instructor Profile | think.er HUB',
        'description' => ($instructor->instructorApplication?->bio ?: ($instructor->occupation ?: 'Tutor profile on think.er HUB.')).' Explore tutor-led courses and enroll to upskill.',
        'keywords' => 'instructor profile, tutor, curated courses, upskill, thinker hub',
        'type' => 'profile',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    @php
        $profileImage = $instructor->profile_photo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($instructor->profile_photo_path)
            : null;

        $coursesCount = $courses->count();
        $learnersCount = (int) $courses->sum(fn ($course) => (int) ($course->enrollments_count ?? 0));

        $ratedCourses = $courses->filter(fn ($course) => ($course->ratings_avg_rating ?? null) !== null);
        $avgRating = $ratedCourses->isNotEmpty()
            ? round((float) $ratedCourses->avg('ratings_avg_rating'), 1)
            : null;

        $courseImageKeywords = [
            'images/courses/office.png' => ['office', 'excel', 'word', 'powerpoint'],
            'images/courses/design.png' => ['design', 'graphics', 'ui', 'ux', 'canva', 'photoshop'],
            'images/courses/data.png' => ['data', 'analytics', 'analysis', 'sql', 'power bi', 'tableau'],
            'images/courses/media_ai.png' => ['social', 'media', 'marketing', 'content', 'ai'],
            'images/courses/computer.png' => ['computer', 'digital', 'ict', 'literacy', 'fundamentals'],
        ];
        $courseImages = array_keys($courseImageKeywords);

        $resolveCourseImage = static function ($course) use ($courseImageKeywords, $courseImages): string {
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

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-16 lg:py-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <a href="{{ route('landing.instructors') }}" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-yellow-400 hover:text-yellow-300 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Instructors
                </a>

                <div class="mt-6 grid gap-8 lg:grid-cols-[220px_1fr] lg:items-center">
                    <div class="mx-auto lg:mx-0">
                        @if ($profileImage)
                            <img src="{{ $profileImage }}" alt="{{ $instructor->name }}" class="h-48 w-48 rounded-3xl object-cover border-4 border-white/15 shadow-2xl">
                        @else
                            <div class="h-48 w-48 rounded-3xl bg-white/10 border-4 border-white/15 shadow-2xl flex items-center justify-center">
                                <span class="text-5xl font-black text-white">{{ strtoupper(substr($instructor->name, 0, 2)) }}</span>
                            </div>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Instructor Profile</p>
                        <h1 class="mt-3 text-4xl font-black text-white sm:text-5xl">{{ $instructor->name }}</h1>
                        @if ($instructor->occupation)
                            <p class="mt-3 text-lg text-slate-200">{{ $instructor->occupation }}</p>
                        @endif
                        @if ($instructor->proficiency)
                            <p class="mt-2 text-sm font-semibold text-teal-200">Specialty: {{ $instructor->proficiency }}</p>
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3 text-xs">
                            <span class="rounded-full bg-white/10 border border-white/15 px-4 py-2 font-semibold text-white">{{ $coursesCount }} {{ \Illuminate\Support\Str::plural('Course', $coursesCount) }}</span>
                            <span class="rounded-full bg-white/10 border border-white/15 px-4 py-2 font-semibold text-white">{{ $learnersCount }} {{ \Illuminate\Support\Str::plural('Learner', $learnersCount) }}</span>
                            @if ($avgRating !== null)
                                <span class="rounded-full bg-white/10 border border-white/15 px-4 py-2 font-semibold text-white">{{ $avgRating }} / 5 Avg Rating</span>
                            @endif
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($instructor->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $instructor->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-emerald-500/90 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-500 transition">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    WhatsApp
                                </a>
                            @endif
                            @if ($instructor->linkedin_url)
                                <a href="{{ $instructor->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-blue-500/90 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500 transition">
                                    <i class="fa-brands fa-linkedin-in"></i>
                                    LinkedIn
                                </a>
                            @endif
                            @if ($instructor->facebook_url)
                                <a href="{{ $instructor->facebook_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-indigo-500/90 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-500 transition">
                                    <i class="fa-brands fa-facebook-f"></i>
                                    Facebook
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-14 lg:py-18">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 grid gap-6 lg:grid-cols-3">
                <article class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">About</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900">Meet {{ $instructor->name }}</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                        {{ $instructor->instructorApplication?->bio ?: 'This tutor is actively teaching on think.er HUB. Explore their courses below and enroll to start upskilling.' }}
                    </p>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Quick Facts</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li><span class="font-semibold text-slate-900">Courses:</span> {{ $coursesCount }}</li>
                        <li><span class="font-semibold text-slate-900">Learners:</span> {{ $learnersCount }}</li>
                        <li><span class="font-semibold text-slate-900">Expertise:</span> {{ $instructor->proficiency ?: 'General' }}</li>
                        <li><span class="font-semibold text-slate-900">Occupation:</span> {{ $instructor->occupation ?: 'Tutor' }}</li>
                    </ul>
                </article>

                @if (filled($instructor->instructorApplication?->qualifications))
                    <article class="lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Qualifications</p>
                        <div class="mt-3 text-sm leading-relaxed text-slate-600 whitespace-pre-line">{{ $instructor->instructorApplication->qualifications }}</div>
                    </article>
                @endif

                @if (filled($instructor->instructorApplication?->experience))
                    <article class="lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Experience</p>
                        <div class="mt-3 text-sm leading-relaxed text-slate-600 whitespace-pre-line">{{ $instructor->instructorApplication->experience }}</div>
                    </article>
                @endif
            </div>
        </section>

        <section class="pb-20 lg:pb-24">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-600">Courses by {{ $instructor->name }}</p>
                        <h2 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Enroll and Upskill</h2>
                    </div>
                </div>

                @if ($courses->isNotEmpty())
                    <div class="mt-10 grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($courses as $course)
                            @php
                                $courseImage = $resolveCourseImage($course);
                                $courseSlug = \Illuminate\Support\Str::slug($course->title ?: $course->code ?: (string) $course->id);
                                $courseStudents = (int) ($course->enrollments_count ?? 0);
                                $courseRating = $course->ratings_avg_rating !== null ? round((float) $course->ratings_avg_rating, 1) : null;
                                $courseRatingCount = (int) ($course->ratings_count ?? 0);
                            @endphp
                            <article class="group rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm hover:shadow-xl transition-all">
                                <div class="relative h-44 overflow-hidden rounded-[1.2rem]">
                                    <img src="{{ asset($courseImage) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="{{ $course->title }} image">
                                </div>
                                <div class="px-2 py-5">
                                    <h3 class="text-lg font-bold text-slate-900">{{ $course->title }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">{{ $course->code }}</p>
                                    <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px]">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">{{ $courseStudents }} {{ \Illuminate\Support\Str::plural('Student', $courseStudents) }}</span>
                                        @if ($courseRating !== null)
                                            <span class="rounded-full bg-amber-50 px-3 py-1 font-semibold text-amber-700">{{ $courseRating }}/5 ({{ $courseRatingCount }})</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => $courseSlug]) }}" class="mt-5 inline-flex items-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#11443c]">
                                        Open Course
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="mt-10 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 p-10 text-center">
                        <i class="fa-solid fa-book-open text-2xl text-slate-300"></i>
                        <p class="mt-3 text-sm font-medium text-slate-500">No courses published by this instructor yet.</p>
                    </div>
                @endif
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
                        think.er HUB connects tutors who run curated courses with learners who enroll to build practical skills.
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
