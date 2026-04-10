<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>think.er HUB</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logos/icon_green.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    <header class="sticky top-0 z-50 bg-[#0a2d27] py-4 shadow-lg">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-white shrink-0">
                <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
            </a>

            <nav class="hidden md:flex items-center gap-10 text-[13px] font-semibold uppercase tracking-wider text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-yellow-400 transition-colors">Home</a>
                <a href="{{ route('landing.courses') }}" class="hover:text-yellow-400 transition-colors">Courses</a>
                <a href="{{ route('landing.instructors') }}" class="hover:text-yellow-400 transition-colors">Instructors</a>
                <a href="{{ route('landing.contact') }}" class="text-yellow-400">Contact</a>
            </nav>

            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('login') }}" class="text-sm font-bold text-white hover:text-yellow-400">Login</a>
                <a href="{{ route('enroll') }}" class="rounded-full bg-yellow-400 px-6 py-2.5 text-sm font-bold text-[#0a2d27] hover:bg-white transition-all">Enroll Now</a>
            </div>

            <button class="md:hidden text-white text-2xl" @click="mobileMenu = !mobileMenu">
                <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
            </button>
        </div>

        <div class="md:hidden bg-[#0a2d27] border-t border-white/10" x-show="mobileMenu" x-transition>
            <nav class="flex flex-col p-6 gap-4 text-white font-semibold">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('landing.courses') }}">Courses</a>
                <a href="{{ route('landing.instructors') }}">Instructors</a>
                <a href="{{ route('landing.contact') }}" class="text-yellow-400">Contact</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-16 lg:py-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Get In Touch</p>
                <h1 class="mt-4 text-4xl font-black text-white sm:text-5xl">Contact Thinker Hub</h1>
                <p class="mx-auto mt-5 max-w-2xl text-slate-300">Questions about enrollment, tracks, or platform access? Send us a message and we will reply quickly.</p>
            </div>
        </section>

        <section class="py-20 lg:py-24">
            <div class="mx-auto grid max-w-6xl gap-8 px-6 lg:grid-cols-2 lg:px-8">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-slate-900">Send a Message</h2>
                    <p class="mt-2 text-sm text-slate-600">Our team usually responds within one business day.</p>
                    <form class="mt-6 space-y-4">
                        <input type="text" placeholder="Full name" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                        <input type="email" placeholder="Email address" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none">
                        <textarea placeholder="How can we help?" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-teal-500 focus:outline-none"></textarea>
                        <button type="button" class="rounded-full bg-yellow-400 px-7 py-3 text-sm font-bold text-[#0a2d27] hover:bg-[#0a2d27] hover:text-white transition-all">Send Message</button>
                    </form>
                </div>

                <div class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold text-slate-900">Contact Details</h2>
                    <div class="mt-6 space-y-6 text-sm text-slate-600">
                        <div class="flex items-start gap-4">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 text-teal-700"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <p class="font-semibold text-slate-900">Office</p>
                                <p>12 Innovation Avenue, Lagos, Nigeria</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 text-teal-700"><i class="fa-solid fa-envelope"></i></span>
                            <div>
                                <p class="font-semibold text-slate-900">Email</p>
                                <p>support@thinkerhub.test</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 text-teal-700"><i class="fa-solid fa-phone"></i></span>
                            <div>
                                <p class="font-semibold text-slate-900">Phone</p>
                                <p>+234 800 000 0000</p>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <p><span class="font-semibold text-slate-700">Phone:</span> +260 977 000 000</p>
                            <p><span class="font-semibold text-slate-700">Email:</span> support@thinkerhub.com</p>
                            <p><span class="font-semibold text-slate-700">Address:</span> Lusaka, Zambia</p>
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
                        <a href="{{ route('landing.contact') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</a>
                        <a href="{{ route('landing.contact') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</a>
                        <a href="{{ route('landing.contact') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
