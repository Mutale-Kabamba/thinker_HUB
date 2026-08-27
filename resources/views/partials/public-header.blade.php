@php
    $navItems = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Courses', 'route' => 'landing.courses'],
        ['label' => 'Hub', 'route' => 'hub.index'],
        ['label' => 'Network', 'route' => 'landing.instructors'],
        ['label' => 'Contact', 'route' => 'landing.contact'],
    ];
@endphp

<header class="hub-public-header sticky top-0 z-50 py-4 bg-white/95 backdrop-blur-md border-b border-slate-200/80">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 text-xl font-bold text-slate-900">
            <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB logo" class="h-9 max-h-9 md:h-10 md:max-h-10 w-auto object-contain dark:hidden">
            <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB logo" class="h-9 max-h-9 md:h-10 md:max-h-10 w-auto object-contain hidden dark:block">
        </a>

        <nav class="hub-public-nav hidden items-center gap-2 text-[13px] font-semibold uppercase tracking-wider text-slate-600 md:flex">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ request()->routeIs($item['route']) ? 'bg-teal-100/80 text-teal-700 px-3 py-1.5 rounded-full font-bold' : 'px-3 py-1.5 hover:text-teal-700 transition-colors' }}"
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden md:flex items-center gap-4">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#11443c] transition-all"
                >
                    <i class="fa-solid fa-gauge"></i> Dashboard ({{ Str::words(auth()->user()->name, 1, '') }})
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-slate-500 hover:text-rose-600 transition-colors">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hub-public-auth-link text-xs font-bold text-slate-700 hover:text-teal-700 transition-colors">Login</a>
                <a href="{{ route('enroll') }}" class="hub-public-cta inline-flex items-center justify-center rounded-full bg-[#0a2d27] px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#11443c] transition duration-300">Enroll Now</a>
            @endauth
        </div>

        <button class="text-2xl text-slate-700 md:hidden p-2.5 rounded-lg hover:bg-slate-100 touch-target active:scale-95" @click="mobileMenu = !mobileMenu" aria-label="Toggle menu">
            <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars-staggered'"></i>
        </button>
    </div>

    <div class="border-t border-slate-200/80 bg-white/98 backdrop-blur-md md:hidden max-h-[calc(100dvh-5rem)] overflow-y-auto safe-pb" x-show="mobileMenu" x-cloak x-transition>
        <nav class="hub-public-nav flex flex-col gap-1.5 p-4 font-semibold text-slate-700">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="min-h-[44px] flex items-center px-4 py-2.5 rounded-xl transition text-sm {{ request()->routeIs($item['route']) ? 'bg-teal-100/80 text-teal-800 font-bold' : 'hover:bg-slate-100' }}"
                >{{ $item['label'] }}</a>
            @endforeach

            @auth
                <div class="pt-3 mt-1 border-t border-slate-100 flex flex-col gap-2">
                    <a href="{{ route('dashboard') }}" class="w-full min-h-[46px] flex items-center justify-center rounded-xl bg-[#0a2d27] py-3 text-center text-xs font-bold text-white shadow-xs active:scale-98">
                        <i class="fa-solid fa-gauge mr-2"></i> Dashboard ({{ auth()->user()->name }})
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full min-h-[44px] rounded-xl border border-slate-200 py-2.5 text-center text-xs font-bold text-rose-600 hover:bg-rose-50 active:scale-98 transition">
                            Log Out
                        </button>
                    </form>
                </div>
            @else
                <div class="pt-3 mt-1 flex gap-3 border-t border-slate-100">
                    <a href="{{ route('login') }}" class="hub-public-auth-link flex-1 min-h-[46px] flex items-center justify-center rounded-xl border border-slate-200 py-2.5 text-center text-xs font-bold text-slate-700 transition active:scale-98">Login</a>
                    <a href="{{ route('register') }}" class="hub-public-cta flex-1 min-h-[46px] flex items-center justify-center rounded-xl py-2.5 text-center text-xs font-bold text-white transition duration-300 ease-out active:scale-98">Join</a>
                </div>
            @endauth
        </nav>
    </div>
</header>
