<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Privacy Policy | think.er HUB',
        'description' => 'Read the think.er HUB privacy policy and data protection practices.',
        'type' => 'article',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.pwa-register')
</head>
<body class="bg-[#f8fcf9] text-slate-900 font-sans antialiased" x-data="{ mobileMenu: false }">

    @include('partials.public-header')

    <main>
        <section class="bg-[#0a2d27] py-14 lg:py-16">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-yellow-400">Legal</p>
                <h1 class="mt-3 text-3xl font-black text-white sm:text-4xl">Privacy Policy</h1>
                <p class="mt-3 text-sm text-slate-300">Last updated: {{ now()->format('F j, Y') }}</p>
            </div>
        </section>

        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-4xl px-6 py-2 lg:px-8">
                <div class="space-y-5 text-sm leading-relaxed text-slate-700">
                    <h2 class="text-lg font-bold text-slate-900">1. Introduction</h2>
                    <p>think.er HUB ("we", "us", "our") is committed to protecting personal data in compliance with the Data Protection Act No. 3 of 2021 and the Cyber Security and Cyber Crimes Act No. 2 of 2021 of Zambia.</p>

                    <h2 class="text-lg font-bold text-slate-900">2. Data We Collect</h2>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Account information: name, email, phone number, and role.</li>
                        <li>Academic data: enrollments, submissions, grades, and progress.</li>
                        <li>Technical data: IP address, browser/device details, access logs.</li>
                        <li>Communication data: messages sent through forms or support channels.</li>
                    </ul>

                    <h2 class="text-lg font-bold text-slate-900">3. Legal Basis & Purpose</h2>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Consent, where you provide it.</li>
                        <li>Contractual necessity to deliver enrolled services.</li>
                        <li>Legitimate interests, including security and fraud prevention.</li>
                    </ul>

                    <h2 class="text-lg font-bold text-slate-900">4. Your Rights</h2>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Access, correction, erasure, and restriction of processing.</li>
                        <li>Data portability where applicable.</li>
                        <li>Right to lodge a complaint with Zambia's Data Protection Commissioner.</li>
                    </ul>

                    <h2 class="text-lg font-bold text-slate-900">5. Security & Retention</h2>
                    <p>We use reasonable technical and organizational safeguards such as encryption in transit, hashed passwords, and role-based controls. Data is retained only as long as required for service delivery, legal obligations, and verification needs.</p>

                    <h2 class="text-lg font-bold text-slate-900">6. Contact</h2>
                    <div class="space-y-3 text-slate-500">
                        <p><span class="font-semibold text-slate-900">Phone:</span> <span class="ml-2 text-slate-900">+260772640546</span></p>
                        <p><span class="font-semibold text-slate-900">Email:</span> <a href="mailto:thinker.learn@gmail.com" class="text-slate-900">thinker.learn@gmail.com</a></p>
                        <p><span class="font-semibold text-slate-900">Address:</span> 10A Off Natwange Street, Airpot, Livingstone Zambia</p>
                        <div class="pt-1 flex items-center gap-5 text-slate-500">
                            <a href="#" class="transition hover:text-[#0a2d27]" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                            <a href="#" class="transition hover:text-[#0a2d27]" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                            <a href="#" class="transition hover:text-[#0a2d27]" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6">
        <div class="mx-auto max-w-6xl px-6 lg:px-8">
            <div class="flex flex-col items-center gap-4 text-center text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-left">
                <p>© {{ now()->year }} Thinker Hub. All rights reserved.</p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('landing.privacy') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Privacy</a>
                    <a href="{{ route('landing.cookies') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">Cookies</a>
                    <a href="{{ route('landing.terms') }}" class="underline-offset-4 hover:text-slate-700 hover:underline">T&amp;Cs</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
