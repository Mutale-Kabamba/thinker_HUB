<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Knowledge Network | think.er HUB',
        'description' => 'Meet our network of Instructors, Researchers, Bloggers, and Employers on think.er HUB.',
        'keywords' => 'knowledge network, tutors, researchers, bloggers, employers, thinker hub',
        'type' => 'website',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-10 sm:py-14 lg:py-16 text-white">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center relative z-10">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Thinker Hub Network</p>
                <h1 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">Knowledge &amp; Talent Network</h1>
                <p class="mx-auto mt-3 max-w-2xl text-slate-300 text-sm sm:text-base leading-relaxed">
                    Connect with course instructors, tech researchers, article bloggers, and hiring employers.
                </p>

                {{-- Role Filter Tabs --}}
                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                    <a
                        href="{{ route('landing.instructors', ['role' => 'all']) }}"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $activeRole === 'all' ? 'bg-yellow-400 text-[#0a2d27] shadow-md' : 'bg-white/10 text-white hover:bg-white/20' }}"
                    >
                        All Members
                    </a>
                    <a
                        href="{{ route('landing.instructors', ['role' => 'instructor']) }}"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $activeRole === 'instructor' ? 'bg-teal-500 text-white shadow-md' : 'bg-white/10 text-white hover:bg-white/20' }}"
                    >
                        <i class="fa-solid fa-chalkboard-user mr-1 text-[11px]"></i> Instructors
                    </a>
                    <a
                        href="{{ route('landing.instructors', ['role' => 'researcher']) }}"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $activeRole === 'researcher' ? 'bg-amber-500 text-white shadow-md' : 'bg-white/10 text-white hover:bg-white/20' }}"
                    >
                        <i class="fa-solid fa-wand-magic-sparkles mr-1 text-[11px]"></i> Researchers
                    </a>
                    <a
                        href="{{ route('landing.instructors', ['role' => 'blogger']) }}"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $activeRole === 'blogger' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white/10 text-white hover:bg-white/20' }}"
                    >
                        <i class="fa-solid fa-newspaper mr-1 text-[11px]"></i> Bloggers
                    </a>
                    <a
                        href="{{ route('landing.instructors', ['role' => 'employer']) }}"
                        class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $activeRole === 'employer' ? 'bg-emerald-500 text-white shadow-md' : 'bg-white/10 text-white hover:bg-white/20' }}"
                    >
                        <i class="fa-solid fa-briefcase mr-1 text-[11px]"></i> Employers
                    </a>
                </div>
            </div>
        </section>

        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if ($instructors->isNotEmpty())
                    <div class="grid gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @foreach ($instructors as $member)
                            @php
                                $roleTitle = match($member->role) {
                                    'instructor' => 'INSTRUCTOR',
                                    'researcher' => 'RESEARCHER',
                                    'blogger' => 'BLOGGER',
                                    'employer' => 'EMPLOYER',
                                    default => strtoupper($member->role)
                                };
                                $profileSlug = \Illuminate\Support\Str::slug($member->name ?: (string) $member->id);
                                $avatarUrl = $member->getFilamentAvatarUrl();

                                $skillsRaw = $member->specialty ?: $member->proficiency;
                                $skills = [];
                                if ($skillsRaw) {
                                    $delimiter = str_contains($skillsRaw, '|') ? '|' : (str_contains($skillsRaw, ',') ? ',' : null);
                                    $skills = $delimiter ? array_filter(array_map('trim', explode($delimiter, $skillsRaw))) : [trim($skillsRaw)];
                                }
                            @endphp

                            <article class="group bg-white rounded-2xl p-3 sm:p-3.5 shadow-sm border border-slate-200/80 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    {{-- Photo Header Container --}}
                                    <div class="relative h-52 sm:h-52 md:h-48 w-full overflow-hidden rounded-xl bg-gradient-to-br from-slate-100 to-teal-50/50 mb-2.5 flex items-center justify-center">
                                        @if ($avatarUrl)
                                            <img
                                                src="{{ $avatarUrl }}"
                                                class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-700"
                                                alt="{{ $member->name }}"
                                                onerror="this.parentElement.innerHTML='<div class=\'w-14 h-14 rounded-full bg-teal-100 flex items-center justify-center\'><span class=\'text-lg font-black text-teal-700\'>{{ strtoupper(substr($member->name, 0, 2)) }}</span></div>'"
                                            >
                                        @else
                                            <div class="w-16 h-16 rounded-full bg-teal-100 flex items-center justify-center shadow-inner">
                                                <span class="text-xl font-black text-teal-700">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Member Details --}}
                                    <div class="px-0.5">
                                        {{-- Neatly Placed Role Badge --}}
                                        <div class="mb-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider text-teal-800 bg-teal-50 border border-teal-200/70">
                                                {{ $roleTitle }}
                                            </span>
                                        </div>

                                        <h3 class="text-sm sm:text-base font-bold text-[#0a2d27] group-hover:text-teal-700 transition-colors leading-snug truncate">
                                            <a href="{{ route('landing.instructors.show', ['instructor' => $member->id, 'slug' => $profileSlug]) }}" title="{{ $member->name }}">
                                                {{ $member->name }}
                                            </a>
                                        </h3>

                                        @if ($member->occupation || $member->company)
                                            <p class="text-[11px] sm:text-xs font-medium text-slate-500 mt-0.5 truncate" title="{{ $member->occupation ?: $member->company }}">
                                                {{ $member->occupation ?: $member->company }}
                                            </p>
                                        @endif

                                        {{-- Skills / Specialty Tags --}}
                                        @if (!empty($skills))
                                            <div class="flex flex-wrap items-center gap-1 my-1.5">
                                                @foreach (array_slice($skills, 0, 2) as $skill)
                                                    <span class="inline-flex items-center bg-teal-50/90 text-teal-800 border border-teal-200/60 text-[10px] font-medium rounded-md px-1.5 py-0.5 leading-tight truncate max-w-[130px]" title="{{ $skill }}">
                                                        {{ $skill }}
                                                    </span>
                                                @endforeach
                                                @if (count($skills) > 2)
                                                    <span class="inline-flex items-center bg-slate-100 text-slate-600 text-[9px] font-bold rounded-md px-1.5 py-0.5" title="{{ implode(', ', array_slice($skills, 2)) }}">
                                                        +{{ count($skills) - 2 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Compact Action Bar: Social Icons & Profile Button Inline --}}
                                <div class="px-0.5 pt-2 mt-auto border-t border-slate-100 flex items-center justify-between gap-1.5">
                                    {{-- Social Icons Row --}}
                                    <div class="flex items-center gap-1">
                                        @if ($member->whatsapp)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->whatsapp) }}" target="_blank" rel="noopener" class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition" title="WhatsApp">
                                                <i class="fa-brands fa-whatsapp text-[11px]"></i>
                                            </a>
                                        @endif
                                        @if ($member->linkedin_url)
                                            <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener" class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition" title="LinkedIn">
                                                <i class="fa-brands fa-linkedin-in text-[10px]"></i>
                                            </a>
                                        @endif
                                        @if ($member->facebook_url)
                                            <a href="{{ $member->facebook_url }}" target="_blank" rel="noopener" class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition" title="Facebook">
                                                <i class="fa-brands fa-facebook-f text-[10px]"></i>
                                            </a>
                                        @endif
                                        @if ($member->github_url)
                                            <a href="{{ $member->github_url }}" target="_blank" rel="noopener" class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center hover:bg-slate-200 transition" title="GitHub">
                                                <i class="fa-brands fa-github text-[10px]"></i>
                                            </a>
                                        @endif
                                        @if ($member->instagram_url)
                                            <a href="{{ $member->instagram_url }}" target="_blank" rel="noopener" class="w-6 h-6 rounded-full bg-pink-50 text-pink-600 flex items-center justify-center hover:bg-pink-100 transition" title="Instagram">
                                                <i class="fa-brands fa-instagram text-[10px]"></i>
                                            </a>
                                        @endif
                                        @if ($member->portfolio_url)
                                            <a href="{{ $member->portfolio_url }}" target="_blank" rel="noopener" class="w-6 h-6 rounded-full bg-teal-50 text-teal-700 flex items-center justify-center hover:bg-teal-100 transition" title="Portfolio">
                                                <i class="fa-solid fa-globe text-[10px]"></i>
                                            </a>
                                        @endif
                                    </div>

                                    {{-- CTA Button --}}
                                    <a
                                        href="{{ route('landing.instructors.show', ['instructor' => $member->id, 'slug' => $profileSlug]) }}"
                                        class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50/80 hover:bg-[#0a2d27] hover:border-[#0a2d27] hover:text-white px-2.5 py-1 text-[10px] sm:text-[11px] font-bold text-slate-700 transition-all shrink-0"
                                    >
                                        View Profile &rarr;
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 bg-white rounded-3xl border border-slate-100 shadow-xs">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-users text-xl text-slate-400"></i>
                        </div>
                        <p class="text-slate-800 font-bold text-base">No network members listed yet for this role.</p>
                        <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">Be the first to join our network! Register as an Instructor, Blogger, Researcher, or Employer.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Call to Action Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
            <div class="rounded-[2.5rem] lg:rounded-[4rem] bg-[#0a2d27] p-8 lg:p-16 text-center lg:text-left relative overflow-hidden text-white shadow-xl">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400 mb-2">JOIN THE NETWORK</p>
                        <h2 class="text-3xl lg:text-4xl font-black leading-tight text-white">Share your knowledge &amp; post opportunities.</h2>
                        <p class="mt-4 text-slate-300 text-sm sm:text-base leading-relaxed">Join as an Instructor, Researcher, Blogger, or Employer to connect with tech enthusiasts and upskill our community.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                        <a href="{{ route('hub.index', ['register' => 1]) }}" class="rounded-full bg-yellow-400 px-8 py-4 font-bold text-[#0a2d27] hover:bg-white transition-all text-center text-sm">Register as Contributor</a>
                        <a href="{{ route('landing.instructors.apply') }}" class="rounded-full border border-white/20 px-8 py-4 font-bold text-white hover:bg-white/10 transition-all text-center text-sm">Apply as Instructor</a>
                    </div>
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-400/5 rounded-full -mr-20 -mt-20 pointer-events-none"></div>
            </div>
        </section>
    </main>

    <footer class="bg-white border-t border-slate-200 py-16">
        <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center lg:text-left">
            <div class="grid gap-12 lg:grid-cols-[1.5fr_1fr_1fr]">
                <div>
                    <div class="flex items-center justify-center gap-3 lg:justify-start">
                        <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500">
                        think.er HUB connects tutors, researchers, bloggers, and employers with learners focused on real-world outcomes.
                    </p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-sm text-slate-500 lg:justify-start">
                        @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-[#0a2d27] px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-[#11443c]">Login</a>
                        @endguest
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

    @include('partials.legal-modals')

</body>
</html>
