<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Instructors | think.er HUB',
        'description' => 'Meet industry-focused instructors guiding learners through practical, project-based training at think.er HUB.',
        'keywords' => 'instructors, mentors, practical learning, thinker hub',
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
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Mentor Network</p>
                <h1 class="mt-4 text-4xl font-black text-white sm:text-5xl">Meet Our Instructors</h1>
                <p class="mx-auto mt-5 max-w-2xl text-slate-300">Learn from experienced mentors dedicated to practical, career-ready learning outcomes.</p>
            </div>
        </section>

        <section class="py-20 lg:py-24">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                @if ($instructors->isNotEmpty())
                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($instructors as $instructor)
                            <article class="group bg-white rounded-[2rem] p-4 shadow-sm hover:shadow-xl transition-all border border-slate-100">
                                <div class="relative h-64 overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-teal-50 to-slate-100 flex items-center justify-center">
                                    @if ($instructor->profile_photo_path)
                                        <img src="{{ Storage::disk('public')->url($instructor->profile_photo_path) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="{{ $instructor->name }}" onerror="this.parentElement.innerHTML='<div class=\'w-24 h-24 rounded-full bg-teal-100 flex items-center justify-center\'><span class=\'text-3xl font-bold text-teal-600\'>{{ strtoupper(substr($instructor->name, 0, 2)) }}</span></div>'">
                                    @else
                                        <div class="w-24 h-24 rounded-full bg-teal-100 flex items-center justify-center">
                                            <span class="text-3xl font-bold text-teal-600">{{ strtoupper(substr($instructor->name, 0, 2)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="px-3 py-6">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-teal-600">Instructor</p>
                                    <h3 class="mt-2 text-xl font-bold text-slate-900 group-hover:text-teal-600 transition-colors">{{ $instructor->name }}</h3>

                                    @if ($instructor->occupation)
                                        <p class="mt-1 text-sm text-slate-500">{{ $instructor->occupation }}</p>
                                    @endif

                                    @if ($instructor->proficiency)
                                        <div class="mt-3">
                                            <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700 border border-teal-200">{{ $instructor->proficiency }}</span>
                                        </div>
                                    @endif

                                    @if ($instructor->whatsapp || $instructor->linkedin_url || $instructor->facebook_url)
                                        <div class="mt-4 flex items-center gap-3">
                                            @if ($instructor->whatsapp)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $instructor->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-green-50 flex items-center justify-center text-green-600 hover:bg-green-100 transition" title="WhatsApp">
                                                    <i class="fa-brands fa-whatsapp text-lg"></i>
                                                </a>
                                            @endif
                                            @if ($instructor->linkedin_url)
                                                <a href="{{ $instructor->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-700 hover:bg-blue-100 transition" title="LinkedIn">
                                                    <i class="fa-brands fa-linkedin-in text-lg"></i>
                                                </a>
                                            @endif
                                            @if ($instructor->facebook_url)
                                                <a href="{{ $instructor->facebook_url }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 hover:bg-indigo-100 transition" title="Facebook">
                                                    <i class="fa-brands fa-facebook-f text-lg"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-chalkboard-user text-2xl text-slate-400"></i>
                        </div>
                        <p class="text-slate-500 font-medium">Our instructor team is being assembled.</p>
                        <p class="mt-2 text-sm text-slate-400">Want to be the first? Apply below!</p>
                    </div>
                @endif
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
                        <a href="{{ route('landing.instructors.apply') }}" class="rounded-full border border-white/20 px-8 py-4 font-bold text-white hover:bg-white/10 transition-all text-center">Apply as Instructor</a>
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
                        <button type="button" @click="$dispatch('open-legal', 'privacy')" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</button>
                        <button type="button" @click="$dispatch('open-legal', 'cookies')" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</button>
                        <button type="button" @click="$dispatch('open-legal', 'terms')" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</button>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @include('partials.legal-modals')

</body>
</html>
