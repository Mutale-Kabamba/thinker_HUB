<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo-meta', [
        'title' => 'Dashboard | think.er HUB',
        'description' => 'Private dashboard area for think.er HUB learners and administrators.',
        'type' => 'website',
        'indexable' => false,
    ])

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800|space-grotesk:500,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-900">
    @php
        $resolvedSection = $section ?? (auth()->user()?->isAdmin() ? 'admin' : 'student');
        $links = $resolvedSection === 'admin'
            ? [
                ['name' => 'Overview', 'route' => 'admin.overview'],
                ['name' => 'Student Manager', 'route' => 'admin.students'],
                ['name' => 'Courses', 'route' => 'admin.courses'],
                ['name' => 'Assignments', 'route' => 'admin.assignments'],
                ['name' => 'Assessments', 'route' => 'admin.assessments'],
                ['name' => 'Learning Materials', 'route' => 'admin.materials'],
            ]
            : [
                ['name' => 'Overview', 'route' => 'student.overview'],
                ['name' => 'Courses', 'route' => 'student.courses'],
                ['name' => 'Assignments', 'route' => 'student.assignments'],
                ['name' => 'Materials', 'route' => 'student.materials'],
            ];
    @endphp

    @if (isset($slot))
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    @else
        <div class="edu-shell lg:grid lg:grid-cols-[280px_1fr]">
            <aside class="edu-sidebar text-slate-100 p-6 lg:p-7">
                <a href="{{ route('dashboard') }}" class="edu-display text-2xl font-black tracking-tight">Thinker HUB</a>
                <p class="mt-2 text-xs uppercase tracking-[0.2em] text-cyan-100/75">Learning Platform</p>

                <nav class="mt-8 space-y-2">
                    @foreach ($links as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            class="edu-nav-link {{ request()->routeIs($link['route']) ? 'is-active' : '' }}"
                        >
                            {{ $link['name'] }}
                        </a>
                    @endforeach
                </nav>

                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                    @csrf
                    <button class="w-full rounded-lg border border-slate-600/60 bg-slate-900/60 px-3 py-2 text-sm font-semibold text-slate-100 transition hover:border-amber-300/70 hover:bg-amber-500/20" type="submit">
                        Log out
                    </button>
                </form>
            </aside>

            <main class="edu-content p-4 sm:p-8 lg:p-10">
                <header class="edu-reveal mb-6 rounded-3xl border border-cyan-100 bg-gradient-to-r from-cyan-50 via-white to-amber-50 p-5 shadow-sm sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Thinker HUB Workspace</p>
                    <h1 class="edu-display mt-2 text-2xl sm:text-3xl font-black tracking-tight text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">Plan courses, track learner progress, and keep every resource in one focused platform.</p>
                </header>

                @yield('content')
            </main>
        </div>
    @endif
</body>
</html>
