<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.seo-meta', [
            'title' => 'Account Access | think.er HUB',
            'description' => 'Sign in or create your account to access think.er HUB learning panels.',
            'type' => 'website',
            'indexable' => false,
        ])

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('partials.pwa-register')
    </head>
    <body class="font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-950">
        <div class="relative min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-24 left-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-teal-200/40 blur-3xl dark:bg-teal-700/20"></div>
                <div class="absolute bottom-0 right-0 h-48 w-48 rounded-full bg-cyan-200/30 blur-3xl dark:bg-cyan-700/20"></div>
            </div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-4 py-8 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="mx-auto inline-flex items-center" aria-label="think.er HUB home">
                    <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-16 w-auto">
                </a>

                <main class="mx-auto mt-6 flex w-full flex-1 items-center justify-center">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
