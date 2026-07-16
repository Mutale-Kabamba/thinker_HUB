<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo-meta', [
        'title' => 'Cookie Policy | think.er HUB',
        'description' => 'Read how think.er HUB uses cookies and how to manage cookie preferences.',
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
                <h1 class="mt-3 text-3xl font-black text-white sm:text-4xl">Cookie Policy</h1>
                <p class="mt-3 text-sm text-slate-300">Last updated: {{ now()->format('F j, Y') }}</p>
            </div>
        </section>

        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-4xl px-6 py-2 lg:px-8">
                <div class="space-y-5 text-sm leading-relaxed text-slate-700">
                    <h2 class="text-lg font-bold text-slate-900">1. What Are Cookies?</h2>
                    <p>Cookies are small text files stored on your device to help websites remember preferences and maintain secure sessions.</p>

                    <h2 class="text-lg font-bold text-slate-900">2. Cookies We Use</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Cookie</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Purpose</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr>
                                    <td class="px-3 py-2">Session cookie</td>
                                    <td class="px-3 py-2">Essential</td>
                                    <td class="px-3 py-2">Keeps you logged in and secures requests.</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2">XSRF-TOKEN</td>
                                    <td class="px-3 py-2">Essential</td>
                                    <td class="px-3 py-2">Cross-site request forgery protection.</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2">thub_cookie_consent</td>
                                    <td class="px-3 py-2">Essential</td>
                                    <td class="px-3 py-2">Stores your consent preference.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-lg font-bold text-slate-900">3. Managing Cookies</h2>
                    <p>You can control cookies through browser settings. Disabling essential cookies may prevent important platform features from working correctly.</p>

                    <h2 class="text-lg font-bold text-slate-900">4. Consent</h2>
                    <p>We request consent for non-essential cookies as required by applicable law. You can change your preference by clearing cookies and revisiting the site.</p>

                    <h2 class="text-lg font-bold text-slate-900">5. Contact</h2>
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
