<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
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
    @include('partials.pwa-register')
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-900 overflow-x-hidden min-h-dvh safe-p" x-data="{ sidebarOpen: false }">
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
        <div class="min-h-screen bg-gray-100 flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {{ $slot }}
            </main>
        </div>
    @else
        <div class="edu-shell min-h-dvh lg:grid lg:grid-cols-[280px_1fr]">
            <!-- Mobile Top Bar with Drawer Trigger -->
            <div class="lg:hidden flex items-center justify-between bg-slate-900 text-white px-4 py-3 border-b border-slate-800 sticky top-0 z-30">
                <div class="flex items-center gap-2">
                    <button type="button" @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg bg-slate-800 text-slate-200 hover:text-white focus:outline-none touch-target" aria-label="Toggle navigation drawer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="edu-display text-lg font-black tracking-tight text-white">Thinker HUB</a>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-teal-900 text-teal-300 border border-teal-700">
                    {{ ucfirst($resolvedSection) }}
                </span>
            </div>

            <!-- Mobile Backdrop -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs lg:hidden" x-transition.opacity></div>

            <!-- Sidebar (Drawer on mobile, fixed/grid column on desktop) -->
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="edu-sidebar text-slate-100 p-6 lg:p-7 fixed inset-y-0 left-0 z-50 w-72 lg:static lg:w-full transition-transform duration-200 ease-in-out overflow-y-auto flex flex-col justify-between"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <a href="{{ route('dashboard') }}" class="edu-display text-2xl font-black tracking-tight">Thinker HUB</a>
                            <p class="mt-1 text-xs uppercase tracking-[0.2em] text-cyan-100/75">Learning Platform</p>
                        </div>
                        <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white" aria-label="Close sidebar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <nav class="mt-8 space-y-2">
                        @foreach ($links as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                class="edu-nav-link min-h-[44px] flex items-center {{ request()->routeIs($link['route']) ? 'is-active' : '' }}"
                            >
                                {{ $link['name'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-8 pt-4 border-t border-slate-700/50">
                    @csrf
                    <button class="w-full min-h-[44px] rounded-lg border border-slate-600/60 bg-slate-900/60 px-3 py-2 text-sm font-semibold text-slate-100 transition hover:border-amber-300/70 hover:bg-amber-500/20 active:scale-98" type="submit">
                        Log out
                    </button>
                </form>
            </aside>

            <main class="edu-content p-4 sm:p-6 lg:p-10 w-full min-w-0 max-w-full overflow-x-hidden">
                <header class="edu-reveal mb-6 rounded-3xl border border-cyan-100 bg-gradient-to-r from-cyan-50 via-white to-amber-50 p-4 sm:p-6 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Thinker HUB Workspace</p>
                    <h1 class="edu-display mt-2 text-xl sm:text-2xl lg:text-3xl font-black tracking-tight text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                    <p class="mt-1 max-w-2xl text-xs sm:text-sm text-slate-600">Plan courses, track learner progress, and keep every resource in one focused platform.</p>
                </header>

                @yield('content')
            </main>
        </div>
    @endif
</body>
</html>
