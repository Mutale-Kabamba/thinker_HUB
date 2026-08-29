<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => $instructor->name.' | Network Profile | think.er HUB',
        'description' => ($instructor->bio ?: ($instructor->occupation ?: 'Member profile on think.er HUB.')).' Explore authored resources and courses.',
        'keywords' => 'network profile, tutor, researcher, blogger, employer, thinker hub',
        'type' => 'profile',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="public-layout hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    @php
        $profileImage = $instructor->getFilamentAvatarUrl();
        $postsCount = isset($posts) ? $posts->count() : 0;
        $coursesCount = $courses->count();
        $learnersCount = (int) $courses->sum(fn ($course) => (int) ($course->enrollments_count ?? 0));

        $roleTitle = match($instructor->role) {
            'instructor' => 'Instructor',
            'researcher' => 'Researcher',
            'blogger' => 'Blogger',
            'employer' => 'Employer',
            default => ucfirst($instructor->role)
        };
    @endphp

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-16 lg:py-20 text-white">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <a href="{{ route('landing.instructors') }}" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-yellow-400 hover:text-yellow-300 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Knowledge Network
                </a>

                <div class="mt-6 grid gap-8 lg:grid-cols-[220px_1fr] lg:items-center">
                    <div class="mx-auto lg:mx-0">
                        @if ($profileImage)
                            <img src="{{ $profileImage }}" alt="{{ $instructor->name }}" class="h-48 w-48 rounded-3xl object-cover border-4 border-white/15">
                        @else
                            <div class="h-48 w-48 rounded-3xl bg-white/10 border-4 border-white/15 flex items-center justify-center">
                                <span class="text-5xl font-black text-white">{{ strtoupper(substr($instructor->name, 0, 2)) }}</span>
                            </div>
                        @endif
                    </div>

                    <div>
                        <span class="inline-block rounded-full bg-yellow-400 px-3.5 py-1 text-xs font-black uppercase tracking-wider text-[#0a2d27]">
                            {{ $roleTitle }}
                        </span>
                        <h1 class="mt-3 text-4xl font-black text-white sm:text-5xl">{{ $instructor->name }}</h1>
                        @if ($instructor->occupation || $instructor->company)
                            <p class="mt-2 text-lg text-slate-200">{{ $instructor->occupation ?: $instructor->company }}</p>
                        @endif
                        @if ($instructor->specialty || $instructor->proficiency)
                            <p class="mt-1 text-sm font-semibold text-teal-200">Specialty: {{ $instructor->specialty ?: $instructor->proficiency }}</p>
                        @endif

                        <div class="mt-3 flex items-center gap-2">
                            <x-rating-stars :rating="$instructor->instructor_rating ?? 0" :count="$instructor->instructor_review_count ?? 0" size="sm" />
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3 text-xs">
                            @if ($instructor->isInstructor())
                                <span class="rounded-full bg-white/10 border border-white/15 px-4 py-2 font-semibold text-white">{{ $coursesCount }} {{ \Illuminate\Support\Str::plural('Course', $coursesCount) }}</span>
                                <span class="rounded-full bg-white/10 border border-white/15 px-4 py-2 font-semibold text-white">{{ $learnersCount }} {{ \Illuminate\Support\Str::plural('Learner', $learnersCount) }}</span>
                            @endif
                            <span class="rounded-full bg-white/10 border border-white/15 px-4 py-2 font-semibold text-white">{{ $postsCount }} Authored {{ \Illuminate\Support\Str::plural('Resource', $postsCount) }}</span>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($instructor->whatsapp)
                                <a href="{{ Str::startsWith($instructor->whatsapp, 'http') ? $instructor->whatsapp : 'https://wa.me/' . preg_replace('/[^0-9]/', '', $instructor->whatsapp) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-emerald-500/90 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-500 transition">
                                    <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                                </a>
                            @endif
                            @if ($instructor->linkedin_url)
                                <a href="{{ $instructor->linkedin_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-blue-600/90 px-4 py-2 text-xs font-bold text-white hover:bg-blue-600 transition">
                                    <i class="fa-brands fa-linkedin-in text-sm"></i> LinkedIn
                                </a>
                            @endif
                            @if ($instructor->facebook_url)
                                <a href="{{ $instructor->facebook_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-blue-500/90 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500 transition">
                                    <i class="fa-brands fa-facebook-f text-sm"></i> Facebook
                                </a>
                            @endif
                            @if ($instructor->github_url)
                                <a href="{{ $instructor->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-slate-800/90 px-4 py-2 text-xs font-bold text-white hover:bg-slate-700 transition">
                                    <i class="fa-brands fa-github text-sm"></i> GitHub
                                </a>
                            @endif
                            @if ($instructor->instagram_url)
                                <a href="{{ $instructor->instagram_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-pink-500 to-purple-600 px-4 py-2 text-xs font-bold text-white hover:opacity-90 transition">
                                    <i class="fa-brands fa-instagram text-sm"></i> Instagram
                                </a>
                            @endif
                            @if ($instructor->portfolio_url)
                                <a href="{{ $instructor->portfolio_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-teal-500/90 px-4 py-2 text-xs font-bold text-white hover:bg-teal-500 transition">
                                    <i class="fa-solid fa-globe text-sm"></i> Website
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-4xl px-6 py-2 lg:px-8">
                <div class="space-y-6 text-sm leading-relaxed text-slate-700">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-700">About {{ $roleTitle }}</p>
                    <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">Biography &amp; Background</h2>
                    <div class="prose prose-slate max-w-none text-base leading-relaxed text-slate-700 whitespace-pre-line">
                        {{ $instructor->bio ?: 'This member is an active contributor on think.er HUB. Explore their authored resources and courses below.' }}
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900">Profile &amp; Expertise</h3>
                        <ul class="mt-3 list-disc pl-5 space-y-2 text-sm text-slate-700">
                            <li><span class="font-semibold text-slate-900">Role:</span> {{ $roleTitle }}</li>
                            @if ($instructor->company)
                                <li><span class="font-semibold text-slate-900">Company / Organization:</span> {{ $instructor->company }}</li>
                            @endif
                            @if ($instructor->specialty || $instructor->proficiency)
                                <li><span class="font-semibold text-slate-900">Specialty:</span> {{ $instructor->specialty ?: $instructor->proficiency }}</li>
                            @endif
                            @if ($instructor->occupation)
                                <li><span class="font-semibold text-slate-900">Occupation:</span> {{ $instructor->occupation }}</li>
                            @endif
                            <li><span class="font-semibold text-slate-900">Authored Resources:</span> {{ $postsCount }}</li>
                            @if ($instructor->isInstructor() && $coursesCount > 0)
                                <li><span class="font-semibold text-slate-900">Active Courses:</span> {{ $coursesCount }} ({{ $learnersCount }} {{ \Illuminate\Support\Str::plural('Learner', $learnersCount) }})</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- Authored Resources Section --}}
        @if (isset($posts) && $posts->isNotEmpty())
            <section class="pb-16 lg:pb-20">
                <div class="mx-auto max-w-5xl px-6 lg:px-8">
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-700">Publications</p>
                        <h2 class="mt-1 text-2xl font-black text-slate-900">Resources by {{ $instructor->name }}</h2>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($posts as $post)
                            <article class="group bg-white rounded-2xl p-4 border border-slate-200 flex flex-col justify-between hover:border-teal-400 hover:-translate-y-0.5 transition-all">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2.5">
                                        <span class="bg-teal-50 text-teal-800 text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full border border-teal-100">
                                            {{ App\Models\HubPost::TYPES[$post->type] ?? ucfirst($post->type) }}
                                        </span>
                                        <span class="text-xs text-slate-400 font-medium">{{ $post->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 group-hover:text-teal-700 transition cursor-pointer line-clamp-2">
                                        <a href="{{ route('hub.show', $post->slug) }}">{{ $post->title }}</a>
                                    </h3>
                                    @if ($post->excerpt)
                                        <p class="mt-1.5 text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $post->excerpt }}</p>
                                    @endif
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-xs text-slate-500 font-semibold">{{ $post->category }}</span>
                                    <a href="{{ route('hub.show', $post->slug) }}" class="text-xs font-bold text-teal-700 hover:underline">
                                        View Resource <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($courses->isNotEmpty())
            <section class="pb-16 lg:pb-20">
                <div class="mx-auto max-w-5xl px-6 lg:px-8">
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-700">Curated Courses</p>
                        <h2 class="mt-1 text-2xl font-black text-slate-900">Courses Taught</h2>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($courses as $course)
                            <article class="group rounded-2xl border border-slate-200 bg-white p-3.5 hover:border-teal-500 hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="bg-teal-50 text-teal-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded border border-teal-200/60">{{ $course->code }}</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-teal-700 transition leading-snug">{{ $course->title }}</h3>
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[11px] text-slate-500"><i class="fa-regular fa-user text-teal-600 mr-1"></i>{{ (int) ($course->enrollments_count ?? 0) }} Students</span>
                                    <a href="{{ route('landing.courses.show', ['course' => $course->id, 'slug' => \Illuminate\Support\Str::slug($course->title)]) }}" class="inline-flex items-center rounded-full bg-[#0a2d27] px-3 py-1 text-[11px] font-bold text-white hover:bg-[#11443c]">
                                        Open Course &rarr;
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Instructor Teaching Reviews Section --}}
        <section class="pb-16 lg:pb-20">
            <div class="mx-auto max-w-5xl px-6 lg:px-8">
                <div class="mb-6">
                    <span class="text-teal-600 font-bold uppercase tracking-[0.2em] text-xs">Testimonials &amp; Feedback</span>
                    <h2 class="text-2xl font-black text-slate-900 mt-1 sm:text-3xl">Student Ratings &amp; Reviews</h2>
                    <p class="mt-1 text-slate-600 text-sm">Feedback from learners who completed courses and mentorship with {{ $instructor->name }}.</p>
                </div>

                <livewire:reviews.review-list target-type="instructor" :target-id="$instructor->id" :target-title="$instructor->name" />
            </div>
        </section>

        <livewire:reviews.submit-review-modal />
    </main>

    <footer class="bg-white border-t border-slate-200 py-12 lg:py-16">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center lg:text-left">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr]">
                <div>
                    <div class="flex items-center justify-center gap-3 lg:justify-start">
                        <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500">
                        think.er HUB connects tutors, researchers, bloggers, and employers with learners.
                    </p>
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
                        <p><span class="font-semibold text-slate-700">Email:</span> <a href="mailto:thinkerhub@oristudiozm.com" class="text-[#0a2d27] hover:underline">thinkerhub@oristudiozm.com</a></p>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-200 pt-4">
                <div class="flex flex-col items-center gap-3 text-center text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-left">
                    <p>© {{ now()->year }} Thinker Hub. All rights reserved.</p>
                    <div class="flex flex-wrap items-center gap-4 font-medium">
                        <a href="{{ route('landing.privacy') }}" class="hover:text-slate-900 transition">Privacy</a>
                        <a href="{{ route('landing.cookies') }}" class="hover:text-slate-900 transition">Cookies</a>
                        <a href="{{ route('landing.terms') }}" class="hover:text-slate-900 transition">T&amp;Cs</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @include('partials.legal-modals')

</body>
</html>
