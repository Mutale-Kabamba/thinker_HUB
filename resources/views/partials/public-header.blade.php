@php
    $navItems = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Courses', 'route' => 'landing.courses'],
        ['label' => 'Instructors', 'route' => 'landing.instructors'],
        ['label' => 'Contact', 'route' => 'landing.contact'],
    ];
@endphp

<header class="hub-public-header sticky top-0 z-50 py-4">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 text-xl font-bold text-slate-900">
            <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
        </a>

        <nav class="hub-public-nav hidden items-center gap-2 text-[13px] font-semibold uppercase tracking-wider text-slate-600 md:flex">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ request()->routeIs($item['route']) ? 'bg-teal-100/80 text-teal-700' : 'hover:text-teal-700 transition-colors' }}"
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden md:flex items-center gap-6">
            <a href="{{ route('login') }}" class="hub-public-auth-link text-sm font-bold text-slate-700 transition-colors">Login</a>
            <a href="{{ route('enroll') }}" class="hub-public-cta inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold transition duration-300 ease-out focus:outline-none focus:ring-4 focus:ring-teal-200/70">Enroll Now</a>
        </div>

        <button class="text-2xl text-slate-700 md:hidden" @click="mobileMenu = !mobileMenu" aria-label="Toggle menu">
            <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
        </button>
    </div>

    <div class="border-t border-slate-200/80 bg-white/95 md:hidden" x-show="mobileMenu" x-cloak x-transition>
        <nav class="hub-public-nav flex flex-col gap-4 p-6 font-semibold text-slate-700">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ request()->routeIs($item['route']) ? 'bg-teal-100/80 text-teal-700' : '' }}"
                >{{ $item['label'] }}</a>
            @endforeach
            <div class="pt-4 flex gap-4">
                <a href="{{ route('login') }}" class="hub-public-auth-link flex-1 border border-slate-200 text-center py-3 text-slate-700 transition-colors">Login</a>
                <a href="{{ route('register') }}" class="hub-public-cta flex-1 text-center py-3 font-bold transition duration-300 ease-out">Join</a>
            </div>
        </nav>
    </div>
</header>
