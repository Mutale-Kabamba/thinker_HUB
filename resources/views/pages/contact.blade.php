<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Contact | think.er HUB',
        'description' => 'Contact think.er HUB for enrollment support, course guidance, and practical digital training information.',
        'keywords' => 'contact thinker hub, enrollment support, digital skills training',
        'type' => 'website',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="public-layout hub-public bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] relative overflow-hidden py-16 lg:py-20">
            <div class="mx-auto max-w-6xl px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Get In Touch</p>
                <h1 class="mt-4 text-4xl font-black text-white sm:text-5xl">Contact Thinker Hub</h1>
                <p class="mx-auto mt-5 max-w-2xl text-slate-300">Questions about enrollment, tracks, or platform access? Send us a message and we will reply quickly.</p>
            </div>
        </section>

        <section class="py-14 lg:py-20">
            <div class="mx-auto grid max-w-6xl gap-8 px-6 lg:grid-cols-2 lg:px-8">
                <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Send a Message</h2>
                    <p class="mt-1.5 text-sm text-slate-600">Our team usually responds within one business day.</p>
                    @if (session('contact_success'))
                        <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('contact_success') }}
                        </div>
                    @endif
                    @if ($errors->has('contact'))
                        <div class="mt-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->first('contact') }}
                        </div>
                    @endif
                    @if (session('contact_warning'))
                        <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            {{ session('contact_warning') }}
                        </div>
                    @endif
                    @php($rateLimitError = $errors->first() ?? null)
                    @if ($rateLimitError && Illuminate\Support\Str::contains(strtolower($rateLimitError), ['too many', '429', 'rate limit', 'throttle']))
                        <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Too many messages sent. Please wait a minute before trying again.
                        </div>
                    @endif
                    <form method="POST" action="{{ route('landing.contact.store') }}" class="mt-5 space-y-3.5">
                        @csrf
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full name" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none" required>
                        @error('name')
                            <p class="-mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email address" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none" required>
                        @error('email')
                            <p class="-mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <div x-data="{ selectedSubject: '{{ old('subject', 'General Inquiry') }}' }" class="space-y-3">
                            <select name="subject" x-model="selectedSubject" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none" required>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Enrollment Support">Enrollment Support</option>
                                <option value="Course Guidance">Course Guidance</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Partnership Opportunity">Partnership Opportunity</option>
                                <option value="Other">Other (type custom subject)</option>
                            </select>
                            <input
                                type="text"
                                name="custom_subject"
                                value="{{ old('custom_subject') }}"
                                x-bind:required="selectedSubject === 'Other'"
                                x-show="selectedSubject === 'Other'"
                                x-transition
                                placeholder="Type your custom subject"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none"
                            >
                        </div>
                        @error('subject')
                            <p class="-mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('custom_subject')
                            <p class="-mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <textarea name="message" placeholder="How can we help?" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:outline-none" required>{{ old('message') }}</textarea>
                        @error('message')
                            <p class="-mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-7 py-3 text-xs sm:text-sm font-bold text-[#0a2d27] hover:bg-amber-300 transition-all cursor-pointer">
                            <span>Send Message</span>
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Direct Contact Details</h3>
                        <div class="mt-4 space-y-2.5 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-800">Phone:</span> <button type="button" @click="$dispatch('open-contact')" class="ml-1 text-[#0a2d27] font-bold underline-offset-2 hover:underline cursor-pointer">+260772640546</button></p>
                            <p><span class="font-semibold text-slate-800">Email:</span> <a href="mailto:thinkerhub@oristudiozm.com" class="text-[#0a2d27] font-semibold underline-offset-2 hover:underline">thinkerhub@oristudiozm.com</a></p>
                            <p><span class="font-semibold text-slate-800">Address:</span> 10A Off Natwange Street, Airport, Livingstone, Zambia</p>
                        </div>
                        <div class="mt-4 flex items-center justify-start gap-3 text-slate-500">
                            <a href="#" class="h-8 w-8 rounded-full border border-slate-200 flex items-center justify-center transition hover:border-[#0a2d27] hover:text-[#0a2d27]" aria-label="Facebook"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                            <a href="#" class="h-8 w-8 rounded-full border border-slate-200 flex items-center justify-center transition hover:border-[#0a2d27] hover:text-[#0a2d27]" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in text-xs"></i></a>
                            <a href="#" class="h-8 w-8 rounded-full border border-slate-200 flex items-center justify-center transition hover:border-[#0a2d27] hover:text-[#0a2d27]" aria-label="YouTube"><i class="fa-brands fa-youtube text-xs"></i></a>
                        </div>
                    </div>

                    <!-- Interactive Google Map -->
                    <div class="mt-6">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            <iframe
                                title="think.er HUB Livingstone Location Map"
                                src="https://maps.google.com/maps?q=10A%20Off%20Natwange%20Street%2C%20Livingstone%2C%20Zambia&t=&z=16&ie=UTF8&iwloc=&output=embed"
                                width="100%"
                                height="220"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                class="w-full block"
                            ></iframe>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5 text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                Livingstone, Zambia
                            </span>
                            <a
                                href="https://maps.app.goo.gl/vRW7DM6UbtwSXHkd7?g_st=aw"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 font-semibold text-teal-700 hover:text-teal-900 transition underline-offset-2 hover:underline"
                            >
                                <span>Open in Google Maps</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 lg:px-8 pb-20">
            <div class="rounded-3xl bg-[#0a2d27] p-8 lg:p-14 text-center lg:text-left relative overflow-hidden shadow-xl">
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="max-w-xl">
                        <h2 class="text-2xl lg:text-4xl font-black leading-tight text-white">Upskill with curated courses or teach your expertise.</h2>
                        <p class="mt-3 text-sm sm:text-base text-slate-300">Enroll in a course that fits your goals, or join as a tutor and manage your learners.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3.5 w-full lg:w-auto shrink-0">
                        <a href="{{ route('register') }}" class="rounded-full bg-yellow-400 px-7 py-3.5 text-xs sm:text-sm font-bold text-[#0a2d27] hover:bg-amber-300 transition-all text-center">Enroll Now</a>
                        <a href="{{ route('landing.courses') }}" class="rounded-full border border-white/25 bg-white/5 px-7 py-3.5 text-xs sm:text-sm font-bold text-white hover:bg-white/15 transition-all text-center">Explore Courses</a>
                    </div>
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-400/5 rounded-full -mr-20 -mt-20 pointer-events-none"></div>
            </div>
        </section>
    </main>

    @include('partials.public-footer')

    @include('partials.legal-modals')
</body>
</html>
