@php
    $navItems = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Courses', 'route' => 'landing.courses'],
        ['label' => 'Instructors', 'route' => 'landing.instructors'],
        ['label' => 'Contact', 'route' => 'landing.contact'],
    ];
@endphp

<header class="sticky top-0 z-50 bg-[#0a2d27] py-4 shadow-lg">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-white shrink-0">
            <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-8 w-auto">
        </a>

        <nav class="hidden md:flex items-center gap-10 text-[13px] font-semibold uppercase tracking-wider text-slate-300">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ request()->routeIs($item['route']) ? 'text-yellow-400' : 'hover:text-yellow-400 transition-colors' }}"
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden md:flex items-center gap-6">
            <a href="{{ route('login') }}" class="text-sm font-bold text-white hover:text-yellow-400">Login</a>
            <a href="{{ route('enroll') }}" class="rounded-full bg-yellow-400 px-6 py-2.5 text-sm font-bold text-[#0a2d27] hover:bg-white transition-all">Enroll Now</a>
        </div>

        <button class="md:hidden text-white text-2xl" @click="mobileMenu = !mobileMenu" aria-label="Toggle menu">
            <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
        </button>
    </div>

    <div class="md:hidden bg-[#0a2d27] border-t border-white/10" x-show="mobileMenu" x-cloak x-transition>
        <nav class="flex flex-col p-6 gap-4 text-white font-semibold">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ request()->routeIs($item['route']) ? 'text-yellow-400' : '' }}"
                >{{ $item['label'] }}</a>
            @endforeach
            <div class="pt-4 flex gap-4">
                <a href="{{ route('login') }}" class="flex-1 text-center py-3 border border-white/20 rounded-xl">Login</a>
                <a href="{{ route('register') }}" class="flex-1 text-center py-3 bg-yellow-400 text-[#0a2d27] rounded-xl">Join</a>
            </div>
        </nav>
    </div>
</header>
